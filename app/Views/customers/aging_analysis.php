<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php
helper('permissions');
$currencySymbol = session()->get('currency_symbol') ?: '$';
$customer = $customer ?? null;
$agingBuckets = $agingBuckets ?? [];
$outstanding = $outstanding ?? 0;
$invoices = $invoices ?? [];
?>
<!-- Header -->
<div class="mb-6">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-4">
            <div class="h-16 w-16 rounded-xl bg-gradient-to-br from-orange-500 to-red-600 flex items-center justify-center shadow-lg">
                <i class="fas fa-clock text-white text-2xl"></i>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900"><?= lang('Customers.aging_analysis') ?></h1>
                <p class="text-gray-600 mt-1"><?= lang('Customers.aging_analysis_subtitle') ?></p>
            </div>
        </div>
        <a href="<?= site_url('customers/ledger/' . ($customer['id'] ?? 0)) ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-all duration-200 border border-gray-300">
            <i class="fas fa-arrow-left"></i>
            <?= lang('Customers.back_to_ledger') ?>
        </a>
    </div>

    <!-- Customer Info Card -->
    <?php if ($customer): ?>
        <div class="bg-gradient-to-r from-orange-600 to-red-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold mb-1"><?= esc($customer['name'] ?? lang('Customers.unknown_customer')) ?></h2>
                    <div class="flex items-center gap-4 text-orange-100">
                        <?php if (!empty($customer['phone'])): ?>
                            <span class="flex items-center gap-1">
                                <i class="fas fa-phone"></i>
                                <?= esc($customer['phone']) ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($customer['email'])): ?>
                            <span class="flex items-center gap-1">
                                <i class="fas fa-envelope"></i>
                                <?= esc($customer['email']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-orange-100 text-sm"><?= lang('Customers.total_outstanding') ?></p>
                    <p class="text-3xl font-bold"><?= $currencySymbol . number_format($outstanding, 2) ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Aging Summary Cards -->
<?php if (!empty($agingBuckets) && $outstanding > 0): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- 0-30 days -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-all duration-200">
            <div class="flex items-start justify-between mb-4">
                <div class="h-12 w-12 rounded-lg bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center shadow-lg">
                    <i class="fas fa-check text-white text-xl"></i>
                </div>
                <div class="h-3 w-3 rounded-full bg-green-500 animate-pulse"></div>
            </div>
            <p class="text-xs font-bold text-gray-500 uppercase mb-2"><?= lang('Customers.aging_0_30_days') ?></p>
            <p class="text-3xl font-bold text-gray-900 mb-2"><?= $currencySymbol . number_format((float)($agingBuckets['0_30'] ?? 0), 2) ?></p>
            <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-green-400 to-green-600" style="width: <?= $outstanding > 0 ? min(100, ($agingBuckets['0_30'] ?? 0) / $outstanding * 100) : 0 ?>%"></div>
            </div>
            <p class="text-xs text-gray-500 mt-2"><?= ($outstanding > 0 ? number_format(($agingBuckets['0_30'] ?? 0) / $outstanding * 100, 1) : 0) . lang('Customers.percent_of_total') ?></p>
        </div>

        <!-- 31-60 days -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-all duration-200">
            <div class="flex items-start justify-between mb-4">
                <div class="h-12 w-12 rounded-lg bg-gradient-to-br from-yellow-400 to-yellow-600 flex items-center justify-center shadow-lg">
                    <i class="fas fa-exclamation text-white text-xl"></i>
                </div>
                <div class="h-3 w-3 rounded-full bg-yellow-500"></div>
            </div>
            <p class="text-xs font-bold text-gray-500 uppercase mb-2"><?= lang('Customers.aging_31_60_days') ?></p>
            <p class="text-3xl font-bold text-gray-900 mb-2"><?= $currencySymbol . number_format((float)($agingBuckets['31_60'] ?? 0), 2) ?></p>
            <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-yellow-400 to-yellow-600" style="width: <?= $outstanding > 0 ? min(100, ($agingBuckets['31_60'] ?? 0) / $outstanding * 100) : 0 ?>%"></div>
            </div>
            <p class="text-xs text-gray-500 mt-2"><?= ($outstanding > 0 ? number_format(($agingBuckets['31_60'] ?? 0) / $outstanding * 100, 1) : 0) . lang('Customers.percent_of_total') ?></p>
        </div>

        <!-- 61-90 days -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-all duration-200">
            <div class="flex items-start justify-between mb-4">
                <div class="h-12 w-12 rounded-lg bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center shadow-lg">
                    <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                </div>
                <div class="h-3 w-3 rounded-full bg-orange-500"></div>
            </div>
            <p class="text-xs font-bold text-gray-500 uppercase mb-2"><?= lang('Customers.aging_61_90_days') ?></p>
            <p class="text-3xl font-bold text-gray-900 mb-2"><?= $currencySymbol . number_format((float)($agingBuckets['61_90'] ?? 0), 2) ?></p>
            <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-orange-400 to-orange-600" style="width: <?= $outstanding > 0 ? min(100, ($agingBuckets['61_90'] ?? 0) / $outstanding * 100) : 0 ?>%"></div>
            </div>
            <p class="text-xs text-gray-500 mt-2"><?= ($outstanding > 0 ? number_format(($agingBuckets['61_90'] ?? 0) / $outstanding * 100, 1) : 0) . lang('Customers.percent_of_total') ?></p>
        </div>

        <!-- 90+ days -->
        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow-lg p-6 hover:shadow-xl transition-all duration-200 text-white">
            <div class="flex items-start justify-between mb-4">
                <div class="h-12 w-12 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center">
                    <i class="fas fa-bell text-white text-xl"></i>
                </div>
                <div class="h-3 w-3 rounded-full bg-white animate-pulse"></div>
            </div>
            <p class="text-xs font-bold uppercase mb-2 text-red-100"><?= lang('Customers.aging_90_plus_days') ?></p>
            <p class="text-3xl font-bold mb-2"><?= $currencySymbol . number_format((float)($agingBuckets['90_plus'] ?? 0), 2) ?></p>
            <div class="h-2 bg-white/20 rounded-full overflow-hidden">
                <div class="h-full bg-white" style="width: <?= $outstanding > 0 ? min(100, ($agingBuckets['90_plus'] ?? 0) / $outstanding * 100) : 0 ?>%"></div>
            </div>
            <p class="text-xs mt-2 text-red-100"><?= ($outstanding > 0 ? number_format(($agingBuckets['90_plus'] ?? 0) / $outstanding * 100, 1) : 0) . lang('Customers.percent_of_total') ?> - <strong><?= lang('Customers.action_required') ?></strong></p>
        </div>
    </div>
<?php endif; ?>

<!-- Detailed Invoice List -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="bg-gradient-to-r from-gray-50 to-orange-50 px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
            <i class="fas fa-file-invoice text-orange-600"></i>
            <?= lang('Customers.outstanding_invoices_breakdown') ?>
        </h3>
    </div>

    <?php if (empty($invoices)): ?>
        <div class="p-12 text-center">
            <i class="fas fa-check-circle text-green-500 text-6xl mb-4"></i>
            <p class="text-xl font-semibold text-gray-700 mb-2"><?= lang('Customers.no_outstanding_invoices_title') ?></p>
            <p class="text-gray-500"><?= lang('Customers.customer_cleared_payments') ?></p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase"><?= lang('Customers.invoice') ?></th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase"><?= lang('Customers.date') ?></th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase"><?= lang('Customers.age_days') ?></th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase"><?= lang('Customers.total_amount') ?></th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase"><?= lang('Customers.paid_amount') ?></th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase"><?= lang('Customers.due_amount') ?></th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase"><?= lang('Customers.status') ?></th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase"><?= lang('Customers.actions') ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($invoices as $invoice):
                        $dueAmount = (float)($invoice['due_amount'] ?? 0);
                        $totalAmount = (float)($invoice['total'] ?? 0);
                        $paidAmount = $totalAmount - $dueAmount;
                        $createdDate = new DateTime($invoice['created_at']);
                        $now = new DateTime();
                        $age = $now->diff($createdDate)->days;

                        if ($age <= 30) {
                            $ageClass = 'text-green-700 bg-green-50';
                            $ageBadge = 'bg-green-100 text-green-700';
                        } elseif ($age <= 60) {
                            $ageClass = 'text-yellow-700 bg-yellow-50';
                            $ageBadge = 'bg-yellow-100 text-yellow-700';
                        } elseif ($age <= 90) {
                            $ageClass = 'text-orange-700 bg-orange-50';
                            $ageBadge = 'bg-orange-100 text-orange-700';
                        } else {
                            $ageClass = 'text-red-700 bg-red-50';
                            $ageBadge = 'bg-red-100 text-red-700';
                        }
                    ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <a href="<?= site_url('sales/receipt/' . $invoice['id']) ?>"
                                    target="_blank"
                                    class="text-blue-600 hover:text-blue-800 font-semibold hover:underline">
                                    <?= esc($invoice['invoice_no']) ?>
                                </a>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <?= date('M d, Y', strtotime($invoice['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold <?= $ageBadge ?>">
                                    <?= str_replace('{days}', (string)$age, lang('Customers.days_label')) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-semibold text-gray-900">
                                <?= $currencySymbol ?><?= number_format($totalAmount, 2) ?>
                            </td>
                            <td class="px-6 py-4 text-right text-emerald-700 font-medium">
                                <?= $currencySymbol ?><?= number_format($paidAmount, 2) ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="font-bold text-lg <?= $age > 90 ? 'text-red-600' : 'text-gray-900' ?>">
                                    <?= $currencySymbol ?><?= number_format($dueAmount, 2) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <?php $status = $invoice['payment_status'] ?? 'due'; ?>
                                <?php $statusLabel = $status === 'paid' ? lang('Customers.paid') : ($status === 'partial' ? lang('Customers.partial') : lang('Customers.due')); ?>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold <?= $ageClass ?>">
                                    <?= $statusLabel ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="<?= site_url('customers/lumpsum-payment/' . ($customer['id'] ?? 0)) ?>"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                                    <i class="fas fa-money-bill-wave"></i>
                                    <?= lang('Customers.pay') ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-gray-50 border-t-2 border-gray-300">
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-right font-bold text-gray-900"><?= lang('Customers.total_outstanding') ?>:</td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-2xl font-bold text-red-600">
                                <?= $currencySymbol ?><?= number_format($outstanding, 2) ?>
                            </span>
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php endif; ?>
</div>

</div>
</div>

<?= $this->endSection() ?>