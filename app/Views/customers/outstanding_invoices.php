<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php
$currencySymbol = session()->get('currency_symbol') ?: '$';
$customer = $customer ?? null;
$invoices = $invoices ?? [];
$totalOutstanding = 0;
foreach ($invoices as $inv) {
    $totalOutstanding += (float)($inv['due_amount'] ?? 0);
}
?>

<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100">
    <div class="max-w-7xl mx-auto px-4 py-6">

        <!-- Header Section -->
        <div class="mb-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <a href="<?= site_url('customers/ledger/' . ($customer['id'] ?? 0)) ?>"
                            class="text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="fas fa-arrow-left text-xl"></i>
                        </a>
                        <h1 class="text-3xl font-bold text-gray-900"><?= lang('Customers.outstanding_invoices') ?></h1>
                    </div>
                    <?php if ($customer): ?>
                        <div class="ml-12 flex items-center gap-4 text-sm text-gray-600">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-user text-blue-600"></i>
                                <span class="font-semibold text-gray-900"><?= esc($customer['name'] ?? lang('Customers.unknown')) ?></span>
                            </div>
                            <?php if (!empty($customer['phone'])): ?>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-phone text-green-600"></i>
                                    <span><?= esc($customer['phone']) ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($customer['email'])): ?>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-envelope text-purple-600"></i>
                                    <span><?= esc($customer['email']) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" onclick="openLumpsumPaymentModal()"
                        class="btn bg-gradient-to-r from-green-600 to-emerald-600 text-white hover:from-green-700 hover:to-emerald-700 shadow-lg">
                        <i class="fas fa-money-bill-wave mr-2"></i>
                        <?= lang('Customers.lumpsum_payment') ?>
                    </button>
                    <button type="button" onclick="window.print()" class="btn btn-secondary">
                        <i class="fas fa-print mr-2"></i>
                        <?= lang('Customers.print') ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-600"><?= lang('Customers.total_outstanding') ?></span>
                    <div class="h-10 w-10 rounded-lg bg-red-100 flex items-center justify-center">
                        <i class="fas fa-exclamation-circle text-red-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-red-600"><?= $currencySymbol . number_format($totalOutstanding, 2) ?></p>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-600"><?= lang('Customers.total_invoices') ?></span>
                    <div class="h-10 w-10 rounded-lg bg-blue-100 flex items-center justify-center">
                        <i class="fas fa-file-invoice text-blue-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900"><?= count($invoices) ?></p>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-600"><?= lang('Customers.oldest_invoice') ?></span>
                    <div class="h-10 w-10 rounded-lg bg-orange-100 flex items-center justify-center">
                        <i class="fas fa-clock text-orange-600"></i>
                    </div>
                </div>
                <p class="text-sm font-semibold text-gray-900">
                    <?php if (!empty($invoices)):
                        $oldest = min(array_column($invoices, 'created_at'));
                        $date = new DateTime($oldest);
                        $now = new DateTime();
                        $diff = $now->diff($date);
                        echo str_replace('{days}', (string)$diff->days, lang('Customers.days_ago'));
                    else: ?>
                        -
                    <?php endif; ?>
                </p>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-600"><?= lang('Customers.average_due') ?></span>
                    <div class="h-10 w-10 rounded-lg bg-purple-100 flex items-center justify-center">
                        <i class="fas fa-chart-line text-purple-600"></i>
                    </div>
                </div>
                <p class="text-lg font-bold text-gray-900">
                    <?= count($invoices) > 0 ? $currencySymbol . number_format($totalOutstanding / count($invoices), 2) : $currencySymbol . '0.00' ?>
                </p>
            </div>
        </div>

        <!-- Invoices Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-list text-blue-600"></i>
                        <?= lang('Customers.invoice_details') ?>
                    </h2>
                    <?php if (count($invoices) > 0): ?>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="selectAllInvoices()" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                <i class="fas fa-check-square mr-1"></i><?= lang('Customers.select_all') ?>
                            </button>
                            <button type="button" onclick="clearSelection()" class="text-sm text-gray-600 hover:text-gray-800 font-medium">
                                <i class="fas fa-times-circle mr-1"></i><?= lang('Customers.clear') ?>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (empty($invoices)): ?>
                <!-- Empty State -->
                <div class="py-16 text-center">
                    <div class="mx-auto h-20 w-20 rounded-full bg-green-100 flex items-center justify-center mb-4">
                        <i class="fas fa-check-circle text-green-600 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2"><?= lang('Customers.all_caught_up') ?></h3>
                    <p class="text-gray-600"><?= lang('Customers.no_outstanding_invoices') ?></p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="w-12 px-6 py-3 text-left">
                                    <input type="checkbox" id="selectAllCheckbox" onchange="toggleAllInvoices(this)"
                                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <?= lang('Customers.invoice_no') ?>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <?= lang('Customers.date') ?>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <?= lang('Customers.age_days') ?>
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <?= lang('Customers.total_amount') ?>
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <?= lang('Customers.paid_amount') ?>
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <?= lang('Customers.due_amount') ?>
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <?= lang('Customers.status') ?>
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <?= lang('Customers.actions') ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($invoices as $invoice):
                                $dueAmount = (float)($invoice['due_amount'] ?? 0);
                                $totalAmount = (float)($invoice['total'] ?? 0);
                                $paidAmount = $totalAmount - $dueAmount;
                                $date = new DateTime($invoice['created_at'] ?? 'now');
                                $now = new DateTime();
                                $ageDays = $now->diff($date)->days;
                                $ageClass = $ageDays > 60 ? 'text-red-600 font-bold' : ($ageDays > 30 ? 'text-orange-600 font-semibold' : 'text-gray-700');
                            ?>
                                <tr class="hover:bg-gray-50 transition-colors invoice-row" data-invoice-id="<?= $invoice['id'] ?>" data-due="<?= $dueAmount ?>">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" class="invoice-checkbox h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                            data-invoice-id="<?= $invoice['id'] ?>" data-due="<?= $dueAmount ?>">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="<?= site_url('receipts/generate/' . $invoice['id']) ?>" target="_blank"
                                            class="text-blue-600 hover:text-blue-800 font-medium hover:underline">
                                            <?= esc($invoice['invoice_no'] ?? '-') ?>
                                            <i class="fas fa-external-link-alt text-xs ml-1"></i>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        <?= date('M d, Y', strtotime($invoice['created_at'] ?? 'now')) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm <?= $ageClass ?>">
                                        <?= str_replace('{days}', (string)$ageDays, lang('Customers.days_label')) ?>
                                        <?php if ($ageDays > 60): ?>
                                            <i class="fas fa-exclamation-triangle ml-1"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                                        <?= $currencySymbol . number_format($totalAmount, 2) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-green-600 font-medium">
                                        <?= $currencySymbol . number_format($paidAmount, 2) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-red-600">
                                        <?= $currencySymbol . number_format($dueAmount, 2) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <?php
                                        $status = $invoice['payment_status'] ?? 'due';
                                        $statusClass = $status === 'paid' ? 'bg-green-100 text-green-800' : ($status === 'partial' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
                                        $statusLabel = $status === 'paid' ? lang('Customers.paid') : ($status === 'partial' ? lang('Customers.partial') : lang('Customers.due'));
                                        ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold <?= $statusClass ?>">
                                            <?= $statusLabel ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <a href="<?= site_url('sales/receive-payment/' . $invoice['id']) ?>"
                                            class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                            <i class="fas fa-dollar-sign mr-1"></i>
                                            <?= lang('Customers.pay_now') ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="bg-gray-50 border-t-2 border-gray-300">
                            <tr>
                                <th colspan="6" class="px-6 py-4 text-right text-sm font-bold text-gray-900">
                                    <?= lang('Customers.total_outstanding') ?>:
                                </th>
                                <th class="px-6 py-4 text-right text-lg font-bold text-red-600">
                                    <?= $currencySymbol . number_format($totalOutstanding, 2) ?>
                                </th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Quick Pay Selected Section -->
                <div id="quickPaySection" class="hidden border-t border-gray-200 bg-blue-50 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-check-circle text-blue-600"></i>
                                <span class="text-sm font-medium text-gray-700">
                                    <span id="selectedCount">0</span> <?= lang('Customers.invoices_selected') ?>
                                </span>
                            </div>
                            <div class="h-6 w-px bg-gray-300"></div>
                            <div class="text-sm font-semibold text-gray-900">
                                <?= lang('Customers.total_due') ?>: <span id="selectedTotal" class="text-blue-600"><?= $currencySymbol ?>0.00</span>
                            </div>
                        </div>
                        <button type="button" onclick="quickPaySelected()"
                            class="btn bg-gradient-to-r from-green-600 to-emerald-600 text-white hover:from-green-700 hover:to-emerald-700">
                            <i class="fas fa-bolt mr-2"></i>
                            <?= lang('Customers.quick_pay_selected') ?>
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Lumpsum Payment Modal -->
<div id="lumpsumPaymentModal" class="fixed z-50 inset-0 hidden" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeLumpsumModal()"></div>
    <div class="flex items-center justify-center min-h-screen px-4 relative z-10">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl transform transition-all duration-300 scale-95 opacity-0 modal-content">
            <div class="px-6 py-5 bg-gradient-to-r from-green-600 to-emerald-600 rounded-t-2xl">
                <div class="flex items-center justify-between text-white">
                    <div>
                        <h3 class="text-xl font-bold"><?= lang('Customers.quick_payment') ?></h3>
                        <p class="text-sm text-white/80 mt-1"><?= lang('Customers.pay_selected_invoices') ?></p>
                    </div>
                    <button onclick="closeLumpsumModal()" class="h-10 w-10 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <div class="p-6">
                <form id="quickPayForm">
                    <input type="hidden" id="quickPayCustomerId" value="<?= $customer['id'] ?? 0 ?>">
                    <input type="hidden" id="quickPayInvoices" value="">

                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('Customers.payment_amount') ?></label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-lg"><?= $currencySymbol ?></span>
                            <input type="number" id="quickPayAmount" step="0.01" min="0.01" required
                                class="w-full pl-10 pr-4 py-3 text-2xl font-bold border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </div>
                        <p class="mt-2 text-sm text-gray-600"><?= lang('Customers.selected_invoices_total') ?>: <span id="modalSelectedTotal" class="font-bold text-green-600">-</span></p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('Customers.payment_date') ?></label>
                            <input type="date" name="payment_date" value="<?= date('Y-m-d') ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('Customers.payment_method') ?></label>
                            <select name="payment_method" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                <option value="cash"><?= lang('Customers.cash') ?></option>
                                <option value="credit_card"><?= lang('Customers.credit_card') ?></option>
                                <option value="bank_transfer"><?= lang('Customers.bank_transfer') ?></option>
                                <option value="check"><?= lang('Customers.check') ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('Customers.notes_optional') ?></label>
                        <textarea name="notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                            placeholder="<?= esc(lang('Customers.add_notes_placeholder')) ?>"></textarea>
                    </div>
                </form>
            </div>

            <div class="px-6 py-4 bg-gray-50 rounded-b-2xl flex justify-end gap-3 border-t">
                <button type="button" onclick="closeLumpsumModal()" class="px-5 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium">
                    <?= lang('Customers.cancel') ?>
                </button>
                <button type="button" onclick="submitQuickPayment()"
                    class="px-5 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg hover:from-green-700 hover:to-emerald-700 font-medium shadow-lg">
                    <i class="fas fa-check mr-2"></i><?= lang('Customers.process_payment') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url() ?>assets/js/jquery.min.js"></script>
<script>
    $(document).ready(function() {
        updateQuickPaySection();

        $('.invoice-checkbox').on('change', function() {
            updateQuickPaySection();
        });
    });

    function toggleAllInvoices(checkbox) {
        $('.invoice-checkbox').prop('checked', checkbox.checked);
        updateQuickPaySection();
    }

    function selectAllInvoices() {
        $('#selectAllCheckbox').prop('checked', true);
        toggleAllInvoices($('#selectAllCheckbox')[0]);
    }

    function clearSelection() {
        $('#selectAllCheckbox').prop('checked', false);
        toggleAllInvoices($('#selectAllCheckbox')[0]);
    }

    function updateQuickPaySection() {
        const currencySymbol = '<?= $currencySymbol ?>';
        let count = 0;
        let total = 0;

        $('.invoice-checkbox:checked').each(function() {
            count++;
            total += parseFloat($(this).data('due')) || 0;
        });

        $('#selectedCount').text(count);
        $('#selectedTotal').text(currencySymbol + total.toFixed(2));

        if (count > 0) {
            $('#quickPaySection').removeClass('hidden');
        } else {
            $('#quickPaySection').addClass('hidden');
        }
    }

    function quickPaySelected() {
        const invoices = [];
        $('.invoice-checkbox:checked').each(function() {
            invoices.push({
                sale_id: $(this).data('invoice-id'),
                amount: parseFloat($(this).data('due'))
            });
        });

        if (invoices.length === 0) {
            alert(<?= json_encode(lang('Customers.select_at_least_one_invoice')) ?>);
            return;
        }

        const total = invoices.reduce((sum, inv) => sum + inv.amount, 0);
        $('#quickPayInvoices').val(JSON.stringify(invoices));
        $('#quickPayAmount').val(total.toFixed(2));
        $('#modalSelectedTotal').text('<?= $currencySymbol ?>' + total.toFixed(2));

        openLumpsumPaymentModal();
    }

    function openLumpsumPaymentModal() {
        $('#lumpsumPaymentModal').removeClass('hidden');
        setTimeout(() => {
            $('.modal-content').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
        }, 10);
    }

    function closeLumpsumModal() {
        $('.modal-content').removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
        setTimeout(() => {
            $('#lumpsumPaymentModal').addClass('hidden');
        }, 300);
    }

    function submitQuickPayment() {
        const amount = parseFloat($('#quickPayAmount').val()) || 0;
        if (amount <= 0) {
            alert(<?= json_encode(lang('Customers.enter_valid_payment_amount')) ?>);
            return;
        }

        const invoicesData = $('#quickPayInvoices').val();
        if (!invoicesData) {
            alert(<?= json_encode(lang('Customers.no_invoices_selected')) ?>);
            return;
        }

        const invoices = JSON.parse(invoicesData);

        const formData = {
            customer_id: $('#quickPayCustomerId').val(),
            payment_amount: amount,
            distribution_mode: 'auto',
            invoices: invoices,
            payment_date: $('#quickPayForm input[name="payment_date"]').val(),
            payment_method: $('#quickPayForm select[name="payment_method"]').val(),
            notes: $('#quickPayForm textarea[name="notes"]').val()
        };

        $.ajax({
            url: '<?= site_url('sales/process-lumpsum-payment') ?>',
            method: 'POST',
            data: formData,
            success: function(response) {
                closeLumpsumModal();
                if (response.success) {
                    alert(<?= json_encode(lang('Customers.payment_processed_successfully')) ?>);
                    location.reload();
                } else {
                    alert(<?= json_encode(lang('Customers.error')) ?> + ': ' + (response.message || <?= json_encode(lang('Customers.failed_to_process_payment')) ?>));
                }
            },
            error: function() {
                alert(<?= json_encode(lang('Customers.failed_to_process_payment_retry')) ?>);
            }
        });
    }
</script>

<style>
    @media print {

        .btn,
        button,
        #quickPaySection {
            display: none !important;
        }

        .bg-gradient-to-br {
            background: white !important;
        }
    }
</style>

<?= $this->endSection() ?>