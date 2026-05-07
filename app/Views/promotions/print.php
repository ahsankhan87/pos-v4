<!DOCTYPE html>
<html lang="<?= esc(current_locale()) ?>" dir="<?= esc(locale_direction(current_locale())) ?>">

<head>
    <?php helper('locale'); ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? lang('Promotions.print_title')) ?></title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
            margin: 10mm;
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

        .badge-active {
            background: #dcfce7;
            color: #15803d;
        }

        .badge-paused {
            background: #fef9c3;
            color: #a16207;
        }

        .trigger-list {
            margin: 0;
            padding-left: 14px;
        }

        .trigger-list li {
            margin-bottom: 2px;
        }

        .no-print {
            margin-bottom: 14px;
            text-align: center;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                margin: 5mm;
            }
        }
    </style>
</head>

<body>

    <div class="no-print">
        <a href="<?= site_url('promotions') ?>" style="padding:6px 18px;font-size:12px;cursor:pointer;text-decoration:none;background:#6b7280;color:#fff;border-radius:4px;margin-right:8px;">&#8592; <?= lang('Promotions.back_to_list') ?></a>
        <button onclick="window.print()" style="padding:6px 18px;font-size:12px;cursor:pointer;">&#128438; <?= lang('Promotions.print') ?></button>
    </div>

    <div class="header">
        <?php if (!empty($storeName)): ?>
            <h2><?= esc($storeName) ?></h2>
        <?php endif; ?>
        <h2><?= esc($title ?? lang('Promotions.print_title')) ?></h2>
        <p><?= lang('Promotions.printed_at') ?>: <?= esc($printed_at ?? date('Y-m-d H:i')) ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:3%">#</th>
                <th style="width:19%"><?= lang('Promotions.name') ?></th>
                <th style="width:28%"><?= lang('Promotions.trigger_products') ?></th>
                <th style="width:20%"><?= lang('Promotions.gift_product') ?></th>
                <th style="width:8%" class="text-center"><?= lang('Promotions.priority') ?></th>
                <th style="width:9%" class="text-center"><?= lang('Promotions.status') ?></th>
                <th style="width:13%"><?= lang('Promotions.date_range') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($promotions)): ?>
                <tr>
                    <td colspan="7" class="text-center" style="padding:16px; color:#888;">
                        <?= lang('Promotions.empty') ?>
                    </td>
                </tr>
            <?php endif; ?>

            <?php foreach ($promotions as $i => $promo): ?>
                <?php
                $status       = strtolower((string) ($promo['status'] ?? 'active'));
                $triggerNames = $promo['trigger_product_names'] ?? [];
                $triggerQtys  = $promo['trigger_qty_list']      ?? [];
                $giftName     = $promo['gift_product_name'] ?? '-';
                $giftQty      = (float) ($promo['gift_qty'] ?? 0);
                $maxApps      = $promo['max_applications_per_invoice'] ?? null;
                $autoApply    = !empty($promo['auto_apply']);
                $startDate    = $promo['start_date'] ?? '-';
                $endDate      = $promo['end_date']   ?? '-';
                ?>
                <tr>
                    <td class="text-center"><?= $i + 1 ?></td>
                    <td>
                        <strong><?= esc($promo['name'] ?? '') ?></strong>
                        <div style="font-size:9px; color:#555; margin-top:2px;">
                            <?= $autoApply ? lang('Promotions.auto_apply_enabled') : lang('Promotions.auto_apply_disabled') ?>
                        </div>
                    </td>
                    <td>
                        <?php if (!empty($triggerNames)): ?>
                            <ul class="trigger-list">
                                <?php foreach ($triggerNames as $j => $tname): ?>
                                    <li>
                                        <?= esc($tname) ?>
                                        <?php if (isset($triggerQtys[$j]) && $triggerQtys[$j] > 0): ?>
                                            &times; <?= esc(rtrim(rtrim(number_format($triggerQtys[$j], 2, '.', ''), '0'), '.')) ?>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= esc($giftName) ?>
                        <?php if ($giftQty > 0): ?>
                            &times; <?= esc(rtrim(rtrim(number_format($giftQty, 2, '.', ''), '0'), '.')) ?>
                        <?php endif; ?>
                        <?php if ($maxApps): ?>
                            <div style="font-size:9px; color:#777; margin-top:2px;">
                                <?= lang('Promotions.max_applications') ?>: <?= (int) $maxApps ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="text-center"><?= (int) ($promo['priority'] ?? 100) ?></td>
                    <td class="text-center">
                        <span class="badge <?= $status === 'active' ? 'badge-active' : 'badge-paused' ?>">
                            <?= esc(ucfirst($status)) ?>
                        </span>
                    </td>
                    <td style="font-size:10px;">
                        <?= esc($startDate) ?><br>
                        <span style="color:#777;"><?= lang('Promotions.to') ?></span>
                        <?= esc($endDate) ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            <tr class="total-row">
                <td colspan="7">
                    <?= lang('Promotions.total_promotions') ?>: <?= count($promotions) ?>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="no-print">
        <button onclick="window.print()" style="padding:6px 18px;font-size:12px;cursor:pointer;">&#128438; <?= lang('Promotions.print') ?></button>
        <button onclick="window.close()" style="padding:6px 18px;font-size:12px;cursor:pointer;margin-left:8px;"><?= lang('App.close') ?? 'Close' ?></button>
    </div>

</body>

</html>