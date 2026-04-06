<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-2 text-center text-3xl font-extrabold text-gray-900">
                <?= lang('Auth.signInToAccount') ?>
            </h2>
        </div>

        <?php if (session()->has('error')): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <?= session('error') ?>
            </div>
        <?php endif; ?>

        <?php if (session()->has('errors')): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <?php foreach (session('errors') as $error): ?>
                    <p><?= $error ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php $message = session('message') ?? ($message ?? null); ?>
        <?php if ($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <?php $loginUsername = $loginUsername ?? null; ?>
        <?php $loginEmail = $loginEmail ?? null; ?>
        <?php if ($loginUsername || $loginEmail): ?>
            <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded relative" role="alert">
                <p class="font-semibold">Your login details</p>
                <?php if ($loginUsername): ?>
                    <p class="mt-1 text-sm">Username: <?= esc($loginUsername) ?></p>
                <?php endif; ?>
                <?php if ($loginEmail): ?>
                    <p class="text-sm">Email: <?= esc($loginEmail) ?></p>
                <?php endif; ?>
                <p class="mt-1 text-sm">Use the password you created during registration.</p>
            </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" action="<?= base_url('login') ?>" method="POST">

            <!-- Add this inside your form -->
            <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
            <?= csrf_field() ?>

            <input type="hidden" name="remember" value="true">
            <div class="rounded-md shadow-sm space-y-4">
                <div>
                    <label for="username" class="sr-only"><?= lang('Auth.username') ?></label>
                    <input id="username" name="username" type="text" required autofocus
                        class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                        placeholder="<?= lang('Auth.username') ?>" value="<?= old('username') ?>">
                </div>
                <div>
                    <label for="password" class="sr-only"><?= lang('Auth.password') ?></label>
                    <input id="password" name="password" type="password" required
                        class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                        placeholder="<?= lang('Auth.password') ?>">
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember-me" name="remember-me" type="checkbox"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="remember-me" class="ml-2 block text-sm text-gray-900">
                        <?= lang('Auth.rememberMe') ?>
                    </label>
                </div>

                <div class="text-sm">
                    <a href="<?= base_url('forgot-password') ?>" class="font-medium text-blue-600 hover:text-blue-500">
                        <?= lang('Auth.forgotPasswordQuestion') ?>
                    </a>
                </div>
            </div>

            <div>
                <button type="submit"
                    class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <?= lang('Auth.signIn') ?>
                </button>
            </div>
        </form>

        <!-- <div class="text-center text-sm">
            <p class="text-gray-600">Don't have an account?</p>
            <a href="<?= base_url('register') ?>" class="font-medium text-blue-600 hover:text-blue-500">
                Register here
            </a>
        </div> -->
    </div>
</div>
<?= $this->endSection() ?>