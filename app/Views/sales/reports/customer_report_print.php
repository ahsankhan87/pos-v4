<!DOCTYPE html>
<html lang="<?= esc(current_locale()) ?>" dir="<?= esc(locale_direction()) ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? (lang('Reports.customer_wise_sales_report') . ' - ' . lang('Reports.print'))) ?></title>
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
    $q = isset($q) ? (string) $q : '';

    $salesFiltered = [];
    foreach (($sales ?? []) as $row) {
        $name = (string) ($row['customer_name'] ?? '');
        if ($q !== '' && stripos($name, $q) === false) {
            continue;
        }
        $salesFiltered[] = $row;
    }

    $totalSales = 0;
    $totalDiscount = 0;
    $saleCount = 0;
    $customerCount = 0;
    foreach ($salesFiltered as $s) {
        $totalSales += (float)($s['total_sales'] ?? 0);
        $totalDiscount += (float)($s['total_discount'] ?? 0);
        $saleCount += (int)($s['sale_count'] ?? 0);
        $customerCount++;
    }
    if (!function_exists('money_fmt')) {
        function money_fmt($v)
        {
            return number_format((float)$v, 2, '.', ',');
        }
    }
    ?>
    <h2><?= lang('Reports.customer_wise_sales_report') ?></h2>
    <p><?= lang('Reports.employee') ?>: <?= esc($employeeName ?? lang('Reports.employee_all')) ?></p>
    <p><?= lang('Reports.period') ?>: <?= esc($from) ?> <?= lang('Reports.to') ?> <?= esc($to) ?></p>
    <?php if ($q !== ''): ?>
        <p><?= lang('Reports.search') ?>: <?= esc($q) ?></p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th><?= lang('Reports.customer') ?></th>
                <th class="text-right"><?= lang('Reports.sales_count_col') ?></th>
                <th class="text-right"><?= lang('Reports.total_sales') ?></th>
                <th class="text-right"><?= lang('Reports.total_discount') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($salesFiltered as $row): ?>
                <tr>
                    <td><?= esc($row['customer_name']) ?></td>
                    <td class="text-right"><?= number_format((int)($row['sale_count'] ?? 0)) ?></td>
                    <td class="text-right"><?= esc($currency) . ' ' . money_fmt($row['total_sales'] ?? 0) ?></td>
                    <td class="text-right"><?= esc($currency) . ' ' . money_fmt($row['total_discount'] ?? 0) ?></td>
                </tr>
            <?php endforeach; ?>

            <?php if (empty($salesFiltered)): ?>
                <tr>
                    <td colspan="4" style="text-align:center; padding: 10px; color: #666;"><?= lang('Reports.no_matching_customers') ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <th><?= lang('Reports.totals') ?></th>
                <th class="text-right"><?= number_format($saleCount) ?></th>
                <th class="text-right"><?= esc($currency) . ' ' . money_fmt($totalSales) ?></th>
                <th class="text-right"><?= esc($currency) . ' ' . money_fmt($totalDiscount) ?></th>
            </tr>
        </tfoot>
    </table>

    <div class="no-print">
        <button onclick="window.print()"><?= lang('Reports.print') ?></button>
        <button onclick="window.close()"><?= lang('Reports.close') ?></button>
    </div>
</body>

</html>