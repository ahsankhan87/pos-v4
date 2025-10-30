<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 no-print">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Print Barcode Labels</h1>
            <p class="mt-1 text-sm text-gray-500">Adjust the number of copies for each product, then print the labels directly.</p>
        </div>
        <div class="flex gap-3 no-print">
            <button type="button" id="resetCopies" class="btn btn-secondary btn-sm">
                <span>Reset Copies</span>
            </button>
            <button type="button" id="printLabels" class="btn btn-primary">
                <i class="fas fa-print"></i>
                <span>Print Labels</span>
            </button>
        </div>
    </div>

    <div class="table-card mt-6 no-print">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Label Copies</h2>
            <span class="text-sm text-gray-500">Total labels: <span id="totalLabels">0</span></span>
        </div>
        <div class="p-6 space-y-6">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <?php foreach ($products as $product): ?>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-sm font-semibold text-slate-900"><?= esc($product['name']) ?></div>
                        <?php if (!empty($product['code'])): ?>
                            <div class="text-xs text-slate-500 mt-1"><?= esc($product['code']) ?></div>
                        <?php else: ?>
                            <div class="text-xs text-slate-400 mt-1">No code</div>
                        <?php endif; ?>
                        <label class="mt-4 block text-xs font-medium text-slate-500 uppercase tracking-wide" for="copies-<?= (int) $product['id'] ?>">
                            Copies
                        </label>
                        <input
                            type="number"
                            id="copies-<?= (int) $product['id'] ?>"
                            class="mt-2 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-blue-500 focus:outline-none"
                            min="0"
                            max="100"
                            value="1"
                            data-product="<?= (int) $product['id'] ?>">
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="pt-6 mt-4 border-t border-gray-100">
                <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wide">Label sizing</h3>
                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    <label class="flex flex-col">
                        <span class="text-xs font-medium text-slate-500 uppercase tracking-wide">Width (mm)</span>
                        <input
                            type="number"
                            id="labelWidth"
                            class="mt-2 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-blue-500 focus:outline-none"
                            min="20"
                            max="120"
                            step="1"
                            value="48">
                    </label>
                    <label class="flex flex-col">
                        <span class="text-xs font-medium text-slate-500 uppercase tracking-wide">Height (mm)</span>
                        <input
                            type="number"
                            id="labelHeight"
                            class="mt-2 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-blue-500 focus:outline-none"
                            min="15"
                            max="80"
                            step="1"
                            value="28">
                    </label>
                    <label class="flex flex-col">
                        <span class="text-xs font-medium text-slate-500 uppercase tracking-wide">Gap (mm)</span>
                        <input
                            type="number"
                            id="labelGap"
                            class="mt-2 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-blue-500 focus:outline-none"
                            min="1"
                            max="20"
                            step="0.5"
                            value="6">
                    </label>
                    <label class="flex flex-col">
                        <span class="text-xs font-medium text-slate-500 uppercase tracking-wide">Padding (mm)</span>
                        <input
                            type="number"
                            id="labelPadding"
                            class="mt-2 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-blue-500 focus:outline-none"
                            min="1"
                            max="15"
                            step="0.5"
                            value="4">
                    </label>
                    <label class="flex flex-col">
                        <span class="text-xs font-medium text-slate-500 uppercase tracking-wide">Barcode height (mm)</span>
                        <input
                            type="number"
                            id="barcodeHeight"
                            class="mt-2 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-blue-500 focus:outline-none"
                            min="10"
                            max="40"
                            step="1"
                            value="18">
                    </label>
                </div>
                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <button type="button" id="resetLabelSize" class="btn btn-secondary btn-sm">Reset sizing</button>
                    <span class="text-xs text-slate-500">Adjust the label dimensions to match your paper size or sticker roll before printing.</span>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-10">
        <div id="labelsGrid" class="labels-grid"></div>
    </div>
</div>

