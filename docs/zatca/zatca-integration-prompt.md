# ZATCA (Saudi Arabia) E-Invoicing Integration — Copilot Build Prompt

> Copy this whole document (or section by section) into VS Code Copilot Chat / Copilot Edits.
> Stack: PHP, CodeIgniter 4.6, TailwindCSS (CDN), MySQL. Existing POS app is already live in Pakistan & Saudi Arabia.

---

## GLOBAL CONTEXT (paste this first, every time you start a new Copilot session on this task)

```
I have an existing POS application built with:
- PHP + CodeIgniter 4.6
- MySQL database
- TailwindCSS via CDN (no build step, classes used directly in views)
- Existing modules: Sales/Invoices, Products, Customers, Settings, Users

I need to add ZATCA (Saudi Arabia's Zakat, Tax and Customs Authority) Phase 2
e-invoicing (Fatoora) integration as an OPTIONAL module.

Hard requirements:
1. Add a checkbox in the Settings page: "Enable E-Invoicing (ZATCA)".
2. When this setting is OFF (default), the app must behave EXACTLY as it does today —
   no ZATCA XML generation, no QR code changes, no API calls, no new required fields
   on the invoice screen, no performance impact. Existing invoice save/print flow must
   be untouched when disabled.
3. When this setting is ON, invoices generated for Saudi Arabia branches/customers
   should go through the full ZATCA compliance flow (XML generation, cryptographic
   stamping, QR code, reporting/clearance API call).
4. This must not break the Pakistan side of the app or any existing functionality.
5. All new code should be modular (its own namespace/folder, own service classes,
   own config file) so it can be enabled/disabled cleanly and later reused for FBR too.

Do not modify unrelated existing modules. Ask me before altering any existing
database table structure — prefer additive migrations (new tables / new nullable
columns) over changing existing columns.
```

---

## PHASE 1 — Settings Toggle & Config Foundation

```
Task: Add an "E-Invoicing" section to the existing Settings page in my CodeIgniter 4.6 POS app.

Requirements:
1. Create a migration to add a `settings` key-value entry (or use existing settings table
   if one exists — inspect app/Models and app/Database/Migrations first and tell me what
   you find before creating a new table).
   Required setting keys:
   - `einvoicing_enabled` (boolean, default 0)
   - `einvoicing_country` (string, default 'SA') — which branch/country this applies to
   - `zatca_environment` (enum: 'sandbox' | 'simulation' | 'production', default 'sandbox')
   - `zatca_seller_vat_number`
   - `zatca_seller_name` (as registered with ZATCA)
   - `zatca_invoice_type` (enum: 'B2C' | 'B2B' | 'both')

2. Add a new "E-Invoicing" tab/section on the Settings page (Tailwind CDN styling,
   consistent with existing settings UI — inspect the current settings view first).
   Include:
   - A checkbox toggle "Enable E-Invoicing (ZATCA)" bound to `einvoicing_enabled`
   - Fields for the settings above, only visible/enabled when the checkbox is checked
     (use simple vanilla JS or Alpine.js if already used in the project — check first)
   - A "Test Connection" button (stub for now, we'll wire it in Phase 3)
   - Save button posts to a new `SettingsController::saveEinvoicing()` method

3. Create a `EinvoicingSettings` helper or model method `isEnabled(): bool` that reads
   `einvoicing_enabled` from DB/cache. This will be the single source of truth checked
   everywhere else in the app.

4. Do NOT touch the invoice save/print controllers yet — this phase is settings-only.

5. Add config file `app/Config/Zatca.php` with placeholders for:
   - API base URLs per environment (sandbox/simulation/production — I will confirm the
     actual ZATCA endpoint URLs from official docs before going live)
   - Certificate storage path (outside public webroot, e.g. app/Zatca/certs/)
   - Default currency, default VAT rate (15%)

Show me the migration file, the settings model/controller changes, and the new view
partial before applying anything destructive.
```

---

## PHASE 2 — Database Schema for ZATCA Data

```
Task: Create additive-only migrations for ZATCA e-invoicing data in my CodeIgniter 4.6 app.
Do not modify existing `sales`/`invoices` table columns — only ADD nullable columns or
new related tables, so behavior with einvoicing_enabled=0 is unaffected.

Create migration(s) for:

1. Add nullable columns to the existing invoices/sales table (confirm actual table name
   from app/Models first):
   - `zatca_uuid` (varchar 64, nullable)
   - `zatca_invoice_hash` (text, nullable) — current invoice hash
   - `zatca_previous_invoice_hash` (text, nullable) — PIH, chained from last invoice
   - `zatca_icv` (int, nullable) — Invoice Counter Value, sequential
   - `zatca_qr_code` (text, nullable) — base64 TLV QR string
   - `zatca_xml_path` (varchar 255, nullable) — path to stored signed XML
   - `zatca_status` (enum: 'pending','reported','cleared','failed', nullable)
   - `zatca_response` (text, nullable) — raw API response for debugging
   - `zatca_submitted_at` (datetime, nullable)

2. New table `zatca_certificates`:
   - id, environment (sandbox/simulation/production), csr (text), private_key (text,
     encrypted at rest — note in comments this must never be exposed to frontend),
     compliance_request_id, binary_security_token (compliance CSID),
     production_binary_security_token (production CSID), secret,
     status (enum: draft/compliance/production), created_at, updated_at

3. New table `zatca_submission_queue`:
   - id, invoice_id (FK), payload (longtext), attempts (int default 0),
     last_error (text nullable), status (enum: queued/processing/success/failed),
     created_at, updated_at
   (This is for retrying failed/offline submissions — sales must never block on ZATCA
   API being down.)

Generate the migration files following CodeIgniter 4.6 migration conventions used
elsewhere in this project (check an existing migration file first for naming/style).
```

