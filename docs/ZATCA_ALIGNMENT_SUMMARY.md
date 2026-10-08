# ZATCA 6-Step Alignment Implementation - Complete Summary

**Status**: ✅ COMPLETED  
**Date**: Current Session  
**Target**: Fix simplified invoice validation errors by aligning with ZATCA official 6-step signing process

---

## Problem Statement

Simplified invoices were failing ZATCA validation with two critical errors:

1. **certificate-hashing** - Certificate digest mismatch
2. **signed-properties-hashing** - SignedProperties digest mismatch

The root cause was using **exclusive C14N** (exclusive canonicalization) when ZATCA's official specification requires **C14N11** (inclusive canonicalization).

---

## ZATCA Official 6-Step Signing Process

According to ZATCA documentation:

| Step | Operation                                                   | Algorithm                 | Output                               |
| ---- | ----------------------------------------------------------- | ------------------------- | ------------------------------------ |
| 1    | Hash invoice content (exclude UBLExtensions, Signature, QR) | **C14N11** + SHA-256      | Base64-encoded invoice hash          |
| 2    | Sign the binary hash with private key                       | ECDSA-SHA256              | Binary signature (DER format)        |
| 3    | Hash the X.509 certificate bytes                            | SHA-256                   | Base64-encoded certificate hash      |
| 4    | Populate SignedProperties XML with all cert info            | —                         | Populated XML element                |
| 5    | Hash the populated SignedProperties                         | **C14N11** + SHA-256      | Base64-encoded SignedProperties hash |
| 6    | Build SignedInfo, canonicalize, and sign                    | **C14N11** + ECDSA-SHA256 | Complete XAdES signature             |

---

## Implementation Changes

### File Modified

`app/Services/ZatcaInvoiceService.php` - `embedXadesSignature()` method

### Change 1: SignedProperties XML Structure (Step 4)

**Before** (Heredoc with indentation):

```php
$signedPropsXml = <<<XML
<xades:SignedProperties xmlns:xades="http://uri.etsi.org/01903/v1.3.2#" Id="xadesSignedProperties">
    <xades:SignedSignatureProperties>
        <xades:SigningTime>$signingTime</xades:SigningTime>
        ...
    </xades:SignedSignatureProperties>
</xades:SignedProperties>
XML;
```

**After** (Concatenated strings, no whitespace):

```php
$signedPropsXml = '<xades:SignedProperties xmlns:xades="http://uri.etsi.org/01903/v1.3.2#" Id="xadesSignedProperties">'
    . '<xades:SignedSignatureProperties>'
    . '<xades:SigningTime>' . htmlspecialchars($signingTime, ENT_XML1) . '</xades:SigningTime>'
    . ...
    . '</xades:SignedProperties>';
```

**Rationale**: Heredoc includes extra whitespace/indentation that affects C14N11 canonicalization. Concatenation ensures exact XML structure ZATCA expects.

---

### Change 2: SignedProperties Hash Canonicalization (Step 5)

**Before**:

```php
$signedPropsCanon = $dom->C14N(true, false);  // Exclusive C14N
$signedPropsHash  = base64_encode(hash('sha256', $signedPropsCanon, true));
```

**After**:

```php
$signedPropsCanon = $dom->C14N(false, false);  // Inclusive C14N11
$signedPropsHash  = base64_encode(hash('sha256', $signedPropsCanon, true));
```

**Rationale**: ZATCA spec requires C14N11 (inclusive). PHP's `C14N(false, false)` = C14N11.

