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

        <div class="flex justify-end">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 font-bold"><?= lang('Settings.save') ?></button>
        </div>
    </form>
</div>

<?= $this->endSection() ?>