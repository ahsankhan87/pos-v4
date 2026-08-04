<?php

return [
    // Section title
    'title' => 'E-Invoicing (ZATCA)',
    'section_header' => 'Saudi Arabia E-Invoicing Configuration',

    // Main toggle
    'enable' => 'Enable E-Invoicing (ZATCA)',
    'enable_help' => 'Enable ZATCA (Saudi Arabia) e-invoicing compliance for selected stores. When disabled, existing invoice flow remains unchanged.',

    // Environment settings
    'environment' => 'Environment',
    'environment_help' => 'Select the ZATCA API environment for testing or production',
    'sandbox' => 'Sandbox (Development)',
    'simulation' => 'Simulation (Pre-Production Testing)',
    'production' => 'Production (Live)',

    // Seller information
    'seller_vat' => 'Seller VAT Number',
    'seller_vat_help' => 'Your 15-digit VAT registration number as registered with ZATCA',
    'seller_vat_placeholder' => 'e.g., 300000000000003',
    'seller_name' => 'Seller Legal Name',
    'seller_name_help' => 'Legal business name exactly as registered with ZATCA',
    'seller_name_placeholder' => 'e.g., Your Company LLC',

    // Invoice type
    'invoice_type' => 'Invoice Type',
    'invoice_type_help' => 'Select the default invoice type used when issuing ZATCA invoices.',
    'invoice_type_simplified' => 'Simplified Invoice',
    'invoice_type_standard' => 'Standard Invoice',
    'invoice_type_b2c' => 'B2C Only (Simplified Invoices)',
    'invoice_type_b2b' => 'B2B Only (Standard Tax Invoices)',
    'invoice_type_both' => 'Both B2C & B2B',

    // Store selection
    'enabled_stores' => 'Enabled Store IDs',
    'enabled_stores_help' => 'JSON array of store IDs where ZATCA applies (leave empty for all stores)',
    'enabled_stores_placeholder' => '[1, 3, 5]',
    'enabled_stores_example' => 'Example: [1, 3, 5] enables ZATCA for stores with ID 1, 3, and 5 only',

    // Actions
    'test_connection' => 'Test Connection',
    'test_connection_help' => 'Verify connectivity to ZATCA API (available in Phase 3)',
    'save' => 'Save E-Invoicing Settings',

    // Messages
    'saved_success' => 'E-Invoicing settings saved successfully',
    'saved_error' => 'Failed to save E-Invoicing settings',
    'disabled_notice' => 'E-Invoicing is currently disabled. Enable the checkbox above to configure ZATCA settings.',

    // Validation messages
    'invalid_vat' => 'Invalid VAT number format. Must be 15 digits.',
    'invalid_store_ids' => 'Invalid store IDs format. Must be a valid JSON array of numbers.',
    'invalid_environment' => 'Invalid environment selected.',
    'invalid_invoice_type' => 'Invalid invoice type selected.',

    // Phase 3: Onboarding
    'setup_wizard' => 'ZATCA Setup Wizard',
    'onboarding_title' => 'ZATCA Onboarding',
    'onboarding_intro' => 'Complete the following steps to enable ZATCA e-invoicing for your store. This is a one-time setup process.',
    'onboarding_status' => 'Onboarding Status',
    'certificate_status' => 'Certificate Status',
    'compliance_status' => 'Compliance Status',
    'feature_disabled' => 'ZATCA e-invoicing is disabled. Please enable it in Settings first.',

    // Step 1: Generate CSR
    'step_1_title' => 'Step 1: Generate CSR',
    'step_1_desc' => 'Generate a Certificate Signing Request (CSR) and private key for your store.',
    'step_1_button' => 'Generate CSR',
    'step_1_status_pending' => 'Not started',
    'step_1_status_complete' => 'CSR Generated',
    'onboarding_csr_generated' => 'CSR generated successfully!',
    'onboarding_csr_failed' => 'Failed to generate CSR',
    'onboarding_missing_settings' => 'Please configure seller VAT number and legal name in the active store profile first.',
    'seller_profile_store_notice' => 'Seller VAT/legal identity is now managed per store. Update these fields in the Store form for the current branch.',

    // Step 2: Compliance CSID
    'step_2_title' => 'Step 2: Get Compliance CSID',
    'step_2_desc' => 'Request a Compliance Cryptographic Stamp Identifier (CSID) from ZATCA using your OTP.',
    'step_2_button' => 'Request Compliance CSID',
    'step_2_status_pending' => 'Waiting for Step 1',
    'step_2_status_complete' => 'Compliance CSID Obtained',
    'step_2_otp_label' => 'OTP (from ZATCA Portal)',
    'step_2_otp_placeholder' => 'Enter 6-digit OTP',
    'onboarding_otp_required' => 'OTP is required',
    'onboarding_csr_not_found' => 'CSR not found. Please generate CSR first (Step 1).',
    'onboarding_compliance_csid_obtained' => 'Compliance CSID obtained successfully!',
    'onboarding_compliance_csid_failed' => 'Failed to obtain Compliance CSID',
    'onboarding_compliance_csid_not_found' => 'Compliance CSID not found. Please complete Step 2 first.',

    // Step 3: Compliance Checks
    'step_3_title' => 'Step 3: Run Compliance Checks',
    'step_3_desc' => 'Submit sample invoices to ZATCA for validation. All checks must pass before going live.',
    'step_3_button' => 'Run Compliance Checks',
    'step_3_status_pending' => 'Waiting for Step 2',
    'step_3_status_complete' => 'All Checks Passed',
    'step_3_status_failed' => 'Some Checks Failed',
    'onboarding_compliance_checks_passed' => 'All compliance checks passed!',
    'onboarding_compliance_checks_failed' => 'Some compliance checks failed. Review the results below.',
    'onboarding_compliance_checks_error' => 'Error running compliance checks',
    'onboarding_compliance_required' => 'You must pass compliance checks before requesting production CSID.',

    // Step 4: Production CSID
    'step_4_title' => 'Step 4: Get Production CSID',
    'step_4_desc' => 'Exchange your compliance CSID for a production CSID. After this, you can start issuing live invoices.',
    'step_4_button' => 'Request Production CSID',
    'step_4_status_pending' => 'Waiting for Step 3',
    'step_4_status_complete' => 'Production CSID Obtained - Ready to Go Live!',
    'onboarding_production_csid_obtained' => 'Production CSID obtained successfully! You can now issue ZATCA-compliant invoices.',
    'onboarding_production_csid_failed' => 'Failed to obtain Production CSID',

    // General
    'current_environment' => 'Current Environment',
    'back_to_settings' => 'Back to Settings',
    'loading' => 'Loading...',
    'please_wait' => 'Please wait...',

    // Manual certificate import
    'import_certificate_title' => 'Manual Certificate Import (External Tool)',
    'import_certificate_desc' => 'Paste the certificate data generated by your external tool (e.g. C# app) to skip Steps 1 & 2.',
    'import_private_key_label' => 'Private Key (PEM)',
    'import_csr_label' => 'CSR (Base64, optional)',
    'import_token_label' => 'Binary Security Token (Compliance CSID)',
    'import_secret_label' => 'Secret',
    'import_request_id_label' => 'Compliance Request ID',
    'import_button' => 'Import & Save',
    'import_certificate_success' => 'Certificate data imported successfully! You can now run compliance checks (Step 3).',
];
