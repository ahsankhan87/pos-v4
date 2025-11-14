<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="max-w-5xl mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold">Subscription</h1>
        <a class="px-3 py-2 bg-blue-600 text-white rounded shadow hover:bg-blue-700" href="<?= site_url('billing/plans') ?>">Browse Plans</a>
    </div>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="mb-3 p-3 bg-red-100 text-red-700 rounded"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('success')): ?>
        <div class="mb-3 p-3 bg-green-100 text-green-700 rounded"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <div class="bg-white shadow rounded p-4">
        <?php if ($subscription): ?>
            <?php
            helper('subscription');
            $info = subscription_info();
            $planModel = new \App\Models\PlanModel();
            $plan = $planModel->find($subscription['plan_id']);
            $planName = $plan['name'] ?? 'Unknown Plan';
            $now = time();
            $target = null;
            if ($subscription['status'] === 'trialing' && !empty($subscription['trial_ends_at'])) {
                $target = strtotime($subscription['trial_ends_at']);
            } elseif (!empty($subscription['renews_at'])) {
                $target = strtotime($subscription['renews_at']);
            } elseif (!empty($subscription['ends_at'])) {
                $target = strtotime($subscription['ends_at']);
            }
            $daysLeft = $info['days_left'] ?? ($target ? max(0, (int) ceil(($target - $now) / 86400)) : null);
            $statusBadge = 'bg-gray-100 text-gray-700';
            if ($subscription['status'] === 'active') {
                $statusBadge = 'bg-green-100 text-green-800';
            }
            if ($subscription['status'] === 'trialing') {
                $statusBadge = 'bg-yellow-100 text-yellow-800';
            }
            if ($subscription['status'] === 'expired') {
                $statusBadge = 'bg-red-100 text-red-800';
            }
            // Color for Days Left badge
            $daysBadge = 'bg-gray-100 text-gray-700';
            if ($daysLeft !== null) {
                if ($daysLeft <= 3) {
                    $daysBadge = 'bg-red-100 text-red-800';
                } elseif ($daysLeft <= 9) {
                    $daysBadge = 'bg-yellow-100 text-yellow-800';
                } else {
                    $daysBadge = 'bg-green-100 text-green-800';
                }
            }
            // Colored dot next to days text
            $dotBadge = 'bg-gray-400';
            if ($daysLeft !== null) {
                if ($daysLeft <= 3) {
                    $dotBadge = 'bg-red-500';
                } elseif ($daysLeft <= 9) {
                    $dotBadge = 'bg-yellow-500';
                } else {
                    $dotBadge = 'bg-green-500';
                }
            }
            // Tooltip with exact date context
            $dateHintLabel = '-';
            $dateHintValue = null;
            if ($subscription['status'] === 'trialing' && !empty($subscription['trial_ends_at'])) {
                $dateHintLabel = 'Trial ends';
                $dateHintValue = $subscription['trial_ends_at'];
            } elseif (!empty($subscription['renews_at'])) {
                $dateHintLabel = 'Renews at';
                $dateHintValue = $subscription['renews_at'];
            } elseif (!empty($subscription['ends_at'])) {
                $dateHintLabel = 'Ends at';
                $dateHintValue = $subscription['ends_at'];
            }
            $daysTitle = $dateHintValue ? ($dateHintLabel . ': ' . $dateHintValue) : '';
            $features = $planModel->features($plan);
            ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                <div class="lg:col-span-2 space-y-4">
                    <div class="border rounded-md p-4">
                        <div class="flex items-center justify-between">
                            <div class="text-lg font-semibold">Current Plan: <?= esc($planName) ?></div>
                            <span class="px-2 py-1 text-xs rounded <?= $statusBadge ?>"><?= esc(ucfirst($subscription['status'])) ?></span>
                        </div>
                        <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                            <div class="bg-gray-50 rounded p-3">
                                <div class="text-gray-500">Renews At</div>
                                <div class="font-medium"><?= esc($subscription['renews_at'] ?? '-') ?></div>
                            </div>
                            <div class="bg-gray-50 rounded p-3">
                                <div class="text-gray-500">Trial Ends</div>
                                <div class="font-medium"><?= esc($subscription['trial_ends_at'] ?? '-') ?></div>
                            </div>
                            <div class="bg-gray-50 rounded p-3">
                                <div class="text-gray-500">Days Left</div>
                                <div class="font-medium">
                                    <?php if ($daysLeft !== null): ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold <?= $daysBadge ?>" title="<?= esc($daysTitle) ?>">
                                            <span class="inline-block w-2 h-2 rounded-full mr-2 <?= $dotBadge ?>"></span>
                                            <?= $daysLeft === 0 ? 'Expired' : esc($daysLeft) . ' day' . ($daysLeft == 1 ? '' : 's') . ' left' ?>
                                        </span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded-md p-4">
                        <div class="text-lg font-semibold mb-2">Included Features</div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                            <?php foreach (($features ?? []) as $k => $v): ?>
                                <div class="flex items-center gap-2">
                                    <i class="fas <?= $v ? 'fa-check-circle text-green-600' : 'fa-times-circle text-gray-400' ?>"></i>
                                    <span class="capitalize"><?= esc(str_replace('_', ' ', $k)) ?></span>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty($features)): ?>
                                <div class="text-gray-500">No feature data for this plan.</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="border rounded-md p-4">
                        <div class="flex items-center justify-between mb-2">
                            <div class="text-lg font-semibold">Scheduled Change</div>
                        </div>
                        <?php if (!empty($subscription['next_plan_id'])): ?>
                            <?php $next = $planModel->find($subscription['next_plan_id']); ?>
                            <div class="flex items-center justify-between bg-yellow-50 border border-yellow-200 rounded p-3">
                                <div>
                                    Move to <strong><?= esc($next['name'] ?? 'Next Plan') ?></strong> on <?= esc($subscription['renews_at'] ?? 'renewal') ?>
                                </div>
                                <form method="post" action="<?= site_url('billing/cancel-scheduled') ?>">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="px-3 py-1 bg-white hover:bg-yellow-100 border border-yellow-300 text-yellow-900 rounded">Cancel change</button>
                                </form>
                            </div>
                        <?php else: ?>
                            <div class="text-sm text-gray-500">No plan change is scheduled.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="border rounded-md p-4">
                        <div class="text-lg font-semibold mb-2">Actions</div>
                        <a class="w-full inline-flex items-center justify-center px-3 py-2 bg-blue-600 text-white rounded shadow hover:bg-blue-700" href="<?= site_url('billing/plans') ?>">
                            <i class="fas fa-sync mr-2"></i> Change Plan
                        </a>
                    </div>

                    <div class="border rounded-md p-4">
                        <div class="text-lg font-semibold mb-2">Activate License</div>
                        <form method="post" action="<?= site_url('billing/activate') ?>">
                            <?= csrf_field() ?>
                            <label class="block text-sm font-medium text-gray-700 mb-1">License Code</label>
                            <input type="text" name="code" class="w-full border rounded px-3 py-2" placeholder="Enter code (e.g., KAS.xxxxx)" required>
                            <button class="mt-3 w-full px-3 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700" type="submit">
                                <i class="fas fa-key mr-2"></i> Activate
                            </button>
                        </form>
                    </div>

                    <?php $billingCfg = config('Billing'); ?>
                    <div class="border rounded-md p-4 <?= ($daysLeft !== null && $daysLeft <= 7) ? 'bg-yellow-50 border-yellow-200' : '' ?>">
                        <div class="text-lg font-semibold mb-1">Need Help Renewing?</div>
                        <?php if ($daysLeft !== null && $daysLeft <= 7): ?>
                            <div class="text-sm text-yellow-800 mb-2">
                                Your subscription <?= ($daysLeft === 0 ? 'has expired' : 'is nearing expiry') ?>.
                            </div>
                        <?php endif; ?>
                        <a target="_blank" rel="noopener" href="<?= esc($billingCfg->supportWebsite) ?>" class="w-full inline-flex items-center justify-center px-3 py-2 bg-indigo-600 text-white rounded shadow hover:bg-indigo-700">
                            <i class="fas fa-globe mr-2"></i> Visit <?= parse_url($billingCfg->supportWebsite, PHP_URL_HOST) ?: 'Website' ?>
                        </a>
                        <?php if (!empty($billingCfg->supportEmail)): ?>
                            <div class="mt-2 text-sm"><i class="fas fa-envelope mr-2"></i><?= esc($billingCfg->supportEmail) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($billingCfg->supportPhone)): ?>
                            <div class="mt-2 grid grid-cols-2 gap-2">
                                <a href="tel:<?= preg_replace('/[^0-9+]/', '', $billingCfg->supportPhone) ?>" class="inline-flex items-center justify-center px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded text-gray-800">
                                    <i class="fas fa-phone mr-2"></i> Call
                                </a>
                                <a target="_blank" rel="noopener" href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $billingCfg->supportPhone) ?>" class="inline-flex items-center justify-center px-3 py-2 bg-green-500 hover:bg-green-600 rounded text-white">
                                    <i class="fab fa-whatsapp mr-2"></i> WhatsApp
                                </a>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">Phone: <?= esc($billingCfg->supportPhone) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <p>No active subscription. Choose a plan to get started.</p>
            <a class="px-3 py-2 bg-blue-600 text-white rounded" href="<?= site_url('billing/plans') ?>">View Plans</a>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>