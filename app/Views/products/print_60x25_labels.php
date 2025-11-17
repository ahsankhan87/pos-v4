<?php

/** @var array $labels */
/** @var string $currencySymbol */
/** @var bool $showPrice */
/** @var float $barcodeHeightMm */
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>Print Labels 60×25</title>
    <style>
        :root {
            --barcode-height-mm: <?= isset($barcodeHeightMm) ? (float)$barcodeHeightMm : 12.0 ?>mm;
            --pad-extra-mm: <?= isset($padMm) ? (float)$padMm : 0.0 ?>mm;
        }

        @page {
            size: 60mm 25mm;
            margin: 0;
        }

        html,
        body {
            width: 60mm;
            height: 25mm;
            margin: 0;
            padding: 0;
            background: #fff;
        }

        /* One label per page */
        .label {
            width: 60mm;
            height: 25mm;
            box-sizing: border-box;
            /* base top/bottom is 1.8mm; allow extra via pad parameter */
            padding: calc(1.8mm + var(--pad-extra-mm, 0mm)) 2mm;
            /* tune if needed */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 1mm;
            page-break-after: always;
            text-align: center;
        }

        .name {
            font-size: 8pt;
            /* compact to fit height */
            font-weight: 700;
            color: #111827;
            text-align: center;
            line-height: 1.1;
            max-width: 56mm;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .price {
            font-size: 7pt;
            font-weight: 600;
            color: #111827;
        }

        .barcode {
            /* keep fixed height, center horizontally */
            height: var(--barcode-height-mm);
            display: block;
            margin: 0 auto;
            width: auto;
            max-width: 100%;
            object-fit: contain;
        }

        .code {
            font-size: 6pt;
            letter-spacing: 0.04em;
            color: #111827;
        }

        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body style="text-align: center;">
    <div class="no-print" style="margin: 15px; text-align: center;">
        <button onclick="window.print()" style="padding: 8px 16px; font-size: 12px;">Print</button>
        <button onclick="window.close()" style="padding: 8px 16px; font-size: 12px;">Close</button>
    </div>

    <?php foreach ($labels as $row): ?>
        <section class="label">
            <div class="name"><?= esc($row['name'] ?? '') ?></div>
            <?php if (!empty($showPrice) && ($row['price'] ?? null) !== null && $row['price'] !== ''): ?>
                <div class="price"><?= esc($currencySymbol) ?> <?= number_format((float)$row['price'], 2) ?></div>
            <?php endif; ?>
            <?php $img = barcode_image($row['barcode'] ?? ''); ?>
            <img class="barcode" src="<?= $img ?>" alt="barcode" />
            <?php if (!empty($row['barcode'])): ?>
                <div class="code"><?= esc($row['barcode']) ?></div>
            <?php endif; ?>
        </section>
    <?php endforeach; ?>
    <script>
        // Auto-print when opened
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 200);
        });
    </script>
</body>

</html>