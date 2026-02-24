<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<div class="container mx-auto p-4">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold"><?= lang('Employees.employeeDetails') ?></h1>
        <a href="<?= base_url('employees') ?>" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md"><?= lang('Employees.backToEmployees') ?></a>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm font-medium text-gray-700"><?= lang('Employees.employeeName') ?>:</p>
                <p class="text-lg font-semibold"><?= esc($employee['name']) ?></p>
            </div>

            <div>
                <p class="text-sm font-medium text-gray-700"><?= lang('Employees.phone') ?>:</p>
                <p class="text-lg font-semibold"><?= esc($employee['phone']) ?></p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-700"><?= lang('Employees.cnic') ?>:</p>
                <p class="text-lg font-semibold"><?= esc($employee['cnic']) ?></p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-700"><?= lang('Employees.commissionRateLabel') ?>:</p>
                <p class="text-lg font-semibold"><?= esc($employee['commission_rate']) ?>%</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-700"><?= lang('Employees.hireDate') ?>:</p>
                <p class="text-lg font-semibold"><?= esc($employee['hire_date']) ?></p>
            </div>
            <?php if ($employee['termination_date']) : ?>
                <div>
                    <p class="text-sm font-medium text-gray-700"><?= lang('Employees.terminationDate') ?>:</p>
                    <p class="text-lg font-semibold"><?= esc($employee['termination_date']) ?></p>
                </div>
            <?php endif; ?>
            <div>
                <p class="text-sm font-medium text-gray-700"><?= lang('Employees.status') ?>:</p>
                <p class="text-lg font-semibold">
                    <?php if ($employee['is_active']) : ?>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800"><?= lang('Employees.active') ?></span>
                    <?php else : ?>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800"><?= lang('Employees.inactive') ?></span>
                    <?php endif; ?>
                </p>
            </div>
            <div class="md:col-span-2">
                <p class="text-sm font-medium text-gray-700"><?= lang('Employees.address') ?>:</p>
                <p class="text-lg font-semibold"><?= esc($employee['address']) ?></p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-700"><?= lang('Employees.createdAt') ?>:</p>
                <p class="text-lg font-semibold"><?= esc($employee['created_at']) ?></p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-700"><?= lang('Employees.lastUpdatedAt') ?>:</p>
                <p class="text-lg font-semibold"><?= esc($employee['updated_at']) ?></p>
            </div>
        </div>

        <div class="mt-6 flex space-x-3">
            <a href="<?= base_url('employees/edit/' . $employee['id']) ?>" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md"><?= lang('Employees.editEmployee') ?></a>
            <a href="<?= base_url('employees/delete/' . $employee['id']) ?>" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md" onclick="return confirm('<?= esc(lang('Employees.deleteEmployeeConfirm')) ?>');"><?= lang('Employees.deleteEmployee') ?></a>
        </div>
    </div>
</div>

<?= $this->endSection() ?>