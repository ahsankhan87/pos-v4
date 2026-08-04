<?php

namespace App\Controllers;

use App\Services\ZatcaApiClient;
use App\Services\ZatcaCertificateService;
use App\Services\ZatcaInvoiceService;
use App\Models\StoreModel;

/**
 * ZATCA Onboarding Controller
 * 
 * Handles the ZATCA certificate onboarding process:
 * 1. Generate CSR
 * 2. Get Compliance CSID
 * 3. Run Compliance Checks
 * 4. Get Production CSID
 */
class ZatcaOnboarding extends BaseController
{
    protected $certificateService;
    protected $apiClient;
    protected $invoiceService;
    protected $certificatesModel;
    protected $logsModel;
    protected $settingsModel;
    protected $storeModel;

    public function __construct()
    {
        helper(['audit', 'zatca_helper']);

        $this->certificateService = new ZatcaCertificateService();
        $this->apiClient = new ZatcaApiClient();
        $this->invoiceService = new ZatcaInvoiceService();
        $this->certificatesModel = model('ZatcaCertificatesModel');
        $this->logsModel = model('ZatcaLogsModel');
        $this->settingsModel = model('SettingsModel');
        $this->storeModel = new StoreModel();
    }

    /**
     * Onboarding dashboard
     */
    public function index()
    {
        // Check if ZATCA is enabled
        if (!zatca_enabled()) {
            return redirect()->to(site_url('settings'))
                ->with('error', lang('Zatca.feature_disabled'));
        }

        $storeId = (int) session('store_id');
        $settings = $this->settingsModel->getZatcaSettings();
        $environment = $settings['zatca_environment'] ?? 'sandbox';

        // Get existing certificate for this store + environment
        $certificate = $this->certificatesModel
            ->where('store_id', $storeId)
            ->where('environment', $environment)
            ->first();

        // Determine current step status
        $stepStatus = [
            'csr_generated' => !empty($certificate['csr']),
            'compliance_csid_obtained' => !empty($certificate['binary_security_token']),
            'compliance_checks_passed' => false, // Will implement check logic
            'production_csid_obtained' => !empty($certificate['production_binary_security_token']),
        ];

        // Check if compliance checks passed (read from session or database flag)
        if (!empty($certificate)) {
            $stepStatus['compliance_checks_passed'] =
                session('zatca_compliance_passed_' . $certificate['id']) ?? false;
        }

        $data = [
            'title' => lang('Zatca.onboarding_title'),
            'settings' => $settings,
            'certificate' => $certificate,
            'stepStatus' => $stepStatus,
            'environment' => $environment,
        ];

        return view('zatca/onboarding', $data);
    }