<style>
    :root {
        --label-width-mm: 48;
        --label-height-mm: 28;
        --label-gap-mm: 6;
        --label-padding-mm: 4;
        --label-barcode-height-mm: 18;
    }

    @page {
        size: auto;
        margin: 6mm;
    }

    .labels-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(calc(var(--label-width-mm, 48) * 1mm), 1fr));
        gap: calc(var(--label-gap-mm, 6) * 1mm);
        justify-items: center;
    }

    .label-card {
        border: 1px solid #cbd5f5;
        border-radius: 0.5rem;
        padding: calc(var(--label-padding-mm, 4) * 1mm);
        text-align: center;
        background: #fff;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 0.1rem;
        width: 100%;
        max-width: calc(var(--label-width-mm, 48) * 1mm);
        min-height: calc(var(--label-height-mm, 28) * 1mm);
        box-sizing: border-box;
    }

    .label-name {
        font-size: 0.9rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.2;
        text-transform: uppercase;
    }

    .label-meta {
        font-size: 0.8rem;
        color: #0f172a;
        letter-spacing: 0.08em;
        font-weight: 600;
    }

    .label-card img {
        max-height: calc(var(--label-barcode-height-mm, 18) * 1mm);
        width: 100%;
        object-fit: contain;
        margin: 0 auto;
    }

    .label-barcode-text {
        font-size: 0.65rem;
        color: #1e293b;
        letter-spacing: 0.05em;
    }

    @media print {

        .no-print,
        header,
        footer,
        nav,
        aside,
        .app-sidebar,
        .app-header,
        .app-footer {
            display: none !important;
        }

        body {
            background: #fff;
        }

        .labels-grid {
            grid-template-columns: repeat(auto-fit, minmax(calc(var(--label-width-mm, 48) * 1mm), 1fr));
            gap: calc(var(--label-gap-mm, 6) * 1mm);
        }

        .label-card {
            max-width: calc(var(--label-width-mm, 48) * 1mm);
            width: 100%;
            min-height: calc(var(--label-height-mm, 28) * 1mm);
            padding: calc(var(--label-padding-mm, 4) * 1mm);
            border-radius: 0.3rem;
            border-color: #94a3b8;
            gap: 0.3rem;
        }

        .label-card img {
            max-height: calc(var(--label-barcode-height-mm, 18) * 1mm);
        }

        .label-barcode-text {
            font-size: 0.6rem;
        }
    }
</style>

