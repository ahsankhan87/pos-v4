<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="max-w-lg mx-auto bg-white p-8 rounded shadow">
    <h2 class="text-2xl font-bold mb-6">Product Details</h2>
    <?php if (isset($product) && is_array($product)): ?>
        <?php if (!empty($product['barcode'])): ?>
            <div class="mb-4 text-center">
                <img src="<?= site_url('products/barcode_image/' . $product['barcode']) ?>"
                    alt="Barcode for <?= esc($product['barcode']) ?>"
                    class="mx-auto border border-gray-300 rounded p-2"
                    style="height:60px;"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <div style="display:none;" class="barcode-text-fallback bg-gray-100 border border-gray-300 px-4 py-2 rounded mt-2">
                    <span class="text-sm font-mono">Barcode: <?= esc($product['barcode']) ?></span>
                </div>
            </div>
        <?php endif; ?> <div class="mb-4">
            <span class="font-semibold">ID:</span>
            <span><?= $product['id'] ?></span>
        </div>
        <div class="mb-4">
            <span class="font-semibold">Name:</span>
            <span><?= $product['name'] ?></span>
        </div>
        <div class="mb-4">
            <span class="font-semibold">Barcode:</span>
            <span><?= $product['barcode'] ?></span>
        </div>
        <div class="mb-4">
            <span class="font-semibold">Code:</span>
            <span><?= $product['code'] ?></span>
        </div>
        <div class="mb-4">
            <span class="font-semibold">Cost Price:</span>
            <span><?= number_format($product['cost_price'], 2) ?></span>
        </div>
        <div class="mb-4">
            <span class="font-semibold">Price:</span>
            <span><?= number_format($product['price'], 2) ?></span>
        </div>
        <div class="mb-4">
            <span class="font-semibold">Quantity:</span>
            <span><?= number_format($product['quantity'], 2) ?></span>
        </div>
        <div class="mb-4">
            <span class="font-semibold">Stock Alert:</span>
            <span><?= number_format($product['stock_alert'], 2) ?></span>
        </div>

        <div class="mb-6">
            <span class="font-semibold">Description:</span>
            <span><?= $product['description'] ?></span>
        </div>
        <div class="mb-4">
            <span class="font-semibold">Created At:</span>
            <span><?= $product['created_at'] ?></span>
        </div>
        <div class="mb-4">
            <span class="font-semibold">Updated At:</span>
            <span><?= $product['updated_at'] ?></span>
        </div>

    <?php endif; ?>
    <a href="<?= site_url('products') ?>" class="bg-blue-500 text-white px-4 py-2 rounded">Back to List</a>
</div>
<?= $this->endSection() ?>