---

## PHASE 3 — ZATCA Onboarding (Compliance CSID / Production CSID)

```
Task: Build a one-time onboarding flow for ZATCA in my CodeIgniter 4.6 app, used to
generate the certificates required before any invoice can be signed/reported.

This is normally a manual/admin action, done once per branch/VAT registration, not per
invoice. Build it as an admin-only screen under Settings > E-Invoicing > "ZATCA Setup".

Steps to implement (ZATCA Fatoora onbooarding flow — verify exact endpoint paths and
payloads against the official ZATCA SDK/API docs at
https://zatca.gov.sa/en/E-Invoicing/SystemsDevelopers/Pages/default.aspx
before finalizing, since I want this technically correct, not guessed):

1. "Generate CSR" button:
   - Generate an EC private key (secp256k1) and a Certificate Signing Request (CSR)
     using OpenSSL via PHP (openssl_csr_new / shell exec if needed), embedding the
     required ZATCA CSR fields: organization identifier (VAT number), organization
     unit name, organization name, country (SA), invoice type, location, industry.
   - Store the private key encrypted in `zatca_certificates` table (Phase 2).
   - Never expose the private key in any API response or view.

2. "Get Compliance CSID" button:
   - Calls ZATCA's compliance CSID endpoint with the CSR and an OTP (I will supply the
     OTP manually from the ZATCA Fatoora portal — add an input field for it).
   - Stores the returned binary security token + secret in `zatca_certificates`.

3. "Run Compliance Checks" step:
   - Submits sample invoices (standard + simplified, invoice + credit note + debit note)
     to ZATCA's compliance API to validate signing/XML correctness before going live.
   - Show pass/fail status per sample type in the UI.

4. "Get Production CSID" button (enabled only after compliance checks pass):
   - Exchanges compliance CSID for the production CSID via ZATCA's production
     onboarding endpoint.
   - Stores production credentials in `zatca_certificates` with status='production'.

5. Wrap all ZATCA HTTP calls in a dedicated service class `app/Zatca/ZatcaApiClient.php`
   using CodeIgniter's `\Config\Services::curlrequest()`, with:
   - environment-aware base URL (from Config/Zatca.php)
   - proper Basic Auth using binary_security_token:secret
   - centralized error handling/logging (log to a `zatca_logs` table or CI4 log files)

Ask me to confirm exact ZATCA endpoint URLs/payload schemas before writing the final
HTTP call code — pull them from the official ZATCA developer documentation rather than
assuming.
```

---

## PHASE 4 — Invoice XML Generation, Signing & QR Code

```
Task: Build the core ZATCA invoice compliance engine for my CodeIgniter 4.6 POS app.
This must only run when `einvoicing_enabled` = true AND the sale's branch/country = SA.
When disabled, none of this code should execute (guard every entry point with
EinvoicingSettings::isEnabled()).

Build `app/Zatca/ZatcaInvoiceBuilder.php` with these responsibilities:

1. Build a UBL 2.1 compliant XML invoice from my existing invoice data model
   (map: invoice number, issue date/time, seller VAT + name, buyer info if B2B,
   line items with VAT category/rate, totals, currency=SAR).
   - Use PHP DOMDocument (no heavy external dependency) or a lightweight UBL library
     if you find a well-maintained composer package — check first and tell me what
     you'd recommend before installing anything.
   - Determine invoice subtype: 388 (Tax Invoice/B2B/Standard) vs simplified (B2C)
     based on `zatca_invoice_type` setting and whether buyer VAT number is present.

2. Implement invoice hash chaining:
   - Compute SHA-256 hash of current invoice XML → `zatca_invoice_hash`
   - Pull `zatca_invoice_hash` of the previous invoice (by ICV order) →
     store as `zatca_previous_invoice_hash` on the new invoice (PIH)
   - Increment and store `zatca_icv` sequentially, scoped per seller/branch

3. Cryptographic stamping:
   - Sign the invoice XML digest using the ECDSA private key from `zatca_certificates`
   - Embed the resulting `UBLExtensions` block (signature, certificate, signed
     properties) into the XML per ZATCA's specification

4. Generate the TLV-encoded Base64 QR code containing:
   seller name, VAT number, timestamp, invoice total, VAT total, invoice hash,
   digital signature, public key, and certificate signature (for standard invoices)
   - Store result in `zatca_qr_code`
   - Return the QR string so the existing invoice print/receipt view can render it
     as an image (use a QR library already in composer.json if present, else suggest
     one — e.g., endroid/qr-code)

5. Store the final signed XML file in the path from Config/Zatca.php, save path to
   `zatca_xml_path`.

Wire this into the EXISTING invoice creation flow with minimal footprint:
- Find the current invoice save controller/service method
- After a successful sale save, IF einvoicing_enabled AND branch=SA:
    call ZatcaInvoiceBuilder → on success, update invoice with ZATCA fields
    → dispatch to submission queue (Phase 5)
  ELSE: do nothing (existing behavior unchanged)
- This call must be wrapped in try/catch so any ZATCA failure NEVER blocks the sale
  from completing — log the error, mark zatca_status='failed', let the queue retry.

Show me the diff/patch for the invoice controller before applying it.
```

