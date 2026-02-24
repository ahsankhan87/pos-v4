<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php $currencySymbol = session()->get('currency_symbol') ?: '$'; ?>
<div class="max-w-3xl mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-3">
        <h1 class="text-2xl font-bold"><?= lang('Expenses.expenseDetails') ?></h1>
        <a class="btn btn-muted" href="<?= site_url('expenses') ?>\"><?= lang('Expenses.back') ?></a>
    </div>
    <div class="bg-white border rounded-lg p-4 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <div class="text-xs text-gray-500"><?= lang('Expenses.date') ?></div>
                <div class="font-semibold"><?= esc($expense['date'] ?? '') ?></div>
            </div>
            <div>
                <div class="text-xs text-gray-500"><?= lang('Expenses.category') ?></div>
                <div class="font-semibold"><?= esc($expense['category_name'] ?? '-') ?></div>
            </div>
            <div>
                <div class="text-xs text-gray-500"><?= lang('Expenses.vendor') ?></div>
                <div class="font-semibold"><?= esc($expense['vendor'] ?? '-') ?></div>
            </div>
            <div>
                <div class="text-xs text-gray-500"><?= lang('Expenses.amount') ?></div>
                <div class="font-semibold"><?= $currencySymbol . number_format((float)($expense['amount'] ?? 0), 2) ?></div>
            </div>
            <div>
                <div class="text-xs text-gray-500"><?= lang('Expenses.tax') ?></div>
                <div class="font-semibold"><?= $currencySymbol . number_format((float)($expense['tax'] ?? 0), 2) ?></div>
            </div>
            <div class="md:col-span-2">
                <div class="text-xs text-gray-500"><?= lang('Expenses.description') ?></div>
                <div class="font-semibold"><?= esc($expense['description'] ?? '-') ?></div>
            </div>
            <div class="md:col-span-2">
                <div class="text-xs text-gray-500"><?= lang('Expenses.notes') ?></div>
                <div class="font-semibold whitespace-pre-wrap"><?= esc($expense['notes'] ?? '-') ?></div>
            </div>
            <?php if (!empty($expense['receipt_path'])): ?>
                <div class="md:col-span-2">
                    <div class="text-xs text-gray-500"><?= lang('Expenses.receipt') ?></div>
                    <a href="<?= base_url($expense['receipt_path']) ?>" target="_blank" class="text-blue-600 hover:underline"><?= lang('Expenses.viewAttachment') ?></a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>