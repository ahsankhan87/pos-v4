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
            text-align: center;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        td:nth-child(2),
        th:nth-child(2) {
            text-align: left;
        }

        .total-row {
            background-color: #f0f0f0;
            font-weight: bold;
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
    <?php
    $currency = (string) (session('currency_symbol') ?? '');
    $shortCategoryLabel = static function ($label, int $max = 10): string {
        $value = trim((string) $label);
        if ($value === '') {
            return (string) lang('Reports.uncategorized');
        }

        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            if (mb_strlen($value) <= $max) {
                return $value;
            }

            return rtrim(mb_substr($value, 0, $max - 1)) . '...';
        }

        if (strlen($value) <= $max) {
            return $value;
        }

        return rtrim(substr($value, 0, $max - 1)) . '...';
    };
    ?>

    <div class="header">
        <?php if (! empty($storeName)): ?>
            <h2><?= esc($storeName) ?></h2>
        <?php endif; ?>
        <h2><?= esc($title) ?></h2>
        <p><?= lang('EmployeeTargets.target_month') ?>: <?= esc($selectedMonth) ?></p>
        <p><?= lang('EmployeeTargets.category_targets_hint') ?></p>
        <p><?= lang('Reports.printed_on') ?? 'Printed on' ?>: <?= date('Y-m-d H:i') ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th><?= lang('EmployeeTargets.category') ?></th>
                <th><?= lang('EmployeeTargets.target_amount') ?></th>
                <th><?= lang('EmployeeTargets.achieved_amount') ?></th>
                <th><?= lang('EmployeeTargets.achievement_percent') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rows)): ?>
                <tr>
                    <td colspan="4"><?= lang('EmployeeTargets.no_achievement_rows') ?></td>
                </tr>
            <?php elseif ((int) ($selectedEmployeeId ?? 0) === 0): ?>
                <!-- All Employees Section -->
                <tr style="background:#dbeafe;border-left:4px solid #2563eb;">
                    <td colspan="4" style="padding:0.75rem;font-weight:bold;">
                        <div style="display:flex;align-items:center;gap:0.5rem;justify-content:space-between;">
                            <div style="display:flex;align-items:center;gap:0.5rem;">
                                <span style="display:inline-block;padding:2px 8px;border-radius:9999px;background:#2563eb;color:white;font-size:10px;font-weight:bold;"><?= lang('EmployeeTargets.all_employees') ?></span>
                                <span style="font-size:11px;color:#1e40af;"><?= lang('Reports.showing') ?> <?= number_format(count($rows)) ?> <?= lang('Reports.employees') ?></span>
                            </div>
                            <span style="font-size:10px;background:#dbeafe;color:#1e40af;padding:2px 6px;border-radius:4px;">Overall: <?= number_format((float) ($totals['achievement_percent'] ?? 0), 1) ?>%</span>
                        </div>
                    </td>
                </tr>
                <!-- Category rows for all employees combined -->
                <?php foreach ($categories as $category): ?>
                    <?php
                    $categoryId = (int) ($category['id'] ?? 0);
                    $categoryName = (string) ($category['name'] ?? lang('Reports.uncategorized'));
                    $categoryTarget = 0.0;
                    $categoryAchieved = 0.0;

                    // Sum up all category totals across all employees
                    foreach ($rows as $row) {
                        $breakdown = $row['category_breakdown'][$categoryId] ?? [];
                        $categoryTarget += (float) ($breakdown['target'] ?? 0);
                        $categoryAchieved += (float) ($breakdown['achieved'] ?? 0);
                    }

                    $categoryPercent = $categoryTarget > 0 ? (($categoryAchieved / $categoryTarget) * 100) : 0;
                    ?>
                    <tr>
                        <td><?= esc($categoryName) ?></td>
                        <td style="text-align:right;"><?= esc($currency) . ' ' . number_format($categoryTarget, 2) ?></td>
                        <td style="text-align:right;"><?= esc($currency) . ' ' . number_format($categoryAchieved, 2) ?></td>
                        <td style="text-align:right;font-weight:bold;color:<?= $categoryPercent >= 100 ? '#15803d' : '#b45309' ?>;"><?= number_format($categoryPercent, 2) ?>%</td>
                    </tr>
                <?php endforeach; ?>
                <!-- Totals row for all employees -->
                <tr style="background:#dbeafe;font-weight:bold;border-bottom:2px solid #9ca3af;">
                    <td style="color:#1e40af;"><?= lang('EmployeeTargets.total') ?></td>
                    <td style="text-align:right;color:#1e40af;"><?= esc($currency) . ' ' . number_format((float) ($totals['target_amount'] ?? 0), 2) ?></td>
                    <td style="text-align:right;color:#1e40af;"><?= esc($currency) . ' ' . number_format((float) ($totals['achieved_amount'] ?? 0), 2) ?></td>
                    <td style="text-align:right;color:<?= ((float) ($totals['achievement_percent'] ?? 0)) >= 100 ? '#15803d' : '#b45309' ?>;"><?= number_format((float) ($totals['achievement_percent'] ?? 0), 2) ?>%</td>
                </tr>
            <?php else: ?>
                <!-- Individual Employee Sections -->
                <?php foreach ($rows as $idx => $row): ?>
                    <?php $achievementPercent = (float) ($row['achievement_percent'] ?? 0); ?>
                    <?php $categoryCount = count($categories); ?>
                    <!-- Employee Header Row -->
                    <tr style="background:#dbeafe;border-left:4px solid #2563eb;">
                        <td colspan="4" style="padding:0.75rem;font-weight:bold;">
                            <div style="display:flex;align-items:center;gap:0.5rem;justify-content:space-between;">
                                <div style="display:flex;align-items:center;gap:0.5rem;">
                                    <span style="display:inline-block;width:20px;height:20px;background:#2563eb;color:white;text-align:center;line-height:20px;border-radius:50%;font-size:10px;font-weight:bold;"><?= (int) $idx + 1 ?></span>
                                    <span style="font-size:12px;color:#1e40af;"><?= esc($row['employee_name'] ?? '-') ?></span>
                                </div>
                                <span style="font-size:10px;background:<?= $achievementPercent >= 100 ? '#dcfce7' : '#fef3c7' ?>;color:<?= $achievementPercent >= 100 ? '#15803d' : '#b45309' ?>;padding:2px 6px;border-radius:4px;">Overall: <?= number_format($achievementPercent, 1) ?>%</span>
                            </div>
                        </td>
                    </tr>
                    <!-- Category Rows -->
                    <?php foreach ($categories as $catIdx => $category): ?>
                        <?php
                        $categoryId = (int) ($category['id'] ?? 0);
                        $breakdown = $row['category_breakdown'][$categoryId] ?? [];
                        $categoryTarget = (float) ($breakdown['target'] ?? 0);
                        $categoryAchieved = (float) ($breakdown['achieved'] ?? 0);
                        $categoryPercent = (float) ($breakdown['percent'] ?? 0);
                        $categoryName = (string) ($category['name'] ?? lang('Reports.uncategorized'));
                        ?>
                        <tr>
                            <td><?= esc($categoryName) ?></td>
                            <td style="text-align:right;"><?= esc($currency) . ' ' . number_format($categoryTarget, 2) ?></td>
                            <td style="text-align:right;"><?= esc($currency) . ' ' . number_format($categoryAchieved, 2) ?></td>
                            <td style="text-align:right;font-weight:bold;color:<?= $categoryPercent >= 100 ? '#15803d' : '#b45309' ?>;"><?= number_format($categoryPercent, 2) ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                    <!-- Totals row for each employee -->
                    <tr style="background:#dbeafe;font-weight:bold;border-bottom:2px solid #9ca3af;">
                        <td style="color:#1e40af;"><?= lang('EmployeeTargets.total') ?></td>
                        <td style="text-align:right;color:#1e40af;"><?= esc($currency) . ' ' . number_format((float) ($row['target_amount'] ?? 0), 2) ?></td>
                        <td style="text-align:right;color:#1e40af;"><?= esc($currency) . ' ' . number_format((float) ($row['achieved_amount'] ?? 0), 2) ?></td>
                        <td style="text-align:right;color:<?= $achievementPercent >= 100 ? '#15803d' : '#b45309' ?>;"><?= number_format($achievementPercent, 2) ?>%</td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="no-print">
        <button onclick="window.print()" style="padding:6px 18px;font-size:12px;cursor:pointer;">&#128438; <?= lang('Reports.print') ?? 'Print' ?></button>
        <button onclick="window.close()" style="padding:6px 18px;font-size:12px;cursor:pointer;margin-left:8px;"><?= lang('App.close') ?? 'Close' ?></button>
    </div>
</body>

</html>