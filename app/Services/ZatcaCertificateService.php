<?php

namespace App\Services;

use Config\Zatca;
use Saleh7\Zatca\CertificateBuilder;

/**
 * ZATCA Certificate Service
 * 
 * Handles CSR generation, private key management, and certificate storage
 * Uses OpenSSL for cryptographic operations
 */
class ZatcaCertificateService
{
    /** @var Zatca */
    protected $config;
    /** @var ZatcaCertificatesModel */
    protected $certificatesModel;

    public function __construct()
    {
        $this->config = config('Zatca');
        $this->certificatesModel = model('ZatcaCertificatesModel');

        // Ensure certificate storage directory exists
        if (!is_dir($this->config->certificateStoragePath)) {
            mkdir($this->config->certificateStoragePath, 0700, true);
        }
    }

    protected function logMessage(string $level, string $message): void
    {
        if (function_exists('log_message')) {
            log_message($level, $message);
            return;
        }

        error_log('[ZATCA][' . strtoupper($level) . '] ' . $message);
    }

    /**
     * Generate EC private key and CSR for ZATCA onboarding
     * 
     * @param int $storeId Store ID for multi-store support
     * @param string $vatNumber Seller VAT number (15 digits)
     * @param string $organizationName Legal business name
     * @param string $organizationUnit Department/branch name
     * @param string $commonName Device/POS identifier
     * @param string $invoiceType '0' = B2C, '1' = B2B, '2' = Both
     * @param string $location Full street address
     * @param string $industry Business category code
     * @return array ['csr' => base64, 'private_key' => encrypted, 'certificate_id' => int]
     * @throws \Exception on OpenSSL failure
     */
    public function generateCsrAndKey(
        int $storeId,
        string $vatNumber,
        string $organizationName,
        string $organizationUnit,
        string $commonName,
        string $invoiceType = '2',
        string $location = 'Riyadh',
        string $industry = 'Retail'
    ): array {
        try {
            $settingsModel = model('SettingsModel');
            $settings = $settingsModel->getZatcaSettings();
            $environment = (string) ($settings['zatca_environment'] ?? 'sandbox');

            $serialUuid = strtoupper(bin2hex(random_bytes(16)));
            $serialUuid = substr($serialUuid, 0, 8) . '-' . substr($serialUuid, 8, 4) . '-' . substr($serialUuid, 12, 4) . '-' . substr($serialUuid, 16, 4) . '-' . substr($serialUuid, 20, 12);
            $tin = substr($vatNumber, 0, 10);

            $builder = (new CertificateBuilder())
                ->setOrganizationIdentifier($vatNumber)
                ->setSerialNumber('POS', '1.0', $serialUuid)
                ->setCommonName('ERP-' . $tin . '-' . $vatNumber)
                ->setCountryName('SA')
                ->setOrganizationName($organizationName)
                ->setOrganizationalUnitName($organizationUnit)
                ->setAddress($location)
                ->setInvoiceType($this->mapInvoiceTypeToBitmask($invoiceType))
                ->setEnvironment($this->mapEnvironmentToBuilderEnvironment($environment))
                ->setBusinessCategory($industry);

            $builder->generate();
            $csrPem = $builder->getCsr();

            $tmpKeyPath = WRITEPATH . 'zatca/private_' . uniqid('', true) . '.pem';
            $builder->savePrivateKey($tmpKeyPath);
            $privateKeyPem = (string) file_get_contents($tmpKeyPath);
            @unlink($tmpKeyPath);

            if ($csrPem === '' || $privateKeyPem === '') {
                throw new \RuntimeException('Generated CSR/private key is empty.');
            }

            $csrBase64 = base64_encode($csrPem);
            $privateKeyRawBody = $this->extractPrivateKeyRawBody($privateKeyPem);

            $existingCert = $this->certificatesModel
                ->where('store_id', $storeId)
                ->where('environment', $environment)
                ->first();

            if ($existingCert) {
                $this->certificatesModel->update($existingCert['id'], [
                    'csr' => $csrBase64,
                    'private_key' => $privateKeyRawBody,
                    'status' => 'draft',
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $certificateId = $existingCert['id'];
            } else {
                $certificateId = $this->certificatesModel->insert([
                    'store_id' => $storeId,
                    'environment' => $environment,
                    'csr' => $csrBase64,
                    'private_key' => $privateKeyRawBody,
                    'status' => 'draft',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }

            return [
                'csr' => $csrBase64,
                'private_key' => $privateKeyPem,
                'certificate_id' => $certificateId,
            ];
        } catch (\Throwable $e) {
            $this->logMessage('warning', 'ZATCA: php-zatca-xml CertificateBuilder failed, falling back to legacy OpenSSL flow: ' . $e->getMessage());
        }

        // --- Step 1: Build CSR identity values ---
        // Serial number: ZATCA format 1-TST|2-TST|3-{UUID} with standard 8-4-4-4-12 dashes
        $uuidBytes    = random_bytes(16);
        $uuidHex      = bin2hex($uuidBytes);
        $uuid         = strtoupper(substr($uuidHex, 0, 8) . '-' . substr($uuidHex, 8, 4) . '-' . substr($uuidHex, 12, 4) . '-' . substr($uuidHex, 16, 4) . '-' . substr($uuidHex, 20, 12));
        $deviceSerial = '1-TST|2-TST|3-' . $uuid;
        $tin          = substr($vatNumber, 0, 10);
        $cnValue      = 'TST-' . $tin . '-' . $vatNumber;

        $this->logMessage('info', 'ZATCA CSR: VAT=' . $vatNumber . ' TIN=' . $tin . ' CN=' . $cnValue);
        $this->logMessage('info', 'ZATCA CSR: SN=' . $deviceSerial . ' org=' . $organizationName . ' branch=' . $organizationUnit);

        // --- Step 2: Write two configs (CLI=full ZATCA, PHP=minimal legacy provider) ---
        // Read environment before writing configs so templateName can be set correctly
        $settingsModel = model('SettingsModel');
        $settings      = $settingsModel->getZatcaSettings();
        $environment   = $settings['zatca_environment'] ?? 'sandbox';

        list($cliConfig, $phpConfig) = $this->writeOpensslConfigs(
            $cnValue,
            $organizationName,
            $organizationUnit,
            $deviceSerial,
            $vatNumber,
            $location,
            $industry,
            $environment
        );

        // --- Step 3: HYBRID — PHP generates secp256k1 key, CLI generates CSR with full ZATCA extensions ---
        $tmpDir     = WRITEPATH . 'zatca';
        $privateTmp = $tmpDir . '/tmp_key_' . uniqid() . '.pem';
        $csrTmp     = $tmpDir . '/tmp_csr_' . uniqid() . '.pem';

        // PHP key generation (private_key_bits=4096 bypasses PHP 8.x 384-bit check; EC uses curve size)
        $phpKey = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name'       => 'secp256k1',
            'private_key_bits' => 4096,
            'config'           => $phpConfig,
        ]);
        if (!$phpKey) {
            throw new \Exception('Failed to generate EC key: ' . openssl_error_string());
        }
        openssl_pkey_export($phpKey, $privateKeyPem, null, ['config' => $phpConfig]);
        file_put_contents($privateTmp, $privateKeyPem);

        // CLI CSR generation from PHP-generated key (handles pipes in SN, full extensions)
        $cliSuccess = $this->generateCsrFromKeyViaCli($cliConfig, $privateTmp, $csrTmp);

        if ($cliSuccess) {
            $csrPem = file_get_contents($csrTmp);
            $this->logMessage('info', 'ZATCA: Full ZATCA CSR generated (key=PHP, csr=CLI)');
        } else {
            // PHP fallback: use the CLI config so req_extensions (subjectAltName, certificateTemplateName) are included
            $this->logMessage('warning', 'ZATCA: CLI CSR failed, using PHP openssl_csr_new with CLI config for ZATCA extensions');
            $dn = [
                'C'  => 'SA',
                'OU' => $organizationUnit,
                'O'  => $organizationName,
                'CN' => $cnValue,
            ];
            $csr = openssl_csr_new($dn, $phpKey, [
                'private_key_type' => OPENSSL_KEYTYPE_EC,
                'curve_name'       => 'secp256k1',
                'private_key_bits' => 4096,
                'digest_alg'       => 'sha256',
                'config'           => $cliConfig,  // CLI config has req_extensions with ZATCA SAN
            ]);
            if (!$csr) {
                throw new \Exception('Failed to generate CSR: ' . openssl_error_string());
            }
            openssl_csr_export($csr, $csrPem);
        }

        @unlink($privateTmp);
        @unlink($csrTmp);

        if (empty($privateKeyPem) || empty($csrPem)) {
            throw new \Exception('Failed to generate CSR and private key');
        }

        // ZATCA expects base64 of the full PEM (including headers)
        $csrBase64 = base64_encode($csrPem);

        $this->logMessage('info', 'ZATCA: CSR PEM length=' . strlen($csrPem) . ' base64 length=' . strlen($csrBase64));

        // Store private key in raw form without PEM headers (ZATCA expects raw base64 body)
        $privateKeyRawBody = $this->extractPrivateKeyRawBody($privateKeyPem);

        $this->logMessage('info', 'ZATCA: Private key raw body length=' . strlen($privateKeyRawBody));

        // Encrypt private key before storing
        // $encryptedPrivateKey = $this->encryptPrivateKey($privateKeyPem);

        // Get current environment
        // (already read above for config generation)
        $existingCert = $this->certificatesModel
            ->where('store_id', $storeId)
            ->where('environment', $environment)
            ->first();

        if ($existingCert) {
            $this->certificatesModel->update($existingCert['id'], [
                'csr'        => $csrBase64,
                'private_key' => $privateKeyRawBody,
                'status'     => 'draft',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $certificateId = $existingCert['id'];
        } else {
            $certificateId = $this->certificatesModel->insert([
                'store_id'    => $storeId,
                'environment' => $environment,
                'csr'         => $csrBase64,
                'private_key' => $privateKeyRawBody,
                'status'      => 'draft',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);
        }

        return [
            'csr'            => $csrBase64,
            'private_key'    => $privateKeyPem,
            'certificate_id' => $certificateId,
        ];
    }

    /**
     * Generate CSR from an existing PEM key file using the OpenSSL CLI.
     * PHP already generated the key — CLI only needs to sign the CSR request.
     */
    protected function generateCsrFromKeyViaCli(string $configPath, string $keyPath, string $csrPath)
    {
        $bin = $this->findOpenSslBinary();
        if (!$bin) {
            $this->logMessage('warning', 'ZATCA: OpenSSL CLI not found');
            return false;
        }

        $binNative    = str_replace('/', '\\', $bin);
        $keyNative    = str_replace('/', '\\', realpath($keyPath));
        $configNative = str_replace('/', '\\', $configPath);
        $csrNative    = str_replace('/', '\\', $csrPath);

        $cmd = $binNative
            . ' req -new'
            . ' -key "'    . $keyNative    . '"'
            . ' -config "' . $configNative . '"'
            . ' -out "'    . $csrNative    . '"';

        // OPENSSL_CONF must be set to a valid file; XAMPP's binary has a wrong compiled-in default path
        $result = $this->runShellCommand($cmd, ['OPENSSL_CONF' => $configNative]);
        $this->logMessage('info', 'ZATCA CLI csr-from-key (exit=' . $result['code'] . '): ' . $result['output']);

        if ($result['code'] !== 0 || !file_exists($csrPath) || filesize($csrPath) < 10) {
            $this->logMessage('error', 'ZATCA CLI csr-from-key FAILED. cmd=' . $cmd . ' | output=' . $result['output']);
            return false;
        }
        return true;
    }

    /**
     * Run a shell command using proc_open (works even when exec is disabled).
     * Returns ['output' => string, 'code' => int]
     */
    protected function runShellCommand(string $cmd, array $extraEnv = []): array
    {
        if (function_exists('proc_open')) {
            $desc = [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ];
            // Pass explicit PATH so Apache PHP worker can find openssl DLLs
            $env = [];
            foreach (['SystemRoot', 'WINDIR', 'TEMP', 'TMP', 'ComSpec'] as $k) {
                $v = getenv($k);
                if ($v !== false) $env[$k] = $v;
            }
            $env['PATH'] = 'D:\\xampp\\apache\\bin;D:\\xampp\\php;C:\\xampp\\apache\\bin;C:\\xampp\\php;' . (getenv('PATH') ?: '');
            $env = array_merge($env, $extraEnv);

            $proc = proc_open($cmd, $desc, $pipes, null, $env);
            if (is_resource($proc)) {
                fclose($pipes[0]);
                $stdout = stream_get_contents($pipes[1]);
                $stderr = stream_get_contents($pipes[2]);
                fclose($pipes[1]);
                fclose($pipes[2]);
                $code = proc_close($proc);
                return ['output' => trim($stdout . ' ' . $stderr), 'code' => $code];
            }
        }

        if (function_exists('exec')) {
            $out = [];
            $rc = -1;
            exec($cmd . ' 2>&1', $out, $rc);
            return ['output' => implode("\n", $out), 'code' => $rc];
        }

        return ['output' => 'proc_open and exec both unavailable', 'code' => -1];
    }

    /**
     * PHP openssl fallback: 4-field DN only (no serialNumber/pipes).
     * Used when OpenSSL CLI is not available.
     */
    protected function generateViaPhpOpenSsl(
        string $configPath,
        string $organizationName,
        string $organizationUnit,
        string $cnValue,
        &$privateKeyPem,
        &$csrPem
    ) {
        $keyConfig = [
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name'       => 'secp256k1',
            // PHP 8.x requires private_key_bits >= 384 even for EC keys.
            // Setting 4096 passes PHP's validation; OpenSSL ignores it and uses the curve size (256-bit).
            'private_key_bits' => 4096,
            'config'           => $configPath,
        ];

        $privateKey = openssl_pkey_new($keyConfig);
        if (!$privateKey) {
            throw new \Exception('PHP openssl key gen failed: ' . openssl_error_string());
        }

        openssl_pkey_export($privateKey, $privateKeyPem, null, $keyConfig);

        $dn = [
            'C'  => 'SA',
            'OU' => $organizationUnit,
            'O'  => $organizationName,
            'CN' => $cnValue,
        ];

        $csr = openssl_csr_new($dn, $privateKey, [
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name'       => 'secp256k1',
            'private_key_bits' => 4096,
            'digest_alg'       => 'sha256',
            'config'           => $configPath,
        ]);

        if (!$csr) {
            throw new \Exception('PHP openssl CSR gen failed: ' . openssl_error_string());
        }

        openssl_csr_export($csr, $csrPem);
    }

    /**
     * Find the OpenSSL CLI binary on Windows (XAMPP) or Linux.
     */
    protected function findOpenSslBinary()
    {
        // Known absolute paths on Windows XAMPP — use file_exists, no exec needed for discovery
        $knownPaths = [
            'D:\\xampp\\apache\\bin\\openssl.exe',
            'D:\\xampp\\php\\extras\\openssl\\openssl.exe',
            'C:\\xampp\\apache\\bin\\openssl.exe',
            'C:\\xampp\\php\\extras\\openssl\\openssl.exe',
            '/usr/bin/openssl',
            '/usr/local/bin/openssl',
        ];

        foreach ($knownPaths as $bin) {
            if (file_exists($bin)) {
                $this->logMessage('info', 'ZATCA: Found OpenSSL binary: ' . $bin);
                return $bin;
            }
        }

        // Last resort: try PATH via proc_open
        if (function_exists('proc_open')) {
            $r = $this->runShellCommand('openssl version');
            if ($r['code'] === 0) {
                $this->logMessage('info', 'ZATCA: openssl found in PATH');
                return 'openssl';
            }
        }

        $this->logMessage('error', 'ZATCA: OpenSSL binary not found. Checked: ' . implode(', ', $knownPaths));
        return null;
    }

    /**
     * Store Compliance CSID response from ZATCA
     * 
     * @param int $certificateId Certificate record ID
     * @param array $apiResponse Response from requestComplianceCsid API
     * @return bool Success
     */
    public function storeComplianceCsid(int $certificateId, array $apiResponse): bool
    {
        $updateData = [
            'binary_security_token' => $apiResponse['binarySecurityToken'] ?? null,
            'secret' => $apiResponse['secret'] ?? null,
            'compliance_request_id' => $apiResponse['requestID'] ?? null,
            'status' => 'compliance',
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        return $this->certificatesModel->update($certificateId, $updateData);
    }

    /**
     * Store Production CSID response from ZATCA
     * 
     * @param int $certificateId Certificate record ID
     * @param array $apiResponse Response from requestProductionCsid API
     * @return bool Success
     */
    public function storeProductionCsid(int $certificateId, array $apiResponse): bool
    {
        $updateData = [
            'production_binary_security_token' => $apiResponse['binarySecurityToken'] ?? null,
            'production_secret' => $apiResponse['secret'] ?? null,
            'status' => 'production',
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        return $this->certificatesModel->update($certificateId, $updateData);
    }

    /**
     * Get active certificate for store and environment
     * 
     * @param int $storeId Store ID
     * @param string $environment Environment name
     * @return array|null Certificate data
     */
    public function getActiveCertificate(int $storeId, string $environment)
    {
        return $this->certificatesModel
            ->where('store_id', $storeId)
            ->where('environment', $environment)
            ->where('status', 'production')
            ->first();
    }

    /**
     * Get certificate by ID (for onboarding flow)
     * 
     * @param int $certificateId
     * @return array|null
     */
    public function getCertificateById(int $certificateId)
    {
        return $this->certificatesModel->find($certificateId);
    }

    /**
     * Encrypt private key using AES-256-CBC
     * 
     * @param string $privateKeyPem Private key in PEM format
     * @return string Encrypted + base64 encoded
     */
    // public function encryptPrivateKey(string $privateKeyPem): string
    // {
    //     $key = config('Encryption')->key; // Use CI4 encryption key
    //     $ivLength = openssl_cipher_iv_length($this->config->privateKeyEncryptionAlgo);
    //     $iv = openssl_random_pseudo_bytes($ivLength);

    //     $encrypted = openssl_encrypt(
    //         $privateKeyPem,
    //         $this->config->privateKeyEncryptionAlgo,
    //         $key,
    //         0,
    //         $iv
    //     );

    //     // Prepend IV to encrypted data for later decryption
    //     return base64_encode($iv . $encrypted);
    // }

    /**
     * Decrypt private key
     * 
     * @param string $encryptedPrivateKey Base64-encoded encrypted key
     * @return string Private key in PEM format
     */
    // public function decryptPrivateKey(string $encryptedPrivateKey): string
    // {
    //     $key = config('Encryption')->key;
    //     $ivLength = openssl_cipher_iv_length($this->config->privateKeyEncryptionAlgo);

    //     $data = base64_decode($encryptedPrivateKey);
    //     if ($data === false || $data === '') {
    //         throw new \RuntimeException('Unable to decode the stored private key payload.');
    //     }

    //     $iv = substr($data, 0, $ivLength);
    //     $encrypted = substr($data, $ivLength);

    //     $decrypted = openssl_decrypt(
    //         $encrypted,
    //         $this->config->privateKeyEncryptionAlgo,
    //         $key,
    //         0,
    //         $iv
    //     );

    //     if ($decrypted === false || trim($decrypted) === '') {
    //         throw new \RuntimeException('Unable to decrypt the stored private key. Check the application encryption key and the imported certificate payload.');
    //     }

    //     return $this->normalizePrivateKeyPem($decrypted);
    // }

    /**
     * Normalize a private key string so OpenSSL accepts it consistently.
     *
     * Accepts PEM content, PEM bodies, raw base64 bodies, DER bytes, and pasted PEM blocks
     * that include surrounding text or escaped newlines.
     */
    /**
     * Extract raw base64 private key body without PEM headers.
     * ZATCA expects private keys in raw form: just the base64 body without BEGIN/END lines.
     */
    public function extractPrivateKeyRawBody(string $privateKeyPem): string
    {
        $raw = trim((string) $privateKeyPem);
        if ($raw === '') {
            return '';
        }

        // If it starts with -----BEGIN, it's PEM format - extract the body
        if (strpos($raw, '-----BEGIN') === 0) {
            // Remove all header/footer lines and whitespace
            $body = preg_replace('/-----[^-]*-----/', '', $raw);
            $body = preg_replace('/\s+/', '', (string) $body);
            return $body;
        }

        // Already in raw form, just clean whitespace
        return preg_replace('/\s+/', '', $raw) ?? $raw;
    }

    public function normalizePrivateKeyPem(string $privateKeyPem): string
    {
        $raw = trim((string) $privateKeyPem);
        if ($raw === '') {
            throw new \RuntimeException('Private key is empty.');
        }

        $cleaned = $this->cleanKeyMaterial($raw);
        if ($cleaned === '') {
            throw new \RuntimeException('Private key is empty.');
        }

        $normalized = str_replace(["\r\n", "\r"], "\n", $cleaned);
        $normalized = preg_replace('/\n{3,}/', "\n\n", $normalized) ?? $normalized;

        $candidates = [$normalized];

        $pemBlock = $this->extractPemBlock($normalized);
        if ($pemBlock !== null) {
            $candidates[] = $pemBlock;
        }

        if (preg_match('/-----BEGIN[ \t]+ENCRYPTED PRIVATE KEY-----/i', $normalized) === 1) {
            throw new \RuntimeException('Encrypted PKCS#8 private keys require a passphrase and are not supported without one.');
        }

        $collapsed = preg_replace('/\s+/', '', $normalized) ?? $normalized;
        $candidates[] = $collapsed;

        $decoded = base64_decode($collapsed, true);
        if ($decoded !== false && $decoded !== '') {
            $decodedText = str_replace(["\r\n", "\r"], "\n", trim((string) $decoded));
            $decodedText = preg_replace('/^\xEF\xBB\xBF/', '', $decodedText) ?? $decodedText;

            if (preg_match('/-----BEGIN[^
\n]+-----/i', $decodedText) === 1) {
                $candidates[] = $decodedText;
            }

            $candidates[] = $decoded;
            $candidates[] = $this->wrapPrivateKeyPem($decoded, 'PRIVATE KEY');
            $candidates[] = $this->wrapPrivateKeyPem($decoded, 'EC PRIVATE KEY');
            $candidates[] = $this->wrapPrivateKeyPem($decoded, 'RSA PRIVATE KEY');
        }

        foreach ($candidates as $candidate) {
            $resolved = $this->tryNormalizePrivateKeyCandidate($candidate);
            if ($resolved !== null) {
                return $resolved;
            }
        }

        throw new \RuntimeException('Imported private key is not a valid PEM private key or raw key payload. Please re-import a valid PEM key or a raw EC/RSA private key body.');
    }

    /**
     * Attempt to load a candidate private key using OpenSSL.
     */
    protected function tryNormalizePrivateKeyCandidate($candidate)
    {
        if (!is_string($candidate)) {
            return null;
        }

        $value = trim($candidate);
        if ($value === '') {
            return null;
        }

        foreach (['', null] as $passphrase) {
            $resource = @openssl_pkey_get_private($value, $passphrase);
            if ($resource === false) {
                continue;
            }

            $pem = '';
            $exported = openssl_pkey_export($resource, $pem);
            @openssl_pkey_free($resource);

            if ($exported === false || trim($pem) === '') {
                continue;
            }

            return $pem;
        }

        $tempPem = $this->tryNormalizeFromTempFile($value);
        if ($tempPem !== null) {
            return $tempPem;
        }

        $cliPem = $this->tryNormalizeWithOpenSslCli($value);
        if ($cliPem !== null) {
            return $cliPem;
        }

        return null;
    }

    /**
     * Attempt to normalize the key through a temporary file using PHP OpenSSL.
     */
    protected function tryNormalizeFromTempFile(string $value)
    {
        $tmpDir = dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'writable' . DIRECTORY_SEPARATOR . 'zatca';
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0700, true);
        }

        $tmpFile = $tmpDir . DIRECTORY_SEPARATOR . 'tmp_key_' . uniqid() . '.pem';
        if (file_put_contents($tmpFile, $value) === false) {
            return null;
        }

        $resource = @openssl_pkey_get_private('file://' . $tmpFile);
        @unlink($tmpFile);

        if ($resource === false) {
            return null;
        }

        $pem = '';
        $exported = openssl_pkey_export($resource, $pem);
        @openssl_pkey_free($resource);

        if ($exported === false || trim($pem) === '') {
            return null;
        }

        return $pem;
    }

    /**
     * Attempt to normalize the key using the OpenSSL CLI.
     */
    protected function tryNormalizeWithOpenSslCli(string $value)
    {
        $bin = $this->findOpenSslBinary();
        if (!$bin) {
            return null;
        }

        $tmpDir = dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'writable' . DIRECTORY_SEPARATOR . 'zatca';
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0700, true);
        }

        $tmpFile = $tmpDir . DIRECTORY_SEPARATOR . 'tmp_key_' . uniqid() . '.pem';
        if (file_put_contents($tmpFile, $value) === false) {
            return null;
        }

        $binNative = str_replace('/', '\\', $bin);
        $keyNative = str_replace('/', '\\', $tmpFile);
        $cmd = $binNative . ' pkey -in "' . $keyNative . '" -outform PEM';
        $result = $this->runShellCommand($cmd);
        @unlink($tmpFile);

        if ($result['code'] !== 0) {
            return null;
        }

        $pem = trim((string) $result['output']);
        if (preg_match('/-----BEGIN[ \t]+[A-Z0-9 _-]+KEY-----/i', $pem) !== 1) {
            return null;
        }

        return $pem;
    }

    /**
     * Clean pasted key material by removing wrappers, normalized escapes, and surrounding text.
     */
    protected function cleanKeyMaterial(string $value): string
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return '';
        }

        $trimmed = preg_replace('/^\xEF\xBB\xBF/', '', $trimmed);
        $trimmed = str_replace(['`', '“', '”'], '', $trimmed);
        $trimmed = str_replace(['\\r\\n', '\\n', '\\r'], ["\r\n", "\n", "\r"], $trimmed);
        $trimmed = preg_replace('/^\s*["\']|["\']\s*$/', '', $trimmed) ?? $trimmed;

        if (preg_match('/^\{.*\}$/s', $trimmed) === 1) {
            $decoded = json_decode($trimmed, true);
            if (is_string($decoded)) {
                $trimmed = $decoded;
            }
        }

        $trimmed = preg_replace('/^\s*private\s*key\s*[:=]\s*/i', '', $trimmed) ?? $trimmed;
        $trimmed = preg_replace('/\s*\(.*?\)\s*$/', '', $trimmed) ?? $trimmed;

        return $trimmed;
    }

    /**
     * Extract a PEM block from a larger string containing surrounding text.
     */
    protected function extractPemBlock(string $value)
    {
        if (preg_match('/-----BEGIN[^
\n]+-----[\s\S]*?-----END[^
\n]+-----/i', $value, $matches) === 1) {
            return trim($matches[0]);
        }

        return null;
    }

    /**
     * Wrap a DER/private-key body as PEM using the requested label.
     */
    protected function wrapPrivateKeyPem(string $data, string $label): string
    {
        $base64 = chunk_split(base64_encode($data), 64, "\n");
        return "-----BEGIN {$label}-----\n" . rtrim($base64, "\n") . "\n-----END {$label}-----\n";
    }

    /**
     * Convert PEM to base64 (remove headers/footers)
     */
    protected function pemToBase64(string $pem): string
    {
        $lines = explode("\n", $pem);
        $base64 = '';
        foreach ($lines as $line) {
            if (strpos($line, '-----') === false) {
                $base64 .= trim($line);
            }
        }
        return $base64;
    }

    /**
     * Write two OpenSSL config files:
     *  - openssl_cli.cnf : full ZATCA config (req_extensions, [dn], [alt_names]) for CLI
     *  - openssl_php.cnf : minimal config (legacy provider only) for PHP fallback
     *
     * @return array [cliConfigPath, phpConfigPath]
     */
    protected function writeOpensslConfigs(
        string $cnValue,
        string $orgName,
        string $orgUnit,
        string $serialNumber,
        string $uid,
        string $address,
        string $industry,
        string $environment = 'sandbox'
    ): array {
        $dir = WRITEPATH . 'zatca';
        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }

        $sanitise = function ($v) {
            return preg_replace('/[\r\n\x00-\x1F\x7F]/', '', $v);
        };
        $cnValue      = $sanitise($cnValue);
        $orgName      = $sanitise($orgName);
        $orgUnit      = $sanitise($orgUnit);
        $serialNumber = $sanitise($serialNumber);
        $uid          = $sanitise($uid);
        $address      = $sanitise($address);
        $industry     = $sanitise($industry);

        // ASN.1 organizationName is capped at 64 bytes; truncate multi-byte strings that exceed it
        if (strlen($orgName) > 64) {
            $orgName = mb_strcut($orgName, 0, 64, 'UTF-8');
        }
        if (strlen($orgUnit) > 64) {
            $orgUnit = mb_strcut($orgUnit, 0, 64, 'UTF-8');
        }

        // Environment-specific certificate template name
        // sandbox/non-production  → TSTZATCA-Code-Signing
        // simulation              → PREZATCA-Code-Signing
        // production              → ZATCA-Code-Signing
        switch ($environment) {
            case 'production':
                $templateName = 'ZATCA-Code-Signing';
                break;
            case 'simulation':
                $templateName = 'PREZATCA-Code-Signing';
                break;
            default: // sandbox / non-production
                $templateName = 'TSTZATCA-Code-Signing';
                break;
        }

        // Shared provider header (legacy provider for secp256k1)
        $providerHeader  = "openssl_conf = openssl_init\n\n";
        $providerHeader .= "[openssl_init]\nproviders = provider_sect\n\n";
        $providerHeader .= "[provider_sect]\ndefault = default_sect\nlegacy = legacy_sect\n\n";
        $providerHeader .= "[default_sect]\nactivate = 1\n\n";
        $providerHeader .= "[legacy_sect]\nactivate = 1\n\n";

        // ---- CLI config (full ZATCA structure, prompt=no) ----
        // oid_section MUST come before openssl_conf at the top of the file
        $cli  = "# ZATCA CLI config - environment=$environment - " . date('Y-m-d H:i:s') . "\n";
        $cli .= "oid_section = OIDs\n\n";  // Define custom OID section first
        $cli .= $providerHeader;
        // Define the certificateTemplateName OID (1.3.6.1.4.1.311.20.2 = Microsoft cert template)
        $cli .= "[OIDs]\n";
        $cli .= "certificateTemplateName = 1.3.6.1.4.1.311.20.2\n\n";
        $cli .= "[req]\n";
        $cli .= "default_md = sha256\n";
        $cli .= "distinguished_name = dn\n";
        $cli .= "req_extensions = req_ext\n";
        $cli .= "prompt = no\n";
        $cli .= "string_mask = utf8only\n\n";
        $cli .= "[dn]\n";
        $cli .= "C = SA\n";
        $cli .= "OU = " . $orgUnit . "\n";
        $cli .= "O = " . $orgName . "\n";
        $cli .= "CN = " . $cnValue . "\n\n";
        $cli .= "[req_ext]\n";
        $cli .= "certificateTemplateName = ASN1:PRINTABLESTRING:" . $templateName . "\n";
        $cli .= "subjectAltName = dirName:alt_names\n\n";
        $cli .= "[alt_names]\n";
        $cli .= "SN = " . $serialNumber . "\n";
        $cli .= "UID = " . $uid . "\n";
        $cli .= "title = 1100\n";
        $cli .= "registeredAddress = " . $address . "\n";
        $cli .= "businessCategory = " . $industry . "\n";

        // ---- PHP config (minimal: legacy provider only, no req_extensions) ----
        $php  = "# ZATCA PHP fallback config - " . date('Y-m-d H:i:s') . "\n";
        $php .= $providerHeader;
        $php .= "[req]\n";
        $php .= "default_md = sha256\n";
        $php .= "distinguished_name = req_distinguished_name\n";
        $php .= "string_mask = utf8only\n\n";
        $php .= "[req_distinguished_name]\n";
        $php .= "countryName = Country Name\n";
        $php .= "organizationName = Organization Name\n";
        $php .= "organizationalUnitName = Organizational Unit Name\n";
        $php .= "commonName = Common Name\n";

        $cliPath = $dir . DIRECTORY_SEPARATOR . 'openssl_cli.cnf';
        $phpPath = $dir . DIRECTORY_SEPARATOR . 'openssl_php.cnf';

        if (file_put_contents($cliPath, $cli) === false) {
            throw new \Exception('Failed to write CLI OpenSSL config');
        }
        if (file_put_contents($phpPath, $php) === false) {
            throw new \Exception('Failed to write PHP OpenSSL config');
        }

        $cliAbs = str_replace('\\', '/', realpath($cliPath));
        $phpAbs = str_replace('\\', '/', realpath($phpPath));

        $this->logMessage('info', 'ZATCA: CLI config: ' . $cliAbs);
        $this->logMessage('info', 'ZATCA: PHP config: ' . $phpAbs);

        return [$cliAbs, $phpAbs];
    }

    protected function mapEnvironmentToBuilderEnvironment(string $environment): string
    {
        $normalized = strtolower(trim($environment));
        if ($normalized === 'production') {
            return CertificateBuilder::ENV_PRODUCTION;
        }
        if ($normalized === 'simulation') {
            return CertificateBuilder::ENV_SIMULATION;
        }

        return CertificateBuilder::ENV_NONPROD;
    }

    protected function mapInvoiceTypeToBitmask(string $invoiceType): string
    {
        $type = trim($invoiceType);
        if ($type === '0') {
            return '0100';
        }
        if ($type === '1') {
            return '1000';
        }

        return '1100';
    }
}
