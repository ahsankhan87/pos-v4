<?php
$currencySymbol = session()->get('currency_symbol') ?: '₹';
$from = esc((string)($from ?? ''));
$to   = esc((string)($to ?? ''));
$supplier = $supplier ?? null;
$logoExists = is_file(FCPATH . 'uploads/logo.png');

function _sl_fmt_date($dateStr)
{
    if (!$dateStr) return '';
    $ts = strtotime((string)$dateStr);
    if (!$ts) return esc((string)$dateStr);
    return date('Y-m-d', $ts);
}

function _sl_money($currencySymbol, $amount)
{
    return $currencySymbol . number_format((float)$amount, 2);
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= lang('SupplierLedger.supplier_ledger') ?></title>
    <style>
        /* POS80 compact print */
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

        .desc {
            padding: 0 0 2px 0;
        }
    </style>
</head>

<body>
    <div class="wrap">
        <div class="center bold" style="font-size:13px; display:flex; align-items:center; justify-content:center; gap:6px;">
            <?php if ($logoExists): ?>
                <img src="<?= base_url('uploads/logo.png') ?>" alt="Logo" style="height:16px;">
            <?php endif; ?>
            <span><?= lang('SupplierLedger.supplier_ledger') ?></span>
        </div>

        <?php if ($supplier): ?>
            <div class="center muted" style="margin-top:2px; margin-bottom:4px;">
                <?= esc($supplier['name'] ?? lang('SupplierLedger.supplier_name')) ?>
                <?php if (!empty($supplier['phone'])): ?> • <?= esc($supplier['phone']) ?><?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="center muted" style="margin-bottom:4px;">
            <?= $from ? lang('SupplierLedger.from_date') . ': ' . $from . ' ' : '' ?><?= $to ? lang('SupplierLedger.to_date') . ': ' . $to : '' ?>
        </div>

        <div class="line"></div>

        <table>
            <tr>
                <th><?= lang('SupplierLedger.opening') ?></th>
                <td class="num bold"><?= _sl_money($currencySymbol, $openingBalance ?? 0) ?></td>
            </tr>
        </table>

        <div class="line"></div>

        <table>
            <thead>
                <tr>
                    <th style="width:28%"><?= lang('SupplierLedger.date') ?></th>
                    <th style="width:18%"><?= lang('SupplierLedger.ref') ?></th>
                    <th class="num" style="width:24%"><?= lang('SupplierLedger.amt') ?></th>
                    <th class="num" style="width:30%"><?= lang('SupplierLedger.bal') ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= esc($from ?: _sl_fmt_date(($transactions[0]['date'] ?? ''))) ?></td>
                    <td>—</td>
                    <td class="num">—</td>
                    <td class="num bold"><?= _sl_money($currencySymbol, $openingBalance ?? 0) ?></td>
                </tr>

                <?php if (!empty($transactions)): ?>
                    <?php foreach (($transactions ?? []) as $t): ?>
                        <?php
                        $credit = (float)($t['credit'] ?? 0);
                        $debit = (float)($t['debit'] ?? 0);
                        $signedAmt = 0.0;
                        $label = 'ADJ';
                        if ($credit > 0) {
                            $signedAmt = $credit;
                            $label = 'PO';
                        } elseif ($debit > 0) {
                            $signedAmt = -$debit;
                            $label = 'PAY';
                        }
                        $ref = $t['ref_no'] ?? '';
                        if (!$ref) {
                            $purchaseId = $t['purchase_id'] ?? null;
                            $ref = $purchaseId ? ('PO-' . $purchaseId) : '-';
                        }
                        $bal = (float)($t['running_balance'] ?? ($t['balance'] ?? 0));
                        ?>
                        <tr>
                            <td><?= esc(_sl_fmt_date($t['date'] ?? '')) ?></td>
                            <td><?= esc((string)$ref) ?></td>
                            <td class="num bold"><?= esc($label) ?> <?= $signedAmt >= 0 ? '+' : '' ?><?= _sl_money($currencySymbol, $signedAmt) ?></td>
                            <td class="num bold"><?= _sl_money($currencySymbol, $bal) ?></td>
                        </tr>
                        <!-- <?php if (!empty($t['description'])): ?>
                            <tr>
                                <td colspan="4" class="muted desc"><?= esc((string)$t['description']) ?></td>
                            </tr>
                        <?php endif; ?> -->
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="center muted" style="padding:8px 0;"><?= lang('SupplierLedger.no_transactions') ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="line"></div>

        <table>
            <tr>
                <th><?= lang('SupplierLedger.totals') ?></th>
                <td class="num">
                    <span class="muted"><?= lang('SupplierLedger.debit_dr') ?></span> <span class="bold"><?= _sl_money($currencySymbol, $totalDebit ?? 0) ?></span>
                    <span class="muted"> <?= lang('SupplierLedger.credit_cr') ?></span> <span class="bold"><?= _sl_money($currencySymbol, $totalCredit ?? 0) ?></span>
                </td>
            </tr>
            <tr>
                <th><?= lang('SupplierLedger.closing') ?></th>
                <td class="num bold"><?= _sl_money($currencySymbol, $closingBalance ?? 0) ?></td>
            </tr>
        </table>

        <div class="line"></div>
        <div class="center muted"><?= lang('SupplierLedger.generated') ?> <?= date('Y-m-d H:i') ?></div>

        <div class="center no-print" style="margin-top:6px;">
            <?php if ($supplier): ?>
                <a href="<?= site_url('supplier-ledger/view/' . ($supplier['id'] ?? 0) . '?from=' . $from . '&to=' . $to) ?>" class="btn"><?= lang('SupplierLedger.back') ?></a>
            <?php endif; ?>
            <button type="button" onclick="window.print()" class="btn btn-primary"><?= lang('SupplierLedger.print') ?></button>
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