<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<div class="container mx-auto p-4">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Add New Employee</h1>
        <a href="<?= base_url('employees') ?>" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">Back to Employees</a>
    </div>

    <?php if (session()->getFlashdata('error')) : ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline"><?= session()->getFlashdata('error') ?></span>
        </div>
    <?php endif; ?>

    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="<?= base_url('employees/create') ?>" method="post">
            <?= csrf_field() ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Employee Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Employee Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" value="<?= old('name') ?>" required>
                    <?php if ($validation->hasError('name')) : ?>
                        <p class="text-red-500 text-xs mt-1"><?= $validation->getError('name') ?></p>
                    <?php endif; ?>
                </div>

                <!-- User Account (Optional) -->
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-700">Link to User Account (Optional)</label>
                    <select name="user_id" id="user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <option value="">-- Select User --</option>
                        <?php foreach ($users as $user) : ?>
                            <option value="<?= $user['id'] ?>" <?= old('user_id') == $user['id'] ? 'selected' : '' ?>><?= esc($user['username']) ?> (<?= esc($user['email']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($validation->hasError('user_id')) : ?>
                        <p class="text-red-500 text-xs mt-1"><?= $validation->getError('user_id') ?></p>
                    <?php endif; ?>
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                    <input type="text" name="phone" id="phone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" value="<?= old('phone') ?>">
                    <?php if ($validation->hasError('phone')) : ?>
                        <p class="text-red-500 text-xs mt-1"><?= $validation->getError('phone') ?></p>
                    <?php endif; ?>
                </div>

                <!-- CNIC -->
                <div>
                    <label for="cnic" class="block text-sm font-medium text-gray-700">CNIC</label>
                    <input type="text" name="cnic" id="cnic" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" value="<?= old('cnic') ?>">
                    <?php if ($validation->hasError('cnic')) : ?>
                        <p class="text-red-500 text-xs mt-1"><?= $validation->getError('cnic') ?></p>
                    <?php endif; ?>
                </div>

                <!-- Commission Rate -->
                <div>
                    <label for="commission_rate" class="block text-sm font-medium text-gray-700">Commission Rate (%)</label>
                    <input type="number" step="0.01" name="commission_rate" id="commission_rate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" value="<?= old('commission_rate', 0) ?>">
                    <?php if ($validation->hasError('commission_rate')) : ?>
                        <p class="text-red-500 text-xs mt-1"><?= $validation->getError('commission_rate') ?></p>
                    <?php endif; ?>
                </div>

                <!-- Hire Date -->
                <div>
                    <label for="hire_date" class="block text-sm font-medium text-gray-700">Hire Date</label>
                    <input type="date" name="hire_date" id="hire_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" value="<?= old('hire_date', date('Y-m-d')) ?>">
                    <?php if ($validation->hasError('hire_date')) : ?>
                        <p class="text-red-500 text-xs mt-1"><?= $validation->getError('hire_date') ?></p>
                    <?php endif; ?>
                </div>

                <!-- Address -->
                <div class="md:col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                    <textarea name="address" id="address" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"><?= old('address') ?></textarea>
                    <?php if ($validation->hasError('address')) : ?>
                        <p class="text-red-500 text-xs mt-1"><?= $validation->getError('address') ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mt-6">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">Add Employee</button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>