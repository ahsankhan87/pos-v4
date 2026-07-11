<!DOCTYPE html>
<html lang="<?= esc(current_locale()) ?>" dir="<?= esc(locale_direction()) ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? (lang('Reports.sales_report') . ' - ' . lang('Reports.print'))) ?></title>
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
    $grossTotal = 0;
    $discountTotal = 0;
    $returnsTotal = 0;
    $netTotal = 0;
    $count = 0;
    foreach (($sales ?? []) as $s) {
        $grossTotal += (float)($s['total'] ?? 0);
        $discountTotal += (float)($s['total_discount'] ?? 0);
        $returnsTotal += (float)($s['total_return_amount'] ?? 0);
        $netTotal += (float)($s['net_total'] ?? (($s['total'] ?? 0) - ($s['total_return_amount'] ?? 0)));
        $count++;
    }
    if (!function_exists('money_fmt')) {
        function money_fmt($v)
        {
            return number_format((float)$v, 2, '.', ',');
        }
    }
    ?>
    <h2><?= lang('Reports.sales_report') ?></h2>
    <p><?= lang('Reports.employee') ?>: <?= esc($employeeName ?? lang('Reports.all')) ?></p>
    <p><?= lang('Reports.period') ?>: <?= esc($from) ?> <?= lang('Reports.to') ?> <?= esc($to) ?></p>
    <table>
        <thead>
            <tr>
                <!-- <th>ID</th> -->
                <th><?= lang('Reports.invoice') ?></th>
                <th><?= lang('Reports.customer') ?></th>
                <th><?= lang('Reports.payment') ?></th>
                <th><?= lang('Reports.date') ?></th>
                <th class="text-right"><?= lang('Reports.gross') ?></th>
                <th class="text-right"><?= lang('Reports.discount') ?></th>
                <th class="text-right"><?= lang('Reports.returned') ?></th>
                <th class="text-right"><?= lang('Reports.net') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (($sales ?? []) as $sale): ?>
                <tr>
                    <!-- <td>#<?= (int)$sale['id'] ?></td> -->
                    <td><?= esc($sale['invoice_no']) ?></td>
                    <td><?= esc($sale['customer_name']) ?></td>
                    <td><?= esc($sale['payment_method']) ?></td>
                    <td><?= esc($sale['created_at']) ?></td>
                    <td class="text-right"><?= esc($currency) . ' ' . money_fmt($sale['total'] ?? 0) ?></td>
                    <td class="text-right"><?= esc($currency) . ' ' . money_fmt($sale['total_discount'] ?? 0) ?></td>
                    <td class="text-right"><?= esc($currency) . ' ' . money_fmt($sale['total_return_amount'] ?? 0) ?></td>
                    <td class="text-right"><?= esc($currency) . ' ' . money_fmt(($sale['net_total'] ?? (($sale['total'] ?? 0) - ($sale['total_return_amount'] ?? 0)))) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" class="text-right"><?= lang('Reports.totals') ?></th>
                <th class="text-right"><?= esc($currency) . ' ' . money_fmt($grossTotal) ?></th>
                <th class="text-right"><?= esc($currency) . ' ' . money_fmt($discountTotal) ?></th>
                <th class="text-right"><?= esc($currency) . ' ' . money_fmt($returnsTotal) ?></th>
                <th class="text-right"><?= esc($currency) . ' ' . money_fmt($netTotal) ?></th>
            </tr>
        </tfoot>
    </table>
    <div class="no-print">
        <button onclick="window.print()"><?= lang('Reports.print') ?></button>
        <button onclick="window.close()"><?= lang('Reports.close') ?></button>
    </div>
</body>

</html>