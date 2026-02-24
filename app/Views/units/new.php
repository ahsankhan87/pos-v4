<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<div class="max-w-3xl mx-auto py-6">
    <div class="bg-white shadow rounded p-6">
        <h1 class="text-2xl font-bold mb-6"><?= lang('Units.add_unit') ?></h1>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc pl-5 text-sm">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= site_url('units/create') ?>" method="post" class="space-y-5">
            <?= csrf_field() ?>

            <div>
                <label class="block text-sm font-medium text-gray-700"><?= lang('Units.name') ?></label>
                <input type="text" name="name" value="<?= old('name') ?>" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700"><?= lang('Units.abbreviation') ?></label>
                <input type="text" name="abbreviation" value="<?= old('abbreviation') ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" placeholder="<?= esc(lang('Units.placeholder_abbreviation')) ?>">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700"><?= lang('Units.description') ?></label>
                <textarea name="description" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" placeholder="<?= esc(lang('Units.placeholder_description')) ?>"><?= old('description') ?></textarea>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="btn btn-primary"><?= lang('Units.save') ?></button>
                <a href="<?= site_url('units') ?>" class="text-gray-600 hover:underline text-sm"><?= lang('Units.cancel') ?></a>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>