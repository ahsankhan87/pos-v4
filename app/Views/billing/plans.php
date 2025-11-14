<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="max-w-5xl mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-4">Plans & Pricing</h1>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="mb-3 p-3 bg-red-100 text-red-700 rounded"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('success')): ?>
        <div class="mb-3 p-3 bg-green-100 text-green-700 rounded"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <div class="mb-4">
        <a href="<?= site_url('billing/manage') ?>" class="mb-4 inline-block px-3 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
            &larr; Back to Subscription
        </a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        <?php
        $currentPlanId = $subscription['plan_id'] ?? null;
        $nextPlanId = $subscription['next_plan_id'] ?? null;
        ?>
        <?php foreach ($plans as $plan): ?>
            <?php
            $isCurrent = $currentPlanId && ((int)$currentPlanId === (int)$plan['id']);
            $isScheduled = $nextPlanId && ((int)$nextPlanId === (int)$plan['id']);
            ?>
            <div class="bg-white shadow rounded p-4">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <h2 class="text-xl font-semibold"><?= esc($plan['name']) ?></h2>
                        <?php if ($isCurrent): ?>
                            <span class="px-2 py-0.5 text-xs rounded bg-green-100 text-green-800">Current</span>
                        <?php elseif ($isScheduled): ?>
                            <span class="px-2 py-0.5 text-xs rounded bg-yellow-100 text-yellow-800">Scheduled</span>
                        <?php endif; ?>
                    </div>
                    <span class="text-gray-600">
                        <?= esc($plan['currency']) ?> <?= number_format($plan['price_monthly'], 2) ?>/mo
                    </span>
                </div>
                <div class="mt-2 text-sm text-gray-700">
                    <?php $features = json_decode($plan['features'] ?? '[]', true) ?: []; ?>
                    <ul class="list-disc ml-5">
                        <?php foreach ($features as $key => $enabled): ?>
                            <li><?= esc(ucwords(str_replace('_', ' ', $key))) ?>: <?= $enabled ? 'Yes' : 'No' ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="mt-4">
                    <?php if ($isCurrent): ?>
                        <button class="px-3 py-2 bg-gray-200 text-gray-600 rounded cursor-not-allowed" disabled>Current Plan</button>
                    <?php elseif ($isScheduled): ?>
                        <button class="px-3 py-2 bg-gray-200 text-gray-600 rounded cursor-not-allowed" disabled>Scheduled</button>
                        <span class="ml-2 text-xs text-yellow-700">(will switch on renewal)</span>
                    <?php else: ?>
                        <a class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" href="<?= site_url('billing/subscribe/' . urlencode($plan['code'])) ?>">
                            Choose <?= esc($plan['name']) ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="mt-8">
        <h3 class="text-lg font-semibold mb-2">Have a license code?</h3>
        <a class="px-3 py-2 bg-emerald-600 text-white rounded" href="<?= site_url('billing/activate') ?>">Activate License</a>
    </div>
</div>
<?= $this->endSection() ?>