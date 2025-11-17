<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.3;
            color: #333;
            background: white;
        }

        .invoice-container {
            max-width: 760px;
            margin: 0 auto;
            padding: 14px;
            background: white;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 14px;
            border-bottom: 1px solid #e5e5e5;
            padding-bottom: 10px;
        }

        .company-info {
            flex: 1;
        }

        .company-logo {
            max-width: 90px;
            max-height: 60px;
            margin-bottom: 6px;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 3px;
        }

        .company-details {
            font-size: 10px;
            color: #666;
            line-height: 1.25;
        }

        .invoice-title {
            text-align: right;
            flex: 1;
        }

        .invoice-title h1 {
            font-size: 22px;
            font-weight: bold;
            color: #dc2626;
            margin-bottom: 6px;
        }

        .invoice-meta {
            font-size: 10px;
            color: #666;
        }

        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 14px;
        }

        .bill-to,
        .invoice-info {
            flex: 1;
            margin-right: 18px;
        }

        .bill-to:last-child,
        .invoice-info:last-child {
            margin-right: 0;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 6px;
            text-transform: uppercase;
            border-bottom: 1px solid #e5e5e5;
            padding-bottom: 4px;
        }

        .detail-item {
            margin-bottom: 4px;
            display: flex;
        }

        .detail-label {
            font-weight: bold;
            min-width: 90px;
            color: #666;
            font-size: 10px;
        }

        .detail-value {
            color: #333;
            font-size: 10.5px;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-received {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-ordered {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .payment-paid {
            background-color: #d1fae5;
            color: #065f46;
        }

        .payment-partial {
            background-color: #fef3c7;
            color: #92400e;
        }

        .payment-unpaid {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            border: 1px solid #e5e5e5;
        }

        .items-table th {
            background-color: #f9fafb;
            padding: 6px 4px;
            text-align: left;
            font-weight: bold;
            font-size: 9.5px;
            text-transform: uppercase;
            color: #374151;
            border-bottom: 1px solid #e5e5e5;
            line-height: 1.2;
        }

        .items-table td {
            padding: 4px 4px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 10px;
            line-height: 1.2;
        }

        .items-table tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        .totals-section {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 8px;
        }

        .totals-table {
            width: 220px;
        }

        .totals-table tr {
            border-bottom: none;
        }

        .totals-table tr:last-child {
            border-top: 1px solid #374151;
            font-weight: bold;
            font-size: 11px;
        }

        .totals-table td {
            padding: 4px 8px;
            font-size: 10px;
            line-height: 1.2;
        }

        .notes-section {
            margin-bottom: 14px;
            border: 1px solid #e5e5e5;
            padding: 10px;
            background-color: #f9fafb;
            font-size: 10.5px;
        }

        .footer {
            text-align: center;
            font-size: 9.5px;
            color: #666;
            border-top: 1px solid #e5e5e5;
            padding-top: 8px;
        }

        /* Payments (history) */
        .payments-section {
            margin-top: 2px;
        }

        .payment-row {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            padding: 3px 0;
            border-bottom: 1px dashed #e5e7eb;
        }

        .payment-date {
            font-size: 10px;
            color: #666;
            min-width: 90px;
        }

        .payment-method {
            font-size: 10px;
            color: #666;
            margin: 0 8px;
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .payment-amount {
            font-size: 10.5px;
            font-weight: bold;
            min-width: 90px;
            text-align: right;
        }

        /* Print styles */
        @media print {
            body {
                font-size: 10.5px;
            }

            .invoice-container {
                max-width: none;
                margin: 0;
                padding: 0.1in;
            }

            .no-print {
                display: none;
            }

            @page {
                margin: 0.35in;
                size: A4;
            }
        }

        .print-button {
            position: fixed;
            top: 12px;
            right: 12px;
            background: #2563eb;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            z-index: 1000;
        }

        .print-button:hover {
            background: #1d4ed8;
        }
    </style>
</head>

<body>
    <button class="print-button no-print" onclick="window.print()">🖨️ Print Invoice</button>

    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="company-info">
                <?php if (!empty($company['logo'])): ?>
                    <img src="<?= base_url($company['logo']) ?>" alt="<?= esc($company['name']) ?>" class="company-logo">
                <?php endif; ?>
                <div class="company-name"><?= esc($company['name']) ?></div>
                <div class="company-details">
                    <?= nl2br(esc($company['address'])) ?><br>
                    Phone: <?= esc($company['phone']) ?><br>
                    Email: <?= esc($company['email']) ?>
                </div>
            </div>
            <div class="invoice-title">
                <h1>PURCHASE INVOICE</h1>
                <div class="invoice-meta">
                    <strong>Invoice #<?= esc($purchase['invoice_no']) ?></strong><br>
                    Date: <?= date('d M Y', strtotime($purchase['date'])) ?><br>
                    Time: <?= date('H:i', strtotime($purchase['date'])) ?>
                </div>
            </div>
        </div>

        <!-- Purchase Details -->
        <div class="invoice-details">
            <div class="bill-to">
                <div class="section-title">Supplier Information</div>
                <div class="detail-item">
                    <span class="detail-label">Company:</span>
                    <span class="detail-value"><?= esc($purchase['supplier']['name'] ?? 'N/A') ?></span>
                </div>
                <?php if (!empty($purchase['supplier']['email'])): ?>
                    <div class="detail-item">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value"><?= esc($purchase['supplier']['email']) ?></span>
                    </div>
                <?php endif; ?>
                <?php if (!empty($purchase['supplier']['phone'])): ?>
                    <div class="detail-item">
                        <span class="detail-label">Phone:</span>
                        <span class="detail-value"><?= esc($purchase['supplier']['phone']) ?></span>
                    </div>
                <?php endif; ?>
                <?php if (!empty($purchase['supplier']['address'])): ?>
                    <div class="detail-item">
                        <span class="detail-label">Address:</span>
                        <span class="detail-value"><?= nl2br(esc($purchase['supplier']['address'])) ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="invoice-info">
                <div class="section-title">Purchase Details</div>
                <!-- <div class="detail-item">
                    <span class="detail-label">Store:</span>
                    <span class="detail-value"><?= esc($purchase['store']['name'] ?? 'N/A') ?></span>
                </div> -->
                <div class="detail-item">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value">
                        <span class="status-badge <?= $purchase['status'] === 'received' ? 'status-received' : ($purchase['status'] === 'pending' ? 'status-pending' : 'status-ordered') ?>">
                            <?= ucfirst($purchase['status']) ?>
                        </span>
                    </span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Payment:</span>
                    <span class="detail-value">
                        <span class="status-badge <?= $purchase['payment_status'] === 'paid' ? 'payment-paid' : ($purchase['payment_status'] === 'partial' ? 'payment-partial' : 'payment-unpaid') ?>">
                            <?= ucfirst($purchase['payment_status']) ?>
                        </span>
                    </span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Method:</span>
                    <span class="detail-value"><?= ucfirst(str_replace('_', ' ', $purchase['payment_method'])) ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Created By:</span>
                    <span class="detail-value"><?= esc($purchase['creator']['username'] ?? 'System') ?></span>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%">#</th>
                    <th style="width: 35%">Product</th>
                    <th style="width: 10%" class="text-center">Qty</th>
                    <th style="width: 12%" class="text-right">Cost Price</th>

                    <th style="width: 18%" class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($purchase['items'] as $index => $item): ?>
                    <tr>
                        <td class="text-center"><?= $index + 1 ?></td>
                        <td>
                            <div class="font-bold"><?= esc($item['product_name']) ?></div>
                        </td>
                        <td class="text-center"><?= number_format($item['quantity'], 2) ?></td>
                        <td class="text-right"><?= number_to_currency($item['cost_price'], session()->get('currency_symbol'), 'en_US', 2) ?></td>

                        <td class="text-right font-bold"><?= number_to_currency($item['subtotal'], session()->get('currency_symbol'), 'en_US', 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Totals -->
        <?php
        $__subtotal = (float)($purchase['total_amount'] ?? 0);
        $__discount = 0.0;
        if (($purchase['discount_type'] ?? '') === 'percentage') {
            $__discount = $__subtotal * (float)($purchase['discount'] ?? 0) / 100;
        } else {
            $__discount = (float)($purchase['discount'] ?? 0);
        }
        $__tax = (float)($purchase['tax_amount'] ?? 0);
        $__shipping = (float)($purchase['shipping_cost'] ?? 0);
        ?>
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td>Subtotal:</td>
                    <td class="text-right"><?= number_to_currency($__subtotal, session()->get('currency_symbol'), 'en_US', 2) ?></td>
                </tr>
                <?php if ($__discount > 0.0001): ?>
                    <tr>
                        <td>Discount:</td>
                        <td class="text-right">-<?= number_to_currency($__discount, session()->get('currency_symbol'), 'en_US', 2) ?></td>
                    </tr>
                <?php endif; ?>
                <?php if ($__tax > 0.0001): ?>
                    <tr>
                        <td>Tax:</td>
                        <td class="text-right"><?= number_to_currency($__tax, session()->get('currency_symbol'), 'en_US', 2) ?></td>
                    </tr>
                <?php endif; ?>
                <?php if ($__shipping > 0.0001): ?>
                    <tr>
                        <td>Shipping:</td>
                        <td class="text-right"><?= number_to_currency($__shipping, session()->get('currency_symbol'), 'en_US', 2) ?></td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td><strong>Grand Total:</strong></td>
                    <td class="text-right"><strong><?= number_to_currency($purchase['grand_total'], session()->get('currency_symbol'), 'en_US', 2) ?></strong></td>
                </tr>
            </table>
        </div>

        <!-- Payment Summary -->
        <div class="invoice-details">
            <div class="bill-to">
                <div class="section-title">Payment Summary</div>
                <div class="detail-item">
                    <span class="detail-label">Grand Total:</span>
                    <span class="detail-value font-bold"><?= number_to_currency($purchase['grand_total'], session()->get('currency_symbol'), 'en_US', 2) ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Amount Paid:</span>
                    <span class="detail-value"><?= number_to_currency($purchase['paid_amount'], session()->get('currency_symbol'), 'en_US', 2) ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Due Amount:</span>
                    <span class="detail-value font-bold" style="color: <?= $purchase['due_amount'] > 0 ? '#dc2626' : '#059669' ?>"><?= number_to_currency($purchase['due_amount'], session()->get('currency_symbol'), 'en_US', 2) ?></span>
                </div>
            </div>

            <!-- Payment History -->
            <?php if (!empty($purchase['payments'])): ?>
                <div class="invoice-info payments-section">
                    <div class="section-title">Payment History</div>
                    <?php foreach ($purchase['payments'] as $payment): ?>
                        <div class="payment-row">
                            <span class="payment-date"><?= date('d M Y', strtotime($payment['payment_date'])) ?></span>
                            <span class="payment-method"><?= ucfirst(str_replace('_', ' ', $payment['payment_method'])) ?><?= !empty($payment['reference']) ? ' - ' . esc($payment['reference']) : '' ?></span>
                            <span class="payment-amount"><?= number_to_currency($payment['amount'], session()->get('currency_symbol'), 'en_US', 2) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Notes -->
        <?php if (!empty($purchase['note'])): ?>
            <div class="notes-section">
                <div class="section-title">Notes</div>
                <div><?= nl2br(esc($purchase['note'])) ?></div>
            </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for your business!</p>
            <p>This is a computer-generated invoice. Generated on <?= date('d M Y H:i') ?></p>
        </div>
    </div>

    <script>
        // Auto-focus on print button for keyboard accessibility
        document.addEventListener('DOMContentLoaded', function() {
            // Optional: Auto-print when opened (uncomment the line below if needed)
            // window.print();
        });
    </script>
</body>

</html>