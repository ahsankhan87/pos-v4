<?php

namespace App\Services;

use Config\Services;
use Config\Zatca;

/**
 * ZATCA API Client
 * 
 * Handles all HTTP communication with ZATCA Fatoora APIs
 * Manages authentication, error handling, and logging
 */
class ZatcaApiClient
{
    /** @var Zatca */
    protected $config;
    /** @var \Config\Services */
    protected $client;
    /** @var \CodeIgniter\Log\Logger */
    protected $logger;

    public function __construct()
    {
        $this->config = config('Zatca');
        $this->client = Services::curlrequest([
            'timeout' => $this->config->apiTimeout,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
        $this->logger = service('logger');
    }

    /**
     * Request Compliance CSID from ZATCA
     * 
     * @param string $csr Base64-encoded CSR
     * @param string $otp One-Time Password from ZATCA portal
     * @return array Response with binary_security_token, secret, request_id
     * @throws \Exception on API failure
     */
    public function requestComplianceCsid(string $csr, string $otp): array
    {
        $url = $this->config->getApiBaseUrl() . $this->config->apiEndpoints['compliance_csid'];

        $payload = [
            'csr' => $csr,
        ];

        // Enhanced request logging for debugging
        log_message('info', '=== ZATCA COMPLIANCE CSID REQUEST ===');
        log_message('info', 'ZATCA: URL: ' . $url);
        log_message('info', 'ZATCA: OTP: ' . substr($otp, 0, 2) . '***');
        log_message('info', 'ZATCA: CSR length: ' . strlen($csr) . ' chars');
        log_message('info', 'ZATCA: CSR first 80 chars: ' . substr($csr, 0, 80));
        log_message('info', 'ZATCA: CSR last 80 chars: ' . substr($csr, -80));
        log_message('info', 'ZATCA: Request Headers: ' . json_encode([
            'Accept' => 'application/json',
            'Accept-Version' => 'V2',
            'OTP' => substr($otp, 0, 2) . '***',
            'Content-Type' => 'application/json',
        ]));
        log_message('info', 'ZATCA: Payload Keys: ' . json_encode(array_keys($payload)));

        try {
            $response = $this->client->request('POST', $url, [
                'json' => $payload,
                'headers' => [
                    'Accept' => 'application/json',
                    'Accept-Version' => 'V2',
                    'OTP' => $otp,
                    'Content-Type' => 'application/json',
                ],
                'http_errors' => false, // Don't throw on 4xx/5xx
                'debug' => false,
            ]);

            $statusCode = $response->getStatusCode();
            $body = $response->getBody();

            // Enhanced response logging
            log_message('info', 'ZATCA: Response Status: ' . $statusCode);
            log_message('info', 'ZATCA: Response Body: ' . $body);

            $this->logApiCall('requestComplianceCsid', $url, $payload, $statusCode, $body);

            if ($statusCode !== 200) {
                // Try to parse error response
                $errorData = json_decode($body, true);
                $errorMessage = $errorData['message'] ?? $body;

                log_message('error', 'ZATCA: Compliance CSID request failed');
                log_message('error', 'ZATCA: Status Code: ' . $statusCode);
                log_message('error', 'ZATCA: Error Response: ' . $body);

                // Log validation errors if present
                if ($errorData && isset($errorData['errors'])) {
                    log_message('error', 'ZATCA: Validation Errors: ' . json_encode($errorData['errors']));
                }

                throw new \Exception("ZATCA API returned {$statusCode}: {$errorMessage}");
            }

            $data = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Invalid JSON response from ZATCA: " . json_last_error_msg());
            }

            // Validate response has required fields
            if (!isset($data['binarySecurityToken']) || !isset($data['secret'])) {
                log_message('error', 'ZATCA: Invalid response structure: ' . $body);
                throw new \Exception("Invalid ZATCA response: missing required fields");
            }

            log_message('info', 'ZATCA: Compliance CSID obtained successfully');

            return $data;
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'ZATCA API') === 0) {
                throw $e; // Re-throw our formatted errors
            }

            // Log curl/network errors
            log_message('error', 'ZATCA: Network error: ' . $e->getMessage());
            throw new \Exception("Failed to connect to ZATCA API: " . $e->getMessage());
        }
    }

    /**
     * Submit invoice to ZATCA compliance API for validation
     * 
     * @param string $invoiceHash Base64-encoded SHA256 hash
     * @param string $uuid Invoice UUID
     * @param string $signedXml Base64-encoded signed XML
     * @param string $binarySecurityToken Compliance CSID
     * @param string $secret CSID secret
     * @return array Validation result
     * @throws \Exception on API failure
     */
    public function submitComplianceInvoice(
        string $invoiceHash,
        string $uuid,
        string $signedXml,
        string $binarySecurityToken,
        string $secret
    ): array {
        $url = $this->config->getApiBaseUrl() . $this->config->apiEndpoints['compliance_invoices'];

        $binarySecurityToken = trim((string) $binarySecurityToken);
        $secret = trim((string) $secret);

        // Use the hash embedded inside the signed XML (computed pre-signature).
        // Re-deriving it from the signed XML risks producing a different hash due to DOM re-serialization.
        $effectiveInvoiceHash = $invoiceHash;

        $payload = [
            'invoiceHash' => $effectiveInvoiceHash,
            'uuid' => $uuid,
            'invoice' => $signedXml,
        ];

        $authorizationHeader = $this->buildBasicAuthorizationHeader($binarySecurityToken, $secret);

        $response = $this->client->request('POST', $url, [
            'json' => $payload,
            'headers' => [
                'Accept' => 'application/json',
                'Accept-Version' => 'V2',
                'Accept-Language' => 'en',
                'Content-Type' => 'application/json',
                'Authorization' => $authorizationHeader,
            ],
            'http_errors' => false,
        ]);

        $statusCode = $response->getStatusCode();
        $body = (string) $response->getBody();

        $this->logApiCall('submitComplianceInvoice', $url, ['uuid' => $uuid], $statusCode, $body);

        if (!in_array($statusCode, [200, 400, 406])) {
            throw new \Exception("ZATCA Compliance API Error: {$statusCode} - {$body}");
        }

        // 406 means this invoice type was already submitted and passed; treat as success
        if ($statusCode === 406) {
            return ['validationResults' => ['status' => 'PASS', 'infoMessages' => [], 'warningMessages' => [], 'errorMessages' => []], 'alreadySubmitted' => true];
        }

        return json_decode($body, true) ?? ['raw_response' => $body];
    }

    protected function computeInvoiceHashFromXml(string $xml): string
    {
        $dom = new \DOMDocument();
        if (!@$dom->loadXML($xml)) {
            return '';
        }

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('ext', 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2');
        $xpath->registerNamespace('cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $xpath->registerNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

        foreach ($xpath->query('//ext:UBLExtensions') as $node) {
            $node->parentNode->removeChild($node);
        }
        foreach ($xpath->query('//cac:Signature') as $node) {
            $node->parentNode->removeChild($node);
        }
        foreach ($xpath->query("//cac:AdditionalDocumentReference[cbc:ID='QR']") as $node) {
            $node->parentNode->removeChild($node);
        }

        $canonical = $dom->C14N(false, false);
        if ($canonical === false) {
            return '';
        }

        return base64_encode(hash('sha256', $canonical, true));
    }

    /**
     * Request Production CSID from ZATCA
     * 
     * @param string $complianceCsid The compliance binary security token
     * @param string $secret CSID secret
     * @return array Response with production binary_security_token
     * @throws \Exception on API failure
     */
    public function requestProductionCsid(string $complianceCsid, string $secret, string $complianceRequestId = '1'): array
    {
        $url = $this->config->getApiBaseUrl() . $this->config->apiEndpoints['production_csid'];

        $complianceCsid = $this->normalizeCsidToken($complianceCsid);
        $secret = trim($secret);

        $payload = [
            'compliance_request_id' => trim($complianceRequestId) !== '' ? trim($complianceRequestId) : '1',
        ];

        $authorizationHeader = $this->buildBasicAuthorizationHeader($complianceCsid, $secret);

        $response = $this->client->request('POST', $url, [
            'json' => $payload,
            'headers' => [
                'Accept' => 'application/json',
                'Accept-Version' => 'V2',
                'Content-Type' => 'application/json',
                'Authorization' => $authorizationHeader,
            ],
        ]);

        $statusCode = $response->getStatusCode();
        $body = $response->getBody();

        $this->logApiCall('requestProductionCsid', $url, $payload, $statusCode, $body);

        if ($statusCode !== 200) {
            throw new \Exception("ZATCA Production CSID Error: {$statusCode} - {$body}");
        }

        return json_decode($body, true);
    }

    /**
     * Report a simplified (B2C) invoice to ZATCA
     * 
     * @param string $invoiceHash Base64-encoded SHA256 hash
     * @param string $uuid Invoice UUID
     * @param string $signedXml Base64-encoded signed XML
     * @param string $binarySecurityToken Production CSID
     * @param string $secret CSID secret
     * @return array API response
     * @throws \Exception on API failure
     */
    public function reportInvoice(
        string $invoiceHash,
        string $uuid,
        string $signedXml,
        string $binarySecurityToken,
        string $secret
    ): array {
        $url = $this->config->getApiBaseUrl() . $this->config->apiEndpoints['report_invoice'];

        $binarySecurityToken = trim((string) $binarySecurityToken);
        $secret = trim((string) $secret);

        if ($binarySecurityToken === '' || $secret === '') {
            throw new \Exception('ZATCA authorization credentials are missing.');
        }

        $payload = [
            'invoiceHash' => $invoiceHash,
            'uuid' => $uuid,
            'invoice' => $signedXml,
        ];

        $authorizationHeader = $this->buildBasicAuthorizationHeader($binarySecurityToken, $secret);

        try {
            $response = $this->client->request('POST', $url, [
                'json' => $payload,
                'headers' => [
                    'Accept' => 'application/json',
                    'Accept-Version' => 'V2',
                    'Accept-Language' => 'en',
                    'Content-Type' => 'application/json',
                    'Authorization' => $authorizationHeader,
                    'Clearance-Status' => '0', // Not cleared
                ],
                'http_errors' => false, // Capture 4xx/5xx body instead of throwing generic cURL 22
            ]);

            $statusCode = $response->getStatusCode();
            $body = (string) $response->getBody();

            $this->logApiCall('reportInvoice', $url, ['uuid' => $uuid], $statusCode, $body);

            if (!in_array($statusCode, [200, 202], true)) {
                $fullBody = trim($body) !== '' ? $body : 'Empty response body';
                throw new \Exception("ZATCA Report Invoice Error: {$statusCode} - {$fullBody}");
            }

            return json_decode($body, true) ?? ['raw_response' => $body];
        } catch (\Throwable $e) {
            throw new \Exception('Report invoice request failed: ' . $e->getMessage());
        }
    }

    /**
     * Clear a standard (B2B) invoice with ZATCA
     * 
     * @param string $invoiceHash Base64-encoded SHA256 hash
     * @param string $uuid Invoice UUID
     * @param string $signedXml Base64-encoded signed XML
     * @param string $binarySecurityToken Production CSID
     * @param string $secret CSID secret
     * @return array Clearance result with cleared XML
     * @throws \Exception on API failure
     */
    public function clearInvoice(
        string $invoiceHash,
        string $uuid,
        string $signedXml,
        string $binarySecurityToken,
        string $secret
    ): array {
        $url = $this->config->getApiBaseUrl() . $this->config->apiEndpoints['clear_invoice'];

        $binarySecurityToken = trim((string) $binarySecurityToken);
        $secret = trim((string) $secret);

        if ($binarySecurityToken === '' || $secret === '') {
            throw new \Exception('ZATCA authorization credentials are missing.');
        }

        $payload = [
            'invoiceHash' => $invoiceHash,
            'uuid' => $uuid,
            'invoice' => $signedXml,
        ];

        $authorizationHeader = $this->buildBasicAuthorizationHeader($binarySecurityToken, $secret);

        try {
            $response = $this->client->request('POST', $url, [
                'json' => $payload,
                'headers' => [
                    'Accept' => 'application/json',
                    'Accept-Version' => 'V2',
                    'Accept-Language' => 'en',
                    'Content-Type' => 'application/json',
                    'Authorization' => $authorizationHeader,
                    'Clearance-Status' => '1', // Request clearance
                ],
                'http_errors' => false, // Capture 4xx/5xx body instead of throwing generic cURL 22
            ]);

            $statusCode = $response->getStatusCode();
            $body = (string) $response->getBody();

            $this->logApiCall('clearInvoice', $url, ['uuid' => $uuid], $statusCode, $body);

            if (!in_array($statusCode, [200, 202], true)) {
                $fullBody = trim($body) !== '' ? $body : 'Empty response body';
                throw new \Exception("ZATCA Clear Invoice Error: {$statusCode} - {$fullBody}");
            }

            return json_decode($body, true) ?? ['raw_response' => $body];
        } catch (\Throwable $e) {
            throw new \Exception('Clear invoice request failed: ' . $e->getMessage());
        }
    }

    protected function normalizeCsidToken(string $token): string
    {
        return trim((string) $token);
    }

    protected function buildBasicAuthorizationHeader(string $cert, string $secret): string
    {
        $cert = $this->normalizeCsidToken($cert);
        $secret = $this->normalizeCsidToken($secret);
        $credentials = base64_encode($cert . ':' . $secret);

        return 'Basic ' . $credentials;
    }

    /**
     * Log API call to database and/or file
     */
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

        // Also log to zatca_logs table
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
            // Fail silently if logging fails
            $this->logger->error("Failed to log ZATCA API call to database: " . $e->getMessage());
        }
    }
}
