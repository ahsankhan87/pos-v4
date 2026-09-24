<!DOCTYPE html>
<html lang="<?= esc(current_locale()) ?>" dir="<?= esc(locale_direction()) ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? (lang('Purchases.purchase_report') . ' - ' . lang('Reports.print'))) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 10mm;
            color: #111827;
        }

        h2 {
            margin: 0 0 6px 0;
        }

        p {
            margin: 2px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
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

        tfoot th {
            background: #f9fafb;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .summary-table td {
            border: none;
            padding: 2px 6px;
        }

        .summary-table td:first-child {
            font-weight: bold;
            width: 40%;
        }

        .summary-grid {
            display: flex;
            gap: 24px;
            margin-bottom: 10px;
        }

        .summary-grid>div {
            flex: 1;
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
    $products = $products ?? [];

    if (!function_exists('purchase_money_fmt')) {
        function purchase_money_fmt($v)
        {
            return number_format((float) $v, 2, '.', ',');
        }
    }
    if (!function_exists('purchase_qty_fmt')) {
        function purchase_qty_fmt($qty, $cartonSize)
        {
            $qty = (float) $qty;
            $cartonSize = (float) $cartonSize;
            if ($cartonSize > 1) {
                $cartons = floor($qty / $cartonSize);
                $remaining = $qty - ($cartons * $cartonSize);
                if ($remaining > 0) {
                    return $cartons . ' ' . lang('Purchases.ctns') . ' + ' . purchase_money_fmt($remaining) . ' ' . lang('Purchases.pcs');
                }
                return $cartons . ' ' . lang('Purchases.ctns');
            }
            return purchase_money_fmt($qty) . ' ' . lang('Purchases.pcs');
        }
    }

    $supplierName = lang('Purchases.all_suppliers');
    if (!empty($supplierId)) {
        foreach (($suppliers ?? []) as $supplier) {
            if ((int) $supplier['id'] === (int) $supplierId) {
                $supplierName = $supplier['name'];
                break;
            }
        }
    }
    ?>

    <h2><?= lang('Purchases.purchase_report') ?></h2>
    <p><?= lang('Purchases.supplier') ?>: <?= esc($supplierName) ?></p>
    <p><?= lang('Reports.period') ?>: <?= esc(date('d M Y', strtotime($from))) ?> <?= lang('Reports.to') ?> <?= esc(date('d M Y', strtotime($to))) ?></p>

    <div class="summary-grid">
        <div>
            <table class="summary-table">
                <tr>
                    <td><?= lang('Purchases.total_purchase_orders') ?>:</td>
                    <td><?= number_format($totalPurchases ?? 0) ?></td>
                </tr>
                <tr>
                    <td><?= lang('Purchases.total_products') ?>:</td>
                    <td><?= count($products) ?></td>
                </tr>
                <tr>
                    <td><?= lang('Purchases.total_purchase_value') ?>:</td>
                    <td><?= esc($currency) ?> <?= purchase_money_fmt($totalAmount ?? 0) ?></td>
                </tr>
                <tr>
                    <td><?= lang('Purchases.less_purchase_returns') ?>:</td>
                    <td>(<?= esc($currency) ?> <?= purchase_money_fmt($totalReturnAmount ?? 0) ?>)</td>
                </tr>
                <tr>
                    <td><?= lang('Purchases.net_purchase_value') ?>:</td>
                    <td><?= esc($currency) ?> <?= purchase_money_fmt($netTotalAmount ?? ($totalAmount ?? 0)) ?></td>
                </tr>
                <tr>
                    <td><?= lang('Purchases.amount_paid') ?>:</td>
                    <td><?= esc($currency) ?> <?= purchase_money_fmt($totalPaid ?? 0) ?></td>
                </tr>
                <tr>
                    <td><?= lang('Purchases.outstanding_due') ?>:</td>
                    <td><?= esc($currency) ?> <?= purchase_money_fmt($totalDue ?? 0) ?></td>
                </tr>
                <tr>
                    <td><?= lang('Purchases.returned_qty') ?>:</td>
                    <td><?= purchase_money_fmt($totalReturnQty ?? 0) ?></td>
                </tr>
            </table>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th><?= lang('Purchases.product_code') ?></th>
                <th><?= lang('Purchases.product_name') ?></th>
                <th><?= lang('Purchases.invoices') ?></th>
                <th class="text-center"><?= lang('Purchases.purchased_gross') ?></th>
                <th class="text-center"><?= lang('Purchases.returns') ?></th>
                <th class="text-center"><?= lang('Purchases.net_purchased') ?></th>
                <th class="text-right"><?= lang('Purchases.avg_cost') ?></th>
                <th class="text-right"><?= lang('Purchases.returns_value') ?></th>
                <th class="text-right"><?= lang('Purchases.total_cost_net') ?></th>
                <th class="text-center"><?= lang('Purchases.orders') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($products)): ?>
                <tr>
                    <td colspan="11" class="text-center"><?= lang('Purchases.no_purchase_data_for_date_range') ?></td>
                </tr>
            <?php else: ?>
                <?php foreach ($products as $idx => $product): ?>
                    <?php
                    $cartonSize = (float) ($product['carton_size'] ?? 0);
                    $retAmt = (float) ($product['returns_amount'] ?? 0);
                    ?>
                    <tr>
                        <td><?= $idx + 1 ?></td>
                        <td><?= esc($product['product_code']) ?></td>
                        <td><?= esc($product['product_name']) ?></td>
                        <td><?= esc($product['invoice_numbers'] ?? '') ?></td>
                        <td class="text-center"><?= purchase_qty_fmt($product['total_quantity'] ?? 0, $cartonSize) ?></td>
                        <td class="text-center"><?= ((float) ($product['returns_qty'] ?? 0) > 0) ? ('-' . purchase_money_fmt($product['returns_qty']) . ' ' . lang('Purchases.pcs')) : '—' ?></td>
                        <td class="text-center"><?= purchase_qty_fmt($product['net_quantity'] ?? ($product['total_quantity'] ?? 0), $cartonSize) ?></td>
                        <td class="text-right"><?= esc($currency) ?> <?= purchase_money_fmt($product['avg_cost_price'] ?? 0) ?></td>
                        <td class="text-right"><?= $retAmt > 0 ? ('(' . esc($currency) . ' ' . purchase_money_fmt($retAmt) . ')') : '—' ?></td>
                        <td class="text-right"><?= esc($currency) ?> <?= purchase_money_fmt($product['net_cost'] ?? ($product['total_cost'] ?? 0)) ?></td>
                        <td class="text-center"><?= (int) ($product['purchase_count'] ?? 0) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <?php if (!empty($products)): ?>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-right"><?= lang('Purchases.totals') ?>:</th>
                    <th class="text-center"><?= purchase_money_fmt($totalQuantity ?? 0) ?> <?= lang('Purchases.pcs') ?></th>
                    <th class="text-center">-<?= purchase_money_fmt($totalReturnQty ?? 0) ?> <?= lang('Purchases.pcs') ?></th>
                    <th class="text-center"><?= purchase_money_fmt($totalNetQty ?? 0) ?> <?= lang('Purchases.pcs') ?></th>
                    <th class="text-right"></th>
                    <th class="text-right">(<?= esc($currency) ?> <?= purchase_money_fmt($totalReturnAmount ?? 0) ?>)</th>
                    <th class="text-right"><?= esc($currency) ?> <?= purchase_money_fmt($totalNetCost ?? ($totalCost ?? 0)) ?></th>
                    <th></th>
                </tr>
            </tfoot>
        <?php endif; ?>
    </table>

    <p class="text-center"><?= lang('Reports.generated') ?>: <?= esc(date('d M Y H:i')) ?></p>

    <div class="no-print">
        <button onclick="window.print()"><?= lang('Reports.print') ?></button>
        <button onclick="window.close()"><?= lang('Reports.close') ?></button>
    </div>

    <script>
        // Auto-print when opened
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 200);
        });
    </script>
</body>

</html>