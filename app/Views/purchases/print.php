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
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: white;
        }

        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: white;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            border-bottom: 2px solid #e5e5e5;
            padding-bottom: 20px;
        }

        .company-info {
            flex: 1;
        }

        .company-logo {
            max-width: 120px;
            max-height: 80px;
            margin-bottom: 10px;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
        }

        .company-details {
            font-size: 11px;
            color: #666;
            line-height: 1.3;
        }

        .invoice-title {
            text-align: right;
            flex: 1;
        }

        .invoice-title h1 {
            font-size: 32px;
            font-weight: bold;
            color: #dc2626;
            margin-bottom: 10px;
        }

        .invoice-meta {
            font-size: 11px;
            color: #666;
        }

        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .bill-to,
        .invoice-info {
            flex: 1;
            margin-right: 30px;
        }

        .bill-to:last-child,
        .invoice-info:last-child {
            margin-right: 0;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 10px;
            text-transform: uppercase;
            border-bottom: 1px solid #e5e5e5;
            padding-bottom: 5px;
        }

        .detail-item {
            margin-bottom: 5px;
            display: flex;
        }

        .detail-label {
            font-weight: bold;
            min-width: 100px;
            color: #666;
        }

        .detail-value {
            color: #333;
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
            margin-bottom: 30px;
            border: 1px solid #e5e5e5;
        }

        .items-table th {
            background-color: #f9fafb;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
            color: #374151;
            border-bottom: 2px solid #e5e5e5;
        }

        .items-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 11px;
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
            margin-bottom: 30px;
        }

        .totals-table {
            width: 300px;
        }

        .totals-table tr {
            border-bottom: 1px solid #f3f4f6;
        }

        .totals-table tr:last-child {
            border-bottom: 2px solid #374151;
            font-weight: bold;
            font-size: 14px;
        }

        .totals-table td {
            padding: 8px 12px;
        }

        .notes-section {
            margin-bottom: 30px;
            border: 1px solid #e5e5e5;
            padding: 15px;
            background-color: #f9fafb;
        }

        .footer {
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #e5e5e5;
            padding-top: 15px;
        }

        /* Print styles */
        @media print {
            body {
                font-size: 11px;
            }

            .invoice-container {
                max-width: none;
                margin: 0;
                padding: 0;
            }

            .no-print {
                display: none;
            }

            @page {
                margin: 0.5in;
                size: A4;
            }
        }

        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #2563eb;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
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
                <div class="detail-item">
                    <span class="detail-label">Store:</span>
                    <span class="detail-value"><?= esc($purchase['store']['name'] ?? 'N/A') ?></span>
                </div>
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
                            <div style="font-size: 10px; color: #666;"><?= esc($item['product_code']) ?></div>
                        </td>
                        <td class="text-center"><?= number_format($item['quantity'], 2) ?></td>
                        <td class="text-right"><?= number_to_currency($item['cost_price'], session()->get('currency_symbol'), 'en_US', 2) ?></td>

                        <td class="text-right font-bold"><?= number_to_currency($item['subtotal'], session()->get('currency_symbol'), 'en_US', 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td>Subtotal:</td>
                    <td class="text-right"><?= number_to_currency($purchase['total_amount'], session()->get('currency_symbol'), 'en_US', 2) ?></td>
                </tr>
                <tr>
                    <td>Discount:</td>
                    <?php
                    $disount = 0;
                    if ($purchase['discount_type'] === 'percentage') {
                        $disount = $purchase['total_amount'] * $purchase['discount'] / 100;
                    } else {
                        $disount = $purchase['discount'];
                    }
                    ?>
                    <td class="text-right">-<?= number_to_currency($disount, session()->get('currency_symbol'), 'en_US', 2) ?></td>
                </tr>
                <tr>
                    <td>Tax:</td>
                    <td class="text-right"><?= number_to_currency($purchase['tax_amount'], session()->get('currency_symbol'), 'en_US', 2) ?></td>
                </tr>
                <tr>
                    <td>Shipping:</td>
                    <td class="text-right"><?= number_to_currency((float)$purchase['shipping_cost'], session()->get('currency_symbol'), 'en_US', 2) ?></td>
                </tr>
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
                <div class="invoice-info">
                    <div class="section-title">Payment History</div>
                    <?php foreach ($purchase['payments'] as $payment): ?>
                        <div class="detail-item" style="margin-bottom: 8px; border-bottom: 1px solid #f3f4f6; padding-bottom: 5px; ">
                            <div style="display: flex; justify-content: space-between;">
                                <span style="font-size: 10px; color: #666;"><?= date('d M Y', strtotime($payment['payment_date'])) ?></span>
                                <span class="font-bold"><?= number_to_currency($payment['amount'], session()->get('currency_symbol'), 'en_US', 2) ?></span>
                            </div>
                            <div style="font-size: 10px; color: #666;"><?= ucfirst(str_replace('_', ' ', $payment['payment_method'])) ?><?= !empty($payment['reference']) ? ' - ' . esc($payment['reference']) : '' ?></div>
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