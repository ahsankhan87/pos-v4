<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="max-w-xl mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-4"><?= lang('Billing.activateLicense') ?></h1>
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
        <label class="block text-sm font-medium text-gray-700 mb-1"><?= lang('Billing.licenseCode') ?></label>
        <input type="text" name="code" class="w-full border rounded px-3 py-2" placeholder="<?= lang('Billing.enterLicenseCodeExample') ?>" required>
        <button class="mt-3 px-3 py-2 bg-emerald-600 text-white rounded" type="submit"><?= lang('Billing.activate') ?></button>
        <button class="mt-3 ml-2 px-3 py-2 bg-gray-200 text-gray-700 rounded" type="reset"><?= lang('Billing.clear') ?></button>
        <a href="<?= site_url('billing/manage') ?>" class="mt-3 ml-2 inline-block px-3 py-2 bg-gray-200 text-gray-700 rounded"><?= lang('Billing.back') ?></a>
    </form>
    <?php if (!empty($subscription)): ?>
        <div class="mt-6 bg-white shadow rounded p-4">
            <h2 class="text-lg font-semibold mb-2"><?= lang('Billing.currentSubscription') ?></h2>
            <div><strong><?= lang('Billing.status') ?>:</strong> <?= esc($subscription['status']) ?></div>
            <div><strong><?= lang('Billing.planId') ?>:</strong> <?= esc($subscription['plan_id']) ?></div>
            <div><strong><?= lang('Billing.renewsAt') ?>:</strong> <?= esc($subscription['renews_at'] ?? '-') ?></div>
            <div><strong><?= lang('Billing.trialEnds') ?>:</strong> <?= esc($subscription['trial_ends_at'] ?? '-') ?></div>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>