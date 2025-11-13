<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 10px;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
        }

        .header h2 {
            margin: 0;
            font-size: 18px;
        }

        .header p {
            margin: 2px 0;
            font-size: 10px;
        }

        .supplier-info {
            margin-bottom: 10px;
        }

        .supplier-info table {
            width: 100%;
        }

        .supplier-info td {
            padding: 2px;
            font-size: 10px;
        }

        table.ledger {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        table.ledger th,
        table.ledger td {
            border: 1px solid #000;
            padding: 4px;
            text-align: left;
            font-size: 10px;
        }

        table.ledger th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total-row {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .opening-row {
            background-color: #e3f2fd;
            font-weight: bold;
        }

        .summary {
            margin-top: 15px;
            padding: 8px;
            border: 1px solid #000;
            background-color: #f9f9f9;
        }

        .summary h4 {
            margin-top: 0;
            margin-bottom: 5px;
            font-size: 12px;
        }

        .summary p {
            margin: 2px 0;
            font-size: 10px;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                margin: 5px;
            }

            .header {
                margin-bottom: 10px;
                padding-bottom: 3px;
            }

            .supplier-info {
                margin-bottom: 8px;
            }

            table.ledger {
                margin-bottom: 8px;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>SUPPLIER LEDGER</h2>
        <p style="margin: 5px 0;">Period: <?= date('d M Y', strtotime($from)) ?> to <?= date('d M Y', strtotime($to)) ?></p>
    </div>

    <div class="supplier-info">
        <table>
            <tr>
                <td style="width: 50%;"><strong>Supplier Name:</strong> <?= esc($supplier['name']) ?></td>
                <td style="width: 50%;"><strong>Phone:</strong> <?= esc($supplier['phone']) ?></td>
            </tr>
            <tr>
                <td><strong>Email:</strong> <?= esc($supplier['email']) ?></td>
                <td><strong>Date:</strong> <?= date('d M Y') ?></td>
            </tr>
            <tr>
                <td colspan="2"><strong>Address:</strong> <?= esc($supplier['address']) ?></td>
            </tr>
        </table>
    </div>

    <table class="ledger">
        <thead>
            <tr>
                <th style="width: 10%;">Date</th>
                <th style="width: 40%;">Description</th>
                <th style="width: 10%;">Ref</th>
                <th style="width: 13%;" class="text-end">Debit (Dr)</th>
                <th style="width: 13%;" class="text-end">Credit (Cr)</th>
                <th style="width: 14%;" class="text-end">Balance</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($openingBalance != 0): ?>
                <tr class="opening-row">
                    <td><?= date('d M Y', strtotime($from)) ?></td>
                    <td>Opening Balance</td>
                    <td class="text-center">-</td>
                    <td class="text-end">-</td>
                    <td class="text-end">-</td>
                    <td class="text-end"><?= number_to_currency($openingBalance, 'PKR', 'en_PK', 2) ?></td>
                </tr>
            <?php endif; ?>

            <?php if (!empty($transactions)): ?>
                <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <td><?= date('d M Y', strtotime($transaction['date'])) ?></td>
                        <td><?= esc($transaction['description']) ?></td>
                        <td class="text-center">
                            <?= $transaction['purchase_id'] ? 'PO-' . $transaction['purchase_id'] : '-' ?>
                        </td>
                        <td class="text-end">
                            <?= $transaction['debit'] > 0 ? number_to_currency($transaction['debit'], 'PKR', 'en_PK', 2) : '-' ?>
                        </td>
                        <td class="text-end">
                            <?= $transaction['credit'] > 0 ? number_to_currency($transaction['credit'], 'PKR', 'en_PK', 2) : '-' ?>
                        </td>
                        <td class="text-end">
                            <?= number_to_currency($transaction['running_balance'], 'PKR', 'en_PK', 2) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">No transactions found for the selected period</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <?php if (!empty($transactions)): ?>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                    <td class="text-end"><strong><?= number_to_currency($totalDebit, 'PKR', 'en_PK', 2) ?></strong></td>
                    <td class="text-end"><strong><?= number_to_currency($totalCredit, 'PKR', 'en_PK', 2) ?></strong></td>
                    <td class="text-end"><strong><?= number_to_currency($closingBalance, 'PKR', 'en_PK', 2) ?></strong></td>
                </tr>
            </tfoot>
        <?php endif; ?>
    </table>

    <?php if (!empty($transactions)): ?>
        <div class="summary">
            <h4 style="margin-top: 0;">Summary</h4>
            <p>Opening Balance: <strong><?= number_to_currency($openingBalance, 'PKR', 'en_PK', 2) ?></strong></p>
            <p>Total Purchases (Debit): <strong><?= number_to_currency($totalDebit, 'PKR', 'en_PK', 2) ?></strong></p>
            <p>Total Payments (Credit): <strong><?= number_to_currency($totalCredit, 'PKR', 'en_PK', 2) ?></strong></p>
            <p>Closing Balance: <strong><?= number_to_currency($closingBalance, 'PKR', 'en_PK', 2) ?></strong></p>
            <?php if ($closingBalance > 0): ?>
                <p style="color: red;"><em>Amount Payable to Supplier</em></p>
            <?php elseif ($closingBalance < 0): ?>
                <p style="color: green;"><em>Amount Receivable from Supplier</em></p>
            <?php else: ?>
                <p style="color: blue;"><em>Account Settled</em></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="no-print" style="margin-top: 15px; text-align: center;">
        <button onclick="window.print()" style="padding: 8px 16px; font-size: 12px;">Print</button>
        <button onclick="window.close()" style="padding: 8px 16px; font-size: 12px;">Close</button>
    </div>

    <script>
        // Auto print when page loads
        window.onload = function() {
            // Uncomment the line below to auto-print
            // window.print();
        };
    </script>
</body>

</html>