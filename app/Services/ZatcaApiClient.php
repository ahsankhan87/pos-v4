<?php

namespace App\Services;

use Config\Zatca;
use Saleh7\Zatca\Exceptions\ZatcaApiException;
use Saleh7\Zatca\ZatcaAPI;

/**
 * ZATCA API Client adapter.
 *
 * Keeps the app's existing method signatures while delegating calls to
 * saleh7/php-zatca-xml.
 */
class ZatcaApiClient
{
    /** @var Zatca */
    protected $config;
    /** @var \CodeIgniter\Log\Logger */
    protected $logger;
    /** @var ZatcaAPI */
    protected $api;

    public function __construct()
    {
        $this->config = config('Zatca');
        $this->logger = service('logger');
        $this->api = new ZatcaAPI($this->resolveEnvironment());
    }

    public function requestComplianceCsid(string $csr, string $otp): array
    {
        try {
            $result = $this->api->requestComplianceCertificate($this->decodeCsrPayload($csr), $otp);

            $data = [
                'binarySecurityToken' => base64_encode($result->getCertificate()),
                'secret' => $result->getSecret(),
                'requestID' => $result->getRequestId(),
            ];

            $this->logApiCall('requestComplianceCsid', '/compliance', ['csr' => '[hidden]'], 200, json_encode($data));

            return $data;
        } catch (\Throwable $e) {
            throw $this->wrapApiError('requestComplianceCsid', $e);
        }
    }

    public function submitComplianceInvoice(
        string $invoiceHash,
        string $uuid,
        string $signedXml,
        string $binarySecurityToken,
        string $secret
    ): array {
        $invoiceXml = $this->normalizeInvoiceXmlPayload($signedXml);
        $certificate = $this->normalizeCertificateForAuth($binarySecurityToken);

        try {
            $response = $this->api->validateInvoiceCompliance(
                $certificate,
                trim($secret),
                $invoiceXml,
                $invoiceHash,
                $uuid
            );

            $result = $response->toArray();
            $this->logApiCall('submitComplianceInvoice', '/compliance/invoices', ['uuid' => $uuid], $response->getStatusCode(), json_encode($result));

            return $result;
        } catch (ZatcaApiException $e) {
            $statusCode = (int) ($e->getContext()['statusCode'] ?? 0);
            $responsePayload = $e->getContext()['response'] ?? [];

            if (in_array($statusCode, [400, 406], true) && is_array($responsePayload)) {
                $this->logApiCall('submitComplianceInvoice', '/compliance/invoices', ['uuid' => $uuid], $statusCode, json_encode($responsePayload));
                return $responsePayload;
            }

            throw $this->wrapApiError('submitComplianceInvoice', $e);
        } catch (\Throwable $e) {
            throw $this->wrapApiError('submitComplianceInvoice', $e);
        }
    }

    public function requestProductionCsid(string $complianceCsid, string $secret, string $complianceRequestId = '1'): array
    {
        try {
            $result = $this->api->requestProductionCertificate(
                $this->normalizeCertificateForAuth($complianceCsid),
                trim($secret),
                trim($complianceRequestId) !== '' ? trim($complianceRequestId) : '1'
            );

            $data = [
                'binarySecurityToken' => base64_encode($result->getCertificate()),
                'secret' => $result->getSecret(),
                'requestID' => $result->getRequestId(),
            ];

            $this->logApiCall('requestProductionCsid', '/production/csids', ['request_id' => $data['requestID']], 200, json_encode($data));

            return $data;
        } catch (\Throwable $e) {
            throw $this->wrapApiError('requestProductionCsid', $e);
        }
    }

    public function reportInvoice(
        string $invoiceHash,
        string $uuid,
        string $signedXml,
        string $binarySecurityToken,
        string $secret
    ): array {
        if (trim($binarySecurityToken) === '' || trim($secret) === '') {
            throw new \Exception('ZATCA authorization credentials are missing.');
        }

        try {
            $response = $this->api->submitReportingInvoice(
                $this->normalizeCertificateForAuth($binarySecurityToken),
                trim($secret),
                $this->normalizeInvoiceXmlPayload($signedXml),
                $invoiceHash,
                $uuid
            );

            $result = $response->toArray();
            $this->logApiCall('reportInvoice', '/invoices/reporting/single', ['uuid' => $uuid], $response->getStatusCode(), json_encode($result));

            return $result;
        } catch (\Throwable $e) {
            throw $this->wrapApiError('reportInvoice', $e);
        }
    }

