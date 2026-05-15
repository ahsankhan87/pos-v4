<?= $this->extend('templates/header') ?>

<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= lang('Promotions.title_index') ?></h1>
            <p class="mt-1 text-sm text-gray-500"><?= lang('Promotions.subtitle_index') ?></p>
        </div>
        <?php if (can('promotions.create')): ?>
            <a href="<?= site_url('promotions/new') ?>" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> <?= lang('Promotions.new_promotion') ?>
            </a>
        <?php endif; ?>
        <?php if (can('promotions.view')): ?>
            <a href="<?= site_url('promotions/print') ?>" target="_blank" class="btn btn-secondary">
                <i class="fas fa-print"></i> <?= lang('Promotions.print') ?>
            </a>
        <?php endif; ?>
    </div>

    <!-- How it works panel -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg mb-6">
        <button type="button" id="toggle-how-it-works" class="w-full flex items-center justify-between px-4 py-3 text-left" aria-expanded="false" aria-controls="how-it-works-body">
            <span class="flex items-center gap-2 text-sm font-semibold text-blue-800">
                <i class="fas fa-circle-info text-blue-500" aria-hidden="true"></i>
                <?= lang('Promotions.how_it_works_title') ?>
            </span>
            <i id="toggle-how-it-works-icon" class="fas fa-chevron-down text-blue-400 transition-transform duration-200" aria-hidden="true"></i>
        </button>
        <div id="how-it-works-body" class="hidden px-4 pb-4">
            <ol class="space-y-3 mt-1">
                <li class="flex gap-3">
                    <span class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center">1</span>
                    <div>
                        <p class="text-sm font-semibold text-blue-900"><?= lang('Promotions.flow_step1_title') ?></p>
                        <p class="text-xs text-blue-700 mt-0.5"><?= lang('Promotions.flow_step1_desc') ?></p>
                    </div>
                </li>
                <li class="flex gap-3">
                    <span class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center">2</span>
                    <div>
                        <p class="text-sm font-semibold text-blue-900"><?= lang('Promotions.flow_step2_title') ?></p>
                        <p class="text-xs text-blue-700 mt-0.5"><?= lang('Promotions.flow_step2_desc') ?></p>
                    </div>
                </li>
                <li class="flex gap-3">
                    <span class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center">3</span>
                    <div>
                        <p class="text-sm font-semibold text-blue-900"><?= lang('Promotions.flow_step3_title') ?></p>
                        <p class="text-xs text-blue-700 mt-0.5"><?= lang('Promotions.flow_step3_desc') ?></p>
                    </div>
                </li>
                <li class="flex gap-3">
                    <span class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center">4</span>
                    <div>
                        <p class="text-sm font-semibold text-blue-900"><?= lang('Promotions.flow_step4_title') ?></p>
                        <p class="text-xs text-blue-700 mt-0.5"><?= lang('Promotions.flow_step4_desc') ?></p>
                    </div>
                </li>
                <li class="flex gap-3">
                    <span class="flex-shrink-0 w-6 h-6 rounded-full bg-amber-500 text-white text-xs font-bold flex items-center justify-center">!</span>
                    <div>
                        <p class="text-sm font-semibold text-amber-800"><?= lang('Promotions.flow_rule_highest_title') ?></p>
                        <p class="text-xs text-amber-700 mt-0.5"><?= lang('Promotions.flow_rule_highest_desc') ?></p>
                    </div>
                </li>
            </ol>
        </div>
    </div>
    <script>
        (function() {
            var btn = document.getElementById('toggle-how-it-works');
            var body = document.getElementById('how-it-works-body');
            var icon = document.getElementById('toggle-how-it-works-icon');
            if (btn && body && icon) {
                btn.addEventListener('click', function() {
                    var open = !body.classList.contains('hidden');
                    body.classList.toggle('hidden', open);
                    icon.style.transform = open ? '' : 'rotate(180deg)';
                    btn.setAttribute('aria-expanded', String(!open));
                });
            }
        })();
    </script>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="rounded-md bg-green-50 p-4 mb-4">
            <p class="text-sm font-medium text-green-800"><?= esc(session()->getFlashdata('success')) ?></p>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="rounded-md bg-red-50 p-4 mb-4">
            <p class="text-sm font-medium text-red-800"><?= esc(session()->getFlashdata('error')) ?></p>
        </div>
    <?php endif; ?>

    <div class="table-card overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th><?= lang('Promotions.name') ?></th>
                    <th><?= lang('Promotions.rule') ?></th>
                    <th><?= lang('Promotions.priority') ?></th>
                    <th><?= lang('Promotions.status') ?></th>
                    <th><?= lang('Promotions.date_range') ?></th>
                    <th class="text-right"><?= lang('Promotions.actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($promotions)): ?>
                    <tr>
                        <td colspan="6" class="py-8 text-center text-sm text-gray-500"><?= lang('Promotions.empty') ?></td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($promotions as $promotion): ?>
                    <?php $status = strtolower((string) ($promotion['status'] ?? 'active')); ?>
                    <?php
                    $triggerNames = $promotion['trigger_product_names'] ?? [];
                    if (!is_array($triggerNames)) {
                        $triggerNames = [];
                    }
                    $triggerNames = array_values(array_filter(array_map('strval', $triggerNames)));
                    $triggerText = !empty($triggerNames) ? implode(', ', $triggerNames) : ((string) ($promotion['trigger_product_name'] ?? '-'));
                    ?>
                    <tr>
                        <td>
                            <div class="font-semibold text-gray-800"><?= esc($promotion['name'] ?? '') ?></div>
                            <div class="text-xs text-gray-500">
                                <?= !empty($promotion['auto_apply']) ? lang('Promotions.auto_apply_enabled') : lang('Promotions.auto_apply_disabled') ?>
                            </div>
                        </td>
                        <td>
                            <div class="text-sm text-gray-800">
                                <?= esc($triggerText) ?>
                                <span class="text-gray-500">x <?= esc(rtrim(rtrim(number_format((float) ($promotion['trigger_qty'] ?? 0), 2, '.', ''), '0'), '.')) ?></span>
                            </div>
                            <div class="text-xs text-gray-500">
                                <?= esc($promotion['gift_product_name'] ?? '-') ?>
                                <span>x <?= esc(rtrim(rtrim(number_format((float) ($promotion['gift_qty'] ?? 0), 2, '.', ''), '0'), '.')) ?></span>
                            </div>
                        </td>
                        <td><?= (int) ($promotion['priority'] ?? 100) ?></td>
                        <td>
                            <span class="inline-block rounded px-2 py-1 text-xs font-semibold <?= $status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                <?= esc(ucfirst($status)) ?>
                            </span>
                        </td>
                        <td class="text-sm text-gray-600">
                            <?= esc($promotion['start_date'] ?? '-') ?>
                            <span class="mx-1">to</span>
                            <?= esc($promotion['end_date'] ?? '-') ?>
                        </td>
                        <td class="text-right">
                            <div class="inline-flex gap-1">
                                <?php if (can('promotions.update')): ?>
                                    <a href="<?= site_url('promotions/edit/' . (int) $promotion['id']) ?>" class="btn btn-outline btn-sm"><?= lang('Promotions.edit') ?></a>
                                    <form action="<?= site_url('promotions/toggle/' . (int) $promotion['id']) ?>" method="post" class="inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-secondary btn-sm"><?= $status === 'active' ? lang('Promotions.pause') : lang('Promotions.resume') ?></button>
                                    </form>
                                <?php endif; ?>
                                <?php if (can('promotions.delete')): ?>
                                    <form action="<?= site_url('promotions/delete/' . (int) $promotion['id']) ?>" method="post" class="inline" onsubmit="return confirm('<?= esc(lang('Promotions.confirm_delete')) ?>');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-danger btn-sm"><?= lang('Promotions.delete') ?></button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>