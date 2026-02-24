<?php

/** @var string $title */
?>
<?= $this->extend('templates/header') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-semibold mb-4 flex items-center gap-2">
        <i class="fa-solid fa-file-import text-slate-600"></i> <?= esc($title ?? lang('Products.import_products_title')) ?>
    </h1>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-red-50 text-red-700 border border-red-200 rounded p-3 mb-4">
            <?= nl2br(esc(session()->getFlashdata('error'))) ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-green-50 text-green-700 border border-green-200 rounded p-3 mb-4">
            <?= nl2br(esc(session()->getFlashdata('success'))) ?>
        </div>
    <?php endif; ?>

    <div class="bg-white shadow rounded p-4">
        <form id="products-import-form" action="<?= site_url('products/import') ?>" method="post" enctype="multipart/form-data" class="space-y-4">
            <?= csrf_field() ?>
            <div>
                <label class="block text-sm font-medium text-gray-700"><?= lang('Products.csv_file') ?></label>
                <input type="file" name="csv_file" accept=".csv,.txt" required class="mt-1 block w-full border border-gray-300 rounded p-2" />
                <p class="text-xs text-gray-500 mt-1"><?= lang('Products.max_5mb_utf8_csv') ?></p>
            </div>
            <div class="text-sm text-gray-700">
                <p class="mb-2 font-medium"><?= lang('Products.accepted_columns_case_insensitive') ?></p>
                <ul class="list-disc pl-5 space-y-1">
                    <li><?= lang('Products.import_col_name_required') ?></li>
                    <li><?= lang('Products.import_col_price_or_unit_price') ?></li>
                    <li><?= lang('Products.import_col_cost_price_optional') ?></li>
                    <li><?= lang('Products.import_col_code_barcode') ?></li>
                    <li><?= lang('Products.import_col_quantity_stock_alert_description') ?></li>
                </ul>
                <p class="mt-2 text-xs text-gray-500"><?= lang('Products.import_upsert_strategy') ?></p>
            </div>
            <div>
                <a href="<?= site_url('products') ?>" class="inline-flex items-center gap-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded">
                    <i class="fa-solid fa-arrow-left"></i>
                    <?= lang('Products.back_to_products') ?>
                </a>
                <button type="submit" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-4 py-2 rounded">
                    <i class="fa-solid fa-file-import"></i>
                    <?= lang('Products.import') ?>
                </button>
                <a href="<?= base_url('assets/samples/products_import_sample.csv') ?>" class="ml-3 text-indigo-700 hover:underline text-sm" download><?= lang('Products.download_sample_csv') ?></a>
            </div>
        </form>
    </div>
</div>

<script>
    const importTexts = {
        chooseCsvFile: <?= json_encode(lang('Products.choose_csv_file_to_upload')) ?>,
        invalidFileType: <?= json_encode(lang('Products.invalid_file_type_csv_txt')) ?>,
    };

    // Client-side guard to ensure a file is selected
    document.getElementById('products-import-form')?.addEventListener('submit', function(e) {
        const input = this.querySelector('input[type="file"][name="csv_file"]');
        if (!input || !input.files || input.files.length === 0) {
            e.preventDefault();
            alert(importTexts.chooseCsvFile);
            input && input.focus();
            return false;
        }
        const file = input.files[0];
        const allowed = ['text/csv', 'application/vnd.ms-excel', 'text/plain'];
        const extOk = /\.(csv|txt)$/i.test(file.name);
        if (!extOk) {
            e.preventDefault();
            alert(importTexts.invalidFileType);
            return false;
        }
    });
</script>
<?= $this->endSection() ?>