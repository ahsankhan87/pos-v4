<?= $this->extend('templates/header') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Edit User</h1>

    <div class="bg-white shadow-lg rounded-lg p-6">
        <form action="<?= site_url('users/update/' . $user['id']) ?>" method="post">
            <?= csrf_field() ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" name="name" id="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2" value="<?= old('name', $user['name']) ?>">
                    <?php if (session('errors.name')) : ?>
                        <p class="text-red-500 text-xs mt-1"><?= session('errors.name') ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <input type="text" name="username" id="username" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2" value="<?= old('username', $user['username']) ?>">
                    <?php if (session('errors.username')) : ?>
                        <p class="text-red-500 text-xs mt-1"><?= session('errors.username') ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" id="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2" value="<?= old('email', $user['email']) ?>">
                    <?php if (session('errors.email')) : ?>
                        <p class="text-red-500 text-xs mt-1"><?= session('errors.email') ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone (Optional)</label>
                    <input type="text" name="phone" id="phone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2" value="<?= old('phone', $user['phone']) ?>">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password (Leave blank to keep current)</label>
                    <input type="password" name="password" id="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2">
                    <?php if (session('errors.password')) : ?>
                        <p class="text-red-500 text-xs mt-1"><?= session('errors.password') ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="role_id" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <select name="role_id" id="role_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2">
                        <?php foreach ($roles as $role) : ?>
                            <option value="<?= $role['id'] ?>" <?= (old('role_id', $user['role_id']) == $role['id']) ? 'selected' : '' ?>>
                                <?= esc($role['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (session('errors.role_id')) : ?>
                        <p class="text-red-500 text-xs mt-1"><?= session('errors.role_id') ?></p>
                    <?php endif; ?>
                </div>
                <div class="col-span-1 md:col-span-2">
                    <label for="is_active" class="flex items-center text-sm font-medium text-gray-700 mb-1">
                        <input type="checkbox" name="is_active" id="is_active" class="h-4 w-4 text-blue-600 border-gray-300 rounded mr-2" value="1" <?= (old('is_active', $user['is_active'])) ? 'checked' : '' ?>>
                        Is Active
                    </label>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <a href="<?= site_url('users') ?>" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg transition duration-200 mr-2">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    Update User
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>