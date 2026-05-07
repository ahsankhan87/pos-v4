<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-3">
        <h1 class="text-2xl font-bold text-gray-900"><?= esc($title) ?></h1>
        <a href="<?= site_url('employee-targets') ?>" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md">
            <?= lang('EmployeeTargets.back_to_targets') ?>
        </a>
    </div>

    <?= $this->include('employee_targets/_form') ?>
</div>

<?= $this->endSection() ?>