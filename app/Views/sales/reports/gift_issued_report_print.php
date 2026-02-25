<!DOCTYPE html>
<html lang="<?= esc(current_locale()) ?>" dir="<?= esc(locale_direction()) ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? (lang('Reports.gift_issued_report') . ' - ' . lang('Reports.print'))) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 10mm;
        }

        h2 {
            margin: 0 0 6px 0;
        }

        p {
            margin: 0 0 4px 0;
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
            vertical-align: top;
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
    $rows = (array)($rows ?? []);

    $totalQty = 0.0;
    $totalAmount = 0.0;
    $customerCount = 0;
    foreach ($rows as $row) {
        $totalQty += (float)($row['gift_qty'] ?? 0);
        $totalAmount += (float)($row['gift_amount'] ?? 0);
        $customerCount++;
    }
    ?>

    <h2><?= lang('Reports.gift_issued_report') ?></h2>
    <p><?= lang('Reports.period') ?>: <?= esc($from ?? date('Y-m-d')) ?> <?= lang('Reports.to') ?> <?= esc($to ?? date('Y-m-d')) ?></p>
    <p><?= lang('Reports.employee') ?>: <?= esc($employeeName ?? lang('Reports.all_employees')) ?></p>
    <p><?= lang('Reports.category') ?>: <?= esc($category_name ?? lang('Reports.gift')) ?></p>

    <table>
        <thead>
            <tr>
                <th><?= lang('Reports.customer') ?></th>
                <th><?= lang('Reports.contact_no') ?></th>
                <th><?= lang('Reports.area') ?></th>
                <th><?= lang('Reports.category') ?></th>
                <th><?= lang('Reports.issued_date') ?></th>
                <th><?= lang('Reports.products') ?></th>
                <th class="text-right"><?= lang('Reports.invoices') ?></th>
                <th class="text-right"><?= lang('Reports.gift_qty') ?></th>
                <th class="text-right"><?= lang('Reports.gift_amount') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($rows)): ?>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= esc($row['customer_name'] ?? lang('Reports.walk_in_customer')) ?></td>
                        <td><?= esc($row['customer_phone'] ?? '-') ?></td>
                        <td><?= esc($row['customer_area'] ?? '-') ?></td>
                        <td><?= esc($row['category_name'] ?? lang('Reports.gift')) ?></td>
                        <td><?= !empty($row['issued_date']) ? esc(date('d-m-Y', strtotime((string)$row['issued_date']))) : '-' ?></td>
                        <td><?= esc($row['gift_products'] ?? '-') ?></td>
                        <td class="text-right"><?= number_format((int)($row['invoice_count'] ?? 0)) ?></td>
                        <td class="text-right"><?= number_format((float)($row['gift_qty'] ?? 0), 2) ?></td>
                        <td class="text-right"><?= esc($currency) . ' ' . number_format((float)($row['gift_amount'] ?? 0), 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" style="text-align:center; padding: 10px; color: #666;"><?= lang('Reports.no_gift_records_filters') ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="6"><?= lang('Reports.totals') ?></th>
                <th class="text-right"><?= number_format($customerCount) ?></th>
                <th class="text-right"><?= number_format($totalQty, 2) ?></th>
                <th class="text-right"><?= esc($currency) . ' ' . number_format($totalAmount, 2) ?></th>
            </tr>
        </tfoot>
    </table>

    <div class="no-print">
        <button onclick="window.print()"><?= lang('Reports.print') ?></button>
        <button onclick="window.close()"><?= lang('Reports.close') ?></button>
    </div>
</body>

</html>