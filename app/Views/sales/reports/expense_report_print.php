<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Report</title>

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
            grid-template-columns: repeat(4, minmax(0, 1fr));
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

            html,
            body {
                margin: 0 !important;
            }

            header,
            footer,
            nav,
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

            .print-container {
                box-shadow: none !important;
                padding: 0 !important;
            }

            .print-container table {
                width: 100%;
                border-collapse: collapse !important;
            }

            .print-container th,
            .print-container td {
                padding: 3px 4px !important;
                border: 1px solid #ddd !important;
                font-size: 9px !important;
            }

            h2 {
                font-size: 13px !important;
                margin: 0 0 3px 0 !important;
            }

            .summary-info {
                font-size: 9px !important;
                margin: 3px 0 !important;
            }

            .summary {
                grid-template-columns: repeat(4, minmax(0, 1fr));
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
    $totalExpenses = count($expenses);
    function money_fmt($v)
    {
        return number_format((float)$v, 2);
    }
    ?>

    <div class="page print-container">
        <div class="header">
            <div>
                <h2>Expense Report</h2>
                <div class="sub">Period: <span class="nowrap"><?= htmlspecialchars($from) ?></span> to <span class="nowrap"><?= htmlspecialchars($to) ?></span></div>
            </div>
            <div class="sub" style="text-align:right;">
                Report Date: <span class="nowrap"><?= date('Y-m-d H:i:s') ?></span>
            </div>
        </div>

        <div class="summary">
            <div class="card">
                <div class="label">Total Expenses</div>
                <div class="value"><?= htmlspecialchars((string)$totalExpenses) ?></div>
            </div>
            <div class="card">
                <div class="label">Total Amount</div>
                <div class="value"><?= htmlspecialchars($currency) ?> <?= money_fmt($totalAmount ?? 0) ?></div>
            </div>
            <div class="card">
                <div class="label">Total Tax</div>
                <div class="value"><?= htmlspecialchars($currency) ?> <?= money_fmt($totalTax ?? 0) ?></div>
            </div>
            <div class="card">
                <div class="label">Grand Total</div>
                <div class="value"><?= htmlspecialchars($currency) ?> <?= money_fmt(((float)($totalAmount ?? 0)) + ((float)($totalTax ?? 0))) ?></div>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width: 90px;">Date</th>
                        <th style="width: 120px;">Category</th>
                        <th style="width: 120px;">Vendor</th>
                        <th>Description</th>
                        <th class="num" style="width: 90px;">Amount</th>
                        <th class="num" style="width: 80px;">Tax</th>
                        <th class="num" style="width: 95px;">Total</th>
                        <th style="width: 160px;">Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($expenses)): ?>
                        <tr>
                            <td colspan="8" style="text-align:center;color:#6b7280;padding:14px;">No expenses found for the selected period.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($expenses as $expense): ?>
                            <?php $total = ((float)($expense['amount'] ?? 0)) + ((float)($expense['tax'] ?? 0)); ?>
                            <tr>
                                <td class="nowrap"><?= htmlspecialchars($expense['date'] ?? '') ?></td>
                                <td><?= htmlspecialchars($expense['category_name'] ?? 'Uncategorized') ?></td>
                                <td><?= htmlspecialchars($expense['vendor'] ?? '') ?></td>
                                <td><?= htmlspecialchars($expense['description'] ?? '') ?></td>
                                <td class="num"><?= htmlspecialchars($currency) ?> <?= money_fmt($expense['amount'] ?? 0) ?></td>
                                <td class="num"><?= htmlspecialchars($currency) ?> <?= money_fmt($expense['tax'] ?? 0) ?></td>
                                <td class="num"><b><?= htmlspecialchars($currency) ?> <?= money_fmt($total) ?></b></td>
                                <td><?= htmlspecialchars($expense['notes'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($expenses)): ?>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="num">TOTALS</td>
                            <td class="num"><?= htmlspecialchars($currency) ?> <?= money_fmt($totalAmount ?? 0) ?></td>
                            <td class="num"><?= htmlspecialchars($currency) ?> <?= money_fmt($totalTax ?? 0) ?></td>
                            <td class="num"><?= htmlspecialchars($currency) ?> <?= money_fmt(((float)($totalAmount ?? 0)) + ((float)($totalTax ?? 0))) ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>

        <div class="actions no-print">
            <button class="btn" onclick="window.print()">Print</button>
            <button class="btn secondary" onclick="window.history.go(-1)">Back</button>
        </div>
    </div>
</body>

</html>