<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<div class="max-w-4xl mx-auto py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Units</h1>
            <p class="text-gray-500 text-sm">Manage units of measure for your products.</p>
        </div>
        <a href="<?= site_url('units/new') ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Add Unit
        </a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded mb-4">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <div class="bg-white shadow rounded">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Abbreviation</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th scope="col" class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (! empty($units)): ?>
                    <?php foreach ($units as $unit): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= esc($unit['name']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= esc($unit['abbreviation'] ?? '-') ?></td>
                            <td class="px-6 py-4 text-sm text-gray-500"><?= esc($unit['description'] ?? '') ?></td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <a href="<?= site_url('units/edit/' . $unit['id']) ?>" class="text-blue-600 hover:text-blue-900 me-3">Edit</a>
                                <form action="<?= site_url('units/delete/' . $unit['id']) ?>" method="post" class="inline" onsubmit="return confirm('Delete this unit?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="px-6 py-6 text-center text-sm text-gray-500">No units found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>