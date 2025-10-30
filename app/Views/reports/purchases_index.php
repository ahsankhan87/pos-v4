<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php
$currency = session()->get('currency_symbol') ?? '$';
$start = esc($filters['start_date'] ?? date('Y-m-01'));
$end = esc($filters['end_date'] ?? date('Y-m-d'));
?>
<div class="max-w-full mx-auto px-4 py-4">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold">Purchases Reports</h1>
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

    <div id="kpiCards" class="grid grid-cols-1 md:grid-cols-5 gap-3 mb-4">
        <div class="bg-white rounded shadow p-3">
            <div class="text-xs text-gray-500">Total Spend</div>
            <div id="kpiSpend" class="text-lg font-bold">-</div>
        </div>
        <div class="bg-white rounded shadow p-3">
            <div class="text-xs text-gray-500">Transactions</div>
            <div id="kpiTx" class="text-lg font-bold">-</div>
        </div>
        <div class="bg-white rounded shadow p-3">
            <div class="text-xs text-gray-500">Avg Bill</div>
            <div id="kpiAvg" class="text-lg font-bold">-</div>
        </div>
        <div class="bg-white rounded shadow p-3">
            <div class="text-xs text-gray-500">Discount / Tax</div>
            <div class="text-sm"><span id="kpiDisc">-</span> / <span id="kpiTax">-</span></div>
        </div>
        <div class="bg-white rounded shadow p-3">
            <div class="text-xs text-gray-500">Paid / Due</div>
            <div class="text-sm"><span id="kpiPaid">-</span> / <span id="kpiDue">-</span></div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded shadow p-3 col-span-2">
            <h3 class="font-semibold mb-2">Spend trend</h3>
            <canvas id="spendTrend" height="120"></canvas>
        </div>
        <div class="bg-white rounded shadow p-3">
            <h3 class="font-semibold mb-2">Payment mix</h3>
            <canvas id="paymentMix" height="120"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
        <div class="bg-white rounded shadow p-3">
            <h3 class="font-semibold mb-2">Top suppliers</h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500">
                        <th class="py-1">Supplier</th>
                        <th class="py-1 text-right">Tx</th>
                        <th class="py-1 text-right">Spend</th>
                    </tr>
                </thead>
                <tbody id="topSuppliersBody"></tbody>
            </table>
        </div>
        <div class="bg-white rounded shadow p-3">
            <h3 class="font-semibold mb-2">Top items purchased</h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500">
                        <th class="py-1">Product</th>
                        <th class="py-1 text-right">Qty</th>
                        <th class="py-1 text-right">Spend</th>
                    </tr>
                </thead>
                <tbody id="topItemsBody"></tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded shadow p-3 mt-4">
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

<script src="<?php echo base_url() ?>assets/js/chartjs/chart.js"></script>

<script>
    (function() {
        const currency = <?= json_encode($currency) ?>;
        const form = document.getElementById('filterForm');
        const qs = () => new URLSearchParams(new FormData(form)).toString();

        async function fetchJSON(url) {
            const res = await fetch(url + '?' + qs());
            if (!res.ok) throw new Error('Network');
            return res.json();
        }

        let trendChart, mixChart;

        function ensureCharts() {
            if (!trendChart) {
                trendChart = new Chart(document.getElementById('spendTrend'), {
                    type: 'line',
                    data: {
                        labels: [],
                        datasets: [{
                            label: 'Spend',
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
            if (!mixChart) {
                mixChart = new Chart(document.getElementById('paymentMix'), {
                    type: 'doughnut',
                    data: {
                        labels: [],
                        datasets: [{
                            data: [],
                            backgroundColor: ['#2563eb', '#16a34a', '#f59e0b', '#ef4444', '#6b7280']
                        }]
                    }
                });
            }
        }

        async function loadKPIs() {
            const s = await fetchJSON('<?= site_url('reports/purchases/summary') ?>');
            document.getElementById('kpiSpend').textContent = currency + Number(s.total_spend || 0).toFixed(2);
            document.getElementById('kpiTx').textContent = Number(s.transactions || 0);
            document.getElementById('kpiAvg').textContent = currency + Number(s.average_bill || 0).toFixed(2);
            document.getElementById('kpiDisc').textContent = '-' + currency + Number(s.discount_total || 0).toFixed(2);
            document.getElementById('kpiTax').textContent = currency + Number(s.tax_total || 0).toFixed(2);
            document.getElementById('kpiPaid').textContent = currency + Number(s.paid_total || 0).toFixed(2);
            document.getElementById('kpiDue').textContent = currency + Number(s.due_total || 0).toFixed(2);
        }

        async function loadTrend() {
            ensureCharts();
            const data = await fetchJSON('<?= site_url('reports/purchases/timeseries') ?>');
            trendChart.data.labels = data.map(r => r.d);
            trendChart.data.datasets[0].data = data.map(r => Number(r.total));
            trendChart.update();
        }
        async function loadPaymentMix() {
            ensureCharts();
            const data = await fetchJSON('<?= site_url('reports/purchases/payment-mix') ?>');
            mixChart.data.labels = data.map(r => r.payment_method || 'N/A');
            mixChart.data.datasets[0].data = data.map(r => Number(r.total));
            mixChart.update();
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

        async function loadTopSuppliers() {
            const data = await fetchJSON('<?= site_url('reports/purchases/top-suppliers') ?>');
            const body = document.getElementById('topSuppliersBody');
            body.innerHTML = '';
            data.forEach(r => body.appendChild(row([r.name || 'Unknown', Number(r.transactions).toFixed(0), currency + Number(r.total).toFixed(2)])));
        }
        async function loadTopItems() {
            const data = await fetchJSON('<?= site_url('reports/purchases/top-items') ?>');
            const body = document.getElementById('topItemsBody');
            body.innerHTML = '';
            data.forEach(r => body.appendChild(row([r.name, Number(r.qty).toFixed(0), currency + Number(r.spend).toFixed(2)])));
        }
        async function loadReturns() {
            const r = await fetchJSON('<?= site_url('reports/purchases/returns-summary') ?>');
            document.getElementById('rTotal').textContent = currency + Number(r.returns_total || 0).toFixed(2);
            document.getElementById('rQty').textContent = Number(r.returns_qty || 0);
            document.getElementById('rCount').textContent = Number(r.count || 0);
        }

        async function refreshAll() {
            await Promise.all([loadKPIs(), loadTrend(), loadPaymentMix(), loadTopSuppliers(), loadTopItems(), loadReturns()]);
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            refreshAll();
        });
        refreshAll();
    })();
</script>
<?= $this->endSection() ?>