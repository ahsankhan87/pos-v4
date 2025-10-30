<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<div class="container mx-auto p-4">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Employee Details</h1>
        <a href="<?= base_url('employees') ?>" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">Back to Employees</a>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm font-medium text-gray-700">Employee Name:</p>
                <p class="text-lg font-semibold"><?= esc($employee['name']) ?></p>
            </div>

            <div>
                <p class="text-sm font-medium text-gray-700">Phone:</p>
                <p class="text-lg font-semibold"><?= esc($employee['phone']) ?></p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-700">CNIC:</p>
                <p class="text-lg font-semibold"><?= esc($employee['cnic']) ?></p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-700">Commission Rate:</p>
                <p class="text-lg font-semibold"><?= esc($employee['commission_rate']) ?>%</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-700">Hire Date:</p>
                <p class="text-lg font-semibold"><?= esc($employee['hire_date']) ?></p>
            </div>
            <?php if ($employee['termination_date']) : ?>
                <div>
                    <p class="text-sm font-medium text-gray-700">Termination Date:</p>
                    <p class="text-lg font-semibold"><?= esc($employee['termination_date']) ?></p>
                </div>
            <?php endif; ?>
            <div>
                <p class="text-sm font-medium text-gray-700">Status:</p>
                <p class="text-lg font-semibold">
                    <?php if ($employee['is_active']) : ?>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                    <?php else : ?>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                    <?php endif; ?>
                </p>
            </div>
            <div class="md:col-span-2">
                <p class="text-sm font-medium text-gray-700">Address:</p>
                <p class="text-lg font-semibold"><?= esc($employee['address']) ?></p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-700">Created At:</p>
                <p class="text-lg font-semibold"><?= esc($employee['created_at']) ?></p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-700">Last Updated At:</p>
                <p class="text-lg font-semibold"><?= esc($employee['updated_at']) ?></p>
            </div>
        </div>

        <div class="mt-6 flex space-x-3">
            <a href="<?= base_url('employees/edit/' . $employee['id']) ?>" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">Edit Employee</a>
            <a href="<?= base_url('employees/delete/' . $employee['id']) ?>" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md" onclick="return confirm('Are you sure you want to delete this employee?');">Delete Employee</a>
        </div>
    </div>
</div>

<?= $this->endSection() ?>