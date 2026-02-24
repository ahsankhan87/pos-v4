<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php
$currency = session()->get('currency_symbol') ?? '$';
$start = esc($filters['start_date'] ?? date('Y-m-01'));
$end = esc($filters['end_date'] ?? date('Y-m-d'));
?>
<div class="max-w-full mx-auto px-4 py-4">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold"><?= esc(lang('Reports.sales_reports_title')) ?></h1>
        <form id="filterForm" class="flex gap-2 items-end">
            <div>
                <label class="text-xs text-gray-600"><?= esc(lang('Reports.start')) ?></label>
                <input type="date" name="start_date" value="<?= $start ?>" class="border rounded px-2 py-1" />
            </div>
            <div>
                <label class="text-xs text-gray-600"><?= esc(lang('Reports.end')) ?></label>
                <input type="date" name="end_date" value="<?= $end ?>" class="border rounded px-2 py-1" />
            </div>
            <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded"><?= esc(lang('Reports.apply')) ?></button>
        </form>
    </div>

    <div id="kpiCards" class="grid grid-cols-1 md:grid-cols-6 gap-3 mb-4">
        <div class="bg-white rounded shadow p-3">
            <div class="text-xs text-gray-500"><?= esc(lang('Reports.revenue')) ?></div>
            <div id="kpiRevenue" class="text-lg font-bold">-</div>
        </div>
        <div class="bg-white rounded shadow p-3">
            <div class="text-xs text-gray-500"><?= esc(lang('Reports.transactions')) ?></div>
            <div id="kpiTx" class="text-lg font-bold">-</div>
        </div>
        <div class="bg-white rounded shadow p-3">
            <div class="text-xs text-gray-500"><?= esc(lang('Reports.avg_order')) ?></div>
            <div id="kpiAov" class="text-lg font-bold">-</div>
        </div>
        <div class="bg-white rounded shadow p-3">
            <div class="text-xs text-gray-500"><?= esc(lang('Reports.discount_tax')) ?></div>
            <div class="text-sm"><span id="kpiDisc">-</span> / <span id="kpiTax">-</span></div>
        </div>
        <div class="bg-white rounded shadow p-3">
            <div class="text-xs text-gray-500"><?= esc(lang('Reports.growth_sales')) ?></div>
            <div id="kpiGrowthSales" class="text-lg font-bold">-</div>
        </div>
        <div class="bg-white rounded shadow p-3">
            <div class="text-xs text-gray-500"><?= esc(lang('Reports.growth_tx_aov')) ?></div>
            <div class="text-sm"><span id="kpiGrowthTx">-</span> / <span id="kpiGrowthAov">-</span></div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded shadow p-3 col-span-2">
            <h3 class="font-semibold mb-2"><?= esc(lang('Reports.sales_trend')) ?></h3>
            <canvas id="salesTrend" height="120"></canvas>
        </div>
        <div class="bg-white rounded shadow p-3">
            <h3 class="font-semibold mb-2"><?= esc(lang('Reports.payment_mix')) ?></h3>
            <canvas id="paymentMix" height="120"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-4">
        <div class="bg-white rounded shadow p-3">
            <h3 class="font-semibold mb-2"><?= esc(lang('Reports.top_products')) ?></h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500">
                        <th class="py-1"><?= esc(lang('Reports.product')) ?></th>
                        <th class="py-1 text-right"><?= esc(lang('Reports.qty')) ?></th>
                        <th class="py-1 text-right"><?= esc(lang('Reports.revenue')) ?></th>
                    </tr>
                </thead>
                <tbody id="topProductsBody"></tbody>
            </table>
        </div>
        <div class="bg-white rounded shadow p-3">
            <h3 class="font-semibold mb-2"><?= esc(lang('Reports.by_employee')) ?></h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500">
                        <th class="py-1"><?= esc(lang('Reports.employee')) ?></th>
                        <th class="py-1 text-right"><?= esc(lang('Reports.tx')) ?></th>
                        <th class="py-1 text-right"><?= esc(lang('Reports.sales')) ?></th>
                    </tr>
                </thead>
                <tbody id="byEmployeeBody"></tbody>
            </table>
        </div>
        <div class="bg-white rounded shadow p-3">
            <h3 class="font-semibold mb-2"><?= esc(lang('Reports.top_customers')) ?></h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500">
                        <th class="py-1"><?= esc(lang('Reports.customer')) ?></th>
                        <th class="py-1 text-right"><?= esc(lang('Reports.tx')) ?></th>
                        <th class="py-1 text-right"><?= esc(lang('Reports.sales')) ?></th>
                    </tr>
                </thead>
                <tbody id="topCustomersBody"></tbody>
            </table>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-4">
        <div class="bg-white rounded shadow p-3">
            <h3 class="font-semibold mb-2"><?= esc(lang('Reports.category_mix')) ?></h3>
            <canvas id="categoryMix" height="120"></canvas>
        </div>
        <div class="bg-white rounded shadow p-3">
            <h3 class="font-semibold mb-2"><?= esc(lang('Reports.hourly_sales')) ?></h3>
            <canvas id="hourlySales" height="120"></canvas>
        </div>
        <div class="bg-white rounded shadow p-3">
            <h3 class="font-semibold mb-2"><?= esc(lang('Reports.discounts_trend')) ?></h3>
            <canvas id="discountsTrend" height="120"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
        <div class="bg-white rounded shadow p-3">
            <h3 class="font-semibold mb-2"><?= esc(lang('Reports.margin')) ?></h3>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <div class="text-xs text-gray-500"><?= esc(lang('Reports.revenue')) ?></div>
                    <div id="mRevenue" class="text-lg font-bold">-</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500"><?= esc(lang('Reports.cogs')) ?></div>
                    <div id="mCogs" class="text-lg font-bold">-</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500"><?= esc(lang('Reports.gross_profit')) ?></div>
                    <div id="mGross" class="text-lg font-bold">-</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500"><?= esc(lang('Reports.margin_percent')) ?></div>
                    <div id="mRate" class="text-lg font-bold">-</div>
                </div>
            </div>
        </div>
        <div class="bg-white rounded shadow p-3">
            <h3 class="font-semibold mb-2"><?= esc(lang('Reports.returns')) ?></h3>
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <div class="text-xs text-gray-500"><?= esc(lang('Reports.total_returned')) ?></div>
                    <div id="rTotal" class="text-lg font-bold">-</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500"><?= esc(lang('Reports.qty')) ?></div>
                    <div id="rQty" class="text-lg font-bold">-</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500"><?= esc(lang('Reports.count')) ?></div>
                    <div id="rCount" class="text-lg font-bold">-</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo base_url() ?>assets/js/chartjs/chart.js"></script>
