<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php
$currencySymbol = session()->get('currency_symbol') ?: '₹';
$from = esc((string)($from ?? ''));
$to   = esc((string)($to ?? ''));
$type = esc((string)($type ?? ''));
$q    = esc((string)($q ?? ''));
$customer = $customer ?? null;
$logoExists = is_file(FCPATH . 'uploads/logo.png');
?>
<style>
    /* Compact 80mm receipt-like styles */
    @page {
        size: 80mm auto;
        margin: 4mm;
    }

    @media print {
        .no-print {
            display: none !important;
        }
    }

    body {
        background: #fff;
    }

    .wrap {
        max-width: 76mm;
        margin: 0 auto;
        font-family: system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", "Apple Color Emoji", "Segoe UI Emoji";
        font-size: 11px;
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
        border-top: 1px dashed #e5e7eb;
        margin: 6px 0;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        padding: 4px 0;
        font-size: 11px;
    }

    th {
        text-align: left;
        color: #6b7280;
    }

    td.num {
        text-align: right;
    }
</style>
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
                    <td><?= esc($entry['date'] ?? '') ?></td>
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
        <a href="<?= site_url('customers/ledger/' . ($customer['id'] ?? 0) . '?from=' . $from . '&to=' . $to . '&type=' . $type . '&q=' . $q) ?>" class="btn btn-muted">Back</a>
        <button onclick="window.print()" class="btn btn-primary">Print</button>
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
<?= $this->endSection() ?>