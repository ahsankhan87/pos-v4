<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<div class="max-w-3xl mx-auto py-6">
    <div class="bg-white shadow rounded p-6">
        <h1 class="text-2xl font-bold mb-6">Edit Unit</h1>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc pl-5 text-sm">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= site_url('units/update/' . $unit['id']) ?>" method="post" class="space-y-5">
            <?= csrf_field() ?>

            <div>
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="name" value="<?= old('name', $unit['name']) ?>" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Abbreviation</label>
                <input type="text" name="abbreviation" value="<?= old('abbreviation', $unit['abbreviation']) ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" placeholder="e.g. pcs, kg, box">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" placeholder="Optional description"><?= old('description', $unit['description']) ?></textarea>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="<?= site_url('units') ?>" class="text-gray-600 hover:underline text-sm">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>