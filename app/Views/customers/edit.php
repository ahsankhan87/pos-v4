<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?= session()->getFlashdata('error') ?>
<?= validation_list_errors() ?>
<?php if (isset($customer) && is_array($customer)): ?>
    <div class="max-w-lg mx-auto bg-white p-8 rounded shadow">
        <h2 class="text-2xl font-bold mb-6">Edit Customer</h2>
        <form method="post" action="<?= site_url('customers/update/' . $customer['id']) ?>">
            <?= csrf_field() ?>
            <div class="mb-4">
                <label class="block mb-1 font-semibold">Name</label>
                <input type="text" name="name" value="<?= esc($customer['name']) ?>" class="w-full border rounded px-3 py-2" required>
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-semibold">Email</label>
                <input type="email" name="email" value="<?= esc($customer['email']) ?>" class="w-full border rounded px-3 py-2" required>
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-semibold">Phone</label>
                <input type="text" name="phone" value="<?= esc($customer['phone']) ?>" class="w-full border rounded px-3 py-2" required>
            </div>
            <div class="mb-6">
                <label class="block mb-1 font-semibold">Address</label>
                <input type="text" name="address" value="<?= esc($customer['address']) ?>" class="w-full border rounded px-3 py-2" required>
            </div>

            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Update</button>
            <a href="<?= site_url('customers') ?>" class="ml-4 text-gray-600 hover:underline">Cancel</a>
        </form>
    </div>
<?php endif; ?>
<?php if (!isset($customer) || !is_array($customer)): ?>
    <p>Customer not found.</p>
<?php endif; ?>
<?= $this->endSection() ?>