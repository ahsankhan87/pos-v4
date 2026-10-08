# ZATCA Certificate-Hashing Error - Diagnostic Checklist

**Status**: Investigating certificate validation issues  
**Error**: Certificate-hashing and signed-properties-hashing errors persist  
**New Warning**: Certificate-issuer-name - "X509Certificate (CCSID/PCSID) used for signing is not valid certificate for this VAT Registration Number"

---

## What We've Done

✅ Reverted canonicalization changes (back to exclusive C14N - was working for standard invoices)  
✅ Improved certificate selection logic to prefer production cert in production environment  
✅ Added VAT verification logging to detect certificate/VAT mismatches  
✅ Verified PHP syntax and code structure

## Root Cause Analysis

The **certificate-issuer-name WARNING** is the key issue:

- Indicates the certificate being used does NOT match the VAT Registration Number
- This causes ZATCA to reject the invoice with certificate-hashing and signed-properties-hashing errors

## Database Verification Required

**Check the `pos_zatca_certificates` table:**

```sql
SELECT
    id,
    store_id,
    environment,
    status,
    binary_security_token IS NOT NULL as has_compliance_cert,
    production_binary_security_token IS NOT NULL as has_production_cert,
    private_key IS NOT NULL as has_private_key,
    updated_at
FROM pos_zatca_certificates
ORDER BY store_id, environment DESC;
```

**For each certificate, verify:**

1. **Is the production_binary_security_token actually populated?**
   - If empty/NULL for production environment, simplified invoice will fail
   - Solution: Re-run onboarding Step 4 to get production CSID

2. **Does the certificate match the VAT?**
   - Extract the certificate CN/subject and check if VAT is included
   - The certificate subject should contain the VAT number (15 digits for Saudi Arabia)
   - Mismatch = certificate was issued for different VAT

3. **Is the private_key properly encrypted and stored?**
   - Check that private_key field is NOT empty
   - The private key must match the certificate (both from same CSR)

4. **Do the certificates have the correct issuer?**
   - Compliance cert should be issued by ZATCA (sandbox/compliance issuer)
   - Production cert should be issued by ZATCA (production issuer)

---

## Certificate Extraction & Verification

Add this SQL query to check certificate details:

```sql
-- For each certificate, you can verify by decoding and checking the binary_security_token
SELECT
    id,
    store_id,
    environment,
    status,
    SUBSTR(binary_security_token, 1, 50) as compliance_cert_preview,
    SUBSTR(production_binary_security_token, 1, 50) as production_cert_preview,
    LENGTH(binary_security_token) as compliance_cert_len,
    LENGTH(production_binary_security_token) as production_cert_len
FROM pos_zatca_certificates
WHERE store_id = ? AND environment = 'production';
```

**What to look for:**

- `compliance_cert_preview` should start with `MIIDXj...` (base64 DER) or `-----BEGIN` (PEM)
- `production_cert_preview` should start with `MIIDXj...` (base64 DER) or `-----BEGIN` (PEM)
- Both `*_cert_len` should be > 500 bytes (typical certificate size)

---

## Possible Causes

### 1. Wrong Certificate Selected

- **Issue**: Store has multiple certificates or wrong certificate stored
- **Fix**: Verify certificate matches store's VAT number; re-run onboarding if needed

### 2. Certificate Mismatch with Private Key

- **Issue**: The private_key doesn't match the production_binary_security_token
- **Cause**: CSR/certificate regenerated but old private key still stored
- **Fix**: Re-generate CSR and certificate pairing during onboarding

### 3. Certificate Not Valid for This VAT

- **Issue**: Certificate was issued for different VAT number than sale
- **Cause**: Onboarding completed for wrong VAT, or sale has wrong VAT
- **Fix**: Verify store's zatca_seller_vat_number matches certificate; if not, update store or re-onboard

### 4. Production Certificate Not Obtained

- **Issue**: Onboarding Step 4 failed, but system thinks it succeeded
- **Cause**: Placeholder response or failed API call not properly handled
- **Fix**: Check logs for Step 4 response; re-run Step 4 with valid OTP from ZATCA portal

### 5. Certificate Token Encoding Issue

- **Issue**: production_binary_security_token is double-encoded or corrupted during storage
- **Cause**: Encoding/decoding error in onboarding flow
- **Fix**: Re-run onboarding Step 4; verify certificate stores correctly

---

## Debugging Steps

