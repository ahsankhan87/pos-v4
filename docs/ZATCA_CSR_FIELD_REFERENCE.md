# ZATCA CSR Generation - Field Mapping Reference

## Updated: 2026-07-31 (Based on Official ZATCA C# SDK Pattern)

### CSR Distinguished Name (DN) Fields

```php
// ZATCA Required Pattern (from C# SDK)
[
    'C'                  => 'SA',                           // countryName
    'OU'                 => 'Riyadh Branch',                // organizationUnitName (Branch)
    'O'                  => 'Company Legal Name',           // organizationName
    'CN'                 => 'TST-2050012095-300589284900003', // commonName (TST-{TIN}-{VAT})
    'serialNumber'       => '1-TST|2-TST|3-{GUID}',        // Device serial
    'UID'                => '300589284900003',              // organizationIdentifier (VAT)
    'title'              => '1100',                         // invoiceType (1100 = Standard & Simplified)
    'registeredAddress'  => 'Makka',                        // locationAddress
    'businessCategory'   => 'Medical Laboratories',         // industryCategory
]
```

### Field Details

| Field                     | OID                       | Value                  | Source                    | Required |
| ------------------------- | ------------------------- | ---------------------- | ------------------------- | -------- |
| C (countryName)           | 2.5.4.6                   | "SA"                   | Hardcoded                 | ✅       |
| OU (organizationUnitName) | 2.5.4.11                  | Branch name            | session('store_name')     | ✅       |
| O (organizationName)      | 2.5.4.10                  | Legal entity           | zatca_seller_name setting | ✅       |
| CN (commonName)           | 2.5.4.3                   | TST-{TIN}-{VAT}        | Auto-generated            | ✅       |
| serialNumber              | 2.5.4.5                   | 1-TST\|2-TST\|3-{GUID} | Random GUID               | ✅       |
| UID                       | 0.9.2342.19200300.100.1.1 | 15-digit VAT           | zatca_seller_vat_number   | ✅       |
| title                     | 2.5.4.12                  | "1100"                 | Invoice type code         | ✅       |
| registeredAddress         | 2.5.4.26                  | Location               | zatca_seller_address      | ✅       |
| businessCategory          | 2.5.4.15                  | Industry               | zatca_seller_industry     | ✅       |

---

## Common Name (CN) Format

### Pattern: `TST-{TIN}-{VAT}`

**Example:**

- VAT Number: `300589284900003` (15 digits)
- TIN: `2050012095` (first 10 digits of VAT)
- Result: `TST-2050012095-300589284900003`

**PHP Implementation:**

```php
$tin = substr($vatNumber, 0, 10);
$cnValue = 'TST-' . $tin . '-' . $vatNumber;
```

---

## Device Serial Format

### Pattern: `1-TST|2-TST|3-{GUID}`

**Example:**

```
1-TST|2-TST|3-A1B2C3D4E5F6G7H8
```

**PHP Implementation:**

```php
$deviceSerial = '1-TST|2-TST|3-' . strtoupper(bin2hex(random_bytes(16)));
```

---

## Invoice Type Codes

| Code | Description                  |
| ---- | ---------------------------- |
| 0100 | B2C Simplified               |
| 0200 | B2B Simplified               |
| 1000 | B2C Standard                 |
| 1100 | Standard & Simplified (Both) |

Default: **1100** (supports both B2B and B2C)

---

## CSR Encoding

ZATCA expects **base64 encoding of the full PEM string** (including headers):

```php
$csrBase64 = base64_encode($csrPem);
// Includes: -----BEGIN CERTIFICATE REQUEST----- ... -----END CERTIFICATE REQUEST-----
```

**Example:**

```
LS0tLS1CRUdJTiBDRVJUSUZJQ0FURSBSRVFVRVNULS0tLS0KTUlJQ0ZUQ0NBYndDQVFBd2RU...
```

Decodes to:

```
-----BEGIN CERTIFICATE REQUEST-----
MIICFTCCAbwCAQAwdTELMAkGA1UEBhMCU0ExFjAUBgNVBAsMDVJpeWFkaCBCcmFu
...
-----END CERTIFICATE REQUEST-----
```

---

## API Request Format

### Endpoint

```
POST https://gw-fatoora.zatca.gov.sa/e-invoicing/developer-portal/compliance
```

### Headers

```json
{
  "Accept": "application/json",
  "Accept-Version": "V2",
  "OTP": "123345",
  "Content-Type": "application/json"
}
```

### Payload

```json
{
  "csr": "LS0tLS1CRUdJTiBDRVJUSUZJQ0FURSBSRVFVRVNULS0tLS0K..."
}
```

---

## Implementation Checklist

Before generating CSR:

- [x] VAT number (15 digits) in settings
- [x] Organization name in settings
- [x] Store/branch name available
- [x] Location/address in settings
- [x] Industry category in settings
- [x] OpenSSL 3.x legacy provider enabled

After CSR generation:

- [x] CN matches pattern: TST-{10digits}-{15digits}
- [x] serialNumber has format: 1-TST|2-TST|3-{GUID}
- [x] UID equals VAT number
- [x] CSR is base64-encoded PEM (with headers)
- [x] CSR length > 700 characters

---

## Testing

### 1. Generate CSR

```bash
# Check logs after generation:
tail -f writable/logs/log-2026-07-31.log | grep "ZATCA: CSR"
```

Expected output:

```
ZATCA: CSR Generation Parameters:
  - VAT Number: 300589284900003
  - TIN (extracted): 2050012095
  - Common Name: TST-2050012095-300589284900003
  - Device Serial: 1-TST|2-TST|3-A1B2C3D4E5F6G7H8
  - DN Fields: {"C":"SA","OU":"...","O":"...","CN":"TST-...","serialNumber":"1-TST|2-TST|3-...","UID":"300589284900003",...}
```

### 2. Request Compliance CSID

Check request/response in logs:

```
=== ZATCA COMPLIANCE CSID REQUEST ===
ZATCA: URL: https://gw-fatoora.zatca.gov.sa/e-invoicing/developer-portal/compliance
ZATCA: CSR first 80 chars: LS0tLS1CRUdJTiBDRVJUSUZJQ0FURSBSRVFVRVNULS0tLS0K...

=== ZATCA COMPLIANCE CSID RESPONSE ===
ZATCA: Response Status: 200 or 400
ZATCA: Response Body: {...}
```

---

## Common Issues

### ❌ Error: "Invalid CSR format"

**Cause:** CSR not in correct PEM format or encoding wrong  
**Fix:** Ensure base64_encode() of full PEM string (with headers)

### ❌ Error: "Missing required fields"

**Cause:** One or more DN fields missing or wrong  
**Fix:** Check all 9 fields are present in DN array

### ❌ Error: "Invalid organization identifier"

**Cause:** VAT number format wrong  
**Fix:** Ensure 15-digit numeric VAT number

### ❌ Error: "Invalid common name"

**Cause:** CN doesn't match TST-{TIN}-{VAT} pattern  
**Fix:** Verify TIN is first 10 digits of VAT

---

## References

- Official ZATCA C# SDK (field patterns)
- ZATCA Sandbox Portal: https://sandbox.zatca.gov.sa/
- API Documentation: https://zatca.gov.sa/en/E-Invoicing/SystemsDevelopers/
- Sample curl command (from ZATCA docs)

---

**Last Updated:** 2026-07-31  
**Status:** ✅ Implemented and ready for testing
