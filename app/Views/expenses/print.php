<html lang="<?= esc(current_locale()) ?>" dir="<?= esc(locale_direction(current_locale())) ?>">

<head>
    <?php helper('locale'); ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= lang('Expenses.expensesReport') ?></title>
    <style>
        @media print {
            .no-print {
                display: none !important
            }
        }

        .table {
            width: 100%;
            border-collapse: collapse
        }

        .table th,
        .table td {
            border: 1px solid #e5e7eb;
            padding: 8px;
            font-size: 12px
        }

        .table th {
            background: #f8fafc;
            text-transform: uppercase;
            color: #64748b
        }
    </style>
</head>

<body>
    <div style="text-align: center;" class="flex items-center justify-between no-print">
        <a href="<?= site_url('expenses') ?>" class="btn btn-muted"><?= lang('Expenses.back') ?></a>
        <button onclick="window.print()" class="btn btn-primary"><?= lang('Expenses.print') ?></button>
    </div>
    <?php $currencySymbol = session()->get('currency_symbol') ?: '$'; ?>
    <h2><?= lang('Expenses.expensesReport') ?></h2>
    <table border="1" cellpadding="5" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th><?= lang('Expenses.date') ?></th>
                <th><?= lang('Expenses.category') ?></th>
                <th><?= lang('Expenses.vendor') ?></th>
                <th><?= lang('Expenses.description') ?></th>
                <th><?= lang('Expenses.amount') ?></th>
                <th><?= lang('Expenses.tax') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $totalAmount = 0;
            $totalTax = 0;
            foreach (($rows ?? []) as $r):
                $totalAmount += (float)($r['amount'] ?? 0);
                $totalTax += (float)($r['tax'] ?? 0);
            ?>
                <tr>
                    <td><?= esc($r['date'] ?? '') ?></td>
                    <td><?= esc($r['category_name'] ?? '-') ?></td>
                    <td><?= esc($r['vendor'] ?? '-') ?></td>
                    <td><?= esc($r['description'] ?? '-') ?></td>
                    <td style="text-align:right;"><?= $currencySymbol ?><?= number_format((float)($r['amount'] ?? 0), 2) ?></td>
                    <td style="text-align:right;"><?= $currencySymbol ?><?= number_format((float)($r['tax'] ?? 0), 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" style="text-align:right;"><?= lang('Expenses.totals') ?></th>
                <th style="text-align:right;"><?= $currencySymbol ?><?= number_format($totalAmount, 2) ?></th>
                <th style="text-align:right;"><?= $currencySymbol ?><?= number_format($totalTax, 2) ?></th>
            </tr>
        </tfoot>
    </table>

</body>

</html>