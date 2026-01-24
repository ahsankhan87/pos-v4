<?php
$currencySymbol = session()->get('currency_symbol') ?: '₹';
$from = esc((string)($from ?? ''));
$to   = esc((string)($to ?? ''));
$type = esc((string)($type ?? ''));
$q    = esc((string)($q ?? ''));
$customer = $customer ?? null;
$logoExists = is_file(FCPATH . 'uploads/logo.png');
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Customer Ledger</title>
    <style>
        /* POS80 compact print - no template header/footer */
        @page {
            size: 80mm auto;
            margin: 2mm;
        }

        @media print {
            .no-print {
                display: none !important;
            }
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: #fff;
        }

        .wrap {
            width: 76mm;
            margin: 0;
            padding: 0;
            font-family: system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans";
            font-size: 10px;
            color: #111827;
        }

        .center {
            text-align: center;
        }

        .muted {
            color: #6b7280;
        }

        .bold {
            font-weight: 700;
        }

        .line {
            border-top: 1px dashed #111827;
            margin: 4px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 2px 0;
            font-size: 10px;
            line-height: 1.2;
            vertical-align: top;
        }

        th {
            text-align: left;
            color: #6b7280;
            font-weight: 600;
        }

        td.num {
            text-align: right;
        }

        .btn {
            display: inline-block;
            padding: 4px 8px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 12px;
            text-decoration: none;
            color: #111827;
            background: #f9fafb;
        }

        .btn-primary {
            border-color: #2563eb;
            background: #2563eb;
            color: #fff;
        }
    </style>
</head>

<body>
    <div class="wrap">
        <div class="center bold" style="font-size:14px; display:flex;align-items:center;justify-content:center;gap:6px;">
            <?php if ($logoExists): ?>
                <img src="<?= base_url('uploads/logo.png') ?>" alt="Logo" style="height:16px;">
            <?php endif; ?>
            <span>Customer Ledger</span>
        </div>
        <?php if ($customer): ?>
            <div class="center muted" style="margin-bottom:4px;">
                <?= esc($customer['name'] ?? 'Customer') ?>
                <?php if (!empty($customer['phone'])): ?> • <?= esc($customer['phone']) ?><?php endif; ?>
                    <?php if (!empty($customer['email'])): ?> • <?= esc($customer['email']) ?><?php endif; ?>
            </div>
        <?php endif; ?>
        <div class="center muted" style="margin-bottom:6px;">
            <?= $from ? 'From: ' . esc($from) . ' ' : '' ?><?= $to ? 'To: ' . esc($to) . ' ' : '' ?><?= $type ? 'Type: ' . esc($type) . ' ' : '' ?>
        </div>

        <div class="line"></div>
        <table>
            <tr>
                <th>Opening</th>
                <td class="num bold"><?= $currencySymbol . number_format((float)($openingBalance ?? 0), 2) ?></td>
            </tr>
        </table>
        <?php if (isset($outstanding) || isset($creditLimit)): ?>
            <div class="line"></div>
            <table>
                <tr>
                    <th>Outstanding</th>
                    <td class="num bold"><?= $currencySymbol . number_format((float)($outstanding ?? 0), 2) ?></td>
                </tr>
                <?php if (isset($creditLimit) && $creditLimit !== null): ?>
                    <tr>
                        <th>Limit / Avail</th>
                        <td class="num bold"><?= $currencySymbol . number_format((float)$creditLimit, 2) ?> / <?= isset($creditAvailable) ? ($currencySymbol . number_format((float)$creditAvailable, 2)) : '—' ?></td>
                    </tr>
                <?php endif; ?>
            </table>
        <?php endif; ?>
        <div class="line"></div>

        <table>
            <thead>
                <tr>
                    <th style="width:28%">Date</th>
                    <th style="width:22%">Ref</th>
                    <th style="width:25%">Type</th>
                    <th class="num" style="width:25%">Balance</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= esc($from ?: ($ledger[0]['date'] ?? '')) ?></td>
                    <td>—</td>
                    <td>Opening</td>
                    <td class="num bold"><?= $currencySymbol . number_format((float)($openingBalance ?? 0), 2) ?></td>
                </tr>
                <?php foreach (($ledger ?? []) as $entry): ?>
                    <tr>
                        <td><?= esc(date('Y-m-d', strtotime($entry['date'])) ?? '') ?></td>
                        <td><?= esc($entry['ref_no'] ?? '-') ?></td>
                        <td><?= esc(ucfirst((string)($entry['type'] ?? '-'))) ?></td>
                        <td class="num bold"><?= $currencySymbol . number_format((float)($entry['balance'] ?? 0), 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($ledger)): ?>
                    <tr>
                        <td colspan="4" class="center muted" style="padding:8px 0;">No transactions</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="line"></div>
        <table>
            <tr>
                <th>Closing</th>
                <td class="num bold"><?= $currencySymbol . number_format((float)($closingBalance ?? 0), 2) ?></td>
            </tr>
        </table>
        <div class="line"></div>
        <div class="center muted">Generated: <?= date('Y-m-d H:i') ?></div>
        <div class="center no-print" style="margin-top:6px;">
            <a href="<?= site_url('customers/ledger/' . ($customer['id'] ?? 0) . '?from=' . $from . '&to=' . $to . '&type=' . $type . '&q=' . $q) ?>" class="btn">Back</a>
            <button type="button" onclick="window.print()" class="btn btn-primary">Print</button>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                try {
                    window.print();
                } catch (e) {}
            }, 150);
        });
    </script>
</body>

</html>