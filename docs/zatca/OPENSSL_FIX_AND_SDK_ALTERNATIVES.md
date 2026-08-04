# ZATCA Integration - OpenSSL Fix & SDK Alternatives

## Current Status: ✅ OpenSSL Fix Applied

The EC private key generation now works correctly with OpenSSL 3.x. Test confirms secp256k1 support is functional.

### What Was Fixed

**Problem:** OpenSSL 3.0+ deprecated the secp256k1 curve to a "legacy provider" that must be explicitly enabled.

**Solution:**

1. Auto-creates custom OpenSSL config at `writable/zatca/openssl.cnf`
2. Enables both default and legacy providers
3. Uses this config for ALL cryptographic operations (key generation + CSR)

### Next Steps

1. **Restart Apache** to ensure PHP loads the updated service file:

   ```bash
   # In XAMPP Control Panel, click "Stop" then "Start" for Apache
   ```

2. **Clear Browser Cache** and try the "Generate CSR" button again

3. **Check Logs** at `writable/logs/log-2026-07-30.log` - you should see:
   ```
   INFO - ZATCA: Created custom OpenSSL config at D:\xampp\htdocs\kasbook\pos-v4\writable\zatca\openssl.cnf
   INFO - ZATCA: Using OpenSSL config for key generation: D:\xampp\htdocs\kasbook\pos-v4\writable\zatca\openssl.cnf
   ```

---

## Alternative Approach: ZATCA SDK Integration

If OpenSSL continues to cause issues, here are SDK alternatives:

### Option 1: ZATCA .NET SDK (Your DLL Question)

ZATCA provides an official .NET SDK. To use it from PHP:

#### A. Via Command-Line Wrapper

Create a .NET console app that wraps the SDK, then call it from PHP:

**C# Wrapper (`ZatcaCli.exe`):**

```csharp
// Simplified example
using Zatca.EInvoice; // Official SDK

class Program {
    static void Main(string[] args) {
        if (args[0] == "generate-csr") {
            var cert = new CertificateGenerator();
            var result = cert.GenerateCSR(args[1], args[2], ...);
            Console.WriteLine(JsonSerializer.Serialize(result));
        }
    }
}
```

**PHP Integration:**

```php
$output = shell_exec('ZatcaCli.exe generate-csr "' . escapeshellarg($vatNumber) . '" ...');
$result = json_decode($output, true);
```

**Pros:**

- Uses official ZATCA SDK
- Well-tested and maintained
- Easier cryptographic operations

**Cons:**

- Requires .NET Runtime on server
- Additional deployment complexity
- Performance overhead (process spawning)
- Security concerns with shell_exec

#### B. Via PHP FFI (Foreign Function Interface)

If you have a native DLL (not .NET), you can call it directly:

```php
$ffi = FFI::cdef("
    char* generate_csr(const char* vatNumber, const char* orgName);
", "zatca_sdk.dll");

$csr = $ffi->generate_csr($vatNumber, $orgName);
```

**Pros:**

- Direct integration
- Better performance

**Cons:**

- Requires PHP 7.4+ with FFI enabled
- Complex setup
- .NET DLLs don't work (need native C/C++)

### Option 2: Third-Party PHP Packages

Search Packagist for ZATCA e-invoicing packages:

```bash
composer search zatca
composer require vendor/zatca-php-sdk
```

**Pros:**

- Pure PHP solution
- Easy Composer integration
- No external dependencies

**Cons:**

- May not be official
- Maintenance concerns
- Need to verify compliance

### Option 3: Microservice Architecture

Create a separate Node.js/Python service that handles ZATCA operations:

**zatca-service (Node.js + ZATCA SDK):**

```javascript
app.post("/generate-csr", (req, res) => {
  const csr = zatcaSdk.generateCSR(req.body);
  res.json({ csr });
});
```

**PHP Integration:**

```php
$ch = curl_init('http://localhost:3000/generate-csr');
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
$response = curl_exec($ch);
```

**Pros:**

- Language-agnostic
- Scalable
- Easier to test independently

**Cons:**

- Operational complexity
- Network overhead
- Additional infrastructure

### Option 4: Java ZATCA SDK via PHP/Java Bridge

If ZATCA has a Java SDK:

```php
// Using PHP/Java Bridge
require_once("java/Java.inc");
$csr = java("com.zatca.CertificateService")->generateCSR($params);
```

**Pros:**

- Direct Java integration

**Cons:**

- Requires Java Bridge extension
- Complex setup
- Performance concerns

---

## 💡 Recommendation

**For your current situation:**

1. **First:** Try the OpenSSL fix after restarting Apache (simplest, no dependencies)
2. **If that fails:** Investigate why OpenSSL 3.x isn't loading the config properly
3. **Last resort:** Consider the .NET CLI wrapper approach if you have the official SDK

**Long-term:**

The pure PHP OpenSSL approach is **best** because:

- ✅ No external dependencies
- ✅ Better performance
- ✅ Easier deployment
- ✅ More secure (no shell execution)
- ✅ Works on any platform

The SDK/DLL approach adds complexity that should only be considered if cryptographic requirements become too complex for PHP's OpenSSL extension.

---

## Debugging Current Issue

If the error persists after Apache restart, check:

1. **File permissions** on `writable/zatca/`:

   ```bash
   icacls writable\zatca /grant Users:F /T
   ```

2. **PHP error logs** for any file write failures

3. **OpenSSL version** - ensure it's 3.x:

   ```bash
   php -r "echo OPENSSL_VERSION_TEXT;"
   ```

4. **Test directly** via the test script:
   ```bash
   php test_zatca_key.php
   ```

Need help with SDK integration? Let me know which SDK you have (DLL name, language, vendor).
