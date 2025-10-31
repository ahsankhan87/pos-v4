<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="max-w-md mx-auto bg-white rounded-xl shadow-lg mt-8 p-8">
    <h2 class="text-2xl font-bold mb-6 text-blue-700">Receive Payment for Invoice #<?= esc($sale['invoice_no']) ?></h2>
    <form method="post">
        <?= csrf_field() ?>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Due Amount</label>
            <input type="text" value="<?= number_format($sale['due_amount'], 2) ?>" readonly class="w-full border rounded px-3 py-2 bg-gray-100">
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Payment Amount</label>
            <input type="number" name="amount" min="1" max="<?= $sale['due_amount'] ?>" step="0.01" required class="w-full border rounded px-3 py-2">
        </div>
        <div class="flex justify-end">
            <a href="<?= site_url('sales') ?>" class="btn btn-secondary mr-2">Cancel</a>
            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 font-bold">Receive Payment</button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>