<script>
    (function() {
        const reportsTexts = {
            networkError: <?= json_encode(lang('Reports.network_error'), JSON_UNESCAPED_UNICODE) ?>,
            sales: <?= json_encode(lang('Reports.sales'), JSON_UNESCAPED_UNICODE) ?>,
            discounts: <?= json_encode(lang('Reports.discounts'), JSON_UNESCAPED_UNICODE) ?>,
            na: <?= json_encode(lang('Reports.na'), JSON_UNESCAPED_UNICODE) ?>,
            unknown: <?= json_encode(lang('Reports.unknown'), JSON_UNESCAPED_UNICODE) ?>,
            unassigned: <?= json_encode(lang('Reports.unassigned'), JSON_UNESCAPED_UNICODE) ?>
        };

        const currency = <?= json_encode($currency) ?>;
        const form = document.getElementById('filterForm');
        const qs = () => new URLSearchParams(new FormData(form)).toString();

        async function fetchJSON(url) {
            const res = await fetch(url + '?' + qs());
            if (!res.ok) throw new Error(reportsTexts.networkError);
            return res.json();
        }

        async function loadKPIs() {
            const data = await fetchJSON('<?= site_url('reports/sales/summary') ?>');
            const revenue = (data.net_sales ?? data.total_sales ?? data.gross_sales ?? 0);
            document.getElementById('kpiRevenue').textContent = currency + Number(revenue || 0).toFixed(2);
            document.getElementById('kpiTx').textContent = data.transactions ?? 0;
            document.getElementById('kpiAov').textContent = currency + (data.average_sale ?? 0).toFixed(2);
            document.getElementById('kpiDisc').textContent = '-' + currency + (data.discount_total ?? 0).toFixed(2);
            document.getElementById('kpiTax').textContent = currency + (data.tax_total ?? 0).toFixed(2);
        }

        let trendChart, mixChart, catChart, hourlyChart, discChart;

        function ensureCharts() {
            const trendCtx = document.getElementById('salesTrend');
            if (!trendChart) {
                trendChart = new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: [],
                        datasets: [{
                            label: reportsTexts.sales,
                            data: [],
                            borderColor: '#2563eb',
                            fill: false
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
            const mixCtx = document.getElementById('paymentMix');
            if (!mixChart) {
                mixChart = new Chart(mixCtx, {
                    type: 'doughnut',
                    data: {
                        labels: [],
                        datasets: [{
                            data: [],
                            backgroundColor: ['#2563eb', '#16a34a', '#f59e0b', '#ef4444', '#6b7280']
                        }]
                    },
                    options: {
                        responsive: true
                    }
                });
            }
        }

        function pctText(v) {
            const n = Number(v || 0);
            const sign = n > 0 ? '+' : '';
            return sign + n.toFixed(2) + '%';
        }

        async function loadTrend() {
            ensureCharts();
            const data = await fetchJSON('<?= site_url('reports/sales/timeseries') ?>');
            trendChart.data.labels = data.map(r => r.d);
            trendChart.data.datasets[0].data = data.map(r => Number(r.total));
            trendChart.update();
        }

        async function loadPaymentMix() {
            ensureCharts();
            const data = await fetchJSON('<?= site_url('reports/sales/payment-mix') ?>');
            mixChart.data.labels = data.map(r => (r.payment_method || reportsTexts.na));
            mixChart.data.datasets[0].data = data.map(r => Number(r.total));
            mixChart.update();
        }

        function ensureExtraCharts() {
            if (!catChart) {
                catChart = new Chart(document.getElementById('categoryMix'), {
                    type: 'doughnut',
                    data: {
                        labels: [],
                        datasets: [{
                            data: [],
                            backgroundColor: ['#3b82f6', '#22c55e', '#f59e0b', '#ef4444', '#a855f7', '#14b8a6', '#eab308']
                        }]
                    },
                    options: {
                        responsive: true
                    }
                });
            }
            if (!hourlyChart) {
                hourlyChart = new Chart(document.getElementById('hourlySales'), {
                    type: 'bar',
                    data: {
                        labels: [],
                        datasets: [{
                            label: reportsTexts.sales,
                            data: [],
                            backgroundColor: '#60a5fa'
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
            if (!discChart) {
                discChart = new Chart(document.getElementById('discountsTrend'), {
                    type: 'line',
                    data: {
                        labels: [],
                        datasets: [{
                            label: reportsTexts.discounts,
                            data: [],
                            borderColor: '#f59e0b',
                            fill: false
                        }]
                    },
                    options: {
                        responsive: true
                    }
                });
            }
        }

        async function loadCategoryMix() {
            ensureExtraCharts();
            const data = await fetchJSON('<?= site_url('reports/sales/category-mix') ?>');
            catChart.data.labels = data.map(r => r.category);
            catChart.data.datasets[0].data = data.map(r => Number(r.revenue));
            catChart.update();
        }

        async function loadHourly() {
            ensureExtraCharts();
            const data = await fetchJSON('<?= site_url('reports/sales/hourly') ?>');
            hourlyChart.data.labels = data.map(r => String(r.hour).padStart(2, '0'));
            hourlyChart.data.datasets[0].data = data.map(r => Number(r.total));
            hourlyChart.update();
        }

        async function loadDiscounts() {
            ensureExtraCharts();
            const data = await fetchJSON('<?= site_url('reports/sales/discounts-trend') ?>');
            discChart.data.labels = data.map(r => r.d);
            discChart.data.datasets[0].data = data.map(r => Number(r.discount_total));
            discChart.update();
        }

        async function loadGrowth() {
            const g = await fetchJSON('<?= site_url('reports/sales/growth') ?>');
            document.getElementById('kpiGrowthSales').textContent = pctText(g.growth.sales_pct);
            document.getElementById('kpiGrowthTx').textContent = pctText(g.growth.tx_pct);
            document.getElementById('kpiGrowthAov').textContent = pctText(g.growth.aov_pct);
        }

        async function loadMargin() {
            const m = await fetchJSON('<?= site_url('reports/sales/margin') ?>');
            document.getElementById('mRevenue').textContent = currency + Number(m.revenue || 0).toFixed(2);
            document.getElementById('mCogs').textContent = currency + Number(m.cogs || 0).toFixed(2);
            document.getElementById('mGross').textContent = currency + Number(m.gross_margin || 0).toFixed(2);
            document.getElementById('mRate').textContent = Number(m.margin_rate || 0).toFixed(2) + '%';
        }

        async function loadReturns() {
            const r = await fetchJSON('<?= site_url('reports/sales/returns-summary') ?>');
            document.getElementById('rTotal').textContent = currency + Number(r.returns_total || 0).toFixed(2);
            document.getElementById('rQty').textContent = Number(r.returns_qty || 0);
            document.getElementById('rCount').textContent = Number(r.count || 0);
        }

        async function loadTopCustomers() {
            const data = await fetchJSON('<?= site_url('reports/sales/top-customers') ?>');
            const body = document.getElementById('topCustomersBody');
            body.innerHTML = '';
            data.forEach(r => body.appendChild(row([r.name || reportsTexts.unknown, Number(r.transactions).toFixed(0), currency + Number(r.total).toFixed(2)])));
        }

        function row(cols) {
            const tr = document.createElement('tr');
            cols.forEach((c, i) => {
                const td = document.createElement('td');
                td.className = 'py-1 ' + (i > 0 ? 'text-right' : '');
                td.textContent = c;
                tr.appendChild(td);
            });
            return tr;
        }

        async function loadTopProducts() {
            const data = await fetchJSON('<?= site_url('reports/sales/top-products') ?>');
            const body = document.getElementById('topProductsBody');
            body.innerHTML = '';
            data.forEach(r => body.appendChild(row([r.name, Number(r.qty).toFixed(0), currency + Number(r.revenue).toFixed(2)])));
        }

        async function loadByEmployee() {
            const data = await fetchJSON('<?= site_url('reports/sales/by-employee') ?>');
            const body = document.getElementById('byEmployeeBody');
            body.innerHTML = '';
            data.forEach(r => body.appendChild(row([r.name || reportsTexts.unassigned, Number(r.transactions).toFixed(0), currency + Number(r.total).toFixed(2)])));
        }

        async function refreshAll() {
            await Promise.all([
                loadKPIs(), loadTrend(), loadPaymentMix(),
                loadTopProducts(), loadByEmployee(), loadTopCustomers(),
                loadCategoryMix(), loadHourly(), loadDiscounts(),
                loadGrowth(), loadMargin(), loadReturns()
            ]);
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            refreshAll();
        });
        refreshAll();
    })();
</script>
<?= $this->endSection() ?>