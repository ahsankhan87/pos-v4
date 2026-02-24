<!DOCTYPE html>
<html lang="<?= esc(current_locale()) ?>" dir="<?= esc(locale_direction()) ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc(lang('Reports.inactive_customers_report')) ?></title>
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

            tbody td {
                padding: 3px 4px !important;
                border: 1px solid #ddd !important;
                font-size: 9px !important;
            }

            thead {
                display: table-header-group;
            }

            tfoot {
                display: table-footer-group;
            }

            tr {
                page-break-inside: avoid;
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

            .header {
                display: flex !important;
                visibility: visible !important;
            }

            h2,
            .sub {
                visibility: visible !important;
            }
        }
    </style>
</head>

<body>
    <?php
    $days = (int)($days ?? 30);
    $cutoffDate = $cutoffDate ?? '';
    $area = $area ?? '';
    $customers = $customers ?? [];
    $totalCustomers = is_array($customers) ? count($customers) : 0;
    ?>

    <div class="page">
        <div class="header">
            <div>
                <h2><?= lang('Reports.inactive_customers_report') ?></h2>
                <div class="sub"><?= lang('Reports.customers_not_purchased_last') ?>: <span class="nowrap"><?= htmlspecialchars((string)$days) ?></span> <?= lang('Reports.days') ?></div>
                <?php if (!empty($area)): ?>
                    <div class="sub"><?= lang('Reports.area') ?>: <span class="nowrap"><?= htmlspecialchars((string)$area) ?></span></div>
                <?php endif; ?>
                <?php if (!empty($cutoffDate)): ?>
                    <div class="sub"><?= lang('Reports.cutoff_date') ?>: <span class="nowrap"><?= htmlspecialchars((string)$cutoffDate) ?></span></div>
                <?php endif; ?>
            </div>
            <div class="sub" style="text-align:right;">
                <?= lang('Reports.report_date') ?>: <span class="nowrap"><?= date('Y-m-d H:i:s') ?></span>
            </div>
        </div>

        <div class="summary">
            <div class="card">
                <div class="label"><?= lang('Reports.total_inactive_customers') ?></div>
                <div class="value"><?= (int)$totalCustomers ?></div>
            </div>
            <div class="card">
                <div class="label"><?= lang('Reports.days_threshold') ?></div>
                <div class="value"><?= (int)$days ?> <?= lang('Reports.days') ?></div>
            </div>
            <div class="card">
                <div class="label"><?= lang('Reports.cutoff_date') ?></div>
                <div class="value"><?= htmlspecialchars($cutoffDate !== '' ? (string)$cutoffDate : '-') ?></div>
            </div>
            <div class="card">
                <div class="label"><?= lang('Reports.generated') ?></div>
                <div class="value"><?= date('Y-m-d') ?></div>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th><?= lang('Reports.customer_name') ?></th>
                        <th style="width: 180px;"><?= lang('Reports.email') ?></th>
                        <th style="width: 140px;"><?= lang('Reports.phone') ?></th>
                        <th style="width: 130px;"><?= lang('Reports.last_purchase') ?></th>
                        <th class="num" style="width: 120px;"><?= lang('Reports.days_inactive') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($customers)): ?>
                        <tr>
                            <td colspan="5" style="text-align:center;color:#6b7280;padding:14px;"><?= lang('Reports.no_inactive_customers') ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td><?= htmlspecialchars((string)($customer['name'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string)($customer['email'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string)($customer['phone'] ?? '')) ?></td>
                                <td class="nowrap"><?= htmlspecialchars((string)($customer['last_purchase'] ?? '')) ?></td>
                                <td class="num">
                                    <?php
                                    $di = $customer['days_inactive'] ?? '';
                                    if (is_numeric($di)) {
                                        echo htmlspecialchars((string)$di) . ' ' . lang('Reports.days');
                                    } else {
                                        echo htmlspecialchars((string)$di);
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="actions no-print">
            <button class="btn" onclick="window.print()"><?= lang('Reports.print') ?></button>
            <button class="btn secondary" onclick="window.history.go(-1)"><?= lang('Reports.back') ?></button>
        </div>
    </div>

    <script>
        // Delay printing slightly so the header renders reliably
        // before print preview captures the page.
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 300);
        });
    </script>
</body>

</html>