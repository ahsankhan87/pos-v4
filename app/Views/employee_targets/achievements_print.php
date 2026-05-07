<!DOCTYPE html>
<html lang="<?= esc(current_locale()) ?>" dir="<?= esc(locale_direction()) ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> - <?= esc($selectedMonth) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 10mm;
            color: #000;
        }

        .header {
            text-align: center;
            margin-bottom: 12px;
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
        }

        .header h2 {
            margin: 0 0 2px 0;
            font-size: 16px;
        }

        .header p {
            margin: 2px 0;
            font-size: 10px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 6px;
            margin-bottom: 12px;
        }

        .summary-box {
            border: 1px solid #ccc;
            padding: 6px 8px;
            text-align: center;
        }

        .summary-box .label {
            font-size: 9px;
            color: #555;
            margin-bottom: 2px;
        }

        .summary-box .value {
            font-size: 13px;
            font-weight: bold;
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
            font-size: 10px;
        }

        thead th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total-row {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .badge {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }

        .badge-gold {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-silver {
            background: #f1f5f9;
            color: #475569;
        }

        .badge-bronze {
            background: #ffedd5;
            color: #9a3412;
        }

        .badge-none {
            background: #f3f4f6;
            color: #374151;
        }

        .status-achieved {
            background: #dcfce7;
            color: #15803d;
        }

        .status-pending {
            background: #fef9c3;
            color: #a16207;
        }

        .no-print {
            margin-top: 14px;
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
    <?php $currency = session('currency_symbol') ?? ''; ?>

    <div class="header">
        <?php if (! empty($storeName)): ?>
            <h2><?= esc($storeName) ?></h2>
        <?php endif; ?>
        <h2><?= esc($title) ?></h2>
        <p><?= lang('EmployeeTargets.target_month') ?>: <?= esc($selectedMonth) ?></p>
        <p><?= lang('Reports.printed_on') ?? 'Printed on' ?>: <?= date('Y-m-d H:i') ?></p>
    </div>

    <div class="summary-grid">
        <div class="summary-box">
            <div class="label"><?= lang('EmployeeTargets.total_targets') ?></div>
            <div class="value"><?= $currency . number_format((float) ($totals['target_amount'] ?? 0), 2) ?></div>
        </div>
        <div class="summary-box">
            <div class="label"><?= lang('EmployeeTargets.total_achieved') ?></div>
            <div class="value"><?= $currency . number_format((float) ($totals['achieved_amount'] ?? 0), 2) ?></div>
        </div>
        <div class="summary-box">
            <?php $variance = (float) ($totals['variance_amount'] ?? 0); ?>
            <div class="label"><?= lang('EmployeeTargets.total_variance') ?></div>
            <div class="value"><?= $currency . number_format($variance, 2) ?></div>
        </div>
        <div class="summary-box">
            <div class="label"><?= lang('EmployeeTargets.overall_achievement') ?></div>
            <div class="value"><?= number_format((float) ($totals['achievement_percent'] ?? 0), 2) ?>%</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th><?= lang('EmployeeTargets.employee') ?></th>
                <th class="text-right"><?= lang('EmployeeTargets.target_amount') ?></th>
                <th class="text-right"><?= lang('EmployeeTargets.achieved_amount') ?></th>
                <th class="text-right"><?= lang('EmployeeTargets.achievement_percent') ?></th>
                <th class="text-right"><?= lang('EmployeeTargets.variance') ?></th>
                <th class="text-center"><?= lang('EmployeeTargets.badge_tier') ?></th>
                <th class="text-center"><?= lang('EmployeeTargets.status') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rows)): ?>
                <tr>
                    <td colspan="8" class="text-center"><?= lang('EmployeeTargets.no_achievement_rows') ?></td>
                </tr>
            <?php else: ?>
                <?php foreach ($rows as $i => $row):
                    $tier = (string) ($row['tier'] ?? lang('EmployeeTargets.tier_none'));
                    $tierClass = 'badge-none';
                    if ($tier === lang('EmployeeTargets.tier_gold')) {
                        $tierClass = 'badge-gold';
                    } elseif ($tier === lang('EmployeeTargets.tier_silver')) {
                        $tierClass = 'badge-silver';
                    } elseif ($tier === lang('EmployeeTargets.tier_bronze')) {
                        $tierClass = 'badge-bronze';
                    }
                    $statusClass = $row['status'] === lang('EmployeeTargets.status_achieved') ? 'status-achieved' : 'status-pending';
                    $varRow = (float) $row['variance_amount'];
                ?>
                    <tr>
                        <td class="text-center"><?= $i + 1 ?></td>
                        <td><?= esc($row['employee_name']) ?></td>
                        <td class="text-right"><?= $currency . number_format((float) $row['target_amount'], 2) ?></td>
                        <td class="text-right"><?= $currency . number_format((float) $row['achieved_amount'], 2) ?></td>
                        <td class="text-right"><?= number_format((float) $row['achievement_percent'], 2) ?>%</td>
                        <td class="text-right"><?= $currency . number_format($varRow, 2) ?></td>
                        <td class="text-center"><span class="badge <?= $tierClass ?>"><?= esc($tier) ?></span></td>
                        <td class="text-center"><span class="badge <?= $statusClass ?>"><?= esc($row['status']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="2"><?= lang('EmployeeTargets.totals') ?? 'Totals' ?> (<?= (int) ($totals['employee_count'] ?? 0) ?> <?= lang('EmployeeTargets.employees') ?? 'employees' ?>)</td>
                    <td class="text-right"><?= $currency . number_format((float) ($totals['target_amount'] ?? 0), 2) ?></td>
                    <td class="text-right"><?= $currency . number_format((float) ($totals['achieved_amount'] ?? 0), 2) ?></td>
                    <td class="text-right"><?= number_format((float) ($totals['achievement_percent'] ?? 0), 2) ?>%</td>
                    <td class="text-right"><?= $currency . number_format((float) ($totals['variance_amount'] ?? 0), 2) ?></td>
                    <td colspan="2"></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="no-print">
        <button onclick="window.print()" style="padding:6px 18px;font-size:12px;cursor:pointer;">&#128438; <?= lang('Reports.print') ?? 'Print' ?></button>
        <button onclick="window.close()" style="padding:6px 18px;font-size:12px;cursor:pointer;margin-left:8px;"><?= lang('App.close') ?? 'Close' ?></button>
    </div>
</body>

</html>