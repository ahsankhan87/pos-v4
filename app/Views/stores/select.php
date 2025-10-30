<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="container mx-auto p-4 max-w-md">
    <h1 class="text-2xl font-bold mb-6 text-center">Select Store</h1>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6">
            <?php if (!empty($stores)): ?>
                <div class="space-y-4">
                    <?php foreach ($stores as $store): ?>
                        <a href="<?= base_url("stores/switch/{$store['id']}") ?>" class="block p-4 border rounded-lg hover:bg-gray-50 transition">
                            <h3 class="font-medium text-lg"><?= $store['name'] ?></h3>
                            <p class="text-sm text-gray-500"><?= $store['address'] ?></p>
                            <p class="text-sm text-gray-500"><?= $store['phone'] ?></p>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-8 text-gray-500">
                    No stores available for your account
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>