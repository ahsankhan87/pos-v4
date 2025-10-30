<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<?= session()->getFlashdata('error') ?>
<?= validation_list_errors() ?>

<div class="max-w-lg mx-auto bg-white p-8 rounded shadow">
    <h2 class="text-2xl font-bold mb-6">Add Customer</h2>
    <form method="post" action="<?= site_url('customers/create') ?>">
        <?= csrf_field() ?>
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Name</label>
            <input type="text" name="name" value="<?= set_value('name') ?>" class="w-full border rounded px-3 py-2" required>
        </div>
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Email</label>
            <input type="email" name="email" value="<?= set_value('email') ?>" class="w-full border rounded px-3 py-2" required>
        </div>
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Phone</label>
            <input type="text" name="phone" value="<?= set_value('phone') ?>" class="w-full border rounded px-3 py-2" required>
        </div>
        <div class="mb-6">
            <label class="block mb-1 font-semibold">Address</label>
            <input type="text" name="address" value="<?= set_value('address') ?>" class="w-full border rounded px-3 py-2" required>
        </div>
        <input type="hidden" name="created_at" value="<?= date('Y-m-d H:i:s') ?>">
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Save</button>
        <a href="<?= site_url('customers') ?>" class="ml-4 text-gray-600 hover:underline">Cancel</a>
    </form>
</div>
<?= $this->endSection() ?>