---

## PHASE 5 — Reporting/Clearance API Submission & Retry Queue

```
Task: Implement ZATCA invoice submission (reporting for B2C, clearance for B2B) with a
resilient retry queue, in my CodeIgniter 4.6 POS app.

1. Extend `ZatcaApiClient` with two methods:
   - `reportInvoice($signedXmlBase64, $invoiceHash, $uuid)` → POST to the Reporting API
     (simplified/B2C invoices — near-real-time, non-blocking to the sale)
   - `clearInvoice($signedXmlBase64, $invoiceHash, $uuid)` → POST to the Clearance API
     (standard/B2B invoices — must get cleared BEFORE the invoice is considered final;
     if clearance fails, invoice should still be saved locally with status='failed'
     and queued for retry, but flag it clearly in the UI for the cashier/admin)

2. Build `app/Zatca/ZatcaQueueProcessor.php`:
   - Picks up rows from `zatca_submission_queue` where status='queued' or
     ('failed' and attempts < max_attempts, e.g. 5)
   - Calls the appropriate API method
   - On success: update invoice zatca_status + store API response
   - On failure: increment attempts, store last_error, exponential backoff before retry
   - On permanent failure after max attempts: mark status='failed' and flag for
     manual admin review (show a badge/count in the admin dashboard)

3. Create a CI4 Spark command `php spark zatca:process-queue` that runs
   ZatcaQueueProcessor — this will be scheduled via cron (document the suggested
   cron line, e.g. every 2 minutes, in a comment).

4. Add a small admin widget/page "ZATCA Queue" under Settings or a new menu item,
   listing pending/failed submissions with a manual "Retry now" button per row —
   only visible when einvoicing_enabled is true.

5. Make sure NONE of this queue processing runs or is scheduled meaningfully when
   einvoicing_enabled is false — the spark command should just exit early with a
   log message "E-Invoicing disabled, skipping" in that case.

Show me the queue processor and spark command code for review before wiring the cron
suggestion into any docs.
```

---

## PHASE 6 — Receipt/Print View Changes (Conditional)

```
Task: Update the existing invoice/receipt print view in my CodeIgniter 4.6 POS app to
conditionally show ZATCA QR code and compliance info.

Requirements:
1. Locate the existing receipt/print Blade-equivalent view (CI4 uses plain PHP views —
   find app/Views/.../receipt or invoice print template).
2. Wrap all ZATCA-related markup in a condition:
   `<?php if ($einvoicingEnabled && $invoice->zatca_qr_code): ?> ... <?php endif; ?>`
3. When enabled and QR present: render the QR code image (from zatca_qr_code TLV
   base64) below the existing totals, plus a small "ZATCA Compliant Invoice" label.
4. When disabled, or QR not yet generated (e.g., still queued): the receipt must render
   EXACTLY as it does today — no empty QR boxes, no layout shift, no broken conditionals.
5. Keep all changes additive and guarded — do not restructure the existing receipt
   template layout.

Show me the diff before applying.
```

---

## Notes for you (the developer) before running these prompts

1. **Verify against official ZATCA docs before Phase 3–5 go live.** Endpoint URLs, exact
   CSR field OIDs, and XML schema details change and must be confirmed against
   `https://zatca.gov.sa` and the official ZATCA SDK/GitHub repo — don't let Copilot
   guess these; feed it the real spec pages when you get there.
2. **Certificates/private keys must never be committed to git or exposed via any route.**
   Store outside webroot, encrypt at rest, restrict file permissions.
3. **Run phases in order** — each depends on the previous phase's tables/config existing.
4. **Test with `einvoicing_enabled = 0` after every phase** to confirm the Pakistan/FBR
   side and general app behavior is 100% unaffected.
5. Consider whether you want to build the XML/signing logic fully in-house or use a
   ZATCA-certified middleware/PHP SDK — building it yourself is more control but higher
   certification risk; mention this tradeoff to Copilot if you want it to suggest
   existing composer packages instead of raw DOMDocument/OpenSSL code.
