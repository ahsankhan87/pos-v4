# ZATCA CSR Format Fix - Based on Official Documentation

## Changes Made (2026-07-31)

### Issue

The CSR generation didn't match the official ZATCA sandbox example format.

### Root Cause Analysis

By decoding the official ZATCA sample CSR from their documentation:

```bash
curl -X POST 'https://gw-fatoora.zatca.gov.sa/e-invoicing/developer-portal/compliance' \
  -H 'OTP: 123345' \
  -H 'Accept-Version: V2' \
  -d '{"csr": "LS0tLS1CRUdJTi..."}'
```

**Official ZATCA CSR Structure:**

- **DN Fields:** Only 4 basic fields: C, OU, O, CN
- **CN Format:** `TST-{VAT}-{uniqueSerial}` (e.g., `TST-886431145-399999999900003`)
- **Encoding:** Base64 of FULL PEM (including `-----BEGIN CERTIFICATE REQUEST-----` headers)
- **Extensions:** Additional ZATCA fields (serialNumber, UID, title, etc.) are in CSR extensions, not main DN

**Our Previous Mistakes:**

1. ❌ Added too many fields directly to DN (serialNumber, UID, title, etc.)
2. ❌ Stripped PEM headers before base64 encoding via `pemToBase64()`
3. ❌ CN format didn't match ZATCA pattern

---

## Files Changed

### 1. `app/Services/ZatcaCertificateService.php`

**Before:**

```php
$dn = [
    'C' => 'SA',
    'OU' => $organizationUnit,
    'O' => $organizationName,
    'CN' => $commonName,
    'serialNumber' => '1-TST|2-TST|3-...',
    'UID' => $vatNumber,
    'title' => '1100',
    'registeredAddress' => $location,
    'businessCategory' => $industry,
];

$csrBase64 = $this->pemToBase64($csrPem); // Strips headers
```

**After:**

```php
// Generate device serial: 1-TST|2-TST|3-{guid}
$deviceSerial = '1-TST|2-TST|3-' . bin2hex(random_bytes(16));

// Format CN: TST-{VAT}-{uniqueSerial}
$cnValue = 'TST-' . $vatNumber . '-' . str_replace('-', '', $deviceSerial);

$dn = [
    'C' => 'SA',                 // Country
    'OU' => $organizationUnit,   // Organization Unit
    'O' => $organizationName,    // Organization
    'CN' => $cnValue,            // Common Name (ZATCA format)
];

$csrBase64 = base64_encode($csrPem); // Full PEM with headers
```

### 2. `app/Config/Zatca.php`

**Confirmed:**

- Endpoint: `/compliance` ✅ (correct, matches official example)
- Headers: `OTP`, `Accept-Version: V2` ✅ (already implemented)
- Payload: `{"csr": "base64..."}` ✅ (already correct)

---

## Testing Steps

### 1. **Restart Apache** (clear config cache)

```
XAMPP Control Panel → Stop Apache → Wait 2s → Start Apache
```

### 2. **Regenerate CSR**

- Go to: **Settings → ZATCA Setup Wizard**
- Click **"Generate CSR"** (Step 1)
- ✅ New CSR will have correct format

### 3. **Request Compliance CSID**

- Enter OTP: `123345` (from ZATCA portal)
- Click **"Request Compliance CSID"** (Step 2)

---

## Expected Log Output

Check `writable/logs/log-2026-07-31.log` after Step 1:

```
ZATCA: CSR DN fields: {"C":"SA","OU":"Riyadh Branch","O":"...","CN":"TST-310122393500003-1TST2TST3..."}
ZATCA: Device Serial: 1-TST|2-TST|3-abc123...
ZATCA: CN: TST-310122393500003-1TST2TST3abc123...
ZATCA: Generated CSR - PEM length: 580, Base64 length: 776
ZATCA: CSR starts with: -----BEGIN CERTIFICATE REQUEST-----
```

---

## Expected Results

### ✅ Success (HTTP 200)

```json
{
  "binarySecurityToken": "MIID...",
  "secret": "xyz...",
  "requestID": "12345"
}
```

→ Step 2 complete, proceed to Step 3 (Compliance Checks)

### ❌ Failure Scenarios

**400 Bad Request with validation errors:**

```json
{
  "errors": [{ "field": "csr", "message": "Invalid CSR format" }]
}
```

→ Check logs for CSR content, verify PEM format

**401 Unauthorized:**

```
OTP is invalid or expired
```

→ Generate new OTP from ZATCA portal

**404 Not Found:**

```
No resources match requested URI
```

→ Verify environment is "sandbox" and Apache was restarted

---

## Next Steps After Success

Once Step 2 succeeds:

1. **Step 3: Run Compliance Checks**
   - Submit sample invoices to ZATCA compliance API
   - Validate XML structure and signatures
   - Must pass all checks before production

2. **Step 4: Get Production CSID**
   - Exchange compliance certificate for production credentials
   - Only enabled after Step 3 passes

3. **Phase 4: Invoice Generation**
   - Build UBL 2.1 XML invoices
   - Implement cryptographic signing
   - Generate QR codes

---

## Technical Notes

### Why Extensions Matter

The official ZATCA CSR includes these fields in **X.509 extensions** (not main DN):

- `serialNumber` (1-TST|2-TST|3-guid)
- `UID` (VAT number: 399999999900003)
- `title` (Invoice type: 1100)
- `registeredAddress` (Location: RRRD2929)
- `businessCategory` (Industry: Supply activities)

These are added via OpenSSL config's `req_extensions` section. PHP's `openssl_csr_new()` doesn't easily support custom OID extensions without a complex config file.

**Current Approach:**

- Basic DN fields match official format
- CN contains device identifier in ZATCA pattern
- Future enhancement: Add proper extensions via custom OpenSSL config

### CSR Encoding

ZATCA expects **base64 encoding of the full PEM string**, including headers:

```
base64_encode("-----BEGIN CERTIFICATE REQUEST-----\nMIIC...\n-----END CERTIFICATE REQUEST-----\n")
```

NOT stripped base64:

```
base64_encode("MIIC...") // ❌ Wrong
```

---

## Verification Checklist

Before testing:

- [ ] Apache restarted
- [ ] Browser cache cleared (or use Incognito)
- [ ] OTP is fresh (not expired)
- [ ] Environment set to "sandbox" in Settings
- [ ] Old CSR deleted (will be regenerated)

After CSR generation:

- [ ] Log shows 4 DN fields (C, OU, O, CN)
- [ ] CN matches pattern: TST-{VAT}-{serial}
- [ ] CSR starts with "-----BEGIN CERTIFICATE REQUEST-----"
- [ ] Base64 length > 700 chars

After CSID request:

- [ ] No 404 errors (endpoint correct)
- [ ] No 400 errors (format correct)
- [ ] Got binarySecurityToken and secret
- [ ] Database record created in pos_zatca_certificates

---

**Status:** Ready for testing
**Next Action:** Restart Apache → Regenerate CSR → Test with OTP 123345