- `C14N(true, false)` = Exclusive C14N (http://www.w3.org/2001/10/xml-exc-c14n#)
- `C14N(false, false)` = Inclusive C14N11 (http://www.w3.org/2006/12/xml-c14n11)

---

### Change 3: SignedInfo CanonicalizationMethod Algorithm (Step 6)

**Before**:

```xml
<ds:CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>
```

**After**:

```xml
<ds:CanonicalizationMethod Algorithm="http://www.w3.org/2006/12/xml-c14n11"/>
```

**Rationale**: Algorithm URI must match the actual canonicalization method being used.

---

### Change 4: SignedInfo Canonicalization for Signing (Step 6)

**Before**:

```php
$signedInfoCanon = $siDom->C14N(true, false);  // Exclusive C14N
```

**After**:

```php
$signedInfoCanon = $siDom->C14N(false, false);  // Inclusive C14N11
```

**Rationale**: The canonicalization method for computing the signature must match the declared algorithm.

---

## Alignment Verification

### Step-by-Step Verification Table

| Step | Component             | Implementation                                      | Canonicalization            | Status     |
| ---- | --------------------- | --------------------------------------------------- | --------------------------- | ---------- |
| 1    | Invoice Hash          | `computeHash()` method                              | C14N(false, false) = C14N11 | ✅ CORRECT |
| 2    | Digital Signature     | `openssl_sign()` with OPENSSL_ALGO_SHA256           | N/A                         | ✅ CORRECT |
| 3    | Certificate Hash      | `extractCertificateDer()` + SHA-256                 | N/A                         | ✅ CORRECT |
| 4    | SignedProperties XML  | Concatenated strings, no whitespace                 | N/A                         | ✅ FIXED   |
| 5    | SignedProperties Hash | C14N(false, false) + SHA-256                        | C14N11                      | ✅ FIXED   |
| 6    | SignedInfo            | Algorithm = C14N11, Signing with C14N(false, false) | C14N11                      | ✅ FIXED   |

---

## Certificate Processing Pipeline

The implementation correctly handles certificate tokens in multiple formats:

```
Input Token (various formats)
  ↓
extractCertificateDer()  ← Detects format (PEM/base64/DER) and returns raw DER bytes
  ↓
Raw DER Bytes (binary)
  ├→ Step 3: Hash with SHA-256 → $certHash (for SigningCertificate)
  └→ normalizeCertificateTokenBase64() → Base64-encoded for X509Certificate element
```

Supported input formats:

- PEM-wrapped certificates (-----BEGIN CERTIFICATE-----)
- Base64-encoded DER
- Double base64-encoded (base64(base64(DER)))
- Raw DER bytes

---

## Code Quality Checks

✅ **PHP Syntax Validation**: `php -l app/Services/ZatcaInvoiceService.php`

- Result: No syntax errors detected

✅ **Canonicalization Consistency**:

- All C14N(false, false) calls use inclusive C14N11
- All C14N(true, false) calls use exclusive C14N (if any remain, verify intentionally)
- Algorithm declarations match implementation

✅ **XML Escaping**:

- All dynamic values in XML use `htmlspecialchars($value, ENT_XML1)`
- Digest values properly escaped
- Issuer names properly escaped

✅ **Element Ordering**:

- SignedProperties structure matches ZATCA specification order
- Reference elements in correct order
- Namespace declarations present and correct

---

## Expected Outcomes After Deployment

### Simplified Invoices (B2C)

- ✅ No "certificate-hashing" validation error
- ✅ No "signed-properties-hashing" validation error
- ✅ reportingStatus = "REPORTED" on successful submission
- ✅ ZATCA accepts invoice for reporting phase

### Standard Invoices (B2B)

- ✅ Already working (clearanceStatus = "CLEARED")
- ✅ No regression expected
- ✅ Continue to pass validation

---

## Testing Recommendations

### Manual Testing Steps

1. **Create a simplified invoice** via POS web UI
2. **Monitor ZATCA response** for validation results
3. **Verify success conditions**:
   - No error messages in validationResults
   - reportingStatus = "REPORTED"
   - clearanceStatus field (if present) appropriate
4. **Check server logs** for any signing errors

### Validation Checklist

- [ ] Simplified invoice submits without certificate-hashing error
- [ ] Simplified invoice submits without signed-properties-hashing error
- [ ] ZATCA response contains reportingStatus = "REPORTED"
- [ ] Sale status updates to "reported" in database
- [ ] Flash message displays success message
- [ ] Standard invoices continue to clear normally

### Rollback Plan

If issues occur:

1. Revert to previous implementation (use exclusive C14N)
2. Check ZATCA response for specific error messages
3. Compare canonicalization outputs with official samples
4. Verify certificate extraction is returning raw DER bytes

---

## Reference Documentation

### ZATCA Signing Documentation

- Step 1: Invoice hash with C14N11 and SHA-256
- Steps 2-6: Digital signature with XAdES-BES format
- SignedProperties must include cert hash, signing time, issuer, serial

### W3C XML Canonicalization

- C14N11 (Inclusive): http://www.w3.org/2006/12/xml-c14n11
- Exclusive C14N: http://www.w3.org/2001/10/xml-exc-c14n#
- PHP mappings: C14N(false, false) = C14N11, C14N(true, false) = Exclusive

### XAdES-BES Specification

- QualifyingProperties with SignedProperties
- SigningTime in UTC ISO 8601 format
- SigningCertificate with CertDigest reference
- IssuerSerial with X509IssuerName and X509SerialNumber

---

## Deployment Checklist

- [x] Code changes implemented
- [x] PHP syntax validated
- [x] Canonicalization methods aligned
- [x] Certificate processing verified
- [x] XML escaping implemented
- [ ] Manual testing completed
- [ ] ZATCA validation successful
- [ ] Production deployment ready

---

**Implementation Date**: Current Session  
**Status**: Ready for Testing  
**Files Modified**: 1 (ZatcaInvoiceService.php)  
**Lines Changed**: ~50 (embedXadesSignature method)  
**Breaking Changes**: None (backward compatible)
