<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<div class="max-w-5xl mx-auto bg-white rounded-xl shadow-lg mt-8 p-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-3xl font-bold text-blue-700"><?= lang('Zatca.onboarding_title') ?></h2>
            <p class="text-gray-600 mt-2"><?= lang('Zatca.onboarding_intro') ?></p>
        </div>
        <div class="text-right">
            <span class="text-sm text-gray-500"><?= lang('Zatca.current_environment') ?>:</span>
            <span class="ml-2 px-3 py-1 rounded-full text-sm font-semibold 
                <?= $environment === 'production' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' ?>">
                <?= ucfirst(esc($environment)) ?>
            </span>
        </div>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-red-100 text-red-700 px-4 py-3 rounded mb-6">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-green-100 text-green-700 px-4 py-3 rounded mb-6">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <!-- Alert/Response Message Area -->
    <div id="alertMessage" class="hidden mb-6"></div>

    <!-- Progress Indicator -->
    <div class="mb-8">
        <div class="flex items-center justify-between relative">
            <div class="absolute left-0 top-5 w-full h-1 bg-gray-200 z-0"></div>
            <div class="absolute left-0 top-5 h-1 bg-blue-600 z-0 transition-all duration-500"
                id="progressBar" style="width: <?= $stepStatus['production_csid_obtained'] ? '100%' : ($stepStatus['compliance_checks_passed'] ? '75%' : ($stepStatus['compliance_csid_obtained'] ? '50%' : ($stepStatus['csr_generated'] ? '25%' : '0%'))) ?>"></div>

            <?php
            $steps = [
                ['key' => 'csr_generated', 'number' => 1],
                ['key' => 'compliance_csid_obtained', 'number' => 2],
                ['key' => 'compliance_checks_passed', 'number' => 3],
                ['key' => 'production_csid_obtained', 'number' => 4],
            ];
            foreach ($steps as $step): ?>
                <div class="flex flex-col items-center z-10">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold
                        <?= $stepStatus[$step['key']] ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600' ?>">
                        <?= $stepStatus[$step['key']] ? '<i class="fas fa-check"></i>' : $step['number'] ?>
                    </div>
                    <span class="text-xs mt-1 <?= $stepStatus[$step['key']] ? 'text-blue-700' : 'text-gray-600' ?>">
                        <?= lang('Zatca.step_' . $step['number'] . '_title') ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Manual Certificate Import Panel -->
    <div class="border-2 border-dashed border-indigo-300 rounded-lg mb-6 bg-indigo-50">
        <button type="button" onclick="toggleImportPanel()"
            class="w-full flex justify-between items-center px-6 py-4 text-left">
            <div class="flex items-center gap-3">
                <span class="bg-indigo-600 text-white rounded-full w-7 h-7 flex items-center justify-center text-sm font-bold">
                    <i class="fas fa-file-import text-xs"></i>
                </span>
                <span class="font-bold text-indigo-800"><?= lang('Zatca.import_certificate_title') ?></span>
            </div>
            <i id="importPanelChevron" class="fas fa-chevron-down text-indigo-600 transition-transform"></i>
        </button>

        <div id="importPanel" class="hidden px-6 pb-6">
            <p class="text-sm text-indigo-700 mb-4"><?= lang('Zatca.import_certificate_desc') ?></p>

            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">
                        <?= lang('Zatca.import_private_key_label') ?> <span class="text-red-500">*</span>
                    </label>
                    <textarea id="importPrivateKey" rows="4"
                        class="w-full border rounded px-3 py-2 font-mono text-xs"
                        placeholder="-----BEGIN EC PRIVATE KEY-----&#10;...&#10;-----END EC PRIVATE KEY-----"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">
                        <?= lang('Zatca.import_token_label') ?> <span class="text-red-500">*</span>
                    </label>
                    <textarea id="importToken" rows="4"
                        class="w-full border rounded px-3 py-2 font-mono text-xs"
                        placeholder="TUlJQ..."></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1">
                            <?= lang('Zatca.import_secret_label') ?> <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="importSecret"
                            class="w-full border rounded px-3 py-2 font-mono text-xs"
                            placeholder="e.g. r0mVJ06VMXog...">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">
                            <?= lang('Zatca.import_request_id_label') ?>
                        </label>
                        <input type="text" id="importRequestId"
                            class="w-full border rounded px-3 py-2 font-mono text-xs"
                            placeholder="e.g. 1234567890123">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">
                        <?= lang('Zatca.import_csr_label') ?>
                    </label>
                    <textarea id="importCsr" rows="3"
                        class="w-full border rounded px-3 py-2 font-mono text-xs"
                        placeholder="LS0tLS1CRUdJTi..."></textarea>
                </div>
            </div>

            <div class="mt-4 flex items-center gap-3">
                <button onclick="importCertificate()" id="btnImportCertificate"
                    class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700 font-bold">
                    <i class="fas fa-save"></i> <?= lang('Zatca.import_button') ?>
                </button>
                <span class="text-xs text-gray-500">Steps 1 &amp; 2 will be marked complete after successful import.</span>
            </div>
        </div>
    </div>

    <!-- Step 1: Generate CSR -->
    <div class="border rounded-lg p-6 mb-4 <?= $stepStatus['csr_generated'] ? 'border-green-300 bg-green-50' : 'border-gray-300' ?>">
        <div class="flex justify-between items-start">
            <div class="flex-1">
                <h3 class="text-xl font-bold mb-2 flex items-center">
                    <?= lang('Zatca.step_1_title') ?>
                    <?php if ($stepStatus['csr_generated']): ?>
                        <span class="ml-3 px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full">
                            <i class="fas fa-check-circle"></i> <?= lang('Zatca.step_1_status_complete') ?>
                        </span>
                    <?php endif; ?>
                </h3>
                <p class="text-gray-600 mb-4"><?= lang('Zatca.step_1_desc') ?></p>

                <?php if (!empty($certificate['csr'])): ?>
                    <div class="bg-white border rounded p-3 mb-3">
                        <p class="text-xs text-gray-500 mb-1">CSR (Base64):</p>
                        <code class="text-xs text-gray-700 break-all"><?= esc(substr($certificate['csr'], 0, 100)) ?>...</code>
                    </div>
                <?php endif; ?>
            </div>
            <div class="ml-4">
                <button onclick="generateCsr()" id="btnGenerateCsr"
                    class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 font-bold disabled:bg-gray-300 disabled:cursor-not-allowed">
                    <?= lang('Zatca.step_1_button') ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Step 2: Request Compliance CSID -->
    <div class="border rounded-lg p-6 mb-4 <?= $stepStatus['compliance_csid_obtained'] ? 'border-green-300 bg-green-50' : 'border-gray-300' ?>">
        <div class="flex justify-between items-start">
            <div class="flex-1">
                <h3 class="text-xl font-bold mb-2 flex items-center">
                    <?= lang('Zatca.step_2_title') ?>
                    <?php if ($stepStatus['compliance_csid_obtained']): ?>
                        <span class="ml-3 px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full">
                            <i class="fas fa-check-circle"></i> <?= lang('Zatca.step_2_status_complete') ?>
                        </span>
                    <?php elseif (!$stepStatus['csr_generated']): ?>
                        <span class="ml-3 px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded-full">
                            <?= lang('Zatca.step_2_status_pending') ?>
                        </span>
                    <?php endif; ?>
                </h3>
                <p class="text-gray-600 mb-4"><?= lang('Zatca.step_2_desc') ?></p>

                <?php if ($stepStatus['csr_generated'] && !$stepStatus['compliance_csid_obtained']): ?>
                    <div class="bg-yellow-50 border border-yellow-200 rounded p-3 mb-3">
                        <label class="block font-semibold mb-2"><?= lang('Zatca.step_2_otp_label') ?></label>
                        <input type="text" id="otpInput"
                            class="w-64 border rounded px-3 py-2"
                            placeholder="<?= lang('Zatca.step_2_otp_placeholder') ?>"
                            maxlength="10">
                        <p class="text-xs text-gray-500 mt-1">Login to ZATCA portal to generate OTP</p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($certificate['binary_security_token'])): ?>
                    <div class="bg-white border rounded p-3 mb-3">
                        <p class="text-xs text-gray-500 mb-1">Compliance CSID:</p>
                        <code class="text-xs text-gray-700 break-all"><?= esc(substr($certificate['binary_security_token'], 0, 80)) ?>...</code>
                    </div>
                <?php endif; ?>
            </div>
            <div class="ml-4">
                <button onclick="requestComplianceCsid()" id="btnRequestComplianceCsid"
                    <?= !$stepStatus['csr_generated'] || $stepStatus['compliance_csid_obtained'] ? 'disabled' : '' ?>
                    class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 font-bold disabled:bg-gray-300 disabled:cursor-not-allowed">
                    <?= lang('Zatca.step_2_button') ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Step 3: Run Compliance Checks -->
    <div class="border rounded-lg p-6 mb-4 <?= $stepStatus['compliance_checks_passed'] ? 'border-green-300 bg-green-50' : 'border-gray-300' ?>">
        <div class="flex justify-between items-start">
            <div class="flex-1">
                <h3 class="text-xl font-bold mb-2 flex items-center">
                    <?= lang('Zatca.step_3_title') ?>
                    <?php if ($stepStatus['compliance_checks_passed']): ?>
                        <span class="ml-3 px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full">
                            <i class="fas fa-check-circle"></i> <?= lang('Zatca.step_3_status_complete') ?>
                        </span>
                    <?php elseif (!$stepStatus['compliance_csid_obtained']): ?>
                        <span class="ml-3 px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded-full">
                            <?= lang('Zatca.step_3_status_pending') ?>
                        </span>
                    <?php endif; ?>
                </h3>
                <p class="text-gray-600 mb-4"><?= lang('Zatca.step_3_desc') ?></p>

                <!-- Compliance Check Results (populated by JavaScript) -->
                <div id="complianceResults" class="hidden"></div>
            </div>
            <div class="ml-4">
                <button onclick="runComplianceChecks()" id="btnRunComplianceChecks"
                    <?= !$stepStatus['compliance_csid_obtained'] || $stepStatus['compliance_checks_passed'] ? 'disabled' : '' ?>
                    class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 font-bold disabled:bg-gray-300 disabled:cursor-not-allowed">
                    <?= lang('Zatca.step_3_button') ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Step 4: Request Production CSID -->
    <div class="border rounded-lg p-6 mb-4 <?= $stepStatus['production_csid_obtained'] ? 'border-green-300 bg-green-50' : 'border-gray-300' ?>">
        <div class="flex justify-between items-start">
            <div class="flex-1">
                <h3 class="text-xl font-bold mb-2 flex items-center">
                    <?= lang('Zatca.step_4_title') ?>
                    <?php if ($stepStatus['production_csid_obtained']): ?>
                        <span class="ml-3 px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full">
                            <i class="fas fa-check-circle"></i> <?= lang('Zatca.step_4_status_complete') ?>
                        </span>
                    <?php elseif (!$stepStatus['compliance_checks_passed']): ?>
                        <span class="ml-3 px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded-full">
                            <?= lang('Zatca.step_4_status_pending') ?>
                        </span>
                    <?php endif; ?>
                </h3>
                <p class="text-gray-600 mb-4"><?= lang('Zatca.step_4_desc') ?></p>

                <?php if (!empty($certificate['production_binary_security_token'])): ?>
                    <div class="bg-green-50 border border-green-200 rounded p-4">
                        <p class="text-green-700 font-semibold mb-2">
                            <i class="fas fa-check-circle"></i> Production CSID Obtained!
                        </p>
                        <p class="text-sm text-gray-600">You can now start issuing ZATCA-compliant invoices.</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="ml-4">
                <button onclick="requestProductionCsid()" id="btnRequestProductionCsid"
                    class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 font-bold disabled:bg-gray-300 disabled:cursor-not-allowed">
                    <?= lang('Zatca.step_4_button') ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Footer Actions -->
    <div class="flex justify-between mt-8 pt-6 border-t">
        <a href="<?= site_url('settings') ?>" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left"></i> <?= lang('Zatca.back_to_settings') ?>
        </a>

        <?php if ($stepStatus['production_csid_obtained']): ?>
            <span class="text-green-700 font-semibold">
                <i class="fas fa-check-circle"></i> Onboarding Complete!
            </span>
        <?php endif; ?>
    </div>
