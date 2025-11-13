<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="min-h-screen bg-slate-100">
    <!-- Top Bar -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="h-16 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center shadow-md">
                        <i class="fas fa-book text-lg"></i>
                    </div>
                    <h1 class="text-xl font-bold text-gray-900"><?= esc($title) ?></h1>
                </div>
                <div class="flex gap-2">
                    <a href="<?= base_url('suppliers') ?>"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                        <i class="fas fa-arrow-left mr-2"></i> Back
                    </a>
                    <a href="<?= base_url('supplier-ledger/print/' . $supplier['id'] . '?from=' . $from . '&to=' . $to) ?>"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm"
                        target="_blank">
                        <i class="fas fa-print mr-2"></i> Print
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Supplier Info Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 mb-3"><?= esc($supplier['name']) ?></h2>
                    <div class="space-y-2 text-sm">
                        <p class="text-gray-600"><span class="font-semibold text-gray-700">Phone:</span> <?= esc($supplier['phone']) ?></p>
                        <p class="text-gray-600"><span class="font-semibold text-gray-700">Email:</span> <?= esc($supplier['email']) ?></p>
                        <p class="text-gray-600"><span class="font-semibold text-gray-700">Address:</span> <?= esc($supplier['address']) ?></p>
                    </div>
                </div>
                <div class="flex flex-col justify-center items-end space-y-3">
                    <div class="text-right">
                        <span class="text-sm font-semibold text-gray-700 mr-2">Opening Balance:</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold <?= $openingBalance > 0 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' ?>">
                            <?= number_to_currency($openingBalance, 'PKR', 'en_PK', 2) ?>
                        </span>
                    </div>
                    <div class="text-right">
                        <span class="text-sm font-semibold text-gray-700 mr-2">Closing Balance:</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold <?= $closingBalance > 0 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' ?>">
                            <?= number_to_currency($closingBalance, 'PKR', 'en_PK', 2) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Date Filter -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <form method="get" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div>
                    <label for="from" class="block text-sm font-semibold text-gray-700 mb-2">From Date</label>
                    <input type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        id="from" name="from" value="<?= esc($from) ?>" required>
                </div>
                <div>
                    <label for="to" class="block text-sm font-semibold text-gray-700 mb-2">To Date</label>
                    <input type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        id="to" name="to" value="<?= esc($to) ?>" required>
                </div>
                <div>
                    <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Transactions Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-slate-50 to-slate-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Invoice/Ref</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Debit (Dr)</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Credit (Cr)</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if ($openingBalance != 0): ?>
                            <tr class="bg-blue-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= esc($from) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">Opening Balance</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">-</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">-</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">-</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 text-right">
                                    <?= number_to_currency($openingBalance, 'PKR', 'en_PK', 2) ?>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php if (!empty($transactions)): ?>
                            <?php foreach ($transactions as $transaction): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?= date('d M Y', strtotime($transaction['date'])) ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <?= esc($transaction['description']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <?php if ($transaction['purchase_id']): ?>
                                            <a href="<?= base_url('purchases/view/' . $transaction['purchase_id']) ?>"
                                                class="text-blue-600 hover:text-blue-800 hover:underline"
                                                target="_blank">
                                                View Purchase
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-500">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                        <?php if ($transaction['debit'] > 0): ?>
                                            <span class="font-medium text-red-600">
                                                <?= number_to_currency($transaction['debit'], 'PKR', 'en_PK', 2) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-gray-500">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                        <?php if ($transaction['credit'] > 0): ?>
                                            <span class="font-medium text-green-600">
                                                <?= number_to_currency($transaction['credit'], 'PKR', 'en_PK', 2) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-gray-500">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                        <?= number_to_currency($transaction['running_balance'], 'PKR', 'en_PK', 2) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i>
                                        <p class="text-gray-500 text-sm">No transactions found for the selected period</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <?php if (!empty($transactions)): ?>
                        <tfoot class="bg-gray-100">
                            <tr>
                                <th colspan="3" class="px-6 py-3 text-right text-sm font-bold text-gray-900">Total:</th>
                                <th class="px-6 py-3 text-right text-sm font-bold text-gray-900">
                                    <?= number_to_currency($totalDebit, 'PKR', 'en_PK', 2) ?>
                                </th>
                                <th class="px-6 py-3 text-right text-sm font-bold text-gray-900">
                                    <?= number_to_currency($totalCredit, 'PKR', 'en_PK', 2) ?>
                                </th>
                                <th class="px-6 py-3 text-right text-sm font-bold text-gray-900">
                                    <?= number_to_currency($closingBalance, 'PKR', 'en_PK', 2) ?>
                                </th>
                            </tr>
                        </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>

        <!-- Summary Box -->
        <?php if (!empty($transactions)): ?>
            <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-6 mt-6">
                <h3 class="text-sm font-bold text-gray-900 mb-3">Summary</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">Opening Balance:</span>
                        <div class="font-semibold text-gray-900"><?= number_to_currency($openingBalance, 'PKR', 'en_PK', 2) ?></div>
                    </div>
                    <div>
                        <span class="text-gray-600">Total Purchases (Dr):</span>
                        <div class="font-semibold text-red-600"><?= number_to_currency($totalDebit, 'PKR', 'en_PK', 2) ?></div>
                    </div>
                    <div>
                        <span class="text-gray-600">Total Payments (Cr):</span>
                        <div class="font-semibold text-green-600"><?= number_to_currency($totalCredit, 'PKR', 'en_PK', 2) ?></div>
                    </div>
                    <div>
                        <span class="text-gray-600">Closing Balance:</span>
                        <div class="font-bold text-gray-900"><?= number_to_currency($closingBalance, 'PKR', 'en_PK', 2) ?></div>
                        <?php if ($closingBalance > 0): ?>
                            <div class="text-xs text-red-600 mt-1">Amount payable to supplier</div>
                        <?php elseif ($closingBalance < 0): ?>
                            <div class="text-xs text-green-600 mt-1">Amount receivable from supplier</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>