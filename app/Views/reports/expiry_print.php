<!DOCTYPE html>
<html lang="<?= esc(current_locale()) ?>" dir="<?= esc(locale_direction()) ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? (lang('Reports.expiry_report_title') . ' - ' . lang('Reports.print'))) ?></title>
    <style>
        @page {
            margin: 0.8cm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.3;
            margin: 0.8cm;
        }

        h2 {
            margin: 0 0 4px 0;
        }

        p {
            margin: 0 0 3px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 2px 4px;
            font-size: 11px;
        }

        thead th {
            background: #f0f0f0;
        }

        .text-right {
            text-align: right;
        }

        .badge {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: bold;
        }

        .badge-expired {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-expiring {
            background: #fef3c7;
            color: #92400e;
        }

        .no-print {
            margin-top: 8px;
            text-align: center;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                margin: 0;
            }
        }
    </style>
</head>

<body>
    <?php
    $currency = session()->get('currency_symbol') ?? '$';
    $allRows = array_merge(
        array_map(function ($r) {
            $r['_status'] = 'expired';
            return $r;
        }, $expired ?? []),
        array_map(function ($r) {
            $r['_status'] = 'expiring';
            return $r;
        }, $expiring ?? [])
    );
    ?>

    <h2><?= lang('Reports.expiry_report_title') ?></h2>
    <p><?= lang('Reports.store') ?>: <?= esc(session()->get('store_name') ?? '') ?></p>
    <p><?= lang('Reports.generated') ?>: <?= esc($generated) ?></p>
    <p><?= lang('Reports.expiring_within_days', ['days' => (int) $soonDays]) ?></p>

    <table>
        <thead>
            <tr>
                <th style="text-align:left;">#</th>
                <th style="text-align:left;"><?= lang('Reports.product') ?></th>
                <th style="text-align:left;"><?= lang('Reports.code') ?></th>
                <th style="text-align:left;"><?= lang('Reports.category') ?></th>
                <th class="text-right"><?= lang('Reports.qty') ?></th>
                <th style="text-align:left;"><?= lang('Reports.expiry_date') ?></th>
                <th style="text-align:left;"><?= lang('Reports.status') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php $sr = 0; ?>
            <?php foreach ($allRows as $row): ?>
                <?php
                $sr++;
                $isExpired = ($row['_status'] ?? '') === 'expired';
                $daysLeft = (int) ($row['days_left'] ?? 0);
                ?>
                <tr>
                    <td style="text-align:left;"><?= $sr ?></td>
                    <td style="text-align:left;"><?= esc($row['name'] ?? '') ?></td>
                    <td style="text-align:left;"><?= esc($row['code'] ?? '') ?></td>
                    <td style="text-align:left;"><?= esc($row['category_name'] ?? '') ?></td>
                    <td class="text-right"><?= number_format((float) ($row['quantity'] ?? 0), 0) ?></td>
                    <td style="text-align:left;"><?= esc($row['expiry_date'] ?? '') ?></td>
                    <td style="text-align:left;">
                        <?php if ($isExpired): ?>
                            <span class="badge badge-expired"><?= lang('Reports.expired') ?></span>
                            <?= lang('Reports.expired_days_ago', ['n' => abs($daysLeft)]) ?>
                        <?php else: ?>
                            <span class="badge badge-expiring"><?= lang('Reports.expiring') ?></span>
                            <?= lang('Reports.days_left', ['n' => $daysLeft]) ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if ($sr === 0): ?>
                <tr>
                    <td colspan="7" style="text-align:left;"><?= lang('Reports.no_expiry_products') ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <div class="no-print">
        <button onclick="window.print()"><?= lang('Reports.print') ?></button>
        <button onclick="window.close()"><?= lang('Reports.close') ?></button>
    </div>
</body>

</html>
