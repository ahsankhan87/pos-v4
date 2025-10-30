<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php
$currency = session()->get('currency_symbol') ?? '$';
$start = esc($filters['start_date'] ?? date('Y-m-01'));
$end = esc($filters['end_date'] ?? date('Y-m-d'));
?>
<div class="max-w-full mx-auto px-4 py-4">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold">Sales Reports</h1>
        <form id="filterForm" class="flex gap-2 items-end">
            <div>
                <label class="text-xs text-gray-600">Start</label>
                <input type="date" name="start_date" value="<?= $start ?>" class="border rounded px-2 py-1" />
            </div>
            <div>
                <label class="text-xs text-gray-600">End</label>
                <input type="date" name="end_date" value="<?= $end ?>" class="border rounded px-2 py-1" />
            </div>
            <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded">Apply</button>
        </form>
    </div>

    <div id="kpiCards" class="grid grid-cols-1 md:grid-cols-6 gap-3 mb-4">
        <div class="bg-white rounded shadow p-3">
            <div class="text-xs text-gray-500">Revenue</div>
            <div id="kpiRevenue" class="text-lg font-bold">-</div>
        </div>
        <div class="bg-white rounded shadow p-3">
            <div class="text-xs text-gray-500">Transactions</div>
            <div id="kpiTx" class="text-lg font-bold">-</div>
        </div>
        <div class="bg-white rounded shadow p-3">
            <div class="text-xs text-gray-500">Avg Order</div>
            <div id="kpiAov" class="text-lg font-bold">-</div>
        </div>
        <div class="bg-white rounded shadow p-3">
            <div class="text-xs text-gray-500">Discount / Tax</div>
            <div class="text-sm"><span id="kpiDisc">-</span> / <span id="kpiTax">-</span></div>
        </div>
        <div class="bg-white rounded shadow p-3">
            <div class="text-xs text-gray-500">Growth (Sales)</div>
            <div id="kpiGrowthSales" class="text-lg font-bold">-</div>
        </div>
        <div class="bg-white rounded shadow p-3">
            <div class="text-xs text-gray-500">Growth (Tx / AOV)</div>
            <div class="text-sm"><span id="kpiGrowthTx">-</span> / <span id="kpiGrowthAov">-</span></div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded shadow p-3 col-span-2">
            <h3 class="font-semibold mb-2">Sales trend</h3>
            <canvas id="salesTrend" height="120"></canvas>
        </div>
        <div class="bg-white rounded shadow p-3">
            <h3 class="font-semibold mb-2">Payment mix</h3>
            <canvas id="paymentMix" height="120"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-4">
        <div class="bg-white rounded shadow p-3">
            <h3 class="font-semibold mb-2">Top products</h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500">
                        <th class="py-1">Product</th>
                        <th class="py-1 text-right">Qty</th>
                        <th class="py-1 text-right">Revenue</th>
                    </tr>
                </thead>
                <tbody id="topProductsBody"></tbody>
            </table>
        </div>
        <div class="bg-white rounded shadow p-3">
            <h3 class="font-semibold mb-2">By employee</h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500">
                        <th class="py-1">Employee</th>
                        <th class="py-1 text-right">Tx</th>
                        <th class="py-1 text-right">Sales</th>
                    </tr>
                </thead>
                <tbody id="byEmployeeBody"></tbody>
            </table>
        </div>
        <div class="bg-white rounded shadow p-3">
            <h3 class="font-semibold mb-2">Top customers</h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500">
                        <th class="py-1">Customer</th>
                        <th class="py-1 text-right">Tx</th>
                        <th class="py-1 text-right">Sales</th>
                    </tr>
                </thead>
                <tbody id="topCustomersBody"></tbody>
            </table>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-4">
        <div class="bg-white rounded shadow p-3">
            <h3 class="font-semibold mb-2">Category mix</h3>
            <canvas id="categoryMix" height="120"></canvas>
        </div>
        <div class="bg-white rounded shadow p-3">
            <h3 class="font-semibold mb-2">Hourly sales</h3>
            <canvas id="hourlySales" height="120"></canvas>
        </div>
        <div class="bg-white rounded shadow p-3">
            <h3 class="font-semibold mb-2">Discounts trend</h3>
            <canvas id="discountsTrend" height="120"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
        <div class="bg-white rounded shadow p-3">
            <h3 class="font-semibold mb-2">Margin</h3>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <div class="text-xs text-gray-500">Revenue</div>
                    <div id="mRevenue" class="text-lg font-bold">-</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">COGS</div>
                    <div id="mCogs" class="text-lg font-bold">-</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Gross</div>
                    <div id="mGross" class="text-lg font-bold">-</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Margin %</div>
                    <div id="mRate" class="text-lg font-bold">-</div>
                </div>
            </div>
        </div>
        <div class="bg-white rounded shadow p-3">
            <h3 class="font-semibold mb-2">Returns</h3>
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <div class="text-xs text-gray-500">Total Returned</div>
                    <div id="rTotal" class="text-lg font-bold">-</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Qty</div>
                    <div id="rQty" class="text-lg font-bold">-</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Count</div>
                    <div id="rCount" class="text-lg font-bold">-</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo base_url() ?>assets/js/chartjs/chart.js"></script>
<script>
    (function() {
        const currency = <?= json_encode($currency) ?>;
        const form = document.getElementById('filterForm');
        const qs = () => new URLSearchParams(new FormData(form)).toString();

        async function fetchJSON(url) {
            const res = await fetch(url + '?' + qs());
            if (!res.ok) throw new Error('Network error');
            return res.json();
        }

        async function loadKPIs() {
            const data = await fetchJSON('<?= site_url('reports/sales/summary') ?>');
            document.getElementById('kpiRevenue').textContent = currency + (data.total_sales ?? 0).toFixed(2);
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
                            label: 'Sales',
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
            mixChart.data.labels = data.map(r => (r.payment_method || 'N/A'));
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
                            label: 'Sales',
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
                            label: 'Discounts',
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
            data.forEach(r => body.appendChild(row([r.name || 'Unknown', Number(r.transactions).toFixed(0), currency + Number(r.total).toFixed(2)])));
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
            data.forEach(r => body.appendChild(row([r.name || 'Unassigned', Number(r.transactions).toFixed(0), currency + Number(r.total).toFixed(2)])));
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