<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php
$currency = session()->get('currency_symbol') ?? '$';
$start = esc($filters['start_date'] ?? date('Y-m-01'));
$end = esc($filters['end_date'] ?? date('Y-m-d'));
?>
<div class="max-w-full mx-auto px-4 py-4">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold">Inventory Reports</h1>
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

    <div id="kpiCards" class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
        <div class="bg-white rounded shadow p-3">
            <div class="text-xs text-gray-500">Stock Valuation (Cost)</div>
            <div id="valCost" class="text-lg font-bold">-</div>
        </div>
        <div class="bg-white rounded shadow p-3">
            <div class="text-xs text-gray-500">Retail Value</div>
            <div id="valRetail" class="text-lg font-bold">-</div>
        </div>
        <div class="bg-white rounded shadow p-3">
            <div class="text-xs text-gray-500">Items</div>
            <div id="valItems" class="text-lg font-bold">-</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-white rounded shadow p-3">
            <h3 class="font-semibold mb-2">Stock movement</h3>
            <canvas id="movementTrend" height="120"></canvas>
        </div>
        <div class="bg-white rounded shadow p-3">
            <h3 class="font-semibold mb-2">Low stock</h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500">
                        <th class="py-1">Product</th>
                        <th class="py-1">Code</th>
                        <th class="py-1 text-right">Qty</th>
                        <th class="py-1 text-right">Alert</th>
                    </tr>
                </thead>
                <tbody id="lowStockBody"></tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded shadow p-3 mt-4">
        <h3 class="font-semibold mb-2">Slow movers</h3>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500">
                    <th class="py-1">Product</th>
                    <th class="py-1">Code</th>
                    <th class="py-1 text-right">Sold (range)</th>
                    <th class="py-1 text-right">On hand</th>
                </tr>
            </thead>
            <tbody id="slowMoversBody"></tbody>
        </table>
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

        async function loadValuation() {
            const v = await fetchJSON('<?= site_url('reports/inventory/valuation') ?>');
            document.getElementById('valCost').textContent = currency + Number(v.cost_value || 0).toFixed(2);
            document.getElementById('valRetail').textContent = currency + Number(v.retail_value || 0).toFixed(2);
            document.getElementById('valItems').textContent = Number(v.items || 0);
        }

        let moveChart;

        function ensureCharts() {
            if (!moveChart) {
                moveChart = new Chart(document.getElementById('movementTrend'), {
                    type: 'bar',
                    data: {
                        labels: [],
                        datasets: [{
                            label: 'In',
                            data: [],
                            backgroundColor: '#16a34a'
                        }, {
                            label: 'Out',
                            data: [],
                            backgroundColor: '#ef4444'
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
        }
        async function loadMovement() {
            ensureCharts();
            const data = await fetchJSON('<?= site_url('reports/inventory/movement') ?>');
            moveChart.data.labels = data.map(r => r.d);
            moveChart.data.datasets[0].data = data.map(r => Number(r.qty_in));
            moveChart.data.datasets[1].data = data.map(r => Number(r.qty_out));
            moveChart.update();
        }

        function row(cols) {
            const tr = document.createElement('tr');
            cols.forEach((c, i) => {
                const td = document.createElement('td');
                td.className = 'py-1 ' + ((i >= 2) ? 'text-right' : '');
                td.textContent = c;
                tr.appendChild(td);
            });
            return tr;
        }

        async function loadLowStock() {
            const data = await fetchJSON('<?= site_url('reports/inventory/low-stock') ?>');
            const body = document.getElementById('lowStockBody');
            body.innerHTML = '';
            data.forEach(r => body.appendChild(row([r.name, r.code, Number(r.quantity).toFixed(0), Number(r.stock_alert).toFixed(0)])));
        }
        async function loadSlowMovers() {
            const data = await fetchJSON('<?= site_url('reports/inventory/slow-movers') ?>');
            const body = document.getElementById('slowMoversBody');
            body.innerHTML = '';
            data.forEach(r => body.appendChild(row([r.name, r.code, Number(r.sold_qty).toFixed(0), Number(r.quantity).toFixed(0)])));
        }

        async function refreshAll() {
            await Promise.all([loadValuation(), loadMovement(), loadLowStock(), loadSlowMovers()]);
        }
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            refreshAll();
        });
        refreshAll();
    })();
</script>
<?= $this->endSection() ?>