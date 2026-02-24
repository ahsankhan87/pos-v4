<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800"><?= lang('Receipts.receiptTemplates') ?></h2>
            <a href="<?= site_url('receipts/templates/create') ?>" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i><?= lang('Receipts.createNewTemplate') ?>
            </a>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Receipts.name') ?></th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Receipts.status') ?></th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Receipts.created') ?></th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Receipts.actions') ?></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($templates)): ?>
                        <?php foreach ($templates as $template): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?= esc($template['name']) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($template['is_default']): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            <?= lang('Receipts.default') ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            <?= lang('Receipts.inactive') ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= date('M d, Y', strtotime($template['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="<?= site_url('receipts/templates/edit/' . $template['id']) ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class="fas fa-edit"></i> <?= lang('Receipts.edit') ?>
                                    </a>
                                    <?php if (!$template['is_default']): ?>
                                        <a href="<?= site_url('receipts/templates/set-default/' . $template['id']) ?>"
                                            class="text-green-600 hover:text-green-900 mr-3"
                                            onclick="return confirm(<?= json_encode(lang('Receipts.confirmSetDefault'), JSON_UNESCAPED_UNICODE) ?>)">
                                            <i class="fas fa-check-circle"></i> <?= lang('Receipts.setDefault') ?>
                                        </a>
                                        <a href="<?= site_url('receipts/templates/delete/' . $template['id']) ?>"
                                            class="text-red-600 hover:text-red-900"
                                            onclick="return confirm(<?= json_encode(lang('Receipts.confirmDeleteTemplate'), JSON_UNESCAPED_UNICODE) ?>)">
                                            <i class="fas fa-trash"></i> <?= lang('Receipts.delete') ?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                <?= lang('Receipts.noTemplatesFound') ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>