    /**
     * Step 1: Generate CSR and private key
     */
    public function generateCsr()
    {
        if (!zatca_enabled()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => lang('Zatca.feature_disabled'),
            ]);
        }

        try {
            $storeId = (int) session('store_id');
            $settings = $this->settingsModel->getZatcaSettings();
            $store = $this->storeModel->find($storeId);
            if (!$store) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Store profile not found.',
                ]);
            }

            $vatNumber = trim((string) ($store['zatca_seller_vat_number'] ?? ''));
            $organizationName = trim((string) ($store['zatca_seller_legal_name'] ?? ''));

            // Validate required store profile fields
            if ($vatNumber === '' || $organizationName === '') {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => lang('Zatca.onboarding_missing_settings'),
                ]);
            }

            $organizationUnit = session('store_name') ?: 'Main Branch'; // Branch name from session
            $commonName = 'POS-Device-' . $storeId; // Not used - generated in service

            // Generate CSR and key
            $result = $this->certificateService->generateCsrAndKey(
                $storeId,
                $vatNumber,
                $organizationName,
                $organizationUnit,
                $commonName, // This will be overridden in the service with TST-{TIN}-{VAT}
                '2', // Invoice type: both B2C and B2B (translates to 1100)
                trim((string) ($store['zatca_street_name'] ?? $store['address'] ?? 'Riyadh')),
                (string) ($store['business_type'] ?? 'Retail')
            );

            // Log action
            $this->logsModel->logAction(
                'generate_csr',
                "CSR generated for store {$storeId}",
                'info',
                null,
                ['certificate_id' => $result['certificate_id']]
            );

            return $this->response->setJSON([
                'success' => true,
                'message' => lang('Zatca.onboarding_csr_generated'),
                'certificate_id' => $result['certificate_id'],
                'csr' => $result['csr'],
            ]);
        } catch (\Exception $e) {
            $this->logsModel->logAction('generate_csr', $e->getMessage(), 'error');

            return $this->response->setJSON([
                'success' => false,
                'message' => lang('Zatca.onboarding_csr_failed') . ': ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Step 2: Request Compliance CSID from ZATCA
     */
    public function requestComplianceCsid()
    {
        if (!zatca_enabled()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => lang('Zatca.feature_disabled'),
            ]);
        }

        try {
            // Get OTP from JSON body (not form POST)
            $json = $this->request->getJSON();
            $otp = $json->otp ?? '';

            if (empty($otp)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => lang('Zatca.onboarding_otp_required'),
                ]);
            }

            $storeId = (int) session('store_id');
            $settings = $this->settingsModel->getZatcaSettings();
            $environment = $settings['zatca_environment'] ?? 'sandbox';
            $store = $this->storeModel->find($storeId);
            if (!$store) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Store profile not found.',
                ]);
            }

            // Get certificate with CSR
            $certificate = $this->certificatesModel
                ->where('store_id', $storeId)
                ->where('environment', $environment)
                ->first();

            if (empty($certificate) || empty($certificate['csr'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => lang('Zatca.onboarding_csr_not_found'),
                ]);
            }

            // Request Compliance CSID from ZATCA
            log_message('info', 'ZatcaOnboarding: Requesting Compliance CSID for store ' . $storeId);
            log_message('info', 'ZatcaOnboarding: Environment: ' . $environment);
            log_message('info', 'ZatcaOnboarding: Certificate ID: ' . $certificate['id']);

            $apiResponse = $this->apiClient->requestComplianceCsid(
                $certificate['csr'],
                $otp
            );

            // Store compliance CSID
            $this->certificateService->storeComplianceCsid(
                $certificate['id'],
                $apiResponse
            );

            // Log action
            $this->logsModel->logAction(
                'request_compliance_csid',
                "Compliance CSID obtained for store {$storeId}",
                'info',
                null,
                ['certificate_id' => $certificate['id']]
            );

            return $this->response->setJSON([
                'success' => true,
                'message' => lang('Zatca.onboarding_compliance_csid_obtained'),
                'data' => [
                    'binary_security_token' => substr($apiResponse['binarySecurityToken'], 0, 50) . '...',
                    'request_id' => $apiResponse['requestID'] ?? null,
                ],
            ]);
        } catch (\Exception $e) {
            log_message('error', 'ZatcaOnboarding: Exception caught - ' . $e->getMessage());
            log_message('error', 'ZatcaOnboarding: Exception trace: ' . $e->getTraceAsString());

            $this->logsModel->logAction('request_compliance_csid', $e->getMessage(), 'error');

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to obtain Compliance CSID',
                'error' => $e->getMessage(),
                'details' => 'Check writable/logs/log-' . date('Y-m-d') . '.log for full details',
            ]);
        }
    }

    /**
     * Step 3: Run Compliance Checks (submit sample invoices)
     */
    public function runComplianceChecks()
    {
        if (!zatca_enabled()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => lang('Zatca.feature_disabled'),
            ]);
        }

        try {
            $storeId = (int) session('store_id');
            $settings = $this->settingsModel->getZatcaSettings();
            $environment = $settings['zatca_environment'] ?? 'sandbox';
            $store = $this->storeModel->find($storeId);
            if (!$store) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Store profile not found.',
                ]);
            }

            // Get certificate with compliance CSID
            $certificate = $this->certificatesModel
                ->where('store_id', $storeId)
                ->where('environment', $environment)
                ->where('status', 'compliance')
                ->first();

            if (empty($certificate) || empty($certificate['binary_security_token'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => lang('Zatca.onboarding_compliance_csid_not_found'),
                ]);
            }

            // Generate properly formatted + signed ZATCA invoices for compliance testing
            $sampleInvoices = $this->invoiceService->generateComplianceTestInvoices($certificate, $settings, $store);
            log_message('info', 'ZatcaOnboarding: Generated ' . count($sampleInvoices) . ' signed test invoices');

            $results = [];
            $allPassed = true;

            foreach ($sampleInvoices as $invoiceType => $sampleData) {
                try {
                    $apiResponse = $this->apiClient->submitComplianceInvoice(
                        $sampleData['hash'],
                        $sampleData['uuid'],
                        $sampleData['xml'],
                        $certificate['binary_security_token'],
                        $certificate['secret']
                    );

                    $toText = static function ($value): string {
                        if ($value === null) {
                            return '';
                        }

                        if (is_scalar($value)) {
                            return trim((string) $value);
                        }

                        $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                        return $json !== false ? $json : '';
                    };

                    $validation = is_array($apiResponse['validationResults'] ?? null) ? $apiResponse['validationResults'] : [];
                    $status = strtoupper($toText($validation['status'] ?? ''));
                    $passed = $status === 'PASS';

                    $message = '';
                    if (!$passed) {
                        $message = $toText($apiResponse['message'] ?? '');
                        if ($message === '' && !empty($apiResponse['code'])) {
                            $message = $toText($apiResponse['code']);
                        }
                    }

                    if ($message === '') {
                        $infoMessage = $validation['infoMessages'][0] ?? '';
                        $warningMessage = $validation['warningMessages'][0] ?? '';
                        $errorMessage = $validation['errorMessages'][0] ?? '';
                        $message = $toText($errorMessage ?: $warningMessage ?: $infoMessage ?: 'Validation complete');
                    }

                    $results[$invoiceType] = [
                        'passed' => $passed,
                        'message' => $message,
                        'status' => $status !== '' ? $status : $toText($apiResponse['code'] ?? 'ERROR'),
                    ];

                    if (!$passed) {
                        $allPassed = false;
                    }
                } catch (\Exception $e) {
                    $results[$invoiceType] = [
                        'passed' => false,
                        'message' => $e->getMessage(),
                    ];
                    $allPassed = false;
                }
            }

            // Store compliance check result in session
            if ($allPassed) {
                session()->set('zatca_compliance_passed_' . $certificate['id'], true);
            }

            // Log action
            $this->logsModel->logAction(
                'run_compliance_checks',
                "Compliance checks " . ($allPassed ? 'PASSED' : 'FAILED') . " for store {$storeId}",
                $allPassed ? 'info' : 'warning',
                null,
                ['certificate_id' => $certificate['id'], 'results' => $results]
            );

            return $this->response->setJSON([
                'success' => $allPassed,
                'message' => $allPassed
                    ? lang('Zatca.onboarding_compliance_checks_passed')
                    : lang('Zatca.onboarding_compliance_checks_failed'),
                'results' => $results,
            ]);
        } catch (\Exception $e) {
            $this->logsModel->logAction('run_compliance_checks', $e->getMessage(), 'error');

            return $this->response->setJSON([
                'success' => false,
                'message' => lang('Zatca.onboarding_compliance_checks_error') . ': ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Step 4: Request Production CSID
     */
    public function requestProductionCsid()
    {
        if (!zatca_enabled()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => lang('Zatca.feature_disabled'),
            ]);
        }

        try {
            $storeId = (int) session('store_id');
            $settings = $this->settingsModel->getZatcaSettings();
            $environment = $settings['zatca_environment'] ?? 'sandbox';
            $store = $this->storeModel->find($storeId);
            if (!$store) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Store profile not found.',
                ]);
            }

            // Get certificate with compliance CSID
            $certificate = $this->certificatesModel
                ->where('store_id', $storeId)
                ->where('environment', $environment)
                ->where('status', 'compliance')
                ->first();

            if (empty($certificate) || empty($certificate['binary_security_token'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => lang('Zatca.onboarding_compliance_csid_not_found'),
                ]);
            }

            // Check if compliance checks passed
            // $compliancePassed = session('zatca_compliance_passed_' . $certificate['id']) ?? false;
            // if (!$compliancePassed) {
            //     return $this->response->setJSON([
            //         'success' => false,
            //         'message' => lang('Zatca.onboarding_compliance_required'),
            //     ]);
            // }

            $configuredSellerVat = trim((string) ($store['zatca_seller_vat_number'] ?? ''));
            $complianceCertVat = $this->extractVatFromCertificateToken((string) ($certificate['binary_security_token'] ?? ''));
            if ($configuredSellerVat !== '' && $complianceCertVat !== '' && $complianceCertVat !== $configuredSellerVat) {
                if ($this->looksLikePlaceholderVat($complianceCertVat)) {
                    $this->logsModel->logAction(
                        'request_production_csid',
                        "Compliance CSID VAT mismatch ignored for placeholder value {$complianceCertVat}",
                        'warning',
                        null,
                        ['certificate_id' => $certificate['id']]
                    );
                } else {
                    $this->logsModel->logAction(
                        'request_production_csid',
                        "Compliance CSID VAT mismatch before Step 4: expected {$configuredSellerVat}, got {$complianceCertVat}",
                        'error',
                        null,
                        ['certificate_id' => $certificate['id']]
                    );

                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Compliance CSID VAT mismatch. Expected ' . $configuredSellerVat . ' but got ' . $complianceCertVat . '. Re-run Step 2 and Step 3 with the configured seller VAT.',
                    ]);
                }
            }

            $complianceRequestId = trim((string) ($certificate['compliance_request_id'] ?? ''));
            if ($complianceRequestId === '') {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Compliance request ID is missing. Re-run Step 2 and Step 3 before Step 4.',
                ]);
            }

            // if ($this->looksLikePlaceholderComplianceRequestId($complianceRequestId)) {
            //     return $this->response->setJSON([
            //         'success' => false,
            //         'message' => 'Compliance request ID looks like a sandbox placeholder response. Re-run Step 2 with a fresh OTP from ZATCA portal and then run Step 3 before requesting Production CSID.',
            //     ]);
            // }

            // Request Production CSID from ZATCA
            $apiResponse = $this->apiClient->requestProductionCsid(
                $certificate['binary_security_token'],
                $certificate['secret'],
                $complianceRequestId
            );

            $productionCertVat = $this->extractVatFromCertificateToken((string) ($apiResponse['binarySecurityToken'] ?? ''));
            if ($configuredSellerVat !== '' && $productionCertVat !== '' && $productionCertVat !== $configuredSellerVat) {
                if ($this->looksLikePlaceholderVat($productionCertVat)) {
                    $this->logsModel->logAction(
                        'request_production_csid',
                        "Production CSID VAT mismatch ignored for placeholder value {$productionCertVat}",
                        'warning',
                        null,
                        ['certificate_id' => $certificate['id']]
                    );
                } else {
                    $this->logsModel->logAction(
                        'request_production_csid',
                        "Production CSID VAT mismatch: expected {$configuredSellerVat}, got {$productionCertVat}",
                        'error',
                        null,
                        ['certificate_id' => $certificate['id']]
                    );

                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Production CSID VAT mismatch. Expected ' . $configuredSellerVat . ' but got ' . $productionCertVat . '. Re-run Step 4 using the same VAT profile.',
                    ]);
                }
            }

            // Store production CSID
            $this->certificateService->storeProductionCsid(
                $certificate['id'],
                $apiResponse
            );

            // Log action
            $this->logsModel->logAction(
                'request_production_csid',
                "Production CSID obtained for store {$storeId}",
                'info',
                null,
                ['certificate_id' => $certificate['id']]
            );

            // Log onboarding completion in ZATCA log stream.
            $this->logsModel->logAction(
                'zatca_onboarding_complete',
                "ZATCA onboarding completed for store {$storeId} in {$environment}",
                'info',
                null,
                ['certificate_id' => $certificate['id']]
            );

            return $this->response->setJSON([
                'success' => true,
                'message' => lang('Zatca.onboarding_production_csid_obtained'),
                'data' => [
                    'binary_security_token' => substr($apiResponse['binarySecurityToken'], 0, 50) . '...',
                ],
            ]);
        } catch (\Exception $e) {
            $this->logsModel->logAction('request_production_csid', $e->getMessage(), 'error');

            return $this->response->setJSON([
                'success' => false,
                'message' => lang('Zatca.onboarding_production_csid_failed') . ': ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Generate sample invoices for compliance testing
     * 
     * NOTE: This is a simplified placeholder. Phase 4 will implement full UBL XML generation.
     * For now, we'll use minimal valid XML structures.
     */
    protected function generateSampleInvoices(): array
    {
        // Placeholder sample invoices (Phase 4 will generate real UBL 2.1 XML)
        return [
            'standard_invoice' => [
                'uuid' => 'INV-' . uniqid(),
                'hash' => base64_encode(hash('sha256', 'sample-standard-invoice', true)),
                'xml' => base64_encode('<Invoice>Sample Standard Invoice XML</Invoice>'),
            ],
            'simplified_invoice' => [
                'uuid' => 'INV-' . uniqid(),
                'hash' => base64_encode(hash('sha256', 'sample-simplified-invoice', true)),
                'xml' => base64_encode('<Invoice>Sample Simplified Invoice XML</Invoice>'),
            ],
            'credit_note' => [
                'uuid' => 'CN-' . uniqid(),
                'hash' => base64_encode(hash('sha256', 'sample-credit-note', true)),
                'xml' => base64_encode('<CreditNote>Sample Credit Note XML</CreditNote>'),
            ],
            'debit_note' => [
                'uuid' => 'DN-' . uniqid(),
                'hash' => base64_encode(hash('sha256', 'sample-debit-note', true)),
                'xml' => base64_encode('<DebitNote>Sample Debit Note XML</DebitNote>'),
            ],
        ];
    }

    protected function looksLikePlaceholderComplianceRequestId(string $value): bool
    {
        $value = trim($value);
        if ($value === '') {
            return true;
        }

        $normalized = strtolower($value);
        $placeholderPatterns = [
            '/^1234567890123$/',
            '/^test(s)?$/i',
            '/^sample$/i',
            '/^placeholder$/i',
            '/^sandbox$/i',
        ];

        foreach ($placeholderPatterns as $pattern) {
            if (preg_match($pattern, $value) === 1) {
                return true;
            }
        }

        return false;
    }

    protected function looksLikePlaceholderVat(string $vat): bool
    {
        $vat = trim($vat);
        if ($vat === '') {
            return true;
        }

        $placeholderVats = [
            '123456789012345',
            '399999999800003',
            '399999999900003',
            '000000000000000',
            '999999999999999',
        ];

        return in_array($vat, $placeholderVats, true);
    }

    protected function extractVatFromCertificateToken(string $token): string
    {
        $token = trim($token);
        if ($token === '') {
            return '';
        }

        $decoded = base64_decode($token, true);
        if ($decoded !== false && strpos($decoded, '-----BEGIN CERTIFICATE-----') !== false) {
            $pemBody = preg_replace('/-----[^-]+-----/', '', $decoded);
            $token = preg_replace('/\s+/', '', (string) $pemBody) ?? '';
        } elseif (strpos($token, '-----BEGIN') !== false) {
            $pemBody = preg_replace('/-----[^-]+-----/', '', $token);
            $token = preg_replace('/\s+/', '', (string) $pemBody) ?? '';
        } else {
            // Handle double-encoded values as seen with CSID tokens.
            $candidate = preg_replace('/\s+/', '', $token) ?? '';
            $candidateDecoded = base64_decode($candidate, true);
            if ($candidateDecoded !== false && preg_match('/^[A-Za-z0-9+\/=\r\n\s]+$/', $candidateDecoded) === 1) {
                $token = preg_replace('/\s+/', '', $candidateDecoded) ?? $candidate;
            } else {
                $token = $candidate;
            }
        }

        if ($token === '') {
            return '';
        }

        $pem = "-----BEGIN CERTIFICATE-----\n"
            . chunk_split($token, 64, "\n")
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

    /**
     * Import certificate data generated by an external tool (e.g. C# app).
     * Accepts private key, CSR, compliance CSID token, secret, and request ID in one call.
     */
    public function importCertificate()
    {
        if (!zatca_enabled()) {
            return $this->response->setJSON(['success' => false, 'message' => lang('Zatca.feature_disabled')]);
        }

        try {
            $json = $this->request->getJSON();

            $privateKey           = trim((string) ($json->private_key ?? ''));
            $csr                  = trim((string) ($json->csr ?? ''));
            $binarySecurityToken  = trim((string) ($json->binary_security_token ?? ''));
            $secret               = trim((string) ($json->secret ?? ''));
            $complianceRequestId  = trim((string) ($json->compliance_request_id ?? ''));

            if ($privateKey === '') {
                return $this->response->setJSON(['success' => false, 'message' => 'Private key is required.']);
            }
            if ($binarySecurityToken === '') {
                return $this->response->setJSON(['success' => false, 'message' => 'Binary security token (Compliance CSID) is required.']);
            }
            if ($secret === '') {
                return $this->response->setJSON(['success' => false, 'message' => 'Secret is required.']);
            }

            $storeId     = (int) session('store_id');
            $settings    = $this->settingsModel->getZatcaSettings();
            $environment = $settings['zatca_environment'] ?? 'sandbox';

            $encryptedKey = $this->certificateService->encryptPrivateKey($privateKey);

            // Store private key in raw form without PEM headers (ZATCA expects raw base64 body)
            $privateKeyRawBody = $this->certificateService->extractPrivateKeyRawBody($privateKey);

            $existing = $this->certificatesModel
                ->where('store_id', $storeId)
                ->where('environment', $environment)
                ->first();

            $data = [
                'binary_security_token' => $binarySecurityToken,
                'secret'                => $secret,
                'compliance_request_id' => $complianceRequestId ?: null,
                'private_key'           => $privateKeyRawBody,
                'status'                => 'compliance',
                'updated_at'            => date('Y-m-d H:i:s'),
            ];

            if ($csr !== '') {
                $data['csr'] = $csr;
            }

            if ($existing) {
                $this->certificatesModel->update($existing['id'], $data);
                $certId = $existing['id'];
            } else {
                $data['store_id']    = $storeId;
                $data['environment'] = $environment;
                $data['created_at']  = date('Y-m-d H:i:s');
                $certId = $this->certificatesModel->insert($data);
            }

            logAction('import_certificate', "Certificate imported manually for store {$storeId}");

            return $this->response->setJSON([
                'success'        => true,
                'message'        => lang('Zatca.import_certificate_success'),
                'certificate_id' => $certId,
            ]);
        } catch (\Exception $e) {
            log_message('error', 'ZatcaOnboarding importCertificate: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Import failed: ' . $e->getMessage()]);
        }
    }
}
