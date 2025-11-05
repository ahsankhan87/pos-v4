<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php
helper('permissions');
$currencySymbol = session()->get('currency_symbol') ?: '₹';
$from = esc((string)($from ?? ''));
$to   = esc((string)($to ?? ''));
$type = esc((string)($type ?? ''));
$q    = esc((string)($q ?? ''));
$showBalance = isset($showBalance) ? (bool)$showBalance : true;
$customer = $customer ?? null;
$logoExists = is_file(FCPATH . 'uploads/logo.png');
$canViewAmounts = can_view_amounts();
?>
<style>
    @media print {
        .no-print {
            display: none !important;
        }

        body {
            background: #fff !important;
        }
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th,
    .table td {
        border: 1px solid #e5e7eb;
        padding: 8px;
        font-size: 12px;
    }

    .table th {
        background: #f8fafc;
        text-transform: uppercase;
        color: #64748b;
    }

    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .subtle {
        color: #64748b;
        font-size: 12px;
    }

    .title {
        font-size: 18px;
        font-weight: 700;
        color: #0f172a;
    }

    .badge {
        display: inline-flex;
        padding: 2px 8px;
        font-size: 10px;
        border-radius: 9999px;
        background: #f1f5f9;
        color: #334155;
    }

    .badge.blue {
        background: #eff6ff;
        color: #1d4ed8;
    }

    .badge.green {
        background: #ecfdf5;
        color: #059669;
    }

    .badge.orange {
        background: #fff7ed;
        color: #ea580c;
    }

    .badge.purple {
        background: #f5f3ff;
        color: #7c3aed;
    }
</style>
<div class="min-h-screen bg-white px-6 py-4">
    <div class="header">
        <div>
            <div class="title" style="display:flex;align-items:center;gap:8px;">
                <?php if ($logoExists): ?>
                    <img src="<?= base_url('uploads/logo.png') ?>" alt="Logo" style="height:28px;">
                <?php endif; ?>
                <span>Customer Ledger</span>
            </div>
            <?php if ($customer): ?>
                <div class="subtle">
                    <?= esc($customer['name'] ?? 'Unknown Customer') ?>
                    <?php if (!empty($customer['phone'])): ?> • <?= esc($customer['phone']) ?><?php endif; ?>
                        <?php if (!empty($customer['email'])): ?> • <?= esc($customer['email']) ?><?php endif; ?>
                </div>
            <?php endif; ?>
            <div class="subtle">
                <?= $from ? 'From: ' . esc($from) . ' ' : '' ?><?= $to ? 'To: ' . esc($to) . ' ' : '' ?><?= $type ? 'Type: ' . esc($type) . ' ' : '' ?>
            </div>
        </div>
        <div class="no-print">
            <a href="<?= site_url('customers/ledger/' . ($customer['id'] ?? 0) . '?from=' . $from . '&to=' . $to . '&type=' . $type . '&q=' . $q . '&show_balance=' . ($showBalance ? '1' : '0')) ?>" class="btn btn-muted">Back</a>
            <button onclick="window.print()" class="btn btn-primary">Print</button>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:12px;">
        <div style="border:1px solid #e5e7eb;border-radius:8px;padding:10px;">
            <div class="subtle">Opening Balance</div>
            <div style="font-weight:700;"><?= $currencySymbol . number_format((float)($openingBalance ?? 0), 2) ?></div>
        </div>
        <div style="border:1px solid #e5e7eb;border-radius:8px;padding:10px;">
            <div class="subtle">Total Debit</div>
            <div style="font-weight:700;color:#dc2626;"><?= $currencySymbol . number_format((float)($totalDebit ?? 0), 2) ?></div>
        </div>
        <div style="border:1px solid #e5e7eb;border-radius:8px;padding:10px;">
            <div class="subtle">Total Credit</div>
            <div style="font-weight:700;color:#059669;"><?= $currencySymbol . number_format((float)($totalCredit ?? 0), 2) ?></div>
        </div>
        <div style="border:1px solid #e5e7eb;border-radius:8px;padding:10px;">
            <div class="subtle">Closing Balance</div>
            <div style="font-weight:700;<?= ((float)($closingBalance ?? 0) < 0 ? 'color:#dc2626;' : '') ?>">
                <?= $currencySymbol . number_format((float)($closingBalance ?? 0), 2) ?>
            </div>
        </div>
    </div>

    <?php if (isset($outstanding) || isset($creditLimit)): ?>
        <table class="table" style="margin-bottom:10px;">
            <tr>
                <th style="width:24%">As of</th>
                <td><?= esc($agingAsOf ?? ($to ?: date('Y-m-d'))) ?></td>
                <th style="width:24%">Outstanding</th>
                <td><?= $currencySymbol . number_format((float)($outstanding ?? 0), 2) ?></td>
            </tr>
            <tr>
                <th>Credit Limit</th>
                <td><?= isset($creditLimit) && $creditLimit !== null ? ($currencySymbol . number_format((float)$creditLimit, 2)) : '—' ?></td>
                <th>Available</th>
                <td><?= isset($creditAvailable) ? ($currencySymbol . number_format((float)$creditAvailable, 2)) : '—' ?></td>
            </tr>
        </table>
    <?php endif; ?>

    <?php if (!empty($agingBuckets ?? [])): ?>
        <table class="table" style="margin-bottom:10px;">
            <thead>
                <tr>
                    <th>0–30</th>
                    <th>31–60</th>
                    <th>61–90</th>
                    <th>90+</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= $currencySymbol . number_format((float)($agingBuckets['0_30'] ?? 0), 2) ?></td>
                    <td><?= $currencySymbol . number_format((float)($agingBuckets['31_60'] ?? 0), 2) ?></td>
                    <td><?= $currencySymbol . number_format((float)($agingBuckets['61_90'] ?? 0), 2) ?></td>
                    <td><?= $currencySymbol . number_format((float)($agingBuckets['90_plus'] ?? 0), 2) ?></td>
                </tr>
            </tbody>
        </table>
    <?php endif; ?>

    <table class="table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Ref</th>
                <th>Description</th>
                <th>Type</th>
                <?php if ($canViewAmounts): ?>
                    <th style="text-align:right;">Debit (<?= esc($currencySymbol) ?>)</th>
                    <th style="text-align:right;">Credit (<?= esc($currencySymbol) ?>)</th>
                <?php endif; ?>
                <?php if ($showBalance && $canViewAmounts): ?>
                    <th style="text-align:right;">Balance (<?= esc($currencySymbol) ?>)</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?= esc($from ?: ($ledger[0]['date'] ?? '')) ?></td>
                <td>—</td>
                <td>Opening Balance</td>
                <td><span class="badge">opening</span></td>
                <?php if ($canViewAmounts): ?>
                    <td style="text-align:right;">—</td>
                    <td style="text-align:right;">—</td>
                <?php endif; ?>
                <?php if ($showBalance && $canViewAmounts): ?>
                    <td style="text-align:right;font-weight:600;"><?= $currencySymbol . number_format((float)($openingBalance ?? 0), 2) ?></td>
                <?php endif; ?>
            </tr>
            <?php foreach (($ledger ?? []) as $entry): ?>
                <?php
                $etype = strtolower((string)($entry['type'] ?? ''));
                $badgeClass = '';
                if ($etype === 'sale') $badgeClass = 'blue';
                elseif ($etype === 'payment') $badgeClass = 'green';
                elseif ($etype === 'return') $badgeClass = 'orange';
                elseif ($etype === 'adjustment') $badgeClass = 'purple';
                ?>
                <tr>
                    <td><?= esc($entry['date'] ?? '') ?></td>
                    <td><?= esc($entry['ref_no'] ?? '-') ?></td>
                    <td><?= esc($entry['description'] ?? '') ?></td>
                    <td><span class="badge <?= $badgeClass ?>"><?= esc($entry['type'] ?? '-') ?></span></td>
                    <?php if ($canViewAmounts): ?>
                        <td style="text-align:right;"><?= number_format((float)($entry['debit'] ?? 0), 2) ?></td>
                        <td style="text-align:right;"><?= number_format((float)($entry['credit'] ?? 0), 2) ?></td>
                    <?php endif; ?>
                    <?php if ($showBalance && $canViewAmounts): ?>
                        <td style="text-align:right;font-weight:600;">
                            <?= $currencySymbol . number_format((float)($entry['balance'] ?? 0), 2) ?>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($ledger)): ?>
                <tr>
                    <td colspan="<?= $canViewAmounts ? ($showBalance ? 7 : 6) : ($showBalance ? 5 : 4) ?>" style="text-align:center;color:#64748b;padding:16px;">No transactions</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" style="text-align:left;">Totals</th>
                <?php if ($canViewAmounts): ?>
                    <th style="text-align:right;color:#b91c1c;"><?= $currencySymbol . number_format((float)($totalDebit ?? 0), 2) ?></th>
                    <th style="text-align:right;color:#047857;"><?= $currencySymbol . number_format((float)($totalCredit ?? 0), 2) ?></th>
                <?php endif; ?>
                <?php if ($showBalance && $canViewAmounts): ?>
                    <th style="text-align:right;font-weight:700;"><?= $currencySymbol . number_format((float)($closingBalance ?? 0), 2) ?></th>
                <?php endif; ?>
            </tr>
        </tfoot>
    </table>
</div>
<?= $this->endSection() ?>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            try {
                window.print();
            } catch (e) {}
        }, 200);
    });
</script>