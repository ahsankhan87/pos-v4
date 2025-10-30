-- Sample Receipt Templates for POS System
-- Run these queries to insert sample templates into your database

-- 1. Thermal Printer 80mm - Simple Receipt (Default)
INSERT INTO `receipt_templates` (`name`, `template`, `is_default`, `created_at`, `updated_at`) VALUES
('Thermal 80mm - Simple', '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @media print {
            @page { margin: 0; }
            body { margin: 0.5cm; }
        }
        body {
            font-family: ''Courier New'', Courier, monospace;
            width: 300px;
            margin: 0 auto;
            padding: 10px;
            font-size: 12px;
            line-height: 1.4;
        }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .large { font-size: 16px; }
        .line {
            border-bottom: 1px dashed #000;
            margin: 8px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
        }
        th, td {
            padding: 2px 0;
            text-align: left;
        }
        th { border-bottom: 1px solid #000; }
        .right { text-align: right; }
        .totals {
            margin-top: 10px;
            border-top: 2px solid #000;
            padding-top: 5px;
        }
        .footer {
            margin-top: 15px;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="center">
        <div class="bold large">{{store_name}}</div>
        <div style="font-size: 10px;">{{store_address}}</div>
        <div style="font-size: 10px;">Tel: {{store_phone}}</div>
    </div>

    <div class="line"></div>

    <div style="font-size: 11px;">
        <div><strong>Receipt #:</strong> {{receipt_number}}</div>
        <div><strong>Date:</strong> {{date}}</div>
        <div><strong>Cashier:</strong> {{cashier}}</div>
        {{customer}}
    </div>

    <div class="line"></div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th class="right">Qty</th>
                <th class="right">Price</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            {{items}}
        </tbody>
    </table>

    <div class="totals">
        <table style="font-size: 11px;">
            <tr>
                <td>Subtotal:</td>
                <td class="right">${{subtotal}}</td>
            </tr>
            <tr>
                <td>Discount:</td>
                <td class="right">-${{total_discount}}</td>
            </tr>
            <tr>
                <td>Tax:</td>
                <td class="right">${{tax}}</td>
            </tr>
            <tr style="border-top: 2px solid #000; font-weight: bold;">
                <td>TOTAL:</td>
                <td class="right">${{total}}</td>
            </tr>
            <tr>
                <td>Paid:</td>
                <td class="right">${{paid}}</td>
            </tr>
            <tr>
                <td>Change:</td>
                <td class="right">${{change}}</td>
            </tr>
        </table>
    </div>

    <div class="line"></div>

    <div class="center footer">
        <div>{{store_footer}}</div>
        <div style="margin-top: 5px;">Thank you for your business!</div>
        <div style="margin-top: 10px;">***</div>
    </div>
</body>
</html>', 1, NOW(), NOW());

-- 2. Thermal Printer 58mm - Compact Receipt
INSERT INTO `receipt_templates` (`name`, `template`, `is_default`, `created_at`, `updated_at`) VALUES
('Thermal 58mm - Compact', '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @media print {
            @page { margin: 0; }
            body { margin: 0.3cm; }
        }
        body {
            font-family: ''Courier New'', Courier, monospace;
            width: 200px;
            margin: 0 auto;
            padding: 5px;
            font-size: 10px;
            line-height: 1.3;
        }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .line {
            border-bottom: 1px dashed #000;
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        th, td { padding: 1px 0; }
        th { border-bottom: 1px solid #000; }
        .right { text-align: right; }
        .totals {
            margin-top: 5px;
            border-top: 2px solid #000;
            padding-top: 3px;
        }
    </style>
</head>
<body>
    <div class="center">
        <div class="bold" style="font-size: 12px;">{{store_name}}</div>
        <div style="font-size: 8px;">{{store_address}}</div>
        <div style="font-size: 8px;">{{store_phone}}</div>
    </div>

    <div class="line"></div>

    <div style="font-size: 9px;">
        <div><strong>#:</strong> {{receipt_number}}</div>
        <div><strong>Date:</strong> {{date}}</div>
        <div><strong>By:</strong> {{cashier}}</div>
        {{customer}}
    </div>

    <div class="line"></div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th class="right">Q</th>
                <th class="right">Amt</th>
            </tr>
        </thead>
        <tbody>
            {{items}}
        </tbody>
    </table>

    <div class="totals">
        <table style="font-size: 9px;">
            <tr><td>Subtotal:</td><td class="right">{{subtotal}}</td></tr>
            <tr><td>Discount:</td><td class="right">-{{total_discount}}</td></tr>
            <tr><td>Tax:</td><td class="right">{{tax}}</td></tr>
            <tr style="border-top: 1px solid #000; font-weight: bold;">
                <td>TOTAL:</td><td class="right">{{total}}</td>
            </tr>
            <tr><td>Paid:</td><td class="right">{{paid}}</td></tr>
            <tr><td>Change:</td><td class="right">{{change}}</td></tr>
        </table>
    </div>

    <div class="line"></div>

    <div class="center" style="font-size: 8px;">
        <div>{{store_footer}}</div>
        <div>Thank you!</div>
    </div>
</body>
</html>', 0, NOW(), NOW());

-- 3. Normal A4/Letter Printer - Professional Receipt
INSERT INTO `receipt_templates` (`name`, `template`, `is_default`, `created_at`, `updated_at`) VALUES
('A4 Letter - Professional', '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @media print {
            @page { margin: 1cm; }
            body { margin: 0; }
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            width: 21cm;
            margin: 0 auto;
            padding: 20px;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            color: #2c3e50;
        }
        .header p {
            margin: 5px 0;
            color: #7f8c8d;
        }
        .receipt-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .info-block {
            flex: 1;
        }
        .info-block h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
        }
        .info-block p {
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        thead {
            background: #34495e;
            color: white;
        }
        th {
            padding: 12px;
            text-align: left;
            font-weight: bold;
        }
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #ecf0f1;
        }
        tbody tr:hover {
            background: #f8f9fa;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totals {
            float: right;
            width: 350px;
            margin-top: 20px;
        }
        .totals table {
            margin: 0;
        }
        .totals td {
            padding: 8px 12px;
        }
        .totals .grand-total {
            font-size: 16px;
            font-weight: bold;
            background: #2c3e50;
            color: white;
        }
        .footer {
            clear: both;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #ecf0f1;
            text-align: center;
            color: #7f8c8d;
        }
        .footer p {
            margin: 5px 0;
        }
        .watermark {
            font-size: 10px;
            color: #bdc3c7;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{store_name}}</h1>
        <p>{{store_address}}</p>
        <p>Phone: {{store_phone}}</p>
    </div>

    <div class="receipt-info">
        <div class="info-block">
            <h3>Receipt Information</h3>
            <p><strong>Receipt Number:</strong> {{receipt_number}}</p>
            <p><strong>Date:</strong> {{date}}</p>
            <p><strong>Cashier:</strong> {{cashier}}</p>
        </div>
        <div class="info-block">
            <h3>Customer Information</h3>
            {{customer}}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 50%;">Item Description</th>
                <th class="text-center" style="width: 10%;">Quantity</th>
                <th class="text-right" style="width: 20%;">Unit Price</th>
                <th class="text-right" style="width: 20%;">Total</th>
            </tr>
        </thead>
        <tbody>
            {{items}}
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td><strong>Subtotal:</strong></td>
                <td class="text-right">${{subtotal}}</td>
            </tr>
            <tr>
                <td><strong>Discount:</strong></td>
                <td class="text-right">-${{total_discount}}</td>
            </tr>
            <tr>
                <td><strong>Tax:</strong></td>
                <td class="text-right">${{tax}}</td>
            </tr>
            <tr class="grand-total">
                <td><strong>GRAND TOTAL:</strong></td>
                <td class="text-right"><strong>${{total}}</strong></td>
            </tr>
            <tr>
                <td>Amount Paid:</td>
                <td class="text-right">${{paid}}</td>
            </tr>
            <tr>
                <td>Change:</td>
                <td class="text-right">${{change}}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p><strong>{{store_footer}}</strong></p>
        <p>Thank you for your business!</p>
        <p class="watermark">This is a computer-generated receipt and does not require a signature.</p>
    </div>
</body>
</html>', 0, NOW(), NOW());

-- 4. Normal A4 - Invoice Style
INSERT INTO `receipt_templates` (`name`, `template`, `is_default`, `created_at`, `updated_at`) VALUES
('A4 Letter - Invoice Style', '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @media print {
            @page { margin: 1.5cm; }
            body { margin: 0; }
        }
        body {
            font-family: ''Times New Roman'', Times, serif;
            width: 21cm;
            margin: 0 auto;
            padding: 30px;
            font-size: 13px;
        }
        .letterhead {
            border: 3px double #000;
            padding: 20px;
            margin-bottom: 30px;
        }
        .letterhead h1 {
            margin: 0;
            font-size: 32px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .letterhead .contact {
            margin-top: 10px;
            font-size: 11px;
        }
        .title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin: 20px 0;
            text-transform: uppercase;
            letter-spacing: 3px;
        }
        .details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .details-box {
            border: 2px solid #000;
            padding: 15px;
            width: 45%;
        }
        .details-box h4 {
            margin: 0 0 10px 0;
            font-size: 14px;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
        }
        .details-box p {
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
            margin: 20px 0;
        }
        th {
            background: #000;
            color: white;
            padding: 12px;
            text-align: left;
            border: 1px solid #000;
        }
        td {
            padding: 10px;
            border: 1px solid #000;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .summary {
            float: right;
            width: 400px;
            border: 2px solid #000;
            margin-top: 20px;
        }
        .summary table {
            border: none;
            margin: 0;
        }
        .summary td {
            border: none;
            border-bottom: 1px solid #ddd;
            padding: 10px 15px;
        }
        .summary .total-row {
            background: #000;
            color: white;
            font-size: 16px;
            font-weight: bold;
        }
        .terms {
            clear: both;
            margin-top: 40px;
            padding: 20px;
            border: 2px solid #000;
            font-size: 11px;
        }
        .terms h4 {
            margin: 0 0 10px 0;
        }
        .signature {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 45%;
            text-align: center;
        }
        .signature-line {
            border-top: 2px solid #000;
            margin-top: 60px;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="letterhead">
        <h1>{{store_name}}</h1>
        <div class="contact">
            <strong>Address:</strong> {{store_address}} | 
            <strong>Phone:</strong> {{store_phone}}
        </div>
    </div>

    <div class="title">SALES RECEIPT</div>

    <div class="details">
        <div class="details-box">
            <h4>RECEIPT DETAILS</h4>
            <p><strong>Receipt No:</strong> {{receipt_number}}</p>
            <p><strong>Date Issued:</strong> {{date}}</p>
            <p><strong>Served By:</strong> {{cashier}}</p>
        </div>
        <div class="details-box">
            <h4>CUSTOMER DETAILS</h4>
            {{customer}}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 45%;">DESCRIPTION</th>
                <th class="text-center" style="width: 15%;">QUANTITY</th>
                <th class="text-right" style="width: 15%;">UNIT PRICE</th>
                <th class="text-right" style="width: 20%;">AMOUNT</th>
            </tr>
        </thead>
        <tbody>
            {{items}}
        </tbody>
    </table>

    <div class="summary">
        <table>
            <tr>
                <td><strong>Subtotal:</strong></td>
                <td class="text-right">${{subtotal}}</td>
            </tr>
            <tr>
                <td><strong>Discount Applied:</strong></td>
                <td class="text-right">-${{total_discount}}</td>
            </tr>
            <tr>
                <td><strong>Tax Amount:</strong></td>
                <td class="text-right">${{tax}}</td>
            </tr>
            <tr class="total-row">
                <td><strong>TOTAL AMOUNT:</strong></td>
                <td class="text-right"><strong>${{total}}</strong></td>
            </tr>
            <tr>
                <td>Amount Paid:</td>
                <td class="text-right">${{paid}}</td>
            </tr>
            <tr>
                <td>Change Returned:</td>
                <td class="text-right">${{change}}</td>
            </tr>
        </table>
    </div>

    <div class="terms">
        <h4>TERMS & CONDITIONS</h4>
        <p>{{store_footer}}</p>
        <p>All sales are final unless otherwise stated. Please retain this receipt for your records.</p>
    </div>

    <div class="signature">
        <div class="signature-box">
            <div class="signature-line">
                Customer Signature
            </div>
        </div>
        <div class="signature-box">
            <div class="signature-line">
                Authorized Signature
            </div>
        </div>
    </div>
</body>
</html>', 0, NOW(), NOW());

-- 5. Thermal 80mm - Modern Design
INSERT INTO `receipt_templates` (`name`, `template`, `is_default`, `created_at`, `updated_at`) VALUES
('Thermal 80mm - Modern', '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @media print {
            @page { margin: 0; }
            body { margin: 0.5cm; }
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            width: 300px;
            margin: 0 auto;
            padding: 10px;
            font-size: 11px;
        }
        .header {
            text-align: center;
            padding: 15px 0;
            background: #2c3e50;
            color: white;
            margin: -10px -10px 15px -10px;
            border-radius: 5px 5px 0 0;
        }
        .header h2 {
            margin: 0;
            font-size: 18px;
            letter-spacing: 1px;
        }
        .header p {
            margin: 3px 0;
            font-size: 9px;
            opacity: 0.9;
        }
        .section {
            margin: 15px 0;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .section-title {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .info-line {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
            font-size: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        thead {
            background: #34495e;
            color: white;
        }
        th {
            padding: 8px 4px;
            text-align: left;
            font-size: 10px;
        }
        td {
            padding: 6px 4px;
            border-bottom: 1px solid #ecf0f1;
            font-size: 10px;
        }
        .right { text-align: right; }
        .totals {
            background: #ecf0f1;
            padding: 10px;
            border-radius: 5px;
            margin-top: 15px;
        }
        .totals table {
            margin: 0;
        }
        .totals td {
            border: none;
            padding: 5px 0;
            font-size: 10px;
        }
        .grand-total {
            background: #2c3e50;
            color: white;
            font-weight: bold;
            padding: 8px !important;
            margin: 5px -10px;
            font-size: 13px !important;
        }
        .footer {
            text-align: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px dashed #bdc3c7;
            font-size: 9px;
            color: #7f8c8d;
        }
        .qr-placeholder {
            width: 80px;
            height: 80px;
            background: #ecf0f1;
            margin: 10px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{store_name}}</h2>
        <p>{{store_address}}</p>
        <p>☎ {{store_phone}}</p>
    </div>

    <div class="section">
        <div class="section-title">Receipt Information</div>
        <div class="info-line">
            <span>Receipt #</span>
            <span><strong>{{receipt_number}}</strong></span>
        </div>
        <div class="info-line">
            <span>Date & Time</span>
            <span>{{date}}</span>
        </div>
        <div class="info-line">
            <span>Served By</span>
            <span>{{cashier}}</span>
        </div>
        {{customer}}
    </div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th class="right">Qty</th>
                <th class="right">Price</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            {{items}}
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td>Subtotal</td>
                <td class="right">${{subtotal}}</td>
            </tr>
            <tr>
                <td>Discount</td>
                <td class="right">-${{total_discount}}</td>
            </tr>
            <tr>
                <td>Tax</td>
                <td class="right">${{tax}}</td>
            </tr>
        </table>
        <div class="grand-total">
            <table>
                <tr>
                    <td>TOTAL</td>
                    <td class="right">${{total}}</td>
                </tr>
            </table>
        </div>
        <table>
            <tr>
                <td>Paid</td>
                <td class="right">${{paid}}</td>
            </tr>
            <tr>
                <td>Change</td>
                <td class="right">${{change}}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p><strong>{{store_footer}}</strong></p>
        <div class="qr-placeholder">
            <small>QR Code</small>
        </div>
        <p>Thank you for shopping with us!</p>
        <p style="margin-top: 10px;">⭐⭐⭐⭐⭐</p>
    </div>
</body>
</html>', 0, NOW(), NOW());

-- Note: After inserting, you may need to adjust the is_default value
-- to set which template you want as default.
