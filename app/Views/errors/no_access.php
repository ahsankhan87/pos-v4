<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="max-w-xl mx-auto p-8 text-center">
    <div class="text-red-600 text-6xl mb-4"><i class="fas fa-ban"></i></div>
    <h1 class="text-2xl font-bold mb-2">No Access</h1>
    <p class="text-gray-600 mb-6">You do not have permission to access this page or perform this action.</p>
    <a href="<?= site_url('/') ?>" class="inline-block bg-blue-600 text-white px-4 py-2 rounded">Go to Dashboard</a>
    <?php if (session('role_id')): ?>
        <p class="text-sm text-gray-500 mt-4">If you believe this is a mistake, please contact your administrator.</p>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="mt-4 text-sm text-red-700 bg-red-50 p-3 rounded">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>