<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<div class="max-w-2xl mx-auto bg-white rounded-xl shadow-lg mt-8 p-8">
    <h2 class="text-2xl font-bold mb-6 text-blue-700">App Settings</h2>
    <?php if (session()->getFlashdata('message')): ?>
        <div class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4">
            <?= session()->getFlashdata('message') ?>
        </div>
    <?php endif; ?>
    <form method="post" action="<?= site_url('settings/update') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= esc($settings['id'] ?? 1) ?>">
        <div class="mb-4">
            <label class="block font-semibold mb-1">Currency Code </label>
            <select name="currency_code" class="w-full border rounded px-3 py-2">
                <option value="USD" <?= @$settings['currency_code'] == 'USD' ? 'selected' : '' ?>>USD</option>
                <option value="EUR" <?= @$settings['currency_code'] == 'EUR' ? 'selected' : '' ?>>EUR</option>
                <option value="PKR" <?= @$settings['currency_code'] == 'PKR' ? 'selected' : '' ?>>PKR</option>
                <option value="INR" <?= @$settings['currency_code'] == 'INR' ? 'selected' : '' ?>>INR</option>
                <option value="SAR" <?= @$settings['currency_code'] == 'SAR' ? 'selected' : '' ?>>SAR</option>
                <!-- Add more as needed -->
            </select>
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Currency Symbol</label>
            <input type="text" name="currency_symbol" value="<?= esc($settings['currency_symbol'] ?? '') ?>" class="w-full border rounded px-3 py-2" required>
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Tax Rate (%)</label>
            <input type="number" name="tax_rate" value="<?= esc($settings['tax_rate'] ?? '0') ?>" min="0" max="100" class="w-24 border rounded px-2 py-1">
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-1">Receipt Template</label>
            <select name="receipt_template_id" class="w-full border rounded px-3 py-2">
                <?php if (!empty($templates)): ?>
                    <?php foreach ($templates as $template): ?>
                        <option value="<?= $template['id'] ?>" <?= $template['is_default'] ? 'selected' : '' ?>>
                            <?= esc($template['name']) ?><?= $template['is_default'] ? ' (Current)' : '' ?>
                        </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="">No templates available</option>
                <?php endif; ?>
            </select>
            <p class="text-sm text-gray-500 mt-1">
                <a href="<?= site_url('receipts/templates') ?>" class="text-blue-600 hover:text-blue-800">
                    <i class="fas fa-cog"></i> Manage Receipt Templates
                </a>
            </p>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 font-bold">Save Settings</button>
        </div>
    </form>
</div>

<?= $this->endSection() ?>