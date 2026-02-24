<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                <?= lang('Auth.createNewAccount') ?>
            </h2>
        </div>

        <?php if (session()->has('errors')): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php foreach (session('errors') as $error): ?>
                    <p><?= $error ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (session()->has('error')): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= session('error') ?>
            </div>
        <?php endif; ?>

        <?php if (session()->has('message')): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <?= session('message') ?>
            </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" action="<?= base_url('register') ?>" method="POST">
            <?= csrf_field() ?>

            <div class="rounded-md shadow-sm space-y-4">
                <div>
                    <label for="username" class="sr-only"><?= lang('Auth.username') ?></label>
                    <input id="username" name="username" type="text" required
                        class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                        placeholder="<?= lang('Auth.username') ?>" value="<?= old('username') ?>">
                </div>

                <div>
                    <label for="email" class="sr-only"><?= lang('Auth.email') ?></label>
                    <input id="email" name="email" type="email" required
                        class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                        placeholder="<?= lang('Auth.email') ?>" value="<?= old('email') ?>">
                </div>

                <div>
                    <label for="name" class="sr-only"><?= lang('Auth.fullName') ?></label>
                    <input id="name" name="name" type="text" required
                        class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                        placeholder="<?= lang('Auth.fullName') ?>" value="<?= old('name') ?>">
                </div>

                <div>
                    <label for="phone" class="sr-only"><?= lang('Auth.phone') ?></label>
                    <input id="phone" name="phone" type="tel"
                        class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                        placeholder="<?= lang('Auth.phoneOptional') ?>" value="<?= old('phone') ?>">
                </div>

                <div>
                    <label for="password" class="sr-only"><?= lang('Auth.password') ?></label>
                    <input id="password" name="password" type="password" required
                        class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                        placeholder="<?= lang('Auth.passwordMin') ?>">
                </div>

                <div>
                    <label for="password_confirm" class="sr-only"><?= lang('Auth.confirmPassword') ?></label>
                    <input id="password_confirm" name="password_confirm" type="password" required
                        class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                        placeholder="<?= lang('Auth.confirmPassword') ?>">
                </div>
            </div>

            <div>
                <button type="submit"
                    class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <?= lang('Auth.register') ?>
                </button>
            </div>
        </form>

        <div class="text-center text-sm">
            <p class="text-gray-600"><?= lang('Auth.alreadyHaveAccount') ?></p>
            <a href="<?= base_url('login') ?>" class="font-medium text-blue-600 hover:text-blue-500">
                <?= lang('Auth.loginHere') ?>
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>