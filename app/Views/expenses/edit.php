<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php $currencySymbol = session()->get('currency_symbol') ?: '$'; ?>
<div class="max-w-3xl mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-4">Edit Expense</h1>

    <?php if (!empty($errors ?? [])): ?>
        <div class="mb-3 p-3 rounded bg-red-50 text-red-800 border border-red-200">
            <ul class="list-disc pl-5">
                <?php foreach ($errors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" action="<?= site_url('expenses/edit/' . ($expense['id'] ?? 0)) ?>" class="bg-white border rounded-lg p-4 shadow-sm">
        <?= csrf_field() ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label class="text-xs text-gray-500">Date</label>
                <input type="date" name="date" value="<?= esc(set_value('date', $expense['date'] ?? date('Y-m-d'))) ?>" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="text-xs text-gray-500">Category</label>
                <select name="category_id" class="w-full border rounded px-3 py-2">
                    <option value="">-- Select --</option>
                    <?php $selectedId = set_value('category_id', $expense['category_id'] ?? '');
                    foreach (($categories ?? []) as $cat): $sel = ((string)$selectedId === (string)$cat['id']) ? 'selected' : ''; ?>
                        <option value="<?= (int)$cat['id'] ?>" <?= $sel ?>><?= esc($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="text-xs text-gray-500">Vendor</label>
                <input type="text" name="vendor" value="<?= esc(set_value('vendor', $expense['vendor'] ?? '')) ?>" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="text-xs text-gray-500">Amount (<?= esc($currencySymbol) ?>)</label>
                <input type="number" step="0.01" name="amount" value="<?= esc(set_value('amount', $expense['amount'] ?? '')) ?>" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="text-xs text-gray-500">Tax (<?= esc($currencySymbol) ?>)</label>
                <input type="number" step="0.01" name="tax" value="<?= esc(set_value('tax', $expense['tax'] ?? '0.00')) ?>" class="w-full border rounded px-3 py-2">
            </div>
            <div class="md:col-span-2">
                <label class="text-xs text-gray-500">Description</label>
                <input type="text" name="description" value="<?= esc(set_value('description', $expense['description'] ?? '')) ?>" class="w-full border rounded px-3 py-2">
            </div>
            <div class="md:col-span-2">
                <label class="text-xs text-gray-500">Notes</label>
                <textarea name="notes" class="w-full border rounded px-3 py-2" rows="3"><?= esc(set_value('notes', $expense['notes'] ?? '')) ?></textarea>
            </div>
            <div class="md:col-span-2">
                <label class="text-xs text-gray-500">Replace Receipt (optional)</label>
                <input type="file" name="receipt" accept="image/*,application/pdf" class="w-full border rounded px-3 py-2">
                <?php if (!empty($expense['receipt_path'])): ?>
                    <div class="text-xs text-gray-500 mt-1">Current: <a href="<?= base_url($expense['receipt_path']) ?>" target="_blank" class="text-blue-600 hover:underline">View</a></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="mt-4 flex items-center gap-2">
            <button class="btn btn-primary" type="submit">Update</button>
            <a class="btn btn-muted" href="<?= site_url('expenses') ?>">Cancel</a>
        </div>
    </form>
</div>
<?= $this->endSection() ?>