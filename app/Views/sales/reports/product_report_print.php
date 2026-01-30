<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Product-wise Sales Report - Print') ?></title>
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
                display: none !important;
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
    $q = isset($q) ? (string)$q : '';
    $canProfit = can('reports.profit_loss');

    $totalQty = 0;
    $totalSales = 0;
    $totalProfit = 0;
    $productCount = 0;
    foreach (($items ?? []) as $it) {
        $totalQty += (float)($it['total_qty'] ?? 0);
        $totalSales += (float)($it['total_sales'] ?? 0);
        if ($canProfit) {
            $totalProfit += (float)($it['profit'] ?? 0);
        }
        $productCount++;
    }

    if (!function_exists('money_fmt')) {
        function money_fmt($v)
        {
            return number_format((float)$v, 2, '.', ',');
        }
    }
    if (!function_exists('avg_price')) {
        function avg_price($sales, $qty)
        {
            $sales = (float)$sales;
            $qty = (float)$qty;
            if ($qty <= 0) {
                return 0.0;
            }
            return $sales / $qty;
        }
    }
    if (!function_exists('formatQuantity')) {
        function formatQuantity($pieces, $cartonSize)
        {
            $pieces = (float)$pieces;
            $cartonSize = (float)$cartonSize;

            $sign = $pieces < 0 ? '-' : '';
            $piecesAbs = abs($pieces);
            if (!$cartonSize || $cartonSize <= 1) {
                return $sign . number_format($piecesAbs, 2) . ' pcs';
            }

            $cartons = floor($piecesAbs / $cartonSize);
            $remaining = $piecesAbs - ($cartons * $cartonSize);
            if ($remaining > 0) {
                return $sign . number_format($cartons) . ' ctns + ' . number_format($remaining, 2) . ' pcs';
            }
            return $sign . number_format($cartons) . ' ctns';
        }
    }
    ?>

    <h2>Product-wise Sales Report</h2>
    <p>Employee: <?= esc($employeeName ?? 'All') ?></p>
    <p>Period: <?= esc($from) ?> to <?= esc($to) ?></p>
    <?php if ($q !== ''): ?>
        <p>Search: <?= esc($q) ?></p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th style="text-align:left;">Product</th>
                <th class="text-right">Total Quantity</th>
                <th class="text-right">Total Sales</th>
                <th class="text-right">Avg</th>
                <?php if ($canProfit): ?>
                    <th class="text-right">Profit</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach (($items ?? []) as $item): ?>
                <?php
                $rowQty = (float)($item['total_qty'] ?? 0);
                $rowSales = (float)($item['total_sales'] ?? 0);
                $rowAvg = avg_price($rowSales, $rowQty);
                $rowProfit = (float)($item['profit'] ?? 0);
                ?>
                <tr>
                    <td style="text-align:left;">
                        <?= esc($item['product_name'] ?? '') ?>
                        <?php if (!empty($item['product_code'])): ?>
                            <div style="font-size:10px;color:#555;">(<?= esc($item['product_code']) ?>)</div>
                        <?php endif; ?>
                    </td>
                    <td class="text-right"><?= formatQuantity($rowQty, (float)($item['carton_size'] ?? 0)) ?></td>
                    <td class="text-right"><?= esc($currency) . ' ' . money_fmt($rowSales) ?></td>
                    <td class="text-right"><?= esc($currency) . ' ' . money_fmt($rowAvg) ?></td>
                    <?php if ($canProfit): ?>
                        <td class="text-right"><?= esc($currency) . ' ' . money_fmt($rowProfit) ?></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th class="text-right">Totals</th>
                <th class="text-right"><?= number_format($totalQty, 2) ?></th>
                <th class="text-right"><?= esc($currency) . ' ' . money_fmt($totalSales) ?></th>
                <th class="text-right"><?= esc($currency) . ' ' . money_fmt(avg_price($totalSales, $totalQty)) ?></th>
                <?php if ($canProfit): ?>
                    <th class="text-right"><?= esc($currency) . ' ' . money_fmt($totalProfit) ?></th>
                <?php endif; ?>
            </tr>
        </tfoot>
    </table>
    <div class="no-print">
        <button onclick="window.print()">Print</button>
        <button onclick="window.close()">Close</button>
    </div>
</body>

</html>