</div>

<script>
    const csrfToken = '<?= csrf_hash() ?>';
    const csrfTokenName = '<?= csrf_token() ?>';

    function showAlert(message, type = 'success') {
        const alertDiv = document.getElementById('alertMessage');
        alertDiv.className = `mb-6 px-4 py-3 rounded ${type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`;
        // Convert newlines to <br> for multi-line error messages
        const formattedMessage = message.replace(/\n/g, '<br>');
        alertDiv.innerHTML = formattedMessage;
        alertDiv.classList.remove('hidden');

        // Auto-hide after 10 seconds for errors, 5 for success
        setTimeout(() => {
            alertDiv.classList.add('hidden');
        }, type === 'error' ? 10000 : 5000);
    }

    function disableButton(buttonId, loading = true) {
        const btn = document.getElementById(buttonId);
        btn.disabled = true;
        if (loading) {
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?= lang('Zatca.loading') ?>';
        }
    }

    function enableButton(buttonId, text) {
        const btn = document.getElementById(buttonId);
        btn.disabled = false;
        btn.innerHTML = text;
    }

    function toggleImportPanel() {
        const panel = document.getElementById('importPanel');
        const chevron = document.getElementById('importPanelChevron');
        panel.classList.toggle('hidden');
        chevron.style.transform = panel.classList.contains('hidden') ? '' : 'rotate(180deg)';
    }

    async function importCertificate() {
        const privateKey = document.getElementById('importPrivateKey').value.trim();
        const token = document.getElementById('importToken').value.trim();
        const secret = document.getElementById('importSecret').value.trim();
        const requestId = document.getElementById('importRequestId').value.trim();
        const csr = document.getElementById('importCsr').value.trim();

        if (!privateKey || !token || !secret) {
            showAlert('Private Key, Binary Security Token, and Secret are required.', 'error');
            return;
        }

        disableButton('btnImportCertificate');

        try {
            const response = await fetch('<?= site_url('zatca/onboarding/import-certificate') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    [csrfTokenName]: csrfToken
                },
                body: JSON.stringify({
                    private_key: privateKey,
                    binary_security_token: token,
                    secret: secret,
                    compliance_request_id: requestId,
                    csr: csr
                })
            });

            const data = await response.json();

            if (data.success) {
                showAlert(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert(data.message || 'Import failed.', 'error');
                enableButton('btnImportCertificate', '<i class="fas fa-save"></i> <?= lang('Zatca.import_button') ?>');
            }
        } catch (e) {
            showAlert('Network error: ' + e.message, 'error');
            enableButton('btnImportCertificate', '<i class="fas fa-save"></i> <?= lang('Zatca.import_button') ?>');
        }
    }

    async function generateCsr() {
        disableButton('btnGenerateCsr');

        try {
            const response = await fetch('<?= site_url('zatca/onboarding/generate-csr') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    [csrfTokenName]: csrfToken
                }
            });

            const data = await response.json();

            if (data.success) {
                showAlert(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert(data.message, 'error');
                enableButton('btnGenerateCsr', '<?= lang('Zatca.step_1_button') ?>');
            }
        } catch (error) {
            showAlert('Error: ' + error.message, 'error');
            enableButton('btnGenerateCsr', '<?= lang('Zatca.step_1_button') ?>');
        }
    }

    async function requestComplianceCsid() {
        const otp = document.getElementById('otpInput').value.trim();
        if (!otp) {
            showAlert('<?= lang('Zatca.onboarding_otp_required') ?>', 'error');
            return;
        }

        disableButton('btnRequestComplianceCsid');

        try {
            const response = await fetch('<?= site_url('zatca/onboarding/request-compliance-csid') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    [csrfTokenName]: csrfToken
                },
                body: JSON.stringify({
                    otp: otp
                })
            });

            const data = await response.json();

            if (data.success) {
                showAlert(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                // Show detailed error message
                let errorMsg = data.message || 'Unknown error';
                if (data.error) {
                    errorMsg += '\n\nDetails: ' + data.error;
                }
                if (data.details) {
                    errorMsg += '\n\n' + data.details;
                }
                showAlert(errorMsg, 'error');
                console.error('=== ZATCA API Error Details ===');
                console.error('Message:', data.message);
                console.error('Error:', data.error);
                console.error('Full Response:', data);
                console.error('Check server logs at: writable/logs/log-<?= date('Y-m-d') ?>.log');
                enableButton('btnRequestComplianceCsid', '<?= lang('Zatca.step_2_button') ?>');
            }
        } catch (error) {
            showAlert('Error: ' + error.message, 'error');
            enableButton('btnRequestComplianceCsid', '<?= lang('Zatca.step_2_button') ?>');
        }
    }

    async function runComplianceChecks() {
        disableButton('btnRunComplianceChecks');

        try {
            const response = await fetch('<?= site_url('zatca/onboarding/run-compliance-checks') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    [csrfTokenName]: csrfToken
                }
            });

            const data = await response.json();

            // Display results
            if (data.results) {
                const resultsDiv = document.getElementById('complianceResults');
                resultsDiv.classList.remove('hidden');

                let html = '<div class="bg-white border rounded p-4 mt-3"><h4 class="font-bold mb-3">Compliance Check Results:</h4><div class="space-y-2">';

                for (const [invoiceType, result] of Object.entries(data.results)) {
                    const icon = result.passed ? '<i class="fas fa-check-circle text-green-600"></i>' : '<i class="fas fa-times-circle text-red-600"></i>';
                    const colorClass = result.passed ? 'text-green-700' : 'text-red-700';
                    html += `<div class="flex items-start">
                    <span class="mr-2">${icon}</span>
                    <div>
                        <span class="font-semibold ${colorClass}">${invoiceType}</span>: 
                        <span class="text-gray-600">${result.message}</span>
                    </div>
                </div>`;
                }

                html += '</div></div>';
                resultsDiv.innerHTML = html;
            }

            if (data.success) {
                showAlert(data.message, 'success');
                setTimeout(() => location.reload(), 2000);
            } else {
                showAlert(data.message, 'error');
                enableButton('btnRunComplianceChecks', '<?= lang('Zatca.step_3_button') ?>');
            }
        } catch (error) {
            showAlert('Error: ' + error.message, 'error');
            enableButton('btnRunComplianceChecks', '<?= lang('Zatca.step_3_button') ?>');
        }
    }

    async function requestProductionCsid() {
        if (!confirm('Are you sure you want to request Production CSID? This will enable live invoice submission.')) {
            return;
        }

        disableButton('btnRequestProductionCsid');

        try {
            const response = await fetch('<?= site_url('zatca/onboarding/request-production-csid') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    [csrfTokenName]: csrfToken
                }
            });

            const data = await response.json();

            if (data.success) {
                showAlert(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert(data.message, 'error');
                enableButton('btnRequestProductionCsid', '<?= lang('Zatca.step_4_button') ?>');
            }
        } catch (error) {
            showAlert('Error: ' + error.message, 'error');
            enableButton('btnRequestProductionCsid', '<?= lang('Zatca.step_4_button') ?>');
        }
    }
</script>

<?= $this->endSection() ?>