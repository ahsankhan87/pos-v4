<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4"><?= lang('Reports.employee_sales_commission_report') ?></h1>

    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <form action="<?= site_url('sales/employee-report') ?>" method="get" class="grid grid-cols-1 md:grid-cols-6 gap-4 items-end">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700"><?= lang('Reports.employee') ?></label>
                <select name="employee_id" class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <option value=""><?= lang('Reports.all_employees') ?></option>
                    <?php foreach (($employees ?? []) as $emp): ?>
                        <option value="<?= $emp['id'] ?>" <?= (!empty($selectedEmployeeId) && (string)$selectedEmployeeId === (string)$emp['id']) ? 'selected' : '' ?>>
                            <?= esc($emp['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700"><?= lang('Reports.from') ?></label>
                <input type="date" name="from" value="<?= esc($from ?? date('Y-m-d', strtotime('-30 days'))) ?>" class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700"><?= lang('Reports.to') ?></label>
                <input type="date" name="to" value="<?= esc($to ?? date('Y-m-d')) ?>" class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
            </div>
            <div class="md:col-span-2 flex gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none"><?= lang('Reports.apply') ?></button>
                <button type="button" onclick="window.print()" class="px-4 py-2 bg-gray-700 text-white rounded-md"><?= lang('Reports.print') ?></button>
                <?php if (can('reports.export')): ?>
                    <a href="<?= site_url('sales/employee-report/export_excel?from=' . urlencode($from ?? date('Y-m-d', strtotime('-30 days'))) . '&to=' . urlencode($to ?? date('Y-m-d')) . (empty($selectedEmployeeId) ? '' : '&employee_id=' . urlencode($selectedEmployeeId))) ?>" class="px-4 py-2 bg-green-600 text-white rounded-md"><?= lang('Reports.export_excel') ?></a>
                    <a href="<?= site_url('sales/employee-report/export_pdf?from=' . urlencode($from ?? date('Y-m-d', strtotime('-30 days'))) . '&to=' . urlencode($to ?? date('Y-m-d')) . (empty($selectedEmployeeId) ? '' : '&employee_id=' . urlencode($selectedEmployeeId))) ?>" class="px-4 py-2 bg-red-600 text-white rounded-md"><?= lang('Reports.export_pdf') ?></a>
                <?php endif; ?>
            </div>
            <div class="md:col-span-6 flex flex-wrap gap-2 text-sm text-gray-600">
                <button name="from" value="<?= date('Y-m-d') ?>" formaction="<?= site_url('sales/employee-report') ?>" class="px-3 py-1 border rounded hover:bg-gray-50"><?= lang('Reports.today') ?></button>
                <button name="from" value="<?= date('Y-m-d', strtotime('-1 day')) ?>" formaction="<?= site_url('sales/employee-report') ?>" class="px-3 py-1 border rounded hover:bg-gray-50"><?= lang('Reports.yesterday') ?></button>
                <button type="button" class="px-3 py-1 border rounded hover:bg-gray-50" onclick="quickRange(7)"><?= lang('Reports.last_7_days') ?></button>
                <button type="button" class="px-3 py-1 border rounded hover:bg-gray-50" onclick="quickRange(30)"><?= lang('Reports.last_30_days') ?></button>
                <button type="button" class="px-3 py-1 border rounded hover:bg-gray-50" onclick="thisMonth()"><?= lang('Reports.this_month') ?></button>
            </div>
        </form>
    </div>

    <?php
    $grandTotal = 0.0;
    $grandCommission = 0.0;
    $saleCount = 0;
    foreach (($reportData ?? []) as $row) {
        $grandTotal += (float)($row['total_amount'] ?? 0);
        $grandCommission += (float)($row['commission_amount'] ?? 0);
        $saleCount++;
    }
    ?>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-gray-500 text-sm"><?= lang('Reports.sales_count') ?></div>
            <div class="text-2xl font-semibold"><?= number_format($saleCount) ?></div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-gray-500 text-sm"><?= lang('Reports.total_sales') ?></div>
            <div class="text-2xl font-semibold"><?= session()->get('currency_symbol') . number_format($grandTotal, 2) ?></div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-gray-500 text-sm"><?= lang('Reports.total_commission') ?></div>
            <div class="text-2xl font-semibold text-green-700"><?= session()->get('currency_symbol') . number_format($grandCommission, 2) ?></div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-gray-500 text-sm"><?= lang('Reports.avg_commission_percent') ?></div>
            <div class="text-2xl font-semibold"><?= ($grandTotal > 0) ? number_format(($grandCommission / $grandTotal) * 100, 2) . '%' : '0.00%' ?></div>
        </div>
    </div>

    <?php if (!empty($reportData)): ?>
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Reports.sale_id') ?></th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Reports.date') ?></th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Reports.employee') ?></th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Reports.customer') ?></th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Reports.total') ?></th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Reports.commission') ?></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($reportData as $row): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><a class="text-blue-600 hover:text-blue-900" target="_blank" href="<?= site_url('sales/receipt/' . $row['id']) ?>">#<?= esc($row['id']) ?></a></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= esc(date('M d, Y H:i', strtotime($row['sale_date']))) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= esc($row['employee_name'] ?? lang('Reports.na')) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= esc($row['customer_name'] ?? lang('Reports.walk_in')) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= session()->get('currency_symbol') . number_format($row['total_amount'], 2) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-700"><?= session()->get('currency_symbol') . number_format($row['commission_amount'] ?? 0, 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="bg-gray-50">
                        <td colspan="4" class="px-6 py-4 whitespace-nowrap text-right text-base font-bold text-gray-900"><?= lang('Reports.grand_total') ?>:</td>
                        <td class="px-6 py-4 whitespace-nowrap text-base font-bold text-gray-900"><?= session()->get('currency_symbol') . number_format($grandTotal, 2) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-base font-bold text-green-700"><?= session()->get('currency_symbol') . number_format($grandCommission, 2) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="bg-white shadow-md rounded-lg p-6 text-center text-gray-500"><?= lang('Reports.no_data_selected_filters') ?></div>
    <?php endif; ?>
</div>

<style>
    @media print {

        nav,
        header,
        footer,
        form,
        .no-print {
            display: none;
        }

        table {
            width: 100%;
        }
    }
</style>
<script>
    function quickRange(days) {
        const to = new Date();
        const from = new Date();
        from.setDate(to.getDate() - (days - 1));
        const fmt = d => d.toISOString().slice(0, 10);
        const params = new URLSearchParams(window.location.search);
        params.set('from', fmt(from));
        params.set('to', fmt(to));
        const emp = document.querySelector('select[name="employee_id"]').value;
        if (emp) params.set('employee_id', emp);
        else params.delete('employee_id');
        window.location = '<?= site_url('sales/employee-report') ?>' + '?' + params.toString();
    }

    function thisMonth() {
        const d = new Date();
        const from = new Date(d.getFullYear(), d.getMonth(), 1);
        const to = new Date(d.getFullYear(), d.getMonth() + 1, 0);
        const fmt = d => d.toISOString().slice(0, 10);
        const params = new URLSearchParams(window.location.search);
        params.set('from', fmt(from));
        params.set('to', fmt(to));
        const emp = document.querySelector('select[name="employee_id"]').value;
        if (emp) params.set('employee_id', emp);
        else params.delete('employee_id');
        window.location = '<?= site_url('sales/employee-report') ?>' + '?' + params.toString();
    }
</script>

<?= $this->endSection() ?>
