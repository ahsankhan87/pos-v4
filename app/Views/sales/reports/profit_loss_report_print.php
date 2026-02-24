<!DOCTYPE html>
<html lang="<?= esc(current_locale()) ?>" dir="<?= esc(locale_direction()) ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? (lang('Reports.profit_loss_report') . ' - ' . lang('Reports.print'))) ?></title>
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
    <h2><?= lang('Reports.profit_loss_report') ?></h2>
    <p><?= lang('Reports.employee') ?>: <?= esc($employeeName ?? lang('Reports.employee_all')) ?></p>
    <p><?= lang('Reports.period') ?>: <?= esc($from) ?> <?= lang('Reports.to') ?> <?= esc($to) ?></p>

    <table style="margin-bottom:8px;">
        <tbody>
            <tr>
                <td><?= lang('Reports.gross_revenue_products') ?></td>
                <td class="text-right"><?= esc($currency) . ' ' . money_fmt($grossRevenueProduct ?? 0) ?></td>
            </tr>
            <tr>
                <td><?= lang('Reports.gross_revenue_services') ?></td>
                <td class="text-right"><?= esc($currency) . ' ' . money_fmt($grossRevenueService ?? (($grossRevenue ?? 0) - ($grossRevenueProduct ?? 0))) ?></td>
            </tr>
            <tr>
                <td><?= lang('Reports.less_sales_returns_products') ?></td>
                <td class="text-right">(<?= esc($currency) . ' ' . money_fmt($productReturnAmount ?? 0) ?>)</td>
            </tr>
            <tr>
                <td><?= lang('Reports.less_sales_returns_services') ?></td>
                <td class="text-right">(<?= esc($currency) . ' ' . money_fmt($serviceReturnAmount ?? (($totalReturns ?? 0) - ($productReturnAmount ?? 0))) ?>)</td>
            </tr>
            <tr>
                <td><strong><?= lang('Reports.net_revenue') ?></strong></td>
                <td class="text-right"><strong><?= esc($currency) . ' ' . money_fmt($totalRevenue ?? 0) ?></strong></td>
            </tr>
            <tr>
                <td><?= lang('Reports.less_cogs_products') ?></td>
                <td class="text-right">(<?= esc($currency) . ' ' . money_fmt($totalCost ?? 0) ?>)</td>
            </tr>
            <tr>
                <td><strong><?= lang('Reports.gross_profit') ?></strong></td>
                <td class="text-right"><strong><?= esc($currency) . ' ' . money_fmt(($totalGrossProfit ?? 0)) ?></strong></td>
            </tr>
        </tbody>
    </table>
    <table>
        <thead>
            <tr>
                <th><?= lang('Reports.product') ?></th>
                <th class="text-right"><?= lang('Reports.qty_sold') ?></th>
                <th class="text-right"><?= lang('Reports.revenue') ?></th>
                <th class="text-right"><?= lang('Reports.cost') ?></th>
                <th class="text-right"><?= lang('Reports.gross_profit') ?></th>
                <th class="text-right"><?= lang('Reports.returns_qty') ?></th>
                <th class="text-right"><?= lang('Reports.returns_amt') ?></th>
                <th class="text-right"><?= lang('Reports.net_revenue') ?></th>
                <th class="text-right"><?= lang('Reports.net_cost') ?></th>
                <th class="text-right"><?= lang('Reports.net_gross_profit') ?></th>
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
                <th><?= lang('Reports.totals') ?></th>
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
            <strong><?= lang('Reports.sales_count_label') ?>:</strong> <?= (int)($salesCount ?? 0) ?>,
            <strong><?= lang('Reports.total_discounts') ?>:</strong> <?= esc($currency) . ' ' . money_fmt($totalDiscounts ?? 0) ?>,
            <strong><?= lang('Reports.total_expenses') ?>:</strong> <?= esc($currency) . ' ' . money_fmt($totalExpenses ?? 0) ?>,
            <strong><?= lang('Reports.total_taxes') ?>:</strong> <?= esc($currency) . ' ' . money_fmt($totalTaxes ?? 0) ?>,
            <strong><?= lang('Reports.profit_margin') ?>:</strong> <?= number_format((float)($profitMargin ?? 0), 2) ?>%
        </p>
    </div>
    <div class="no-print">
        <button onclick="window.print()"><?= lang('Reports.print') ?></button>
        <button onclick="window.close()"><?= lang('Reports.close') ?></button>
    </div>
</body>

</html>