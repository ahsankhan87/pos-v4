<!DOCTYPE html>
<html lang="<?= esc(current_locale()) ?>" dir="<?= esc(locale_direction()) ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc(lang('Reports.category_wise_expense_report')) ?></title>
    <style>
        :root {
            --border: #d7dbe0;
            --muted: #55606d;
            --text: #111827;
            --bg: #ffffff;
            --row: #f8fafc;
        }

        body {
            font-family: Arial, sans-serif;
            color: var(--text);
            background: #f3f4f6;
            margin: 0;
            padding: 16px;
            font-size: 12px;
        }

        .page {
            max-width: 1100px;
            margin: 0 auto;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 14px 14px 10px;
        }

        .header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 10px;
        }

        h2 {
            margin: 0;
            font-size: 18px;
            line-height: 1.25;
        }

        .sub {
            margin-top: 4px;
            color: var(--muted);
            font-size: 12px;
        }

        .summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 8px;
            margin: 10px 0 12px;
        }

        .card {
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 10px;
            background: #fff;
        }

        .card .label {
            color: var(--muted);
            font-size: 11px;
        }

        .card .value {
            margin-top: 4px;
            font-weight: 700;
            font-size: 14px;
        }

        .table-wrap {
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            background: #f3f4f6;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .02em;
            color: #374151;
            padding: 8px 8px;
            border-bottom: 1px solid var(--border);
            text-align: left;
            vertical-align: bottom;
        }

        tbody td {
            padding: 7px 8px;
            border-top: 1px solid #eef0f3;
            vertical-align: top;
            word-break: break-word;
        }

        tbody tr:nth-child(even) td {
            background: var(--row);
        }

        .num {
            text-align: right;
            white-space: nowrap;
        }

        .nowrap {
            white-space: nowrap;
        }

        tfoot td {
            padding: 8px;
            border-top: 1px solid var(--border);
            background: #f3f4f6;
            font-weight: 700;
        }

        .actions {
            margin-top: 12px;
            display: flex;
            gap: 8px;
        }

        .btn {
            appearance: none;
            border: 1px solid var(--border);
            background: #111827;
            color: #fff;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 12px;
        }

        .btn.secondary {
            background: #fff;
            color: #111827;
        }

        @media print {
            @page {
                size: A4;
                margin: 10mm;
            }

            .no-print {
                display: none !important;
            }

            body {
                background: #fff !important;
                padding: 0 !important;
                font-size: 10px;
            }

            .page {
                max-width: none !important;
                border: 0 !important;
                border-radius: 0 !important;
                padding: 0 !important;
            }

            thead th {
                padding: 3px 4px !important;
                border: 1px solid #ddd !important;
                font-size: 9px !important;
            }

            tbody td,
            tfoot td {
                padding: 3px 4px !important;
                border: 1px solid #ddd !important;
                font-size: 9px !important;
            }

            h2 {
                font-size: 13px !important;
            }

            .summary {
                gap: 6px;
                margin: 8px 0 10px;
            }

            .card {
                padding: 6px;
                border-radius: 8px;
            }
        }
    </style>
</head>

<body onload="window.print()">
    <?php
    $currency = session()->get('currency_symbol') ?? '$';
    $from = isset($from) ? $from : date('Y-m-01');
    $to = isset($to) ? $to : date('Y-m-d');
    function money_fmt($v)
    {
        return number_format((float)$v, 2);
    }

    $categoryCount = is_array($rows ?? null) ? count($rows) : 0;
    ?>

    <div class="page">
        <div class="header">
            <div>
                <h2><?= lang('Reports.category_wise_expense_report') ?></h2>
                <div class="sub"><?= lang('Reports.period') ?>: <span class="nowrap"><?= htmlspecialchars($from) ?></span> <?= lang('Reports.to') ?> <span class="nowrap"><?= htmlspecialchars($to) ?></span></div>
                <div class="sub"><?= lang('Reports.categories') ?>: <?= (int)$categoryCount ?></div>
            </div>
            <div class="sub" style="text-align:right;">
                <?= lang('Reports.report_date') ?>: <span class="nowrap"><?= date('Y-m-d H:i:s') ?></span>
            </div>
        </div>

        <div class="summary">
            <div class="card">
                <div class="label"><?= lang('Reports.total_expenses') ?></div>
                <div class="value"><?= (int)($grandCount ?? 0) ?></div>
            </div>
            <div class="card">
                <div class="label"><?= lang('Reports.total_amount') ?></div>
                <div class="value"><?= htmlspecialchars($currency) ?> <?= money_fmt($grandAmount ?? 0) ?></div>
            </div>
            <div class="card">
                <div class="label"><?= lang('Reports.total_taxes') ?></div>
                <div class="value"><?= htmlspecialchars($currency) ?> <?= money_fmt($grandTax ?? 0) ?></div>
            </div>
            <div class="card">
                <div class="label"><?= lang('Reports.grand_total') ?></div>
                <div class="value"><?= htmlspecialchars($currency) ?> <?= money_fmt((float)($grandAmount ?? 0) + (float)($grandTax ?? 0)) ?></div>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th><?= lang('Reports.category') ?></th>
                        <th class="num" style="width: 120px;"><?= lang('Reports.expense_count') ?></th>
                        <th class="num" style="width: 120px;"><?= lang('Reports.amount') ?></th>
                        <th class="num" style="width: 110px;"><?= lang('Reports.tax') ?></th>
                        <th class="num" style="width: 130px;"><?= lang('Reports.total') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="5" style="text-align:center;color:#6b7280;padding:14px;"><?= lang('Reports.no_expenses_period') ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $r): ?>
                            <?php
                            $amt = (float)($r['total_amount'] ?? 0);
                            $tax = (float)($r['total_tax'] ?? 0);
                            $total = $amt + $tax;
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($r['category_name'] ?? lang('Reports.uncategorized')) ?></td>
                                <td class="num"><?= (int)($r['expense_count'] ?? 0) ?></td>
                                <td class="num"><?= htmlspecialchars($currency) ?> <?= money_fmt($amt) ?></td>
                                <td class="num"><?= htmlspecialchars($currency) ?> <?= money_fmt($tax) ?></td>
                                <td class="num"><b><?= htmlspecialchars($currency) ?> <?= money_fmt($total) ?></b></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($rows)): ?>
                    <tfoot>
                        <tr>
                            <td class="num"><?= strtoupper(lang('Reports.totals')) ?></td>
                            <td class="num"><?= (int)($grandCount ?? 0) ?></td>
                            <td class="num"><?= htmlspecialchars($currency) ?> <?= money_fmt($grandAmount ?? 0) ?></td>
                            <td class="num"><?= htmlspecialchars($currency) ?> <?= money_fmt($grandTax ?? 0) ?></td>
                            <td class="num"><?= htmlspecialchars($currency) ?> <?= money_fmt((float)($grandAmount ?? 0) + (float)($grandTax ?? 0)) ?></td>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>

        <div class="actions no-print">
            <button class="btn" onclick="window.print()"><?= lang('Reports.print') ?></button>
            <button class="btn secondary" onclick="window.history.go(-1)"><?= lang('Reports.back') ?></button>
        </div>
    </div>
</body>

</html>