<script>
    (() => {
        const currencySymbol = <?= json_encode($currencySymbol ?? '') ?>;

        const productsData = <?= json_encode(array_map(static function ($product) {
                                    $price = $product['price'] ?? null;
                                    if ($price === '' || $price === null) {
                                        $priceValue = null;
                                    } else {
                                        $priceValue = (float) $price;
                                    }

                                    return [
                                        'id' => (int) $product['id'],
                                        'name' => $product['name'],
                                        'barcode' => $product['barcode'],
                                        'price' => $priceValue,
                                        'image' => barcode_image($product['barcode']),
                                    ];
                                }, $products), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

        const labelsGrid = document.getElementById('labelsGrid');
        const totalLabelsEl = document.getElementById('totalLabels');
        const copiesInputs = Array.from(document.querySelectorAll('[data-product]'));
        const resetButton = document.getElementById('resetCopies');
        const printButton = document.getElementById('printLabels');
        const labelSizeResetButton = document.getElementById('resetLabelSize');
        const root = document.documentElement;

        const dimensionInputs = {
            width: document.getElementById('labelWidth'),
            height: document.getElementById('labelHeight'),
            gap: document.getElementById('labelGap'),
            padding: document.getElementById('labelPadding'),
            barcode: document.getElementById('barcodeHeight'),
        };

        const dimensionDefaults = {
            width: 48,
            height: 28,
            gap: 6,
            padding: 4,
            barcode: 18,
        };

        const dimensionVarMap = {
            width: '--label-width-mm',
            height: '--label-height-mm',
            gap: '--label-gap-mm',
            padding: '--label-padding-mm',
            barcode: '--label-barcode-height-mm',
        };

        function applyDimensionValue(key, value) {
            const input = dimensionInputs[key];
            if (!input) {
                return;
            }

            const min = input.min === '' ? null : Number(input.min);
            const max = input.max === '' ? null : Number(input.max);
            let coerced = Number(value);

            if (!Number.isFinite(coerced)) {
                coerced = dimensionDefaults[key];
            }

            if (min !== null && coerced < min) {
                coerced = min;
            }

            if (max !== null && coerced > max) {
                coerced = max;
            }

            input.value = coerced;
            root.style.setProperty(dimensionVarMap[key], coerced.toString());
        }

        function syncDimensionsFromInputs() {
            Object.keys(dimensionInputs).forEach((key) => {
                const input = dimensionInputs[key];
                if (!input) {
                    return;
                }
                applyDimensionValue(key, input.value);
            });
        }

        function formatPrice(value) {
            if (value === null || Number.isNaN(value)) {
                return null;
            }
            const formatted = Number(value).toFixed(2);
            return currencySymbol ? `${currencySymbol} ${formatted}` : formatted;
        }

        function buildLabel(product) {
            const card = document.createElement('div');
            card.className = 'label-card';

            const nameEl = document.createElement('div');
            nameEl.className = 'label-name';
            nameEl.textContent = product.name;
            card.appendChild(nameEl);

            const priceFormatted = formatPrice(product.price);
            if (priceFormatted !== null) {
                const metaEl = document.createElement('div');
                metaEl.className = 'label-meta';
                metaEl.textContent = priceFormatted;
                card.appendChild(metaEl);
            }

            const imgEl = document.createElement('img');
            imgEl.src = product.image;
            imgEl.alt = 'Barcode for ' + product.name;
            card.appendChild(imgEl);

            if (product.barcode) {
                const barcodeTextEl = document.createElement('div');
                barcodeTextEl.className = 'label-barcode-text';
                barcodeTextEl.textContent = product.barcode;
                card.appendChild(barcodeTextEl);
            }

            return card;
        }

        function sanitizeCopies(value) {
            const parsed = Number(value);
            if (!Number.isFinite(parsed) || parsed < 0) {
                return 0;
            }
            return Math.min(100, Math.floor(parsed));
        }

        function getCopiesMap() {
            const copies = new Map();
            copiesInputs.forEach((input) => {
                const productId = Number(input.dataset.product);
                const sanitized = sanitizeCopies(input.value);
                input.value = sanitized;
                copies.set(productId, sanitized || 0);
            });
            return copies;
        }

        function renderLabels() {
            const copies = getCopiesMap();
            labelsGrid.innerHTML = '';
            let total = 0;

            productsData.forEach((product) => {
                const count = copies.get(product.id) ?? 1;
                for (let i = 0; i < count; i += 1) {
                    labelsGrid.appendChild(buildLabel(product));
                    total += 1;
                }
            });

            if (total === 0) {
                const emptyState = document.createElement('div');
                emptyState.className = 'text-sm text-slate-400 text-center';
                emptyState.textContent = 'No labels to display. Increase the copy count to add labels.';
                emptyState.style.gridColumn = '1 / -1';
                labelsGrid.appendChild(emptyState);
            }

            if (totalLabelsEl) {
                totalLabelsEl.textContent = total;
            }
        }

        copiesInputs.forEach((input) => {
            input.addEventListener('input', renderLabels);
        });

        Object.entries(dimensionInputs).forEach(([key, input]) => {
            if (!input) {
                return;
            }
            input.addEventListener('input', () => {
                applyDimensionValue(key, input.value);
                renderLabels();
            });
        });

        if (resetButton) {
            resetButton.addEventListener('click', () => {
                copiesInputs.forEach((input) => {
                    input.value = 1;
                });
                renderLabels();
            });
        }

        if (labelSizeResetButton) {
            labelSizeResetButton.addEventListener('click', () => {
                Object.entries(dimensionDefaults).forEach(([key, value]) => {
                    const input = dimensionInputs[key];
                    if (!input) {
                        return;
                    }
                    input.value = value;
                    root.style.setProperty(dimensionVarMap[key], value.toString());
                });
                renderLabels();
            });
        }

        if (printButton) {
            printButton.addEventListener('click', () => {
                window.print();
            });
        }

        syncDimensionsFromInputs();
        renderLabels();
    })();
</script>

<?= $this->endSection() ?>