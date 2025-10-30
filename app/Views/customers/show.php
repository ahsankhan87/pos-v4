<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="max-w-lg mx-auto bg-white p-8 rounded shadow">
    <h2 class="text-2xl font-bold mb-6">Customer Details</h2>
    <div class="mb-4">
        <span class="font-semibold">ID:</span>
        <span><?= $customer['id'] ?></span>
    </div>
    <div class="mb-4">
        <span class="font-semibold">Loyalty Points:</span>
        <span><?= esc($customer['points']) ?></span>
    </div>
    <div class="mb-4">
        <span class="font-semibold">Name:</span>
        <span><?= $customer['name'] ?></span>
    </div>
    <div class="mb-4">
        <span class="font-semibold">Email:</span>
        <span><?= $customer['email'] ?></span>
    </div>
    <div class="mb-4">
        <span class="font-semibold">Phone:</span>
        <span><?= $customer['phone'] ?></span>
    </div>
    <div class="mb-4">
        <span class="font-semibold">Address:</span>
        <span><?= $customer['address'] ?></span>
    </div>
    <div class="mb-6">
        <span class="font-semibold">Created At:</span>
        <span><?= $customer['created_at'] ?></span>
    </div>
    <a href="<?= site_url('customers') ?>" class="bg-blue-500 text-white px-4 py-2 rounded">Back to List</a>

</div>
<?= $this->endSection() ?>