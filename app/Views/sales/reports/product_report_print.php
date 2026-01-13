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

        .text-center {
            text-align: center;
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
    $totalQty = 0;
    $totalSales = 0;
    $productCount = 0;
    foreach (($items ?? []) as $it) {
        $totalQty += (float)($it['total_qty'] ?? 0);
        $totalSales += (float)($it['total_sales'] ?? 0);
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
            if (!$cartonSize || $cartonSize <= 1) return number_format($pieces, 2) . ' pcs';
            $cartons = floor($pieces / $cartonSize);
            $remaining = $pieces - ($cartons * $cartonSize);
            return $remaining > 0 ? (number_format($cartons) . ' ctns + ' . number_format($remaining, 2) . ' pcs') : (number_format($cartons) . ' ctns');
        }
    }
    ?>
    <h2>Product-wise Sales Report</h2>
    <p>Employee: <?= esc($employeeName ?? 'All') ?></p>
    <p>Range: Period: <?= esc($from) ?> to <?= esc($to) ?></p>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th class="text-right">Total Quantity</th>
                <th class="text-right">Total Sales</th>
                <th class="text-right">Avg</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (($items ?? []) as $item): ?>
                <?php
                $rowQty = (float)($item['total_qty'] ?? 0);
                $rowSales = (float)($item['total_sales'] ?? 0);
                $rowAvg = avg_price($rowSales, $rowQty);
                ?>
                <tr>
                    <td><?= esc($item['product_name']) ?></td>
                    <td class="text-right"><?= formatQuantity($rowQty, (float)($item['carton_size'] ?? 0)) ?></td>
                    <td class="text-right"><?= esc($currency) . ' ' . money_fmt($rowSales) ?></td>
                    <td class="text-right"><?= esc($currency) . ' ' . money_fmt($rowAvg) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th class="text-right">Totals</th>
                <th id="totalQtyCellPrint" class="text-right"><?= number_format($totalQty, 2) ?></th>
                <th id="totalSalesCellPrint" class="text-right"><?= esc($currency) . ' ' . money_fmt($totalSales) ?></th>
                <th id="totalAvgCellPrint" class="text-right"><?= esc($currency) . ' ' . money_fmt(avg_price($totalSales, $totalQty)) ?></th>
            </tr>
        </tfoot>
    </table>
    <div class="no-print">
        <button onclick="window.print()">Print</button>
        <button onclick="window.close()">Close</button>
    </div>
</body>

</html>