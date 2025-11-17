<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Category-wise Sales Report - Print') ?></title>
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
    $totalSales = 0;
    $totalQty = 0;
    $rowCount = 0;
    $totalSaleCount = 0;
    foreach (($rows ?? []) as $r) {
        $totalSales += (float)($r['total_sales'] ?? 0);
        $totalQty += (float)($r['total_qty'] ?? 0);
        $totalSaleCount += (int)($r['sale_count'] ?? 0);
        $rowCount++;
    }
    if (!function_exists('money_fmt')) {
        function money_fmt($v)
        {
            return number_format((float)$v, 2, '.', ',');
        }
    }
    ?>
    <h2>Category-wise Sales Report</h2>
    <p>Employee: <?= esc($employeeName ?? 'All') ?></p>
    <p>Period: <?= esc($from) ?> to <?= esc($to) ?></p>
    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th class="text-right">Sales Count</th>
                <th class="text-right">Total Quantity</th>
                <th class="text-right">Total Sales</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (($rows ?? []) as $row): ?>
                <tr>
                    <td><?= esc($row['category_name'] ?? 'Uncategorized') ?></td>
                    <td class="text-right"><?= number_format((int)($row['sale_count'] ?? 0)) ?></td>
                    <td class="text-right"><?= number_format((float)($row['total_qty'] ?? 0), 2) ?></td>
                    <td class="text-right"><?= esc($currency) . ' ' . money_fmt($row['total_sales'] ?? 0) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th>Totals</th>
                <th class="text-right"><?= number_format($totalSaleCount) ?></th>
                <th class="text-right"><?= number_format($totalQty, 2) ?></th>
                <th class="text-right"><?= esc($currency) . ' ' . money_fmt($totalSales) ?></th>
            </tr>
        </tfoot>
    </table>
    <div class="no-print">
        <button onclick="window.print()">Print</button>
        <button onclick="window.close()">Close</button>
    </div>
</body>

</html>