<?php

namespace App\Services;

use App\Models\M_customers;
use App\Models\M_sales;
use App\Models\SettingsModel;
use App\Models\StoreModel;
use App\Models\ZatcaCertificatesModel;
use Saleh7\Zatca\GeneratorInvoice;
use Saleh7\Zatca\Helpers\Certificate;
use Saleh7\Zatca\InvoiceSigner;
use Saleh7\Zatca\Mappers\InvoiceMapper;

/**
 * ZATCA Invoice Service
 *
 * Generates ZATCA-compliant UBL 2.1 invoices for:
 *  - Standard Tax Invoice (B2B)
 *  - Simplified Tax Invoice (B2C)
 *  - Credit Note
 *  - Debit Note
 *
 * Signs each invoice with the compliance private key using ECDSA-SHA256.
 * Computes SHA256 hash of canonicalized XML as required by ZATCA Phase 2.
 */
class ZatcaInvoiceService
{
    /** @var ZatcaCertificateService */
    protected $certService;
    /** @var ZatcaApiClient */
    protected $apiClient;
    /** @var \Config\Zatca */
    protected $config;

    const DEFAULT_PIH = 'NWZlY2ViNjZmZmM4NmYzOGQ5NTI3ODZjNmQ2OTZjNzljMmRiYzIzOWRkNGU5MWI0NjcyOWQ3M2EyN2ZiNTdlOQ==';

    public function __construct()
    {
        $this->certService = new ZatcaCertificateService();
        $this->apiClient = new ZatcaApiClient();
        $this->config = config('Zatca');
    }

    /**
     * Generate all required compliance test invoices signed with the compliance private key.
     *
     * @param array $certificate  Row from pos_zatca_certificates (must have private_key + binary_security_token)
     * @param array $settings     ZATCA settings (environment/toggles and compatibility fallbacks)
     * @param array $store        Active store profile containing seller VAT/legal identity/address
     * @return array  Keys: standard_invoice, simplified_invoice, credit_note, debit_note,
     *                 simplified_credit_note, simplified_debit_note
     */
    public function generateComplianceTestInvoices(array $certificate, array $settings, array $store = []): array
    {
        $privateKeyPem = $certificate['private_key']; // $this->certService->decryptPrivateKey($certificate['private_key']);
        // NOTE: Certificate token is resolved per invoice type below, not globally
        $sellerVat = trim((string) ($store['zatca_seller_vat_number'] ?? ''));
        if ($sellerVat === '') {
            throw new \RuntimeException('ZATCA seller VAT number is required in store profile.');
        }

        $sellerName = trim((string) ($store['zatca_seller_legal_name'] ?? ''));
        if ($sellerName === '') {
            $sellerName = trim((string) ($store['name'] ?? 'Test Seller'));
        }

        $params = [
            'seller_vat'     => $sellerVat,
            'seller_name'    => $sellerName,
            'seller_address' => $this->normalizeSellerAddressValue((string) ($store['zatca_street_name'] ?? $store['address'] ?? 'Riyadh'), 'Riyadh'),
            'seller_building_number' => $this->normalizeSellerBuildingNumber((string) ($store['zatca_building_number'] ?? '1234')),
            'seller_city_subdivision_name' => $this->normalizeSellerAddressValue((string) ($store['zatca_city_subdivision_name'] ?? 'Al-Murabba'), 'Al-Murabba'),
            'seller_city_name' => $this->normalizeSellerAddressValue((string) ($store['zatca_city_name'] ?? 'Riyadh'), 'Riyadh'),
            'seller_postal_code' => $this->normalizeSellerPostalCode((string) ($store['zatca_postal_code'] ?? '12345')),
            'seller_country' => $this->normalizeSellerAddressValue(strtoupper(trim((string) ($store['zatca_country_code'] ?? 'SA'))), 'SA'),
            'currency'       => 'SAR',
        ];

        return [
            'standard_invoice'   => $this->buildAndSign('standard',  $privateKeyPem, $this->resolveSigningCertificateToken($certificate, $settings, 'standard'), $params, 1),
            'simplified_invoice' => $this->buildAndSign('simplified', $privateKeyPem, $this->resolveSigningCertificateToken($certificate, $settings, 'simplified'), $params, 2),
            'credit_note'        => $this->buildAndSign('credit_note', $privateKeyPem, $this->resolveSigningCertificateToken($certificate, $settings, 'standard'), $params, 3),
            'debit_note'         => $this->buildAndSign('debit_note',  $privateKeyPem, $this->resolveSigningCertificateToken($certificate, $settings, 'standard'), $params, 4),
            'simplified_credit_note' => $this->buildAndSign('simplified_credit_note', $privateKeyPem, $this->resolveSigningCertificateToken($certificate, $settings, 'simplified'), $params, 5),
            'simplified_debit_note'  => $this->buildAndSign('simplified_debit_note',  $privateKeyPem, $this->resolveSigningCertificateToken($certificate, $settings, 'simplified'), $params, 6),
        ];
    }

    /**
     * Generate, sign, and attach ZATCA invoice artifacts to an existing completed sale.
     *
     * @return array{success:bool, skipped?:bool, message:string, sale_id:int, uuid?:string, hash?:string, qr?:string, xml_path?:string, icv?:int, submission_status?:string, submission_success?:bool, submission_message?:string}
     */
    public function generateAndAttachToSale(int $saleId, bool $submitAfterSign = true): array
    {
        $salesModel = new M_sales();
        $settingsModel = new SettingsModel();
        $certModel = new ZatcaCertificatesModel();

        $sale = $salesModel->forStore()->find($saleId);
        if (!$sale) {
            return [
                'success' => false,
                'sale_id' => $saleId,
                'message' => 'Sale not found in current store scope.',
            ];
        }

        $storeId = (int) ($sale['store_id'] ?? 0);
        $store = (new StoreModel())->find($storeId);
        if (!$store) {
            return [
                'success' => false,
                'sale_id' => $saleId,
                'message' => 'Store profile not found.',
            ];
        }
        $settings = $settingsModel->getSettings() ?? [];
        $isEnabled = !empty($settings['einvoicing_enabled']) && strtoupper((string) ($settings['einvoicing_country'] ?? '')) === 'SA';
        if (!$isEnabled) {
            return [
                'success' => true,
                'skipped' => true,
                'sale_id' => $saleId,
                'message' => 'E-invoicing is disabled or non-SA branch.',
            ];
        }

        $environment = (string) ($settings['zatca_environment'] ?? 'sandbox');
        $certificate = $certModel
            ->where('store_id', $storeId)
            ->where('environment', $environment)
            ->whereIn('status', ['production', 'compliance'])
            ->orderBy('id', 'DESC')
            ->first();

        if (!$certificate) {
            return [
                'success' => false,
                'sale_id' => $saleId,
                'message' => 'No ZATCA certificate found for this store/environment.',
            ];
        }

        // Get seller VAT for this sale
        $sellerVat = trim((string) ($sale['seller_vat'] ?? $store['zatca_seller_vat_number'] ?? ''));
        if ($sellerVat === '') {
            return [
                'success' => false,
                'sale_id' => $saleId,
                'message' => 'Seller VAT number is not set.',
            ];
        }

        // Determine invoice flavor EARLY - needed for certificate selection
        $customerVat = '';
        $customerId = (int) ($sale['customer_id'] ?? 0);
        if ($customerId > 0) {
            $customer = (new M_customers())->find($customerId);
            if ($customer) {
                $customerVat = trim((string) ($customer['vat_number'] ?? ''));
            }
        }

        $invoiceFlavor = $this->determineInvoiceFlavor(
            (string) ($sale['zatca_invoice_type'] ?? ($settings['zatca_invoice_type'] ?? 'simplified')),
            $customerVat
        );

        // Verify the certificate is valid for this VAT number
        $certVat = $this->extractVatFromCertificateToken((string) ($certificate['production_binary_security_token'] ?? $certificate['binary_security_token'] ?? ''));
        if ($certVat !== '' && $certVat !== $sellerVat) {
            log_message('warning', "ZATCA Invoice: Certificate VAT ({$certVat}) does not match sale VAT ({$sellerVat}) for sale {$saleId}");
        }

        // Select certificate token based on invoice flavor:
        // - Simplified (B2C) invoices ALWAYS use production certificate (Report endpoint is less strict)
        // - Standard (B2B) invoices use production certificate if available (Clearance endpoint is stricter)
        $rawToken = $this->resolveSigningCertificateToken($certificate, $settings, $invoiceFlavor);
        if ($rawToken === '') {
            return [
                'success' => false,
                'sale_id' => $saleId,
                'message' => 'Missing binary security token in certificate.',
            ];
        }

        $privateKeyPem = (string) ($certificate['private_key'] ?? '');  //$this->certService->decryptPrivateKey((string) ($certificate['private_key'] ?? ''));
        if (trim($privateKeyPem) === '') {
            return [
                'success' => false,
                'sale_id' => $saleId,
                'message' => 'Missing or unreadable private key.',
            ];
        }

        $itemRows = $salesModel->db->table('pos_sale_items si')
            ->select('si.product_id, si.quantity, si.price, si.subtotal, si.discount, si.discount_type, si.is_gift, p.name as product_name')
            ->join('pos_products p', 'p.id = si.product_id', 'left')
            ->where('si.sale_id', $saleId)
            ->get()
            ->getResultArray();

        if (empty($itemRows)) {
            return [
                'success' => false,
                'sale_id' => $saleId,
                'message' => 'Sale has no items to invoice.',
            ];
        }

        $customerVat = '';
        $customerName = '';
        $customerAddress = '';
        $customerRegistrationName = '';
        $customerCrNumber = '';
        $customerStreetName = '';
        $customerBuildingNumber = '';
        $customerCitySubdivisionName = '';
        $customerCityName = '';
        $customerPostalCode = '';
        $customerCountryCode = 'SA';
        $customerId = (int) ($sale['customer_id'] ?? 0);
        if ($customerId > 0) {
            $customer = (new M_customers())->forStore()->find($customerId);
            if ($customer) {
                $customerVat = trim((string) ($customer['vat_number'] ?? ''));
                $customerName = trim((string) ($customer['name'] ?? ''));
                $customerAddress = trim((string) ($customer['address'] ?? ''));
                $customerRegistrationName = trim((string) ($customer['zatca_registration_name'] ?? ''));
                $customerCrNumber = trim((string) ($customer['zatca_cr_number'] ?? ''));
                $customerStreetName = trim((string) ($customer['zatca_street_name'] ?? ''));
                $customerBuildingNumber = trim((string) ($customer['zatca_building_number'] ?? ''));
                $customerCitySubdivisionName = trim((string) ($customer['zatca_city_subdivision_name'] ?? ''));
                $customerCityName = trim((string) ($customer['zatca_city_name'] ?? ''));
                $customerPostalCode = trim((string) ($customer['zatca_postal_code'] ?? ''));
                $customerCountryCode = strtoupper(trim((string) ($customer['zatca_country_code'] ?? 'SA')));
            }
        }

        // invoiceFlavor already determined early for certificate selection
        $typeCode = $invoiceFlavor === 'simplified' ? '388' : '388';
        $subType = $invoiceFlavor === 'simplified' ? '0200000' : '0100000';

        $totals = $this->buildTotalsFromSaleRows($itemRows, (float) ($sale['total_tax'] ?? 0), (float) ($sale['total'] ?? 0));

        $chain = $this->getInvoiceChainContext($storeId, $saleId);
        $icv = $chain['next_icv'];
        $pih = $chain['previous_hash'];

        $issuedAt = $this->normalizeSaleIssuedAt((string) ($sale['created_at'] ?? date('Y-m-d H:i:s')));
        $uuid = $this->newUuid();
        $invoiceNo = trim((string) ($sale['invoice_no'] ?? ''));
        if ($invoiceNo === '') {
            $invoiceNo = 'SAL-' . $saleId;
        }

        $sellerName = trim((string) ($store['zatca_seller_legal_name'] ?? ''));
        if ($sellerName === '') {
            $sellerName = trim((string) ($store['name'] ?? 'Seller'));
        }
        $sellerVat = trim((string) ($store['zatca_seller_vat_number'] ?? ''));
        if ($sellerVat === '') {
            return [
                'success' => false,
                'sale_id' => $saleId,
                'message' => 'ZATCA seller VAT number is missing in store profile.',
            ];
        }
        $sellerAddress = $this->normalizeSellerAddressValue((string) ($store['zatca_street_name'] ?? ''), trim((string) ($store['address'] ?? 'Riyadh')));
        $sellerBuildingNumber = $this->normalizeSellerBuildingNumber((string) ($store['zatca_building_number'] ?? '1234'));
        $sellerCitySubdivision = $this->normalizeSellerAddressValue((string) ($store['zatca_city_subdivision_name'] ?? ''), 'Al-Murabba');
        $sellerCityName = $this->normalizeSellerAddressValue((string) ($store['zatca_city_name'] ?? ''), 'Riyadh');
        $sellerPostalCode = $this->normalizeSellerPostalCode((string) ($store['zatca_postal_code'] ?? '12345'));
        $sellerCountryCode = $this->normalizeSellerAddressValue(strtoupper(trim((string) ($store['zatca_country_code'] ?? 'SA'))), 'SA');

        $xml = $this->buildSaleXml([
            'uuid' => $uuid,
            'invoice_no' => $invoiceNo,
            'issue_date' => $issuedAt['date'],
            'issue_time' => $issuedAt['time'],
            'type_code' => $typeCode,
            'sub_type' => $subType,
            'seller_name' => $sellerName,
            'seller_vat' => $sellerVat,
            'seller_address' => $sellerAddress,
            'seller_building_number' => $sellerBuildingNumber,
            'seller_city_subdivision_name' => $sellerCitySubdivision,
            'seller_city_name' => $sellerCityName,
            'seller_postal_code' => $sellerPostalCode,
            'seller_country' => $sellerCountryCode,
            'buyer_name' => $customerName,
            'buyer_vat' => $customerVat,
            'buyer_address' => $customerAddress,
            'buyer_registration_name' => $customerRegistrationName,
            'buyer_cr_number' => $customerCrNumber,
            'buyer_street_name' => $customerStreetName,
            'buyer_building_number' => $customerBuildingNumber,
            'buyer_city_subdivision_name' => $customerCitySubdivisionName,
            'buyer_city_name' => $customerCityName,
            'buyer_postal_code' => $customerPostalCode,
            'buyer_country' => $customerCountryCode,
            'invoice_flavor' => $invoiceFlavor,
            'currency' => 'SAR',
            'icv' => $icv,
            'pih' => $pih,
            'rows' => $totals['rows'],
            'taxable_total' => $totals['taxable_total'],
            'tax_total' => $totals['tax_total'],
            'payable_total' => $totals['payable_total'],
        ]);

        $signResult = $this->signInvoiceWithLibrary($xml, $rawToken, $privateKeyPem);
        $invoiceHash = $signResult['hash'];
        $qr = $signResult['qr'];
        $signedXml = $signResult['signed_xml'];

        log_message('info', 'ZATCA QR generated (sale): sale_id=' . $saleId . ' qr_b64_len=' . strlen($qr));

        $relativePath = $this->saveSignedInvoiceXml($saleId, $uuid, $signedXml);

        $salesModel->update($saleId, [
            'zatca_uuid' => $uuid,
            'zatca_invoice_hash' => $invoiceHash,
            'zatca_previous_invoice_hash' => $pih,
            'zatca_icv' => $icv,
            'zatca_qr_code' => $qr,
            'zatca_xml_path' => $relativePath,
            'zatca_status' => 'signed',
            'zatca_response' => null,
            'zatca_submitted_at' => null,
        ]);

        if (!$submitAfterSign) {
            return [
                'success' => true,
                'sale_id' => $saleId,
                'uuid' => $uuid,
                'hash' => $invoiceHash,
                'qr' => $qr,
                'xml_path' => $relativePath,
                'icv' => $icv,
                'submission_success' => false,
                'submission_status' => 'signed',
                'submission_message' => 'Invoice signed successfully.',
                'submission_response' => null,
                'message' => 'ZATCA invoice signed and attached to sale.',
            ];
        }

        $submissionResult = $this->submitSaleToZatca($saleId);

        return [
            'success' => true,
            'sale_id' => $saleId,
            'uuid' => $uuid,
            'hash' => $invoiceHash,
            'qr' => $qr,
            'xml_path' => $relativePath,
            'icv' => $icv,
            'submission_success' => (bool) ($submissionResult['success'] ?? false),
            'submission_status' => (string) ($submissionResult['status'] ?? 'pending'),
            'submission_message' => (string) ($submissionResult['message'] ?? 'ZATCA submission pending.'),
            'submission_response' => $submissionResult['response'] ?? null,
            'message' => 'ZATCA invoice generated and attached to sale.',
        ];
    }

