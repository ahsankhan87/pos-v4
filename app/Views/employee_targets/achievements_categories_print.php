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
        <p><?= lang('Reports.printed_on') ?? 'Printed on' ?>: <?= date('Y-m-d H:i') ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th><?= lang('Reports.s_no') ?></th>
                <th><?= lang('EmployeeTargets.employee') ?></th>
                <th><?= lang('EmployeeTargets.target_amount') ?></th>
                <th><?= lang('EmployeeTargets.achieved_amount') ?></th>
                <?php foreach ($categories as $category): ?>
                    <?php $categoryName = (string) ($category['name'] ?? lang('Reports.uncategorized')); ?>
                    <th title="<?= esc($categoryName, 'attr') ?>\"><?= esc($shortCategoryLabel($categoryName)) ?></th>
                <?php endforeach; ?>
                <th><?= lang('EmployeeTargets.achievement_percent') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rows)): ?>
                <tr>
                    <td colspan="<?= 5 + count($categories) ?>"><?= lang('EmployeeTargets.no_achievement_rows') ?></td>
                </tr>
            <?php else: ?>
                <?php foreach ($rows as $idx => $row): ?>
                    <tr>
                        <td><?= (int) $idx + 1 ?></td>
                        <td><?= esc($row['employee_name'] ?? '-') ?></td>
                        <td><?= esc($currency) . ' ' . number_format((float) ($row['target_amount'] ?? 0), 2) ?></td>
                        <td><?= esc($currency) . ' ' . number_format((float) ($row['achieved_amount'] ?? 0), 2) ?></td>
                        <?php foreach ($categories as $category): ?>
                            <?php $categoryId = (int) ($category['id'] ?? 0); ?>
                            <td><?= number_format((float) ($row['category_percents'][$categoryId] ?? 0), 2) ?>%</td>
                        <?php endforeach; ?>
                        <td><?= number_format((float) ($row['achievement_percent'] ?? 0), 2) ?>%</td>
                    </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td></td>
                    <td><?= lang('EmployeeTargets.totals') ?></td>
                    <td><?= esc($currency) . ' ' . number_format((float) ($totals['target_amount'] ?? 0), 2) ?></td>
                    <td><?= esc($currency) . ' ' . number_format((float) ($totals['achieved_amount'] ?? 0), 2) ?></td>
                    <?php foreach ($categories as $category): ?>
                        <?php $categoryId = (int) ($category['id'] ?? 0); ?>
                        <td><?= number_format((float) ($categoryTotalsPercent[$categoryId] ?? 0), 2) ?>%</td>
                    <?php endforeach; ?>
                    <td><?= number_format((float) ($totals['achievement_percent'] ?? 0), 2) ?>%</td>
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