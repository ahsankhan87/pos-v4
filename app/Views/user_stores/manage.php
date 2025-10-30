<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-6">Manage Stores for <?= $user['username'] ?></h1>

    <form action="<?= base_url("user-stores/update/{$user['id']}") ?>" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Select Stores:</label>
        </div>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-4 text-red-600">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6">
                <?php if (empty($stores)): ?>
                    <p class="text-gray-500">No stores available</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($stores as $store): ?>
                            <div class="flex items-center">
                                <input type="checkbox" name="stores[]" id="store_<?= $store['id'] ?>"
                                    value="<?= $store['id'] ?>"
                                    <?= in_array($store['id'], $currentStores) ? 'checked' : '' ?>
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="store_<?= $store['id'] ?>" class="ml-3 block text-sm font-medium text-gray-700">
                                    <?= $store['name'] ?> - <?= $store['address'] ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="px-6 py-4 bg-gray-50 flex justify-end">
                <a href="<?= base_url('users') ?>" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 mr-3">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Save Changes
                </button>
            </div>
        </div>
    </form>
</div>
<?= $this->endSection() ?>