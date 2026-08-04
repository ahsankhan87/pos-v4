<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<div class="max-w-2xl mx-auto bg-white rounded-xl shadow-lg mt-8 p-8">
    <h2 class="text-2xl font-bold mb-6 text-blue-700"><?= lang('Settings.title') ?></h2>
    <?php if (session()->getFlashdata('message')): ?>
        <div class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4">
            <?= session()->getFlashdata('message') ?>
        </div>
    <?php endif; ?>
    <form method="post" action="<?= site_url('settings/update') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= esc($settings['id'] ?? 1) ?>">
        <!-- <div class="mb-4">
            <label class="block font-semibold mb-1">Currency Code </label>
            <select name="currency_code" class="w-full border rounded px-3 py-2">
                <option value="USD" <?= @$settings['currency_code'] == 'USD' ? 'selected' : '' ?>>USD</option>
                <option value="EUR" <?= @$settings['currency_code'] == 'EUR' ? 'selected' : '' ?>>EUR</option>
                <option value="PKR" <?= @$settings['currency_code'] == 'PKR' ? 'selected' : '' ?>>PKR</option>
                <option value="INR" <?= @$settings['currency_code'] == 'INR' ? 'selected' : '' ?>>INR</option>
                <option value="SAR" <?= @$settings['currency_code'] == 'SAR' ? 'selected' : '' ?>>SAR</option>

            </select>
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Currency Symbol</label>
            <input type="text" name="currency_symbol" value="<?= esc($settings['currency_symbol'] ?? '') ?>" class="w-full border rounded px-3 py-2" required>
        </div> -->
        <div class="mb-4">
            <label class="block font-semibold mb-1"><?= lang('Settings.tax_rate') ?></label>
            <input type="number" name="tax_rate" value="<?= esc($settings['tax_rate'] ?? '0') ?>" min="0" max="100" class="w-24 border rounded px-2 py-1">
        </div>

        <div class="mb-6">
            <label class="block font-semibold mb-2"><?= lang('Settings.sales_screen') ?></label>
            <input type="hidden" name="sales_show_discount_type" value="0">
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="sales_show_discount_type" value="1" <?= ((int) ($settings['sales_show_discount_type'] ?? 1) === 1) ? 'checked' : '' ?> class="h-4 w-4">
                <span><?= lang('Settings.show_item_discount_type', ['currency' => esc($settings['currency_symbol'] ?? session()->get('currency_symbol') ?? '$')]) ?></span>
            </label>
            <p class="text-sm text-gray-500 mt-1"><?= lang('Settings.discount_help') ?></p>
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-1"><?= lang('Settings.receipt_template') ?></label>
            <select name="receipt_template_id" class="w-full border rounded px-3 py-2">
                <?php if (!empty($templates)): ?>
                    <?php foreach ($templates as $template): ?>
                        <option value="<?= $template['id'] ?>" <?= $template['is_default'] ? 'selected' : '' ?>>
                            <?= esc($template['name']) ?><?= $template['is_default'] ? (' (' . lang('Settings.current') . ')') : '' ?>
                        </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value=""><?= lang('Settings.no_templates') ?></option>
                <?php endif; ?>
            </select>
            <p class="text-sm text-gray-500 mt-1">
                <a href="<?= site_url('receipts/templates') ?>" class="text-blue-600 hover:text-blue-800">
                    <i class="fas fa-cog"></i> <?= lang('Settings.manage_templates') ?>
                </a>
            </p>
        </div>

        <!-- ZATCA E-Invoicing Section -->
        <div class="mb-6 border-t pt-6">
            <h3 class="text-xl font-bold mb-4 text-blue-700"><?= lang('Zatca.section_header') ?></h3>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4">
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <div class="mb-4">
                <input type="hidden" name="einvoicing_enabled" value="0">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" id="zatca_enable_checkbox" name="einvoicing_enabled" value="1"
                        <?= !empty($settings['einvoicing_enabled']) ? 'checked' : '' ?>
                        class="h-4 w-4"
                        onchange="toggleZatcaFields()">
                    <span class="font-semibold"><?= lang('Zatca.enable') ?></span>
                </label>
                <p class="text-sm text-gray-500 mt-1"><?= lang('Zatca.enable_help') ?></p>
            </div>

            <div id="zatca_fields" style="display: none;">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 space-y-4">

                    <!-- Environment -->
                    <div>
                        <label class="block font-semibold mb-1"><?= lang('Zatca.environment') ?></label>
                        <select name="zatca_environment" class="w-full border rounded px-3 py-2">
                            <option value="sandbox" <?= ($settings['zatca_environment'] ?? 'sandbox') === 'sandbox' ? 'selected' : '' ?>>
                                <?= lang('Zatca.sandbox') ?>
                            </option>
                            <option value="simulation" <?= ($settings['zatca_environment'] ?? '') === 'simulation' ? 'selected' : '' ?>>
                                <?= lang('Zatca.simulation') ?>
                            </option>
                            <option value="production" <?= ($settings['zatca_environment'] ?? '') === 'production' ? 'selected' : '' ?>>
                                <?= lang('Zatca.production') ?>
                            </option>
                        </select>
                        <p class="text-sm text-gray-500 mt-1"><?= lang('Zatca.environment_help') ?></p>
                    </div>

                    <div class="rounded border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                        <?= lang('Zatca.seller_profile_store_notice') ?>
                    </div>

                    <!-- Invoice Type -->
                    <div>
                        <label class="block font-semibold mb-2" for="zatca_invoice_type"><?= lang('Zatca.invoice_type') ?></label>
                        <?php
                        $configuredType = strtolower((string) ($settings['zatca_invoice_type'] ?? 'both'));
                        if (!in_array($configuredType, ['simplified', 'standard', 'both'], true)) {
                            $configuredType = 'both';
                        }
                        ?>
                        <select id="zatca_invoice_type" name="zatca_invoice_type" class="w-full border rounded px-3 py-2">
                            <option value="both" <?= $configuredType === 'both' ? 'selected' : '' ?>><?= lang('Zatca.invoice_type_both') ?></option>
                            <option value="simplified" <?= $configuredType === 'simplified' ? 'selected' : '' ?>><?= lang('Zatca.invoice_type_simplified') ?></option>
                            <option value="standard" <?= $configuredType === 'standard' ? 'selected' : '' ?>><?= lang('Zatca.invoice_type_standard') ?></option>
                        </select>
                        <p class="text-sm text-gray-500 mt-1"><?= lang('Zatca.invoice_type_help') ?></p>
                    </div>

                    <!-- Enabled Store IDs -->
                    <div>
                        <label class="block font-semibold mb-1"><?= lang('Zatca.enabled_stores') ?></label>
                        <textarea name="zatca_enabled_store_ids"
                            class="w-full border rounded px-3 py-2"
                            rows="2"
                            placeholder="<?= lang('Zatca.enabled_stores_placeholder') ?>"><?= esc($settings['zatca_enabled_store_ids'] ?? '') ?></textarea>
                        <p class="text-sm text-gray-500 mt-1"><?= lang('Zatca.enabled_stores_help') ?></p>
                        <p class="text-xs text-gray-400 mt-1"><?= lang('Zatca.enabled_stores_example') ?></p>
                    </div>

                    <!-- Test Connection Button (disabled for Phase 3) -->
                    <div class="pt-2">
                        <a href="<?= site_url('zatca/onboarding') ?>"
                            class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 mr-3">
                            <i class="fas fa-cog"></i> <?= lang('Zatca.setup_wizard') ?>
                        </a>
                        <p class="text-xs text-gray-500 mt-2">
                            Complete the ZATCA onboarding process to obtain your production certificate.
                        </p>
                    </div>

                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 font-bold"><?= lang('Settings.save') ?></button>
        </div>
    </form>
</div>

<script>
    function toggleZatcaFields() {
        const checkbox = document.getElementById('zatca_enable_checkbox');
        const fieldsDiv = document.getElementById('zatca_fields');
        fieldsDiv.style.display = checkbox.checked ? 'block' : 'none';
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleZatcaFields();
    });
</script>

<?= $this->endSection() ?>