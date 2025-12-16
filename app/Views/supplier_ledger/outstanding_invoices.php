<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="min-h-screen bg-slate-100">
    <!-- Top Bar -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="h-16 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-red-500 to-pink-600 text-white flex items-center justify-center shadow-md">
                        <i class="fas fa-file-invoice-dollar text-lg"></i>
                    </div>
                    <h1 class="text-xl font-bold text-gray-900"><?= esc($title) ?></h1>
                </div>
                <div class="flex gap-2">
                    <a href="<?= base_url('supplier-ledger/view/' . $supplier['id']) ?>"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Ledger
                    </a>
                    <a href="<?= base_url('supplier-ledger/lumpsum-payment/' . $supplier['id']) ?>"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white text-sm font-semibold rounded-lg transition-colors shadow-md">
                        <i class="fas fa-money-bill-wave mr-2"></i> Make Payment
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Supplier Info -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 mb-2"><?= esc($supplier['name']) ?></h2>
                    <div class="flex gap-6 text-sm">
                        <p class="text-gray-600"><span class="font-semibold text-gray-700">Phone:</span> <?= esc($supplier['phone']) ?></p>
                        <p class="text-gray-600"><span class="font-semibold text-gray-700">Email:</span> <?= esc($supplier['email']) ?></p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm font-semibold text-gray-500 mb-1">Total Outstanding</div>
                    <div class="text-3xl font-bold text-red-600"><?= number_to_currency($totalOutstanding, 'PKR', 'en_PK', 2) ?></div>
                </div>
            </div>
        </div>

        <!-- Outstanding Invoices Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-slate-50 to-slate-100">
                <h3 class="text-lg font-semibold text-gray-900">Outstanding Purchases</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Total Amount</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Outstanding</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (!empty($outstandingInvoices)): ?>
                            <?php foreach ($outstandingInvoices as $invoice): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?= date('d M Y', strtotime($invoice['date'])) ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <?= esc($invoice['description']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-700">
                                        <?= number_to_currency($invoice['amount'], 'PKR', 'en_PK', 2) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                        <span class="font-semibold text-red-600">
                                            <?= number_to_currency($invoice['remaining'], 'PKR', 'en_PK', 2) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                        <?php if ($invoice['purchase_id']): ?>
                                            <a href="<?= base_url('purchases/view/' . $invoice['purchase_id']) ?>"
                                                class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition-colors"
                                                target="_blank">
                                                <i class="fas fa-eye mr-1"></i> View
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-xs">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="bg-gray-50 font-semibold">
                                <td colspan="3" class="px-6 py-4 text-sm text-gray-900 text-right">Total Outstanding:</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-700">
                                    <?= number_to_currency($totalOutstanding, 'PKR', 'en_PK', 2) ?>
                                </td>
                                <td></td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-check-circle text-4xl text-green-300 mb-3"></i>
                                        <p class="text-gray-500 text-sm font-medium">No outstanding purchases</p>
                                        <p class="text-gray-400 text-xs mt-1">All purchases have been paid</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if (!empty($outstandingInvoices)): ?>
            <div class="mt-6 flex justify-end">
                <a href="<?= base_url('supplier-ledger/lumpsum-payment/' . $supplier['id']) ?>"
                    class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white text-sm font-semibold rounded-lg transition-all duration-200 shadow-md hover:shadow-lg">
                    <i class="fas fa-money-bill-wave mr-2"></i> Make Payment for Outstanding Purchases
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>