    /**
     * Submit an existing signed sale invoice XML to ZATCA.
     */
    public function submitSaleToZatca(int $saleId): array
    {
        $salesModel = new M_sales();
        $settingsModel = new SettingsModel();
        $certModel = new ZatcaCertificatesModel();

        $sale = $salesModel->forStore()->find($saleId);
        if (!$sale) {
            return [
                'success' => false,
                'status' => 'pending',
                'message' => 'Sale not found in current store scope.',
            ];
        }

        $settings = $settingsModel->getSettings() ?? [];
        $isEnabled = !empty($settings['einvoicing_enabled']) && strtoupper((string) ($settings['einvoicing_country'] ?? '')) === 'SA';
        if (!$isEnabled) {
            return [
                'success' => true,
                'status' => 'skipped',
                'message' => 'E-invoicing is disabled or non-SA branch.',
            ];
        }

        if (!empty($sale['zatca_status']) && in_array((string) $sale['zatca_status'], ['reported', 'cleared'], true)) {
            return [
                'success' => true,
                'status' => (string) $sale['zatca_status'],
                'message' => 'Invoice already submitted to ZATCA.',
            ];
        }

        $storeId = (int) ($sale['store_id'] ?? 0);
        $store = (new StoreModel())->find($storeId);
        if (!$store) {
            return [
                'success' => false,
                'status' => 'pending',
                'message' => 'Store profile not found.',
            ];
        }
        $environment = (string) ($settings['zatca_environment'] ?? 'sandbox');
        $certificate = $certModel
            ->where('store_id', $storeId)
            ->where('environment', $environment)
            ->whereIn('status', ['production', 'compliance'])
            ->orderBy('id', 'DESC')
            ->first();

        if (!$certificate) {
            return [
                'success' => false,
                'status' => 'pending',
                'message' => 'No ZATCA certificate found for this store/environment.',
            ];
        }

        // Always re-sign before submitting to ensure the embedded certificate matches the
        // current production CSID used for HTTP auth. Stale XMLs signed with an old/compliance
        // cert would cause certificate-hashing and signed-properties-hashing errors.
        $reSignResult = $this->generateAndAttachToSale($saleId, false);
        if (!($reSignResult['success'] ?? false)) {
            return [
                'success' => false,
                'status' => 'pending',
                'message' => 'Failed to re-sign invoice before submission: ' . ($reSignResult['message'] ?? 'Unknown error'),
            ];
        }

        // Reload sale to pick up fresh hash/uuid/xml_path written by generateAndAttachToSale
        $sale = $salesModel->forStore()->find($saleId);

        $xmlPath = trim((string) ($sale['zatca_xml_path'] ?? ''));
        if ($xmlPath === '') {
            return [
                'success' => false,
                'status' => 'pending',
                'message' => 'Signed invoice XML is missing for this sale.',
            ];
        }

        $absolutePath = $this->resolveXmlPath($xmlPath);
        if ($absolutePath === '' || !is_file($absolutePath)) {
            return [
                'success' => false,
                'status' => 'pending',
                'message' => 'Signed invoice XML file not found on disk.',
            ];
        }

        $signedXml = (string) file_get_contents($absolutePath);
        if ($signedXml === '') {
            return [
                'success' => false,
                'status' => 'pending',
                'message' => 'Signed invoice XML file is empty.',
            ];
        }
        $invoicePayload = $this->prepareInvoicePayloadForApi($signedXml);

        //$invoiceHash = trim((string) ($sale['zatca_invoice_hash'] ?? ''));

        // Create new invoice hash from the re-signed XML to ensure it matches the signed payload
        $invoiceHash = $this->computeHash($signedXml);
        $uuid = trim((string) ($sale['zatca_uuid'] ?? ''));
        if ($invoiceHash === '' || $uuid === '') {
            return [
                'success' => false,
                'status' => 'pending',
                'message' => 'Missing invoice hash or UUID.',
            ];
        }

        $customerVat = '';
        $customerId = (int) ($sale['customer_id'] ?? 0);
        if ($customerId > 0) {
            $customer = (new M_customers())->forStore()->find($customerId);
            $customerVat = trim((string) ($customer['vat_number'] ?? ''));
        }

        $invoiceFlavor = $this->determineInvoiceFlavor(
            (string) ($sale['zatca_invoice_type'] ?? ($settings['zatca_invoice_type'] ?? 'simplified')),
            $customerVat
        );
        $configuredSellerVat = trim((string) ($store['zatca_seller_vat_number'] ?? ''));

        // Both simplified and standard invoices authenticate with the production CSID in production.
        // The compliance CSID is only used during onboarding (Steps 2-3).
        $signingCertToken = (string) ($certificate['binary_security_token'] ?? '');
        $submissionToken  = (string) ($certificate['production_binary_security_token'] ?? '');
        if ($submissionToken === '') {
            return [
                'success' => false,
                'status' => 'pending',
                'message' => 'Production CSID is missing. Complete ZATCA onboarding Step 4 before invoice submission.',
            ];
        }

        $submissionSecret = (string) ($certificate['production_secret'] ?? '');
        if ($submissionSecret === '') {
            return [
                'success' => false,
                'status' => 'pending',
                'message' => 'Production CSID secret is missing. Re-run onboarding Step 4 to refresh production credentials.',
            ];
        }

        if ($signingCertToken !== '' && $configuredSellerVat !== '') {
            $signingCertVat = $this->extractVatFromCertificateToken($signingCertToken);
            if ($signingCertVat !== '' && $signingCertVat !== $configuredSellerVat) {
                return [
                    'success' => false,
                    'status' => 'pending',
                    'message' => 'Signing certificate VAT does not match configured seller VAT. Update the store VAT profile or regenerate compliance CSID for VAT ' . $configuredSellerVat . '.',
                ];
            }
        }

        if ($configuredSellerVat !== '') {
            $submissionCertVat = $this->extractVatFromCertificateToken($submissionToken);
            if ($submissionCertVat !== '' && $submissionCertVat !== $configuredSellerVat) {
                return [
                    'success' => false,
                    'status' => 'pending',
                    'message' => 'Production CSID VAT mismatch. Expected ' . $configuredSellerVat . ' but got ' . $submissionCertVat . '. Re-run onboarding Step 4 with the correct VAT.',
                ];
            }
        }

        try {
            log_message('info', "ZATCA Invoice Submission (sale={$saleId}, flavor={$invoiceFlavor}): Submitting to " . ($invoiceFlavor === 'simplified' ? 'Report' : 'Clearance') . " endpoint with production CSID.");

            if ($invoiceFlavor === 'standard') {
                $response = $this->apiClient->clearInvoice($invoiceHash, $uuid, $invoicePayload, $submissionToken, $submissionSecret);
                $finalStatus = 'cleared';
            } else {
                $response = $this->apiClient->reportInvoice($invoiceHash, $uuid, $invoicePayload, $submissionToken, $submissionSecret);
                $finalStatus = 'reported';
            }

            $evaluation = $this->evaluateSubmissionResponse($response, $invoiceFlavor);
            if ($evaluation['success']) {
                $salesModel->update($saleId, [
                    'zatca_status' => $evaluation['status'],
                    'zatca_response' => json_encode([
                        'status' => $evaluation['status'],
                        'invoice_flavor' => $invoiceFlavor,
                        'submitted_at' => date('c'),
                        'response' => $response,
                    ]),
                    'zatca_submitted_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            } else {
                $salesModel->update($saleId, [
                    'zatca_status' => 'signed',
                    'zatca_response' => json_encode([
                        'status' => 'signed',
                        'invoice_flavor' => $invoiceFlavor,
                        'submitted_at' => date('c'),
                        'response' => $response,
                    ]),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }

            return array_merge([
                'success' => $evaluation['success'],
                'status' => $evaluation['status'],
                'message' => $evaluation['message'],
                'response' => $response,
                'has_warnings' => $evaluation['has_warnings'],
                'has_errors' => $evaluation['has_errors'],
                'flash_type' => $evaluation['flash_type'],
            ], $evaluation['details']);
        } catch (\Throwable $e) {
            $salesModel->update($saleId, [
                'zatca_status' => 'signed',
                'zatca_response' => json_encode([
                    'status' => 'signed',
                    'invoice_flavor' => $invoiceFlavor,
                    'submitted_at' => date('c'),
                    'error' => $e->getMessage(),
                ]),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            return [
                'success' => false,
                'status' => 'signed',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate and submit a ZATCA credit note for returned sale items.
     *
     * @param int   $saleId
     * @param array $returnedLines Each item must include: name, quantity, unit_price, line_subtotal, line_tax, line_total
     * @param string $reason
     * @param string|null $issuedAt
     * @return array{success:bool,status:string,message:string,uuid?:string,hash?:string,xml_path?:string,response?:array,has_warnings?:bool,has_errors?:bool}
     */
    public function generateAndSubmitCreditNoteForSaleReturn(int $saleId, array $returnedLines, string $reason = '', $issuedAt = null): array
    {
        $salesModel = new M_sales();
        $settingsModel = new SettingsModel();
        $certModel = new ZatcaCertificatesModel();

        $sale = $salesModel->forStore()->find($saleId);
        if (!$sale) {
            return [
                'success' => false,
                'status' => 'pending',
                'message' => 'Sale not found in current store scope.',
            ];
        }

        if (empty($returnedLines)) {
            return [
                'success' => false,
                'status' => 'pending',
                'message' => 'No returned items found to generate a credit note.',
            ];
        }

        $settings = $settingsModel->getSettings() ?? [];
        $isEnabled = !empty($settings['einvoicing_enabled']) && strtoupper((string) ($settings['einvoicing_country'] ?? '')) === 'SA';
        if (!$isEnabled) {
            return [
                'success' => true,
                'status' => 'skipped',
                'message' => 'E-invoicing is disabled or non-SA branch.',
            ];
        }

        $storeId = (int) ($sale['store_id'] ?? 0);
        $store = (new StoreModel())->find($storeId);
        if (!$store) {
            return [
                'success' => false,
                'status' => 'pending',
                'message' => 'Store profile not found.',
            ];
        }

        $environment = (string) ($settings['zatca_environment'] ?? 'sandbox');
        $certificate = $certModel
            ->where('store_id', $storeId)
            ->where('environment', $environment)
            ->whereIn('status', ['production', 'compliance'])
            ->orderBy('id', 'DESC')
            ->first();

        if (!$certificate) {
            return [
                'success' => false,
                'status' => 'pending',
                'message' => 'No ZATCA certificate found for this store/environment.',
            ];
        }

        $customerVat = '';
        $customerName = '';
        $customerAddress = '';
        $customerRegistrationName = '';
        $customerCrNumber = '';
        $customerStreetName = '';
        $customerBuildingNumber = '';
        $customerCitySubdivisionName = '';
        $customerCityName = '';
        $customerPostalCode = '';
        $customerCountryCode = 'SA';

        $customerId = (int) ($sale['customer_id'] ?? 0);
        if ($customerId > 0) {
            $customer = (new M_customers())->forStore()->find($customerId);
            if ($customer) {
                $customerVat = trim((string) ($customer['vat_number'] ?? ''));
                $customerName = trim((string) ($customer['name'] ?? ''));
                $customerAddress = trim((string) ($customer['address'] ?? ''));
                $customerRegistrationName = trim((string) ($customer['zatca_registration_name'] ?? ''));
                $customerCrNumber = trim((string) ($customer['zatca_cr_number'] ?? ''));
                $customerStreetName = trim((string) ($customer['zatca_street_name'] ?? ''));
                $customerBuildingNumber = trim((string) ($customer['zatca_building_number'] ?? ''));
                $customerCitySubdivisionName = trim((string) ($customer['zatca_city_subdivision_name'] ?? ''));
                $customerCityName = trim((string) ($customer['zatca_city_name'] ?? ''));
                $customerPostalCode = trim((string) ($customer['zatca_postal_code'] ?? ''));
                $customerCountryCode = strtoupper(trim((string) ($customer['zatca_country_code'] ?? 'SA')));
            }
        }

        $invoiceFlavor = $this->determineInvoiceFlavor(
            (string) ($sale['zatca_invoice_type'] ?? ($settings['zatca_invoice_type'] ?? 'simplified')),
            $customerVat
        );

        $submissionToken  = (string) ($certificate['production_binary_security_token'] ?? '');
        $submissionSecret = (string) ($certificate['production_secret'] ?? '');
        if ($submissionToken === '' || $submissionSecret === '') {
            return [
                'success' => false,
                'status' => 'pending',
                'message' => 'Production CSID credentials are missing. Complete onboarding Step 4 before issuing credit notes.',
            ];
        }

        $signingToken = $submissionToken;
        $privateKeyPem = (string) ($certificate['private_key'] ?? '');
        if (trim($privateKeyPem) === '') {
            return [
                'success' => false,
                'status' => 'pending',
                'message' => 'Missing or unreadable private key.',
            ];
        }

        $sellerName = trim((string) ($store['zatca_seller_legal_name'] ?? ''));
        if ($sellerName === '') {
            $sellerName = trim((string) ($store['name'] ?? 'Seller'));
        }
        $sellerVat = trim((string) ($store['zatca_seller_vat_number'] ?? ''));
        if ($sellerVat === '') {
            return [
                'success' => false,
                'status' => 'pending',
                'message' => 'ZATCA seller VAT number is missing in store profile.',
            ];
        }

        $totalsRows = [];
        $taxableTotal = 0.0;
        $taxTotal = 0.0;
        $payableTotal = 0.0;
        foreach ($returnedLines as $line) {
            $qty = max(0.0, (float) ($line['quantity'] ?? 0));
            if ($qty <= 0) {
                continue;
            }

            $lineSubtotal = max(0.0, (float) ($line['line_subtotal'] ?? 0));
            $lineTax = max(0.0, (float) ($line['line_tax'] ?? 0));
            $lineTotal = max(0.0, (float) ($line['line_total'] ?? ($lineSubtotal + $lineTax)));
            $unitPrice = max(0.0, (float) ($line['unit_price'] ?? ($qty > 0 ? ($lineSubtotal / $qty) : 0)));

            $taxableTotal += $lineSubtotal;
            $taxTotal += $lineTax;
            $payableTotal += $lineTotal;

            $totalsRows[] = [
                'name' => trim((string) ($line['name'] ?? 'Returned Item')),
                'qty' => $qty,
                'unit_price' => $unitPrice,
                'line_subtotal' => round($lineSubtotal, 2),
                'line_tax' => round($lineTax, 2),
                'line_total' => round($lineTotal, 2),
            ];
        }

        if (empty($totalsRows)) {
            return [
                'success' => false,
                'status' => 'pending',
                'message' => 'Returned items were invalid for credit note generation.',
            ];
        }

        $issuedTimestamp = $issuedAt ?? date('Y-m-d H:i:s');
        $issued = $this->normalizeSaleIssuedAt($issuedTimestamp);

        $chain = $this->getInvoiceChainContext($storeId, 0);
        $icv = (int) ($chain['next_icv'] ?? 1);
        $pih = (string) ($chain['previous_hash'] ?? self::DEFAULT_PIH);

        $creditUuid = $this->newUuid();
        $creditInvoiceNo = 'CRN-' . (string) ($sale['invoice_no'] ?? ('SALE-' . $saleId)) . '-' . gmdate('YmdHis');
        $subType = $invoiceFlavor === 'simplified' ? '0200000' : '0100000';

        $xml = $this->buildSaleXml([
            'uuid' => $creditUuid,
            'invoice_no' => $creditInvoiceNo,
            'issue_date' => $issued['date'],
            'issue_time' => $issued['time'],
            'type_code' => '381',
            'sub_type' => $subType,
            'seller_name' => $sellerName,
            'seller_vat' => $sellerVat,
            'seller_address' => $this->normalizeSellerAddressValue((string) ($store['zatca_street_name'] ?? ''), trim((string) ($store['address'] ?? 'Riyadh'))),
            'seller_building_number' => $this->normalizeSellerBuildingNumber((string) ($store['zatca_building_number'] ?? '1234')),
            'seller_city_subdivision_name' => $this->normalizeSellerAddressValue((string) ($store['zatca_city_subdivision_name'] ?? ''), 'Al-Murabba'),
            'seller_city_name' => $this->normalizeSellerAddressValue((string) ($store['zatca_city_name'] ?? ''), 'Riyadh'),
            'seller_postal_code' => $this->normalizeSellerPostalCode((string) ($store['zatca_postal_code'] ?? '12345')),
            'seller_country' => $this->normalizeSellerAddressValue(strtoupper(trim((string) ($store['zatca_country_code'] ?? 'SA'))), 'SA'),
            'buyer_name' => $customerName,
            'buyer_vat' => $customerVat,
            'buyer_address' => $customerAddress,
            'buyer_registration_name' => $customerRegistrationName,
            'buyer_cr_number' => $customerCrNumber,
            'buyer_street_name' => $customerStreetName,
            'buyer_building_number' => $customerBuildingNumber,
            'buyer_city_subdivision_name' => $customerCitySubdivisionName,
            'buyer_city_name' => $customerCityName,
            'buyer_postal_code' => $customerPostalCode,
            'buyer_country' => $customerCountryCode,
            'invoice_flavor' => $invoiceFlavor,
            'currency' => 'SAR',
            'icv' => $icv,
            'pih' => $pih,
            'rows' => $totalsRows,
            'taxable_total' => round($taxableTotal, 2),
            'tax_total' => round($taxTotal, 2),
            'payable_total' => round($payableTotal, 2),
            'adjustment_reason' => trim($reason) !== '' ? trim($reason) : 'Sales return',
            'reference_invoice_no' => trim((string) ($sale['invoice_no'] ?? ('SALE-' . $saleId))),
        ]);

        $signResult = $this->signInvoiceWithLibrary($xml, $signingToken, $privateKeyPem);
        $signedXml = $signResult['signed_xml'];
        $invoiceHash = $this->computeHash($signedXml);
        $invoicePayload = $this->prepareInvoicePayloadForApi($signedXml);
        $xmlPath = $this->saveSignedInvoiceXml($saleId, $creditUuid, $signedXml);

        try {
            if ($invoiceFlavor === 'standard') {
                $response = $this->apiClient->clearInvoice($invoiceHash, $creditUuid, $invoicePayload, $submissionToken, $submissionSecret);
            } else {
                $response = $this->apiClient->reportInvoice($invoiceHash, $creditUuid, $invoicePayload, $submissionToken, $submissionSecret);
            }

            $evaluation = $this->evaluateSubmissionResponse($response, $invoiceFlavor);

            return array_merge([
                'success' => $evaluation['success'],
                'status' => (string) ($evaluation['status'] ?? 'pending'),
                'message' => (string) ($evaluation['message'] ?? 'Credit note submission completed.'),
                'uuid' => $creditUuid,
                'hash' => $invoiceHash,
                'xml_path' => $xmlPath,
                'response' => $response,
                'has_warnings' => (bool) ($evaluation['has_warnings'] ?? false),
                'has_errors' => (bool) ($evaluation['has_errors'] ?? false),
            ], $evaluation['details'] ?? []);
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'status' => 'pending',
                'message' => 'Credit note submission failed: ' . $e->getMessage(),
                'uuid' => $creditUuid,
                'hash' => $invoiceHash,
                'xml_path' => $xmlPath,
            ];
        }
    }

    protected function evaluateSubmissionResponse(array $response, string $invoiceFlavor): array
    {
        $validation = is_array($response['validationResults'] ?? null) ? $response['validationResults'] : [];
        $errorMessages = $this->normalizeValidationMessages($validation['errorMessages'] ?? []);
        $warningMessages = $this->normalizeValidationMessages($validation['warningMessages'] ?? []);
        $infoMessages = $this->normalizeValidationMessages($validation['infoMessages'] ?? []);
        $hasErrors = !empty($errorMessages);
        $hasWarnings = !empty($warningMessages);
        $status = strtoupper((string) ($response['clearanceStatus'] ?? $response['reportingStatus'] ?? $response['status'] ?? ''));
        if ($status === '') {
            $status = strtoupper((string) ($validation['status'] ?? ''));
        }

        $normalizedStatus = $invoiceFlavor === 'standard' ? 'cleared' : 'reported';
        if ($status === 'CLEARED') {
            $normalizedStatus = 'cleared';
        } elseif ($status === 'REPORTED') {
            $normalizedStatus = 'reported';
        } elseif ($status === 'WARNING') {
            $normalizedStatus = $invoiceFlavor === 'standard' ? 'cleared' : 'reported';
        }

        $details = [
            'error_messages' => $errorMessages,
            'warning_messages' => $warningMessages,
            'info_messages' => $infoMessages,
        ];

        if ($hasErrors) {
            return [
                'success' => false,
                'status' => 'signed',
                'message' => $this->buildValidationMessage($errorMessages, $warningMessages, $infoMessages),
                'has_warnings' => $hasWarnings,
                'has_errors' => true,
                'flash_type' => 'error',
                'details' => $details,
            ];
        }

        $message = 'Invoice submitted to ZATCA successfully.';
        if ($hasWarnings) {
            $message = 'Invoice submitted to ZATCA successfully with warnings: ' . $this->buildValidationMessage([], $warningMessages, $infoMessages);
        }

        return [
            'success' => true,
            'status' => $normalizedStatus,
            'message' => $message,
            'has_warnings' => $hasWarnings,
            'has_errors' => false,
            'flash_type' => $hasWarnings ? 'warning' : 'success',
            'details' => $details,
        ];
    }

    protected function normalizeValidationMessages($messages): array
    {
        if (!is_array($messages)) {
            return [];
        }

        $normalized = [];
        foreach ($messages as $message) {
            if (!is_array($message)) {
                $messageText = trim((string) $message);
                if ($messageText !== '') {
                    $normalized[] = $messageText;
                }
                continue;
            }

            $messageText = trim((string) ($message['message'] ?? $message['code'] ?? ''));
            if ($messageText !== '') {
                $normalized[] = $messageText;
            }
        }

        return $normalized;
    }

    protected function buildValidationMessage(array $errorMessages, array $warningMessages, array $infoMessages): string
    {
        $parts = [];
        if (!empty($errorMessages)) {
            $parts[] = 'Errors: ' . implode(' | ', $errorMessages);
        }
        if (!empty($warningMessages)) {
            $parts[] = 'Warnings: ' . implode(' | ', $warningMessages);
        }
        if (!empty($infoMessages)) {
            $parts[] = 'Info: ' . implode(' | ', $infoMessages);
        }

        return trim(implode(' ', $parts));
    }

    /**
     * Validate a signed invoice XML against ZATCA compliance rules.
     * Submits the invoice to ZATCA for validation without persisting submission status.
     *
     * @param string $invoicePayload  Base64-encoded signed invoice XML
     * @param array  $certificate     ZATCA certificate row
     * @param array  $settings        ZATCA settings
     * @param array  $sale           Sale row (for context)
     * @return array Validation results with errors, warnings, info messages
     */
    public function validateInvoiceXml(string $invoicePayload, array $certificate, array $settings, array $sale): array
    {
        try {
            // Determine invoice flavor
            $customerVat = '';
            $customerId = (int) ($sale['customer_id'] ?? 0);
            if ($customerId > 0) {
                $customer = (new M_customers())->forStore()->find($customerId);
                if ($customer) {
                    $customerVat = trim((string) ($customer['vat_number'] ?? ''));
                }
            }

            $invoiceFlavor = $this->determineInvoiceFlavor(
                (string) ($sale['zatca_invoice_type'] ?? ($settings['zatca_invoice_type'] ?? 'simplified')),
                $customerVat
            );

            // CRITICAL: Compliance check endpoint requires Compliance CSID (binarySecurityToken + secret).
            // Per ZATCA API docs, /compliance/invoices uses Authorization: Basic(binarySecurityToken:secret)
            $submissionToken  = (string) ($certificate['binary_security_token'] ?? '');
            $submissionSecret = (string) ($certificate['secret'] ?? '');

            if ($submissionToken === '' || $submissionSecret === '') {
                log_message('error', "ZATCA Validation Debug: certificate keys = " . json_encode(array_keys($certificate)));
                log_message('error', "ZATCA Validation Debug: binary_security_token present = " . (isset($certificate['binary_security_token']) ? 'yes' : 'no'));
                log_message('error', "ZATCA Validation Debug: secret present = " . (isset($certificate['secret']) ? 'yes' : 'no'));
                return [
                    'success' => false,
                    'has_errors' => true,
                    'has_warnings' => false,
                    'error_messages' => [
                        'Compliance CSID credentials are missing. Complete ZATCA onboarding Step 2 to obtain Compliance Certificate before validation.'
                    ],
                    'warning_messages' => [],
                    'info_messages' => [],
                ];
            }

            log_message('info', "ZATCA Invoice Validation (sale={$sale['id']}, flavor={$invoiceFlavor}): Submitting to Compliance endpoint with Compliance CSID" .
                " (token_len=" . strlen($submissionToken) . ", secret_len=" . strlen($submissionSecret) . ")");

            // $invoiceHash = trim((string) ($sale['zatca_invoice_hash'] ?? ''));
            // if ($invoiceHash === '') {

            // Create new invoice hash
            $rawInvoiceXml = $invoicePayload;
            $trimmedPayload = trim($invoicePayload);
            if ($trimmedPayload !== '') {
                if (strpos($trimmedPayload, '<') === 0) {
                    $rawInvoiceXml = $trimmedPayload;
                } else {
                    $decoded = base64_decode($trimmedPayload, true);
                    if ($decoded !== false && strpos(ltrim($decoded), '<') === 0) {
                        $rawInvoiceXml = $decoded;
                    }
                }
            }

            $invoiceHash = $this->computeHash($rawInvoiceXml);
            //}

            $uuid = trim((string) ($sale['zatca_uuid'] ?? ''));
            if ($uuid === '') {
                $uuid = $this->newUuid();
            }
            // Submit to compliance endpoint for validation only (no persistence of submission status)
            $response = $this->apiClient->submitComplianceInvoice(
                $invoiceHash,
                $uuid,
                $invoicePayload,
                $submissionToken,
                $submissionSecret
            );

            // Evaluate validation response (similar to submission evaluation)
            $evaluation = $this->evaluateSubmissionResponse($response, $invoiceFlavor);

            return [
                'success' => true,
                'has_errors' => (bool) ($evaluation['has_errors'] ?? false),
                'has_warnings' => (bool) ($evaluation['has_warnings'] ?? false),
                'error_messages' => $evaluation['details']['error_messages'] ?? [],
                'warning_messages' => $evaluation['details']['warning_messages'] ?? [],
                'info_messages' => $evaluation['details']['info_messages'] ?? [],
                'status' => $evaluation['status'] ?? 'unknown',
                'message' => $evaluation['message'] ?? 'Validation completed.',
                'full_response' => $response,
            ];
        } catch (\Throwable $e) {
            log_message('error', 'ZATCA validation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'has_errors' => true,
                'has_warnings' => false,
                'error_messages' => ['Validation failed: ' . $e->getMessage()],
                'warning_messages' => [],
                'info_messages' => [],
            ];
        }
    }

    protected function resolveSigningCertificateToken(array $certificate, array $settings, string $invoiceFlavor = 'simplified'): string
    {
        // CRITICAL: Simplified (B2C) invoices MUST use production certificate (Report endpoint)
        // Standard (B2B) invoices MUST use production certificate (Clearance endpoint)
        // Mixing these causes certificate-hashing and signed-properties-hashing errors

        if ($invoiceFlavor === 'simplified') {
            // Simplified invoices ALWAYS use production certificate for Report endpoint
            $productionToken = trim((string) ($certificate['production_binary_security_token'] ?? ''));
            if ($productionToken !== '') {
                return $productionToken;
            }
            // Fallback to compliance only if production not available
            return trim((string) ($certificate['binary_security_token'] ?? ''));
        }

        if ($invoiceFlavor === 'standard' || $invoiceFlavor === 'credit_note' || $invoiceFlavor === 'debit_note') {
            // Standard/Credit/Debit invoices use production certificate for Clearance endpoint
            $productionToken = trim((string) ($certificate['production_binary_security_token'] ?? ''));
            if ($productionToken !== '') {
                return $productionToken;
            }
            // Fallback to compliance only if production not available
            return trim((string) ($certificate['binary_security_token'] ?? ''));
        }

        // Standard/Credit/Debit invoices use production certificate for Clearance endpoint
        $environment = strtolower(trim((string) ($settings['zatca_environment'] ?? 'sandbox')));
        if ($environment === 'production') {
            $productionToken = trim((string) ($certificate['production_binary_security_token'] ?? ''));
            if ($productionToken !== '') {
                return $productionToken;
            }
        }

        // Fallback to compliance if production not available
        $complianceToken = trim((string) ($certificate['binary_security_token'] ?? ''));
        if ($complianceToken !== '') {
            return $complianceToken;
        }

        return trim((string) ($certificate['production_binary_security_token'] ?? ''));
    }

    protected function resolveXmlPath(string $relativePath): string
    {
        $relativePath = trim($relativePath);
        if ($relativePath === '') {
            return '';
        }

        if (preg_match('#^(?:/|[A-Za-z]:\\\\)#', $relativePath)) {
            return $relativePath;
        }

        $relativePath = ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath), DIRECTORY_SEPARATOR);
        if (stripos($relativePath, 'writable' . DIRECTORY_SEPARATOR) === 0) {
            $relativePath = substr($relativePath, strlen('writable' . DIRECTORY_SEPARATOR));
        }

        return WRITEPATH . $relativePath;
    }

    protected function buildAndSign(string $type, string $privateKey, string $rawToken, array $p, int $icv = 1): array
    {
        $uuid = $this->newUuid();
        $invNum = strtoupper(substr($type, 0, 3)) . '-' . gmdate('Ymd') . '-' . rand(100, 999);
        $date = gmdate('Y-m-d');
        $time = gmdate('H:i:s');

        $xml = $this->buildXml($type, $uuid, $invNum, $date, $time, $p, $icv);
        $signResult = $this->signInvoiceWithLibrary($xml, $rawToken, $privateKey);

        log_message('info', 'ZATCA QR generated (compliance): type=' . $type . ' qr_b64_len=' . strlen($signResult['qr']));

        return [
            'uuid' => $uuid,
            'hash' => $signResult['hash'],
            'xml' => base64_encode($signResult['signed_xml']),
        ];
    }

    protected function signInvoiceWithLibrary(string $unsignedXml, string $rawToken, string $privateKeyPem): array
    {
        try {
            $certificate = new Certificate(
                $this->normalizeCertificateForSigner($rawToken),
                $this->certService->normalizePrivateKeyPem($privateKeyPem),
                ''
            );

            $signer = InvoiceSigner::signInvoice($unsignedXml, $certificate);

            return [
                'signed_xml' => $signer->getInvoice(),
                'hash' => $signer->getHash(),
                'qr' => $signer->getQRCode(),
            ];
        } catch (\Throwable $e) {
            throw new \RuntimeException('Unable to sign ZATCA invoice using php-zatca-xml: ' . $e->getMessage());
        }
    }

    protected function normalizeCertificateForSigner(string $rawToken): string
    {
        $token = trim($rawToken);
        if ($token === '') {
            return '';
        }

        // InvoiceSigner writes this value inside ds:X509Certificate, so it must be
        // base64 text (XML-safe), not raw DER binary.
        $normalized = $this->normalizeCertificateTokenBase64($token);
        if ($normalized !== '') {
            return $normalized;
        }

        return $token;
    }

    // -------------------------------------------------------------------------
    // UBL 2.1 XML builders
    // -------------------------------------------------------------------------

    // -------------------------------------------------------------------------
    // UBL 2.1 XML builders — fully aligned with official ZATCA SDK samples
    // -------------------------------------------------------------------------

    protected function buildXml(string $type, string $uuid, string $invNum, string $date, string $time, array $p, int $icv = 1): string
    {
        $invoiceFlavor = 'standard';
        $typeCode = '388';
        $subType = '0100000';
        $lineAmount = 100.00;
        $taxAmount = 15.00;
        $payableTotal = 115.00;
        $buyerName = trim((string) ($p['buyer_name'] ?? ''));
        $buyerVat = trim((string) ($p['buyer_vat'] ?? ''));
        $buyerAddress = trim((string) ($p['buyer_address'] ?? ''));
        $buyerRegistrationName = trim((string) ($p['buyer_registration_name'] ?? ''));
        $buyerCrNumber = trim((string) ($p['buyer_cr_number'] ?? ''));
        $buyerStreetName = trim((string) ($p['buyer_street_name'] ?? ''));
        $buyerBuildingNumber = trim((string) ($p['buyer_building_number'] ?? ''));
        $buyerCitySubdivisionName = trim((string) ($p['buyer_city_subdivision_name'] ?? ''));
        $buyerCityName = trim((string) ($p['buyer_city_name'] ?? ''));
        $buyerPostalCode = trim((string) ($p['buyer_postal_code'] ?? ''));
        $buyerCountry = trim((string) ($p['buyer_country'] ?? 'SA'));

        if ($type === 'simplified') {
            $invoiceFlavor = 'simplified';
            $subType = '0200000';
            $lineAmount = 100.00;
            $taxAmount = 15.00;
            $payableTotal = 115.00;
            $buyerVat = '';
            $buyerName = '';
            $buyerAddress = '';
            $buyerRegistrationName = '';
            $buyerCrNumber = '';
            $buyerStreetName = '';
            $buyerBuildingNumber = '';
            $buyerCitySubdivisionName = '';
            $buyerCityName = '';
            $buyerPostalCode = '';
            $buyerCountry = 'SA';
        } elseif ($type === 'simplified_credit_note') {
            $invoiceFlavor = 'simplified';
            $typeCode = '381';
            $subType = '0200000';
            $lineAmount = 4.00;
            $taxAmount = 0.60;
            $payableTotal = 4.60;
            $buyerVat = '';
            $buyerName = '';
            $buyerAddress = '';
            $buyerRegistrationName = '';
            $buyerCrNumber = '';
            $buyerStreetName = '';
            $buyerBuildingNumber = '';
            $buyerCitySubdivisionName = '';
            $buyerCityName = '';
            $buyerPostalCode = '';
            $buyerCountry = 'SA';
        } elseif ($type === 'simplified_debit_note') {
            $invoiceFlavor = 'simplified';
            $typeCode = '383';
            $subType = '0200000';
            $lineAmount = 24.00;
            $taxAmount = 3.60;
            $payableTotal = 27.60;
            $buyerVat = '';
            $buyerName = '';
            $buyerAddress = '';
            $buyerRegistrationName = '';
            $buyerCrNumber = '';
            $buyerStreetName = '';
            $buyerBuildingNumber = '';
            $buyerCitySubdivisionName = '';
            $buyerCityName = '';
            $buyerPostalCode = '';
            $buyerCountry = 'SA';
        } elseif ($type === 'credit_note') {
            $typeCode = '381';
            $lineAmount = 4.00;
            $taxAmount = 0.60;
            $payableTotal = 4.60;
        } elseif ($type === 'debit_note') {
            $typeCode = '383';
            $lineAmount = 24.00;
            $taxAmount = 3.60;
            $payableTotal = 27.60;
        }

        $ctx = [
            'uuid' => $uuid,
            'invoice_no' => $invNum,
            'issue_date' => $date,
            'issue_time' => $time,
            'type_code' => $typeCode,
            'sub_type' => $subType,
            'seller_name' => (string) ($p['seller_name'] ?? 'Test Seller'),
            'seller_vat' => (string) ($p['seller_vat'] ?? ''),
            'seller_address' => (string) ($p['seller_address'] ?? 'Riyadh'),
            'seller_building_number' => (string) ($p['seller_building_number'] ?? '1234'),
            'seller_city_subdivision_name' => (string) ($p['seller_city_subdivision_name'] ?? 'Al-Murabba'),
            'seller_city_name' => (string) ($p['seller_city_name'] ?? 'Riyadh'),
            'seller_postal_code' => (string) ($p['seller_postal_code'] ?? '12345'),
            'seller_country' => (string) ($p['seller_country'] ?? 'SA'),
            'buyer_name' => $buyerName,
            'buyer_vat' => $buyerVat,
            'buyer_address' => $buyerAddress,
            'buyer_registration_name' => $buyerRegistrationName,
            'buyer_cr_number' => $buyerCrNumber,
            'buyer_street_name' => $buyerStreetName,
            'buyer_building_number' => $buyerBuildingNumber,
            'buyer_city_subdivision_name' => $buyerCitySubdivisionName,
            'buyer_city_name' => $buyerCityName,
            'buyer_postal_code' => $buyerPostalCode,
            'buyer_country' => $buyerCountry,
            'invoice_flavor' => $invoiceFlavor,
            'currency' => 'SAR',
            'icv' => $icv,
            'pih' => self::DEFAULT_PIH,
            'rows' => [[
                'name' => 'Compliance Test Item',
                'qty' => 1,
                'line_subtotal' => $lineAmount,
                'line_tax' => $taxAmount,
                'line_total' => $payableTotal,
                'unit_price' => $lineAmount,
            ]],
            'taxable_total' => $lineAmount,
            'tax_total' => $taxAmount,
            'payable_total' => $payableTotal,
        ];

        return $this->buildSaleXml($ctx);
    }

    protected function billingRef(string $refId): string
    {
        return <<<XML

    <cac:BillingReference>
        <cac:InvoiceDocumentReference>
            <cbc:ID>$refId</cbc:ID>
        </cac:InvoiceDocumentReference>
    </cac:BillingReference>
XML;
    }

    protected function standardBuyer(): string
    {
        return <<<XML
<cac:AccountingCustomerParty>
        <cac:Party>
            <cac:PostalAddress>
                <cbc:StreetName>Salah Al-Din</cbc:StreetName>
                <cbc:BuildingNumber>1111</cbc:BuildingNumber>
                <cbc:CitySubdivisionName>Al-Murooj</cbc:CitySubdivisionName>
                <cbc:CityName>Riyadh</cbc:CityName>
                <cbc:PostalZone>12222</cbc:PostalZone>
                <cac:Country><cbc:IdentificationCode>SA</cbc:IdentificationCode></cac:Country>
            </cac:PostalAddress>
            <cac:PartyTaxScheme>
                <cbc:CompanyID>399999999800003</cbc:CompanyID>
                <cac:TaxScheme><cbc:ID>VAT</cbc:ID></cac:TaxScheme>
            </cac:PartyTaxScheme>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName>Fatoora Samples LTD</cbc:RegistrationName>
            </cac:PartyLegalEntity>
        </cac:Party>
    </cac:AccountingCustomerParty>
XML;
    }

    // -------------------------------------------------------------------------
    // Hash + Signing
    // -------------------------------------------------------------------------

    /**
     * Compute SHA256 hash using C14N11 XML canonicalization (excluding signature artifacts).
     */
    protected function computeHash(string $xml): string
    {
        $dom = new \DOMDocument();
        $dom->loadXML($xml);
        $xpath = new \DOMXPath($dom);

        // Register all needed namespaces
        $xpath->registerNamespace('ext', 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2');
        $xpath->registerNamespace('cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $xpath->registerNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

        // Remove UBLExtensions (signature)
        foreach ($xpath->query('//ext:UBLExtensions') as $node) {
            $node->parentNode->removeChild($node);
        }
        // Remove cac:Signature
        foreach ($xpath->query('//cac:Signature') as $node) {
            $node->parentNode->removeChild($node);
        }
        // Remove QR AdditionalDocumentReference
        foreach ($xpath->query("//cac:AdditionalDocumentReference[cbc:ID='QR']") as $node) {
            $node->parentNode->removeChild($node);
        }

        $canonical = $dom->C14N(false, false);
        return base64_encode(hash('sha256', $canonical, true));
    }

    /**
     * Embed a simplified XAdES-BES signature into the invoice XML.
     *
     * rawToken = binary_security_token from ZATCA = plain base64 DER certificate (no PEM headers)
     */
    protected function embedXadesSignature(
        string $xml,
        string $invoiceHash,
        string $privateKeyPem,
        string $rawToken,
        string $signingTime
    ): string {
        $certBase64 = $this->normalizeCertificateTokenBase64($rawToken);
        $certDer = $this->extractCertificateDer($rawToken);
        $certHash = $certDer === '' ? '' : base64_encode(hash('sha256', $certDer, true));

        log_message('debug', 'ZATCA embedXadesSignature: certDer_len=' . strlen($certDer)
            . ' certHash=' . $certHash
            . ' certBase64_prefix=' . substr($certBase64, 0, 20));

        // Parse cert for issuer/serial (internally wrap as PEM just for openssl_x509_parse)
        $issuerName   = 'CN=ZATCA';
        $serialNumber = '0';
        $pemForParsing = "-----BEGIN CERTIFICATE-----\n"
            . chunk_split($certBase64, 64, "\n")
            . "-----END CERTIFICATE-----\n";
        $certRes = @openssl_x509_read($pemForParsing);
        if ($certRes) {
            $info         = openssl_x509_parse($certRes);
            $issuerRaw    = $info['issuer'] ?? [];
            $issuerName   = $this->buildIssuerName(is_array($issuerRaw) ? $issuerRaw : []);
            // CRITICAL: Convert hex serial number to decimal integer
            // openssl_x509_parse() returns large serial numbers in hex format (e.g., '0x130000862BF49BEA...')
            // but ZATCA's XSD expects a decimal integer value
            // For large numbers, hexdec() returns a float which renders as scientific notation.
            // Use GMP for proper large integer handling.
            if (isset($info['serialNumber'])) {
                $serialStr = (string)$info['serialNumber'];
                if (function_exists('gmp_init')) {
                    // Use GMP for reliable large number conversion
                    $serialNumber = gmp_strval(gmp_init($serialStr, 16));
                } else {
                    // Fallback: use base_convert (may truncate for very large numbers)
                    $serialNumber = base_convert(ltrim($serialStr, '0x'), 16, 10);
                }
            }
        } else {
            log_message('warning', 'ZATCA Invoice: could not parse cert for issuer/serial, using fallback');
        }

        // Build SignedProperties XML according to ZATCA spec (Step 4: Populate Signed Properties)
        // No whitespace/indentation to ensure canonicalization matches ZATCA expectations
        $signedPropsXml = '<xades:SignedProperties xmlns:xades="http://uri.etsi.org/01903/v1.3.2#" Id="xadesSignedProperties">'
            . '<xades:SignedSignatureProperties>'
            . '<xades:SigningTime>' . htmlspecialchars($signingTime, ENT_XML1) . '</xades:SigningTime>'
            . '<xades:SigningCertificate>'
            . '<xades:Cert>'
            . '<xades:CertDigest>'
            . '<ds:DigestMethod xmlns:ds="http://www.w3.org/2000/09/xmldsig#" Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/>'
            . '<ds:DigestValue xmlns:ds="http://www.w3.org/2000/09/xmldsig#">' . htmlspecialchars($certHash, ENT_XML1) . '</ds:DigestValue>'
            . '</xades:CertDigest>'
            . '<xades:IssuerSerial>'
            . '<ds:X509IssuerName xmlns:ds="http://www.w3.org/2000/09/xmldsig#">' . htmlspecialchars($issuerName, ENT_XML1) . '</ds:X509IssuerName>'
            . '<ds:X509SerialNumber xmlns:ds="http://www.w3.org/2000/09/xmldsig#">' . htmlspecialchars($serialNumber, ENT_XML1) . '</ds:X509SerialNumber>'
            . '</xades:IssuerSerial>'
            . '</xades:Cert>'
            . '</xades:SigningCertificate>'
            . '</xades:SignedSignatureProperties>'
            . '</xades:SignedProperties>';

        // Step 5: Generate Signed Properties Hash
        // CRITICAL: Use the SAME canonicalization method that the Transform specifies in SignedInfo
        // SignedInfo specifies exclusive C14N (http://www.w3.org/2001/10/xml-exc-c14n#)
        // So we must hash SignedProperties using exclusive C14N to match ZATCA's validation
        $dom = new \DOMDocument();
        $dom->loadXML($signedPropsXml);
        $signedPropsCanon = $dom->C14N(true, false);  // Exclusive C14N (true = exclusive)
        $signedPropsHash  = base64_encode(hash('sha256', $signedPropsCanon, true));

        // Build SignedInfo XML according to ZATCA spec - use inclusive C14N 1.1
        $signedInfoXml = '<ds:SignedInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#">'
            . '<ds:CanonicalizationMethod Algorithm="http://www.w3.org/2006/12/xml-c14n11"/>'
            . '<ds:SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#ecdsa-sha256"/>'
            . '<ds:Reference Id="invoiceSignedData" URI="">'
            . '<ds:Transforms>'
            . '<ds:Transform Algorithm="http://www.w3.org/TR/1999/REC-xpath-19991116">'
            . '<ds:XPath>not(//ancestor-or-self::ext:UBLExtensions)</ds:XPath>'
            . '</ds:Transform>'
            . '<ds:Transform Algorithm="http://www.w3.org/TR/1999/REC-xpath-19991116">'
            . '<ds:XPath>not(//ancestor-or-self::cac:Signature)</ds:XPath>'
            . '</ds:Transform>'
            . '<ds:Transform Algorithm="http://www.w3.org/TR/1999/REC-xpath-19991116">'
            . '<ds:XPath>not(//ancestor-or-self::cac:AdditionalDocumentReference[cbc:ID=\'QR\'])</ds:XPath>'
            . '</ds:Transform>'
            . '<ds:Transform Algorithm="http://www.w3.org/2006/12/xml-c14n11"/>'
            . '</ds:Transforms>'
            . '<ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/>'
            . '<ds:DigestValue>' . htmlspecialchars($invoiceHash, ENT_XML1) . '</ds:DigestValue>'
            . '</ds:Reference>'
            . '<ds:Reference Type="http://www.w3.org/2000/09/xmldsig#SignatureProperties" URI="#xadesSignedProperties">'
            . '<ds:Transforms>'
            . '<ds:Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>'
            . '</ds:Transforms>'
            . '<ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/>'
            . '<ds:DigestValue>' . htmlspecialchars($signedPropsHash, ENT_XML1) . '</ds:DigestValue>'
            . '</ds:Reference>'
            . '</ds:SignedInfo>';

        // Canonicalize SignedInfo using inclusive C14N 1.1 and sign it
        $siDom = new \DOMDocument();
        $siDom->loadXML($signedInfoXml);
        $signedInfoCanon = $siDom->C14N(false, false);

        $normalizedKeyPem = $this->certService->normalizePrivateKeyPem($privateKeyPem);
        $privateKey = openssl_pkey_get_private($normalizedKeyPem);
        $loadError = openssl_error_string();
        if ($privateKey === false) {
            $privateKey = openssl_pkey_get_private($normalizedKeyPem, '');
            $loadError = openssl_error_string();
        }

        if ($privateKey === false) {
            throw new \RuntimeException('Unable to load the private key for invoice signing. Please re-import a valid PEM private key. OpenSSL: ' . trim((string) $loadError));
        }

        $signResult = openssl_sign($signedInfoCanon, $signatureBin, $privateKey, OPENSSL_ALGO_SHA256);
        if ($signResult === false) {
            throw new \RuntimeException('Unable to sign the compliance invoice. OpenSSL: ' . trim((string) openssl_error_string()));
        }
        $signatureRaw = $this->ecdsaDerToRaw((string) $signatureBin, 32);
        $signatureValue = base64_encode($signatureRaw);

        // Build full ds:Signature element
        $dsSignature = <<<XML
<ds:Signature Id="signature" xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
    $signedInfoXml
    <ds:SignatureValue>$signatureValue</ds:SignatureValue>
    <ds:KeyInfo>
        <ds:X509Data>
            <ds:X509Certificate>$certBase64</ds:X509Certificate>
        </ds:X509Data>
    </ds:KeyInfo>
    <ds:Object>
        <xades:QualifyingProperties xmlns:xades="http://uri.etsi.org/01903/v1.3.2#" Target="#signature">
            $signedPropsXml
        </xades:QualifyingProperties>
    </ds:Object>
</ds:Signature>
XML;

        // Build UBLDocumentSignatures wrapper
        $ublSigs = <<<XML
<sig:UBLDocumentSignatures xmlns:sig="urn:oasis:names:specification:ubl:schema:xsd:CommonSignatureComponents-2"
    xmlns:sac="urn:oasis:names:specification:ubl:schema:xsd:SignatureAggregateComponents-2"
    xmlns:sbc="urn:oasis:names:specification:ubl:schema:xsd:SignatureBasicComponents-2">
    <sac:SignatureInformation>
        <cbc:ID xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2">urn:oasis:names:specification:ubl:signature:1</cbc:ID>
        <sbc:ReferencedSignatureID>urn:oasis:names:specification:ubl:signature:Invoice</sbc:ReferencedSignatureID>
        $dsSignature
    </sac:SignatureInformation>
</sig:UBLDocumentSignatures>
XML;

        // Inject the signature into the XML's ExtensionContent
        $fullDom = new \DOMDocument();
        $fullDom->loadXML($xml);
        $xpathFull = new \DOMXPath($fullDom);
        $xpathFull->registerNamespace('ext', 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2');

        foreach ($xpathFull->query('//ext:ExtensionContent') as $node) {
            $sigDom = new \DOMDocument();
            $sigDom->loadXML($ublSigs);
            $imported = $fullDom->importNode($sigDom->documentElement, true);
            $node->appendChild($imported);
            break;
        }

        return $fullDom->saveXML();
    }

    protected function ecdsaDerToRaw(string $derSignature, int $partLength = 32): string
    {
        $offset = 0;
        $seq = $this->readAsn1Element($derSignature, $offset);
        if (!$seq || $seq['tag'] !== 0x30) {
            return $derSignature;
        }

        $inner = (string) $seq['value'];
        $innerOffset = 0;
        $r = $this->readAsn1Element($inner, $innerOffset);
        $s = $this->readAsn1Element($inner, $innerOffset);
        if (!$r || !$s || $r['tag'] !== 0x02 || $s['tag'] !== 0x02) {
            return $derSignature;
        }

        $rBin = ltrim((string) $r['value'], "\x00");
        $sBin = ltrim((string) $s['value'], "\x00");

        $rBin = str_pad(substr($rBin, -$partLength), $partLength, "\x00", STR_PAD_LEFT);
        $sBin = str_pad(substr($sBin, -$partLength), $partLength, "\x00", STR_PAD_LEFT);

        return $rBin . $sBin;
    }

    protected function determineInvoiceFlavor(string $invoiceSetting, string $customerVat): string
    {
        $mode = strtolower(trim($invoiceSetting));
        if (in_array($mode, ['b2c', 'simplified'], true)) {
            return 'simplified';
        }
        if (in_array($mode, ['b2b', 'standard'], true)) {
            return 'standard';
        }

        return trim($customerVat) !== '' ? 'standard' : 'simplified';
    }

    protected function normalizeSaleIssuedAt(string $timestamp): array
    {
        $ts = strtotime($timestamp);
        if ($ts === false) {
            $ts = time();
        }

        return [
            'date' => gmdate('Y-m-d', $ts),
            'time' => gmdate('H:i:s', $ts),
            'datetime' => gmdate('Y-m-d\TH:i:s', $ts),
        ];
    }

    protected function getInvoiceChainContext(int $storeId, int $currentSaleId): array
    {
        $salesModel = new M_sales();
        $last = $salesModel->forStore($storeId)
            ->select('zatca_icv, zatca_invoice_hash')
            ->where('status', 'completed')
            ->where('zatca_icv IS NOT NULL', null, false)
            ->where('zatca_invoice_hash IS NOT NULL', null, false)
            ->where('id !=', $currentSaleId)
            ->orderBy('zatca_icv', 'DESC')
            ->first();

        $lastIcv = (int) ($last['zatca_icv'] ?? 0);
        $previousHash = trim((string) ($last['zatca_invoice_hash'] ?? ''));
        if ($previousHash === '') {
            $previousHash = self::DEFAULT_PIH;
        }

        return [
            'next_icv' => $lastIcv + 1,
            'previous_hash' => $previousHash,
        ];
    }

    protected function normalizeSellerAddressValue($value, string $fallback): string
    {
        $value = trim((string) ($value ?? ''));
        if ($value === '') {
            $value = $fallback;
        }

        $value = preg_replace('/\s+/', ' ', $value);
        $value = trim((string) $value);
        if ($value === '') {
            $value = $fallback;
        }

        return substr($value, 0, 127);
    }

    protected function normalizeSellerBuildingNumber($value): string
    {
        $digits = preg_replace('/\D/', '', (string) ($value ?? ''));
        if ($digits === '') {
            $digits = '0000';
        }

        $digits = substr($digits, 0, 4);
        return str_pad($digits, 4, '0', STR_PAD_LEFT);
    }

    protected function normalizeSellerPostalCode($value): string
    {
        $digits = preg_replace('/\D/', '', (string) ($value ?? ''));
        if ($digits === '') {
            $digits = '00000';
        }

        $digits = substr($digits, 0, 5);
        return str_pad($digits, 5, '0', STR_PAD_LEFT);
    }

    protected function normalizeCrnValue($value): string
    {
        $value = preg_replace('/\D/', '', (string) ($value ?? ''));
        if ($value === '') {
            $value = '1001001000';
        }

        $value = substr($value, 0, 15);
        if (strlen($value) < 10) {
            $value = str_pad($value, 10, '0', STR_PAD_LEFT);
        }

        return $value;
    }

    protected function buildTotalsFromSaleRows(array $rows, float $saleTaxTotal, float $saleGrandTotal): array
    {
        $normalizedRows = [];
        $taxableTotal = 0.0;

        foreach ($rows as $row) {
            if (!empty($row['is_gift'])) {
                continue;
            }

            $qty = max(0.0, (float) ($row['quantity'] ?? 0));
            if ($qty <= 0) {
                continue;
            }

            $unitPrice = max(0.0, (float) ($row['price'] ?? 0));
            $lineSubtotal = isset($row['subtotal']) ? (float) $row['subtotal'] : ($unitPrice * $qty);
            $lineSubtotal = max(0.0, $lineSubtotal);
            $taxableTotal += $lineSubtotal;

            $normalizedRows[] = [
                'name' => trim((string) ($row['product_name'] ?? 'Item')),
                'qty' => $qty,
                'unit_price' => $unitPrice,
                'line_subtotal' => $lineSubtotal,
                'line_tax' => 0.0,
                'line_total' => 0.0,
            ];
        }

        if (empty($normalizedRows)) {
            throw new \RuntimeException('No billable items found for ZATCA invoice generation.');
        }

        $taxTotal = max(0.0, round($saleTaxTotal, 2));
        if ($taxTotal <= 0.0 && $saleGrandTotal > $taxableTotal) {
            $taxTotal = round(max(0.0, $saleGrandTotal - $taxableTotal), 2);
        }

        $allocatedTax = 0.0;
        $rowCount = count($normalizedRows);
        foreach ($normalizedRows as $i => &$line) {
            if ($i === $rowCount - 1) {
                $lineTax = round($taxTotal - $allocatedTax, 2);
            } else {
                $share = $taxableTotal > 0 ? ($line['line_subtotal'] / $taxableTotal) : 0;
                $lineTax = round($taxTotal * $share, 2);
                $allocatedTax += $lineTax;
            }
            if ($lineTax < 0) {
                $lineTax = 0.0;
            }
            $line['line_tax'] = $lineTax;
            $line['line_total'] = round($line['line_subtotal'] + $lineTax, 2);
        }
        unset($line);

        $payableTotal = max(0.0, round($saleGrandTotal > 0 ? $saleGrandTotal : ($taxableTotal + $taxTotal), 2));

        return [
            'rows' => $normalizedRows,
            'taxable_total' => round($taxableTotal, 2),
            'tax_total' => $taxTotal,
            'payable_total' => $payableTotal,
        ];
    }

    protected function buildSaleXml(array $ctx): string
    {
        $currency = 'SAR';
        $issueDate = (string) ($ctx['issue_date'] ?? gmdate('Y-m-d'));
        $issueTime = (string) ($ctx['issue_time'] ?? gmdate('H:i:s'));
        $invoiceFlavor = (string) ($ctx['invoice_flavor'] ?? 'simplified');
        $subType = (string) ($ctx['sub_type'] ?? ($invoiceFlavor === 'simplified' ? '0200000' : '0100000'));
        $typeCode = (string) ($ctx['type_code'] ?? '388');

        $vatPercent = 15.0;
        if ((float) ($ctx['taxable_total'] ?? 0) > 0) {
            $vatPercent = round(((float) ($ctx['tax_total'] ?? 0) / (float) $ctx['taxable_total']) * 100, 2);
        }

        $invoiceType = 'invoice';
        if ($typeCode === '381') {
            $invoiceType = 'credit';
        } elseif ($typeCode === '383') {
            $invoiceType = 'debit';
        }
        $isAdjustmentNote = $typeCode === '381' || $typeCode === '383';

        $flags = str_pad($subType, 7, '0', STR_PAD_RIGHT);
        $invoiceData = [
            'uuid' => (string) ($ctx['uuid'] ?? $this->newUuid()),
            'id' => (string) ($ctx['invoice_no'] ?? 'INV-' . gmdate('YmdHis')),
            'issueDate' => $issueDate,
            'issueTime' => $issueTime,
            'currencyCode' => $currency,
            'taxCurrencyCode' => $currency,
            'invoiceType' => [
                'invoice' => $invoiceFlavor === 'simplified' ? 'simplified' : 'standard',
                'type' => $invoiceType,
                'isThirdParty' => substr($flags, 2, 1) === '1',
                'isNominal' => substr($flags, 3, 1) === '1',
                'isExport' => substr($flags, 4, 1) === '1',
                'isSummary' => substr($flags, 5, 1) === '1',
                'isSelfBilled' => substr($flags, 6, 1) === '1',
            ],
            'additionalDocuments' => [
                [
                    'id' => 'ICV',
                    'uuid' => (string) (int) ($ctx['icv'] ?? 1),
                ],
                [
                    'id' => 'PIH',
                    'attachment' => [
                        'content' => (string) ($ctx['pih'] ?? self::DEFAULT_PIH),
                    ],
                ],
            ],
            'supplier' => [
                'registrationName' => (string) ($ctx['seller_name'] ?? 'Seller'),
                'taxId' => (string) ($ctx['seller_vat'] ?? ''),
                'identificationId' => '1001001000',
                'identificationType' => 'CRN',
                'address' => [
                    'street' => $this->normalizeSellerAddressValue((string) ($ctx['seller_address'] ?? ''), 'Riyadh'),
                    'buildingNumber' => $this->normalizeSellerBuildingNumber((string) ($ctx['seller_building_number'] ?? '1234')),
                    'subdivision' => $this->normalizeSellerAddressValue((string) ($ctx['seller_city_subdivision_name'] ?? 'Al-Murabba'), 'Al-Murabba'),
                    'city' => $this->normalizeSellerAddressValue((string) ($ctx['seller_city_name'] ?? 'Riyadh'), 'Riyadh'),
                    'postalZone' => $this->normalizeSellerPostalCode((string) ($ctx['seller_postal_code'] ?? '12345')),
                    'country' => strtoupper((string) ($ctx['seller_country'] ?? 'SA')),
                ],
            ],
            'paymentMeans' => [
                'code' => '10',
            ],
            'allowanceCharges' => [
                [
                    'isCharge' => false,
                    'reason' => 'discount',
                    'amount' => 0.00,
                    'taxCategories' => [
                        [
                            'id' => 'S',
                            'percent' => $vatPercent,
                            'taxScheme' => ['id' => 'VAT'],
                        ],
                    ],
                ],
            ],
            'taxTotal' => [
                'taxAmount' => (float) ($ctx['tax_total'] ?? 0),
                'subTotals' => [
                    [
                        'taxableAmount' => (float) ($ctx['taxable_total'] ?? 0),
                        'taxAmount' => (float) ($ctx['tax_total'] ?? 0),
                        'taxCategory' => [
                            'id' => 'S',
                            'percent' => $vatPercent,
                            'taxScheme' => ['id' => 'VAT'],
                        ],
                    ],
                ],
            ],
            'legalMonetaryTotal' => [
                'lineExtensionAmount' => (float) ($ctx['taxable_total'] ?? 0),
                'taxExclusiveAmount' => (float) ($ctx['taxable_total'] ?? 0),
                'taxInclusiveAmount' => (float) ($ctx['payable_total'] ?? 0),
                'prepaidAmount' => 0,
                'payableAmount' => (float) ($ctx['payable_total'] ?? 0),
                'allowanceTotalAmount' => 0,
            ],
            'invoiceLines' => [],
        ];

        if ($isAdjustmentNote) {
            $reason = trim((string) ($ctx['adjustment_reason'] ?? 'Correction of previous invoice'));
            if ($reason === '') {
                $reason = 'Correction of previous invoice';
            }

            $referenceInvoiceId = trim((string) ($ctx['reference_invoice_no'] ?? 'INV-REF-1'));
            if ($referenceInvoiceId === '') {
                $referenceInvoiceId = 'INV-REF-1';
            }

            // BR-KSA-17 (KSA-10): Credit/Debit notes must carry the issuing reason.
            $invoiceData['paymentMeans']['note'] = $reason;
            $invoiceData['billingReferences'] = [
                ['id' => $referenceInvoiceId],
            ];
        }

        if ($invoiceFlavor === 'simplified') {
            $invoiceData['note'] = 'ABC';
            $invoiceData['languageID'] = 'ar';
        } else {
            $buyerVat = trim((string) ($ctx['buyer_vat'] ?? ''));
            if ($buyerVat === '') {
                $buyerVat = '399999999800003';
            }

            $buyerRegistration = trim((string) ($ctx['buyer_registration_name'] ?? $ctx['buyer_name'] ?? ''));
            if ($buyerRegistration === '') {
                $buyerRegistration = 'Fatoora Samples LTD';
            }

            $buyerStreet = trim((string) ($ctx['buyer_street_name'] ?? $ctx['buyer_address'] ?? ''));
            if ($buyerStreet === '') {
                $buyerStreet = 'Salah Al-Din';
            }

            $buyerBuilding = trim((string) ($ctx['buyer_building_number'] ?? ''));
            if ($buyerBuilding === '') {
                $buyerBuilding = '1';
            }

            $buyerSubdivision = trim((string) ($ctx['buyer_city_subdivision_name'] ?? ''));
            if ($buyerSubdivision === '') {
                $buyerSubdivision = 'Al-Murooj';
            }

            $buyerCity = trim((string) ($ctx['buyer_city_name'] ?? ''));
            if ($buyerCity === '') {
                $buyerCity = 'Riyadh';
            }

            $buyerPostal = trim((string) ($ctx['buyer_postal_code'] ?? ''));
            if ($buyerPostal === '') {
                $buyerPostal = '12222';
            }

            $buyerCountry = strtoupper(trim((string) ($ctx['buyer_country'] ?? 'SA')));
            if ($buyerCountry === '') {
                $buyerCountry = 'SA';
            }

            $invoiceData['delivery'] = [
                'actualDeliveryDate' => $issueDate,
            ];

            $invoiceData['customer'] = [
                'registrationName' => $buyerRegistration,
                'taxId' => $buyerVat,
                'identificationId' => $this->normalizeCrnValue((string) ($ctx['buyer_cr_number'] ?? '1001001000')),
                'identificationType' => 'CRN',
                'address' => [
                    'street' => $buyerStreet,
                    'buildingNumber' => $buyerBuilding,
                    'subdivision' => $buyerSubdivision,
                    'city' => $buyerCity,
                    'postalZone' => $buyerPostal,
                    'country' => $buyerCountry,
                ],
            ];
        }

        $lineNumber = 1;
        foreach ((array) ($ctx['rows'] ?? []) as $line) {
            $invoiceData['invoiceLines'][] = [
                'id' => $lineNumber,
                'unitCode' => 'PCE',
                'quantity' => (float) ($line['qty'] ?? 0),
                'lineExtensionAmount' => (float) ($line['line_subtotal'] ?? 0),
                'item' => [
                    'name' => (string) ($line['name'] ?? 'Item'),
                    'classifiedTaxCategory' => [
                        [
                            'id' => 'S',
                            'percent' => $vatPercent,
                            'taxScheme' => ['id' => 'VAT'],
                        ],
                    ],
                ],
                'price' => [
                    'amount' => (float) ($line['unit_price'] ?? 0),
                    'unitCode' => 'UNIT',
                ],
                'taxTotal' => [
                    'taxAmount' => (float) ($line['line_tax'] ?? 0),
                    'roundingAmount' => (float) ($line['line_total'] ?? 0),
                ],
            ];
            $lineNumber++;
        }

        try {
            $invoice = (new InvoiceMapper())->mapToInvoice($invoiceData);
            return GeneratorInvoice::invoice($invoice)->getXML();
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to build invoice XML using php-zatca-xml: ' . $e->getMessage());
        }
    }

    protected function extractSignatureValue(string $signedXml): string
    {
        $dom = new \DOMDocument();
        if (!@$dom->loadXML($signedXml)) {
            return '';
        }

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');
        $node = $xpath->query('//ds:SignatureValue')->item(0);
        return $node ? trim((string) $node->nodeValue) : '';
    }

    protected function extractCertificateBase64FromSignedXml(string $signedXml): string
    {
        $dom = new \DOMDocument();
        if (!@$dom->loadXML($signedXml)) {
            return '';
        }

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');
        $node = $xpath->query('//ds:X509Certificate')->item(0);
        if (!$node) {
            return '';
        }

        $cert = preg_replace('/\s+/', '', trim((string) $node->nodeValue));
        return is_string($cert) ? $cert : '';
    }

    protected function extractPublicKeyBase64FromCertificateBase64(string $certificateBase64): string
    {
        $normalized = preg_replace('/\s+/', '', trim($certificateBase64));
        if (!is_string($normalized) || $normalized === '') {
            return '';
        }

        $pem = "-----BEGIN CERTIFICATE-----\n"
            . chunk_split($normalized, 64, "\n")
            . "-----END CERTIFICATE-----\n";

        $pub = openssl_pkey_get_public($pem);
        if (!$pub) {
            return '';
        }

        $details = openssl_pkey_get_details($pub);
        $pubPem = (string) ($details['key'] ?? '');
        if ($pubPem === '') {
            return '';
        }

        // Tag 8 expects the ECDSA public key in DER SubjectPublicKeyInfo form.
        return base64_encode($this->pemToDer($pubPem));
    }

    protected function extractEcPublicKeyBase64FromCertDer(string $certDer): string
    {
        if ($certDer === '') {
            return '';
        }

        $offset = 0;
        $root = $this->readAsn1Element($certDer, $offset);
        if (!$root || $root['tag'] !== 0x30) {
            return '';
        }

        $rootInner = (string) $root['value'];
        $rootOffset = 0;
        $tbs = $this->readAsn1Element($rootInner, $rootOffset);
        if (!$tbs || $tbs['tag'] !== 0x30) {
            return '';
        }

        $tbsInner = (string) $tbs['value'];
        $tbsOffset = 0;

        $first = $this->readAsn1Element($tbsInner, $tbsOffset);
        if (!$first) {
            return '';
        }

        // Optional [0] EXPLICIT version field.
        $serial = $first['tag'] === 0xA0
            ? $this->readAsn1Element($tbsInner, $tbsOffset)
            : $first;
        if (!$serial) {
            return '';
        }

        $signatureAlg = $this->readAsn1Element($tbsInner, $tbsOffset);
        $issuer = $this->readAsn1Element($tbsInner, $tbsOffset);
        $validity = $this->readAsn1Element($tbsInner, $tbsOffset);
        $subject = $this->readAsn1Element($tbsInner, $tbsOffset);
        $spki = $this->readAsn1Element($tbsInner, $tbsOffset);

        if (!$signatureAlg || !$issuer || !$validity || !$subject || !$spki || $spki['tag'] !== 0x30) {
            return '';
        }

        $spkiInner = (string) $spki['value'];
        $spkiOffset = 0;
        $algorithm = $this->readAsn1Element($spkiInner, $spkiOffset);
        $subjectPublicKey = $this->readAsn1Element($spkiInner, $spkiOffset);
        if (!$algorithm || !$subjectPublicKey || $subjectPublicKey['tag'] !== 0x03) {
            return '';
        }

        $bitString = (string) $subjectPublicKey['value'];
        if ($bitString === '') {
            return '';
        }

        // BIT STRING first byte is unused-bits count.
        $keyBytes = substr($bitString, 1);
        if ($keyBytes === false || $keyBytes === '') {
            return '';
        }

        return base64_encode($keyBytes);
    }

    protected function extractCertificateSignatureBase64FromCertificateBase64(string $certificateBase64): string
    {
        $normalized = preg_replace('/\s+/', '', trim($certificateBase64));
        if (!is_string($normalized) || $normalized === '') {
            return '';
        }

        $der = base64_decode($normalized, true);
        if ($der === false || $der === '') {
            return '';
        }

        $offset = 0;
        $root = $this->readAsn1Element($der, $offset);
        if (!$root || $root['tag'] !== 0x30) {
            return '';
        }

        $inner = $root['value'];
        $innerOffset = 0;
        $first = $this->readAsn1Element($inner, $innerOffset);
        $second = $this->readAsn1Element($inner, $innerOffset);
        $third = $this->readAsn1Element($inner, $innerOffset);

        if (!$first || !$second || !$third || $third['tag'] !== 0x03) {
            return '';
        }

        $bitString = $third['value'];
        if ($bitString === '') {
            return '';
        }

        $signatureBytes = substr($bitString, 1);
        if ($signatureBytes === false || $signatureBytes === '') {
            return '';
        }

        return base64_encode($signatureBytes);
    }

    protected function extractPublicKeyBase64FromToken(string $rawToken): string
    {
        $pem = $this->binaryTokenToCertPem($rawToken);
        $pub = openssl_pkey_get_public($pem);
        if (!$pub) {
            return '';
        }

        $details = openssl_pkey_get_details($pub);
        $pubPem = (string) ($details['key'] ?? '');
        if ($pubPem === '') {
            return '';
        }

        // Tag 8 expects the ECDSA public key in DER SubjectPublicKeyInfo form.
        return base64_encode($this->pemToDer($pubPem));
    }

    protected function extractCertificateSignatureBase64FromToken(string $rawToken): string
    {
        $der = $this->pemToDer($this->binaryTokenToCertPem($rawToken));
        if ($der === '') {
            return '';
        }

        $offset = 0;
        $root = $this->readAsn1Element($der, $offset);
        if (!$root || $root['tag'] !== 0x30) {
            return '';
        }

        $inner = $root['value'];
        $innerOffset = 0;
        $first = $this->readAsn1Element($inner, $innerOffset);  // tbsCertificate
        $second = $this->readAsn1Element($inner, $innerOffset); // signatureAlgorithm
        $third = $this->readAsn1Element($inner, $innerOffset);  // signatureValue BIT STRING

        if (!$first || !$second || !$third || $third['tag'] !== 0x03) {
            return '';
        }

        $bitString = $third['value'];
        if ($bitString === '') {
            return '';
        }

        // First byte in BIT STRING is the unused-bits counter.
        $signatureBytes = substr($bitString, 1);
        if ($signatureBytes === false || $signatureBytes === '') {
            return '';
        }

        return base64_encode($signatureBytes);
    }

    protected function readAsn1Element(string $data, int &$offset)
    {
        $lenData = strlen($data);
        if ($offset + 2 > $lenData) {
            return null;
        }

        $tag = ord($data[$offset]);
        $offset++;

        $firstLenByte = ord($data[$offset]);
        $offset++;

        if (($firstLenByte & 0x80) === 0) {
            $length = $firstLenByte;
        } else {
            $numBytes = $firstLenByte & 0x7F;
            if ($numBytes <= 0 || $numBytes > 4 || ($offset + $numBytes) > $lenData) {
                return null;
            }

            $length = 0;
            for ($i = 0; $i < $numBytes; $i++) {
                $length = ($length << 8) | ord($data[$offset]);
                $offset++;
            }
        }

        if ($length < 0 || ($offset + $length) > $lenData) {
            return null;
        }

        $value = substr($data, $offset, $length);
        $offset += $length;

        return [
            'tag' => $tag,
            'length' => $length,
            'value' => $value,
        ];
    }

    protected function decodeBase64ToBinaryOrKeep(string $value): string
    {
        if ($value === '') {
            return '';
        }

        $decoded = base64_decode($value, true);
        if ($decoded === false || $decoded === '') {
            return $value;
        }

        return $decoded;
    }

    protected function buildTlvQrBase64(array $tagValues): string
    {
        $binary = '';
        foreach ($tagValues as $tag => $value) {
            $tagNum = (int) $tag;
            if ($tagNum <= 0 || $tagNum > 255) {
                continue;
            }

            $stringValue = (string) $value;
            $binary .= chr($tagNum) . $this->encodeTlvLength(strlen($stringValue)) . $stringValue;
        }

        return base64_encode($binary);
    }

    protected function encodeTlvLength(int $length): string
    {
        if ($length < 0x80) {
            return chr($length);
        }

        if ($length <= 0xFF) {
            return chr(0x81) . chr($length);
        }

        return chr(0x82) . pack('n', min($length, 0xFFFF));
    }

    protected function saveSignedInvoiceXml(int $saleId, string $uuid, string $signedXml): string
    {
        $basePath = rtrim((string) $this->config->xmlStoragePath, '/\\');
        if (!is_dir($basePath)) {
            mkdir($basePath, 0755, true);
        }

        $fileName = 'sale-' . $saleId . '-' . preg_replace('/[^A-Za-z0-9\-]/', '', $uuid) . '.xml';
        $absolutePath = $basePath . DIRECTORY_SEPARATOR . $fileName;
        file_put_contents($absolutePath, $signedXml);

        return 'writable/zatca/invoices/' . $fileName;
    }

    public function prepareInvoicePayloadForApi(string $signedXml): string
    {
        $trimmed = trim($signedXml);
        if ($trimmed === '') {
            return '';
        }

        // ZATCA report/clear payload requires Base64-encoded invoice content.
        if (strpos($trimmed, '<') === 0) {
            return base64_encode($signedXml);
        }

        $decoded = base64_decode($trimmed, true);
        if ($decoded !== false && strpos(ltrim($decoded), '<') === 0) {
            return $trimmed;
        }

        return base64_encode($signedXml);
    }

    // -------------------------------------------------------------------------
    // Utility helpers
    // -------------------------------------------------------------------------

    protected function newUuid(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }

    protected function binaryTokenToCertPem(string $token): string
    {
        $der = $this->extractCertificateDer($token);
        if ($der === '') {
            return "-----BEGIN CERTIFICATE-----\n"
                . chunk_split($this->normalizeCertificateTokenBase64($token), 64, "\n")
                . "-----END CERTIFICATE-----\n";
        }

        return "-----BEGIN CERTIFICATE-----\n"
            . chunk_split(base64_encode($der), 64, "\n")
            . "-----END CERTIFICATE-----\n";
    }

    protected function extractCertificateDer(string $token): string
    {
        $token = trim($token);
        if ($token === '') {
            return '';
        }

        if (strpos($token, '-----BEGIN') !== false) {
            $pemBody = preg_replace('/-----[^-]+-----/', '', $token);
            $candidate = preg_replace('/\s+/', '', (string) $pemBody) ?? '';
            $der = base64_decode($candidate, true);
            return $der !== false ? $der : '';
        }

        $normalized = preg_replace('/\s+/', '', $token) ?? '';
        if ($normalized === '') {
            return '';
        }

        $decoded = base64_decode($normalized, true);
        if ($decoded !== false && $decoded !== '') {
            if (strpos($decoded, '-----BEGIN CERTIFICATE-----') !== false) {
                $pemBody = preg_replace('/-----[^-]+-----/', '', $decoded);
                $nested = preg_replace('/\s+/', '', (string) $pemBody) ?? '';
                $nestedDer = base64_decode($nested, true);
                return $nestedDer !== false ? $nestedDer : '';
            }

            if (strlen($decoded) >= 2 && ord($decoded[0]) === 0x30) {
                return $decoded;
            }

            $candidate = preg_replace('/\s+/', '', $decoded) ?? '';
            $candidateDer = base64_decode($candidate, true);
            if ($candidateDer !== false && $candidateDer !== '' && ord($candidateDer[0]) === 0x30) {
                return $candidateDer;
            }
        }

        if (strlen($token) >= 2 && ord($token[0]) === 0x30) {
            return $token;
        }

        return '';
    }

    protected function normalizeCertificateTokenBase64(string $token): string
    {
        $der = $this->extractCertificateDer($token);
        return $der === '' ? '' : base64_encode($der);
    }

    protected function pemToDer(string $pem): string
    {
        $pem = preg_replace('/-----[^-]+-----/', '', $pem);
        return base64_decode(str_replace(["\r", "\n", " "], '', $pem));
    }

    protected function buildIssuerName(array $issuer): string
    {
        $parts = [];
        // Order matters: Standard X.500 naming convention
        $fields = ['CN', 'O', 'OU', 'L', 'ST', 'C', 'DC', 'emailAddress'];
        foreach ($fields as $f) {
            if (!empty($issuer[$f])) {
                $value = $issuer[$f];
                // DC fields may be an array if multiple DC components exist
                if (is_array($value)) {
                    foreach ($value as $v) {
                        $parts[] = $f . '=' . $v;
                    }
                } else {
                    $parts[] = $f . '=' . $value;
                }
            }
        }
        return implode(', ', $parts);
    }

    protected function extractVatFromCertificateToken(string $token): string
    {
        $normalized = $this->normalizeCertificateTokenBase64($token);
        if ($normalized === '') {
            return '';
        }

        $pem = "-----BEGIN CERTIFICATE-----\n"
            . chunk_split($normalized, 64, "\n")
            . "-----END CERTIFICATE-----\n";

        $certRes = @openssl_x509_read($pem);
        if (!$certRes) {
            return '';
        }

        $info = openssl_x509_parse($certRes);
        if (!is_array($info)) {
            return '';
        }

        $subject = $info['subject'] ?? [];
        $cn = is_array($subject) ? trim((string) ($subject['CN'] ?? '')) : '';
        if ($cn === '') {
            return '';
        }

        if (preg_match('/(\d{15})$/', $cn, $m) === 1) {
            return $m[1];
        }

        return '';
    }
}
