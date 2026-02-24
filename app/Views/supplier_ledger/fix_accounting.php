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
                <h5 class="text-lg font-semibold mb-0"><?= lang('SupplierLedger.fix_accounting_title') ?></h5>
            </div>
            <div class="p-6 space-y-4">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <strong class="text-gray-900 block mb-2"><?= lang('SupplierLedger.what_this_will_do') ?></strong>
                    <ul class="mb-0 mt-2 list-disc list-inside space-y-1 text-gray-700">
                        <li><?= lang('SupplierLedger.fix_step_1') ?></li>
                        <li><?= lang('SupplierLedger.fix_step_2') ?></li>
                        <li><?= lang('SupplierLedger.fix_step_3') ?></li>
                    </ul>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <strong class="text-gray-900 block mb-2"><?= lang('SupplierLedger.important') ?></strong>
                    <ul class="mb-0 mt-2 list-disc list-inside space-y-1 text-gray-700">
                        <li><?= lang('SupplierLedger.important_step_1') ?></li>
                        <li><?= lang('SupplierLedger.important_step_2') ?></li>
                        <li><?= lang('SupplierLedger.important_step_3') ?></li>
                        <li><?= lang('SupplierLedger.important_step_4') ?></li>
                    </ul>
                </div>

                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <strong class="text-gray-900 block mb-2"><?= lang('SupplierLedger.result_after_fix') ?></strong>
                    <ul class="mb-0 mt-2 list-disc list-inside space-y-1 text-gray-700">
                        <li><strong><?= lang('SupplierLedger.debit_dr') ?>:</strong> <?= lang('SupplierLedger.debit_explain') ?></li>
                        <li><strong><?= lang('SupplierLedger.credit_cr') ?>:</strong> <?= lang('SupplierLedger.credit_explain') ?></li>
                        <li><strong><?= lang('SupplierLedger.balance') ?>:</strong> <?= lang('SupplierLedger.balance_explain') ?></li>
                        <li><strong><?= lang('SupplierLedger.formula') ?>:</strong> <?= lang('SupplierLedger.formula_explain') ?></li>
                    </ul>
                </div>

                <form method="POST" class="mt-6">
                    <?= csrf_field(); ?>

                    <div class="flex items-center mb-4">
                        <input class="w-4 h-4 rounded border-gray-300 text-yellow-400 focus:ring-yellow-400 cursor-pointer" type="checkbox" id="confirmCheckbox" required>
                        <label class="ml-3 text-gray-700" for="confirmCheckbox">
                            <?= lang('SupplierLedger.confirm_fix_with_backup') ?>
                        </label>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="px-6 py-3 bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-semibold rounded-lg disabled:opacity-50 disabled:cursor-not-allowed transition-colors" id="submitBtn" disabled>
                            <i class="fas fa-tools mr-2"></i> <?= lang('SupplierLedger.fix_accounting_now') ?>
                        </button>
                        <a href="<?= site_url('supplier-ledger'); ?>" class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition-colors inline-flex items-center">
                            <i class="fas fa-times mr-2"></i> <?= lang('SupplierLedger.cancel') ?>
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