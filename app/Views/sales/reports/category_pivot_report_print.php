<!DOCTYPE html>
<html lang="<?= esc(current_locale()) ?>" dir="<?= esc(locale_direction()) ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? (lang('Reports.category_pivot_sales_report') . ' - ' . lang('Reports.print'))) ?></title>
    <style>
        @page {
            size: A4 landscape;
            margin: 5mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 8mm;
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
            padding: 4px 5px;
            font-size: 10px;
            white-space: nowrap;
        }

        th.col-sno,
        td.col-sno {
            width: 8mm;
            text-align: center;
        }

        th.col-emp,
        td.col-emp {
            width: 32mm;
        }

        th.col-area,
        td.col-area {
            width: 24mm;
        }

        th.col-count,
        td.col-count {
            width: 14mm;
        }

        th.col-total,
        td.col-total {
            width: 22mm;
        }

        th.cat-col,
        td.cat-cell {
            width: 11mm;
        }

        th.cat-total-cell {
            width: 11mm;
        }

        thead th,
        tfoot th,
        tfoot td {
            background: #f0f0f0;
        }

        .text-right {
            text-align: right;
        }

        .no-print {
            margin-top: 10px;
            text-align: center;
        }

        .table-wrap {
            overflow-x: auto;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                margin: 5mm;
            }

            .table-wrap {
                overflow: visible;
            }

            table {
                table-layout: fixed;
            }

            th,
            td {
                font-size: 8px;
                padding: 2px 3px;
                white-space: normal;
                word-break: break-word;
            }

            thead th.cat-col {
                writing-mode: vertical-rl;
                transform: rotate(180deg);
                text-align: left;
                vertical-align: bottom;
                line-height: 1.05;
                padding: 2px 1px;
            }

            td.cat-cell {
                text-align: right;
            }
        }
    </style>
</head>

<body>
    <?php
    $currency = session()->get('currency_symbol') ?? '$';
    $categories = $categories ?? [];
    $rows = $rows ?? [];
    $categoryTotals = $categoryTotals ?? [];
    $grand = $grand ?? ['sale_count' => 0, 'product_count' => 0, 'total_sales' => 0];
    $hideAreaInPrint = count($categories) > 14;

    if (!function_exists('cat_abbr')) {
        function cat_abbr($name)
        {
            $name = trim((string)$name);
            if ($name === '') {
                return '';
            }

            $parts = preg_split('/\s+/', $name) ?: [];
            if (count($parts) > 1) {
                $abbr = '';
                foreach ($parts as $p) {
                    if ($p === '') {
                        continue;
                    }
                    $abbr .= function_exists('mb_substr') ? mb_substr($p, 0, 1) : substr($p, 0, 1);
                    if ((function_exists('mb_strlen') ? mb_strlen($abbr) : strlen($abbr)) >= 4) {
                        break;
                    }
                }
                if ($abbr !== '') {
                    return function_exists('mb_strtoupper') ? mb_strtoupper($abbr) : strtoupper($abbr);
                }
            }

            $max = 6;
            $len = function_exists('mb_strlen') ? mb_strlen($name) : strlen($name);
            if ($len <= $max) {
                return $name;
            }

            return function_exists('mb_substr') ? mb_substr($name, 0, $max) : substr($name, 0, $max);
        }
    }

    if (!function_exists('money_fmt')) {
        function money_fmt($v)
        {
            return number_format((float)$v, 2, '.', ',');
        }
    }
    ?>

    <h2><?= lang('Reports.category_pivot_sales_report') ?></h2>
    <p><?= lang('Reports.employee') ?>: <?= esc($employeeName ?? lang('Reports.all_employees')) ?></p>
    <p><?= lang('Reports.period') ?>: <?= esc($from) ?> <?= lang('Reports.to') ?> <?= esc($to) ?></p>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th class="col-sno"><?= lang('Reports.s_no') ?></th>
                    <th class="col-emp"><?= lang('Reports.employee') ?></th>
                    <?php if (!$hideAreaInPrint): ?>
                        <th class="col-area"><?= lang('Reports.area') ?></th>
                    <?php endif; ?>
                    <th class="text-right col-count"><?= lang('Reports.tx') ?></th>
                    <th class="text-right col-count"><?= lang('Reports.products') ?></th>
                    <?php foreach ($categories as $cat): ?>
                        <?php $fullCat = (string)($cat['name'] ?? lang('Reports.uncategorized')); ?>
                        <th class="text-right cat-col" title="<?= esc($fullCat) ?>"><?= esc(cat_abbr($fullCat)) ?></th>
                    <?php endforeach; ?>
                    <th class="text-right col-total"><?= lang('Reports.total') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="<?= ($hideAreaInPrint ? 5 : 6) + count($categories) ?>" style="text-align:center;"><?= lang('Reports.no_data_period') ?></td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rows as $idx => $row): ?>
                        <tr>
                            <td class="col-sno"><?= (int)$idx + 1 ?></td>
                            <td class="col-emp"><?= esc($row['employee_name'] ?? lang('Reports.unassigned')) ?></td>
                            <?php if (!$hideAreaInPrint): ?>
                                <td class="col-area"><?= esc($row['area_route'] ?? '-') ?></td>
                            <?php endif; ?>
                            <td class="text-right col-count"><?= number_format((int)($row['sale_count'] ?? 0)) ?></td>
                            <td class="text-right col-count"><?= number_format((int)($row['product_count'] ?? 0)) ?></td>
                            <?php foreach ($categories as $cat):
                                $cid = (int)($cat['id'] ?? 0);
                                $value = (float)($row['categories'][$cid] ?? 0);
                            ?>
                                <td class="text-right cat-cell"><?= money_fmt($value) ?></td>
                            <?php endforeach; ?>
                            <td class="text-right col-total"><?= esc($currency) . ' ' . money_fmt($row['total_sales'] ?? 0) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>

                    <th></th>
                    <?php if (!$hideAreaInPrint): ?>
                        <th></th>
                    <?php endif; ?>
                    <th><?= lang('Reports.totals') ?></th>
                    <th class="text-right col-count"><?= number_format((int)($grand['sale_count'] ?? 0)) ?></th>
                    <th class="text-right col-count"><?= number_format((int)($grand['product_count'] ?? 0)) ?></th>
                    <?php foreach ($categories as $cat):
                        $cid = (int)($cat['id'] ?? 0);
                        $sum = (float)($categoryTotals[$cid] ?? 0);
                    ?>
                        <th class="text-right cat-total-cell"><?= money_fmt($sum) ?></th>
                    <?php endforeach; ?>
                    <th class="text-right col-total"><?= esc($currency) . ' ' . money_fmt($grand['total_sales'] ?? 0) ?></th>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="no-print">
        <button onclick="window.print()"><?= lang('Reports.print') ?></button>
        <button onclick="window.close()"><?= lang('Reports.close') ?></button>
    </div>
</body>

</html>