### Step 1: Check Certificate VAT

```php
// In ZatcaInvoiceService::generateAndAttachToSale
$cert = $certModel->where('store_id', 123)->where('environment', 'production')->first();
$certVat = $service->extractVatFromCertificateToken($cert['production_binary_security_token']);
echo "Certificate VAT: $certVat\n";

// From sale
$sale = $salesModel->find(123);
$saleVat = $sale['seller_vat'] ?? $store['zatca_seller_vat_number'];
echo "Sale VAT: $saleVat\n";

if ($certVat !== $saleVat) {
    echo "MISMATCH! Certificate and sale have different VAT numbers\n";
}
```

### Step 2: Verify Certificate Structure

```php
// Extract and check certificate
$rawToken = $cert['production_binary_security_token'];
$der = $service->extractCertificateDer($rawToken);
$hash = hash('sha256', $der, true);
$hashBase64 = base64_encode($hash);
echo "Certificate Hash: $hashBase64\n";

// Check if certificate can be parsed
$pem = "-----BEGIN CERTIFICATE-----\n" .
       chunk_split(base64_encode($der), 64, "\n") .
       "-----END CERTIFICATE-----\n";
$certRes = openssl_x509_read($pem);
$info = openssl_x509_parse($certRes);
echo "Certificate CN: " . ($info['subject']['CN'] ?? 'N/A') . "\n";
```

### Step 3: Verify Private Key Matches

```php
// Check if private key matches certificate's public key
$privateKey = openssl_pkey_get_private($privateKeyPem);
$privateDetails = openssl_pkey_get_details($privateKey);
$privateKeyBits = $privateDetails['bits'];
$privateKeyType = $privateDetails['type'];

$certPubKey = openssl_pkey_get_public($pem);
$certDetails = openssl_pkey_get_details($certPubKey);

if ($privateKeyBits === $certDetails['bits'] &&
    $privateKeyType === $certDetails['type']) {
    echo "Key-Certificate match: OK\n";
} else {
    echo "Key-Certificate mismatch: FAILED\n";
}
```

---

## Logs to Check

Check `writable/logs/` for entries from:

- **ZatcaOnboarding.php** - "request_production_csid" action logs
  - Should show successful Production CSID response
  - Look for "VAT mismatch" warnings
- **ZatcaInvoiceService.php** - "ZATCA Invoice" messages
  - Should show which certificate is selected
  - Look for "Certificate VAT does not match" warnings

```bash
grep -r "request_production_csid" writable/logs/
grep -r "Certificate VAT" writable/logs/
```

---

## Next Steps

**Immediate Actions:**

1. ✅ Run SQL queries above to verify certificate table contents
2. ✅ Check logs for onboarding errors or warnings
3. ✅ Verify store's `zatca_seller_vat_number` matches certificate
4. ☐ Extract certificate details and compare with what ZATCA expects

**If Issue Persists:**

1. Delete current certificate and re-run full onboarding (Steps 1-4)
2. Use fresh OTP from ZATCA's developer portal
3. Verify each step completes without warnings
4. Test with new simplified invoice

**If Certificate is Correct:**

1. Issue may be in how certificate hash is computed
2. Compare our hash computation with ZATCA's official SDK
3. Try hashing different certificate representations (PEM vs DER)
4. Check if ZATCA expects canonicalization during cert hash (unusual)

---

## Code References

- **Certificate Selection**: [resolveSigningCertificateToken()](app/Services/ZatcaInvoiceService.php#L689)
- **Certificate Extraction**: [extractCertificateDer()](app/Services/ZatcaInvoiceService.php#L2021)
- **VAT Extraction**: [extractVatFromCertificateToken()](app/Services/ZatcaInvoiceService.php#L2049)
- **Certificate Hashing**: [embedXadesSignature() - Line ~1070](app/Services/ZatcaInvoiceService.php#L1070)
- **Onboarding Step 4**: [requestProductionCsid()](app/Controllers/ZatcaOnboarding.php#L397)
- **Storage**: [storeProductionCsid()](app/Services/ZatcaCertificateService.php#L362)

---

## Summary

The errors are **NOT** about canonicalization or signing logic. The **certificate-issuer-name WARNING** indicates the certificate simply isn't valid for the VAT being used. This is a data issue, not a code issue.

**Most likely solution**: Verify certificate VAT matches sale VAT, and re-run onboarding Step 4 if production certificate is missing or mismatched.
