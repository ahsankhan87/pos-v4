<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="max-w-lg mx-auto bg-white p-8 rounded shadow">
    <h2 class="text-2xl font-bold mb-6">Supplier Details</h2>
    <div class="mb-4">
        <span class="font-semibold">ID:</span>
        <span><?= $supplier['id'] ?></span>
    </div>
    <div class="mb-4">
        <span class="font-semibold">Name:</span>
        <span><?= $supplier['name'] ?></span>
    </div>
    <div class="mb-4">
        <span class="font-semibold">Email:</span>
        <span><?= $supplier['email'] ?></span>
    </div>
    <div class="mb-4">
        <span class="font-semibold">Phone:</span>
        <span><?= $supplier['phone'] ?></span>
    </div>
    <div class="mb-4">
        <span class="font-semibold">Address:</span>
        <span><?= $supplier['address'] ?></span>
    </div>
    <div class="mb-6">
        <span class="font-semibold">Created At:</span>
        <span><?= $supplier['created_at'] ?></span>
    </div>
    <a href="<?= site_url('suppliers') ?>" class="bg-blue-500 text-white px-4 py-2 rounded">Back to List</a>

</div>
<?= $this->endSection() ?>