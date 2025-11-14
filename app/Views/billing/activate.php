<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="max-w-xl mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-4">Activate License</h1>
    <?php if (!empty($error)): ?>
        <div class="mb-3 p-3 bg-red-100 text-red-700 rounded"><?= esc($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="mb-3 p-3 bg-green-100 text-green-700 rounded"><?= esc($success) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="mb-3 p-3 bg-red-100 text-red-700 rounded"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('success')): ?>
        <div class="mb-3 p-3 bg-green-100 text-green-700 rounded"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= site_url('billing/activate') ?>" class="bg-white shadow rounded p-4">
        <?= csrf_field() ?>
        <label class="block text-sm font-medium text-gray-700 mb-1">License Code</label>
        <input type="text" name="code" class="w-full border rounded px-3 py-2" placeholder="Enter license code (e.g., KAS.xxxxx)" required>
        <button class="mt-3 px-3 py-2 bg-emerald-600 text-white rounded" type="submit">Activate</button>
        <button class="mt-3 ml-2 px-3 py-2 bg-gray-200 text-gray-700 rounded" type="reset">Clear</button>
        <a href="<?= site_url('billing/manage') ?>" class="mt-3 ml-2 inline-block px-3 py-2 bg-gray-200 text-gray-700 rounded">Back</a>
    </form>
    <?php if (!empty($subscription)): ?>
        <div class="mt-6 bg-white shadow rounded p-4">
            <h2 class="text-lg font-semibold mb-2">Current Subscription</h2>
            <div><strong>Status:</strong> <?= esc($subscription['status']) ?></div>
            <div><strong>Plan ID:</strong> <?= esc($subscription['plan_id']) ?></div>
            <div><strong>Renews At:</strong> <?= esc($subscription['renews_at'] ?? '-') ?></div>
            <div><strong>Trial Ends:</strong> <?= esc($subscription['trial_ends_at'] ?? '-') ?></div>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>