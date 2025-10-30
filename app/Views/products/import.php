<?php

/** @var string $title */
?>
<?= $this->extend('templates/header') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-semibold mb-4 flex items-center gap-2">
        <i class="fa-solid fa-file-import text-slate-600"></i> <?= esc($title ?? 'Import Products') ?>
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
        <form action="<?= site_url('products/import') ?>" method="post" enctype="multipart/form-data" class="space-y-4">
            <?= csrf_field() ?>
            <div>
                <label class="block text-sm font-medium text-gray-700">CSV File</label>
                <input type="file" name="csv_file" accept=".csv,.txt" required class="mt-1 block w-full border border-gray-300 rounded p-2" />
                <p class="text-xs text-gray-500 mt-1">Max 5 MB. UTF-8 CSV recommended.</p>
            </div>
            <div class="text-sm text-gray-700">
                <p class="mb-2 font-medium">Accepted columns (case-insensitive):</p>
                <ul class="list-disc pl-5 space-y-1">
                    <li><code>name</code> (required)</li>
                    <li><code>price</code> or <code>unit_price</code> (one is optional but recommended)</li>
                    <li><code>cost_price</code> (optional, included as requested)</li>
                    <li><code>code</code>, <code>barcode</code> (used to update if match found)</li>
                    <li><code>quantity</code>, <code>stock_alert</code>, <code>description</code> (optional)</li>
                </ul>
                <p class="mt-2 text-xs text-gray-500">Upsert strategy: match by <strong>barcode</strong> (preferred) or <strong>code</strong> within the selected store. If no match, a new product is created.</p>
            </div>
            <div>
                <button type="submit" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-4 py-2 rounded">
                    <i class="fa-solid fa-file-import"></i>
                    Import
                </button>
                <a href="#" onclick="downloadSample(); return false;" class="ml-3 text-indigo-700 hover:underline text-sm">Download sample CSV</a>
            </div>
        </form>
    </div>
</div>

<script>
    function downloadSample() {
        const rows = [
            ['name', 'code', 'barcode', 'price', 'cost_price', 'quantity', 'stock_alert', 'description'],
            ['Sample Product', 'SP-001', '1234567890123', '150', '120', '10', '2', 'Sample item'],
            ['Another Product', 'AP-002', '9876543210987', '200', '130', '5', '1', 'Optional desc']
        ];
        let csv = rows.map(r => r.map(v => '"' + String(v).replaceAll('"', '""') + '"').join(',')).join('\r\n');
        const blob = new Blob([csv], {
            type: 'text/csv;charset=utf-8;'
        });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'products_import_sample.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }
</script>
<?= $this->endSection() ?>