<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Profit & Loss Report - Print') ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 10mm;
        }

        h2 {
            margin: 0 0 6px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 11px;
        }

        thead th {
            background: #f0f0f0;
        }

        .text-right {
            text-align: right;
        }

        .no-print {
            margin-top: 10px;
            text-align: center;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                margin: 5mm;
            }
        }
    </style>
</head>

<body>
    <?php
    $currency = session()->get('currency_symbol') ?? '$';
    if (!function_exists('money_fmt')) {
        function money_fmt($v)
        {
            return number_format((float)$v, 2, '.', ',');
        }
    }
    if (!function_exists('formatQuantity')) {
        function formatQuantity($pieces, $cartonSize)
        {
            if (!$cartonSize || $cartonSize <= 1) {
                return number_format($pieces, 2) . ' pcs';
            }
            $cartons = floor($pieces / $cartonSize);
            $remaining = $pieces - ($cartons * $cartonSize);
            return $remaining > 0 ? (number_format($cartons) . ' ctns + ' . number_format($remaining, 2) . ' pcs') : (number_format($cartons) . ' ctns');
        }
    }
    ?>
    <h2>Profit &amp; Loss Report</h2>
    <p>Employee: <?= esc($employeeName ?? 'All') ?></p>
    <p>Period: <?= esc($from) ?> to <?= esc($to) ?></p>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th class="text-right">Qty Sold</th>
                <th class="text-right">Revenue</th>
                <th class="text-right">Cost</th>
                <th class="text-right">Gross Profit</th>
                <th class="text-right">Returns (Qty)</th>
                <th class="text-right">Returns (Amt)</th>
                <th class="text-right">Net Revenue</th>
                <th class="text-right">Net Cost</th>
                <th class="text-right">Net Gross Profit</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (($products ?? []) as $p): ?>
                <tr>
                    <td><?= esc($p['product_name']) ?></td>
                    <td class="text-right"><?= number_format((float)($p['net_qty_sold'] ?? $p['total_qty_sold'] ?? 0), 2) ?></td>
                    <td class="text-right"><?= esc($currency) . ' ' . money_fmt($p['total_revenue'] ?? 0) ?></td>
                    <td class="text-right"><?= esc($currency) . ' ' . money_fmt($p['total_cost'] ?? 0) ?></td>
                    <td class="text-right"><?= esc($currency) . ' ' . money_fmt($p['gross_profit'] ?? 0) ?></td>
                    <td class="text-right"><?= number_format((float)($p['returns_qty'] ?? 0), 2) ?></td>
                    <td class="text-right"><?= esc($currency) . ' ' . money_fmt($p['returns_revenue'] ?? 0) ?></td>
                    <td class="text-right"><?= esc($currency) . ' ' . money_fmt($p['net_revenue'] ?? ($p['total_revenue'] ?? 0)) ?></td>
                    <td class="text-right"><?= esc($currency) . ' ' . money_fmt($p['net_cost'] ?? ($p['total_cost'] ?? 0)) ?></td>
                    <td class="text-right"><?= esc($currency) . ' ' . money_fmt($p['net_gross_profit'] ?? ($p['gross_profit'] ?? 0)) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th>Totals</th>
                <th></th>
                <th class="text-right"><?= esc($currency) . ' ' . money_fmt($totalRevenue ?? 0) ?></th>
                <th class="text-right"><?= esc($currency) . ' ' . money_fmt($totalCost ?? 0) ?></th>
                <th class="text-right"><?= esc($currency) . ' ' . money_fmt($totalGrossProfit ?? 0) ?></th>
                <th></th>
                <th class="text-right"><?= esc($currency) . ' ' . money_fmt($totalReturns ?? 0) ?></th>
                <th class="text-right"><?= esc($currency) . ' ' . money_fmt($totalRevenue ?? 0) ?></th>
                <th class="text-right"><?= esc($currency) . ' ' . money_fmt($totalCost ?? 0) ?></th>
                <th class="text-right"><?= esc($currency) . ' ' . money_fmt($netProfit ?? 0) ?></th>
            </tr>
        </tfoot>
    </table>
    <div style="margin-top:10px;">
        <p>
            <strong>Sales Count:</strong> <?= (int)($salesCount ?? 0) ?>,
            <strong>Total Discounts:</strong> <?= esc($currency) . ' ' . money_fmt($totalDiscounts ?? 0) ?>,
            <strong>Total Expenses:</strong> <?= esc($currency) . ' ' . money_fmt($totalExpenses ?? 0) ?>,
            <strong>Total Taxes:</strong> <?= esc($currency) . ' ' . money_fmt($totalTaxes ?? 0) ?>,
            <strong>Profit Margin:</strong> <?= number_format((float)($profitMargin ?? 0), 2) ?>%
        </p>
    </div>
    <div class="no-print">
        <button onclick="window.print()">Print</button>
        <button onclick="window.close()">Close</button>
    </div>
</body>

</html>