<?php $this->extend('templates/header'); ?>

<?php $this->section('content'); ?>

<div class="w-full mt-10">
    <?php if (session()->has('message')): ?>
        <div class="mb-4 bg-green-50 border-l-4 border-green-500 text-green-800 rounded-lg p-4 shadow-sm">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-3 text-green-500"></i>
                <span><?= session('message') ?></span>
            </div>
        </div>
    <?php endif; ?>

    <?php if (session()->has('error')): ?>
        <div class="mb-4 bg-red-50 border-l-4 border-red-500 text-red-800 rounded-lg p-4 shadow-sm">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-3 text-red-500"></i>
                <span><?= session('error') ?></span>
            </div>
        </div>
    <?php endif; ?>

    <div class="max-w-2xl mx-auto px-4">
        <div class="border border-yellow-400 rounded-lg shadow-lg overflow-hidden">
            <div class="bg-yellow-400 text-gray-900 px-6 py-4">
                <h5 class="text-lg font-semibold mb-0">⚠️ Fix Supplier Ledger Accounting</h5>
            </div>
            <div class="p-6 space-y-4">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <strong class="text-gray-900 block mb-2">What this will do:</strong>
                    <ul class="mb-0 mt-2 list-disc list-inside space-y-1 text-gray-700">
                        <li>Swap all DEBIT and CREDIT values in the supplier ledger</li>
                        <li>Recalculate all running balances using the correct formula (CREDIT - DEBIT)</li>
                        <li>This fixes historical entries that were recorded with incorrect accounting logic</li>
                    </ul>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <strong class="text-gray-900 block mb-2">⚠️ Important:</strong>
                    <ul class="mb-0 mt-2 list-disc list-inside space-y-1 text-gray-700">
                        <li>This operation <strong>cannot be undone</strong></li>
                        <li>All ledger balances will be recalculated from the beginning</li>
                        <li>Make sure you have a database backup before proceeding</li>
                        <li>This should only be run once to fix historical data</li>
                    </ul>
                </div>

                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <strong class="text-gray-900 block mb-2">Result after fix:</strong>
                    <ul class="mb-0 mt-2 list-disc list-inside space-y-1 text-gray-700">
                        <li><strong>DEBIT:</strong> Payments, Returns, Advances (reduces what we owe)</li>
                        <li><strong>CREDIT:</strong> Purchases (increases what we owe)</li>
                        <li><strong>Balance:</strong> Positive = Amount owed to supplier</li>
                        <li><strong>Formula:</strong> Balance = Total Credits - Total Debits</li>
                    </ul>
                </div>

                <form method="POST" class="mt-6">
                    <?= csrf_field(); ?>

                    <div class="flex items-center mb-4">
                        <input class="w-4 h-4 rounded border-gray-300 text-yellow-400 focus:ring-yellow-400 cursor-pointer" type="checkbox" id="confirmCheckbox" required>
                        <label class="ml-3 text-gray-700" for="confirmCheckbox">
                            I understand this will permanently fix all ledger entries and I have a database backup
                        </label>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="px-6 py-3 bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-semibold rounded-lg disabled:opacity-50 disabled:cursor-not-allowed transition-colors" id="submitBtn" disabled>
                            <i class="fas fa-tools mr-2"></i> Fix Accounting Now
                        </button>
                        <a href="<?= site_url('supplier-ledger'); ?>" class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition-colors inline-flex items-center">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('confirmCheckbox').addEventListener('change', function() {
        document.getElementById('submitBtn').disabled = !this.checked;
    });
</script>

<?php $this->endSection(); ?>