    public function clearInvoice(
        string $invoiceHash,
        string $uuid,
        string $signedXml,
        string $binarySecurityToken,
        string $secret
    ): array {
        if (trim($binarySecurityToken) === '' || trim($secret) === '') {
            throw new \Exception('ZATCA authorization credentials are missing.');
        }

        try {
            $response = $this->api->submitClearanceInvoice(
                $this->normalizeCertificateForAuth($binarySecurityToken),
                trim($secret),
                $this->normalizeInvoiceXmlPayload($signedXml),
                $invoiceHash,
                $uuid
            );

            $result = $response->toArray();
            $this->logApiCall('clearInvoice', '/invoices/clearance/single', ['uuid' => $uuid], $response->getStatusCode(), json_encode($result));

            return $result;
        } catch (\Throwable $e) {
            throw $this->wrapApiError('clearInvoice', $e);
        }
    }

    protected function resolveEnvironment(): string
    {
        $settingsModel = model('SettingsModel');
        $settings = $settingsModel->getSettings();
        $environment = strtolower(trim((string) ($settings['zatca_environment'] ?? 'sandbox')));
        if (in_array($environment, ['sandbox', 'simulation', 'production'], true)) {
            return $environment;
        }

        return 'sandbox';
    }

    protected function decodeCsrPayload(string $csrPayload): string
    {
        $trimmed = trim($csrPayload);
        if ($trimmed === '') {
            return '';
        }

        if (strpos($trimmed, '-----BEGIN CERTIFICATE REQUEST-----') !== false) {
            return $trimmed;
        }

        $decoded = base64_decode($trimmed, true);
        if ($decoded !== false && strpos($decoded, '-----BEGIN CERTIFICATE REQUEST-----') !== false) {
            return $decoded;
        }

        return $trimmed;
    }

    protected function normalizeInvoiceXmlPayload(string $payload): string
    {
        $trimmed = trim($payload);
        if ($trimmed === '') {
            return '';
        }

        if (strpos($trimmed, '<') === 0) {
            return $trimmed;
        }

        $decoded = base64_decode($trimmed, true);
        if ($decoded !== false && strpos(ltrim($decoded), '<') === 0) {
            return $decoded;
        }

        return $trimmed;
    }

    protected function normalizeCertificateForAuth(string $certificate): string
    {
        $trimmed = trim($certificate);
        if ($trimmed === '') {
            return '';
        }

        if (strpos($trimmed, '-----BEGIN CERTIFICATE-----') !== false) {
            return $trimmed;
        }

        $decoded = base64_decode($trimmed, true);
        if ($decoded !== false && $decoded !== '') {
            return $decoded;
        }

        return $trimmed;
    }

    protected function wrapApiError(string $action, \Throwable $e): \Exception
    {
        if ($e instanceof ZatcaApiException) {
            $context = $e->getContext();
            $statusCode = (string) ($context['statusCode'] ?? 'n/a');
            $response = $context['response'] ?? [];
            $responsePreview = is_array($response) ? json_encode($response) : (string) $response;

            return new \Exception("{$action} failed (HTTP {$statusCode}): {$e->getMessage()} {$responsePreview}");
        }

        return new \Exception("{$action} failed: {$e->getMessage()}");
    }

    protected function logApiCall(string $action, string $url, array $payload, int $statusCode, string $response): void
    {
        if ($this->config->debugLogging) {
            $this->logger->info("ZATCA API Call: {$action}", [
                'url' => $url,
                'payload' => json_encode($payload),
                'status_code' => $statusCode,
                'response' => $response,
            ]);
        }

        try {
            $logsModel = model('ZatcaLogsModel');
            $logsModel->insert([
                'invoice_id' => null,
                'action' => $action,
                'level' => $statusCode >= 400 ? 'error' : 'info',
                'message' => "API {$action} - Status: {$statusCode}",
                'context' => json_encode([
                    'url' => $url,
                    'status_code' => $statusCode,
                    'response_preview' => substr($response, 0, 4000),
                ]),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to log ZATCA API call to database: ' . $e->getMessage());
        }
    }
}
