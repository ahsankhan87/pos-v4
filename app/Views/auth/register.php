<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php
$tenantBaseUrl = old('tenant_base_url', $tenant_base_url_default ?? '');
$companySlug = old('company_slug', '');
$tenantPreviewUrl = rtrim((string) $tenantBaseUrl, '/') . '/' . ($companySlug !== '' ? $companySlug : '<slug>');
?>

<div class="h-[100dvh] overflow-hidden bg-slate-100">
    <div class="mx-auto h-full max-w-7xl p-4 sm:p-6 lg:p-8">
        <div class="grid h-full gap-6 lg:grid-cols-[420px_minmax(0,1fr)]">
            <section class="hidden h-full rounded-[28px] bg-slate-900 p-8 text-white shadow-2xl lg:flex lg:flex-col lg:justify-between">
                <div>
                    <div class="inline-flex items-center rounded-full bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-200">
                        Business Registration
                    </div>
                    <h1 class="mt-6 text-4xl font-black leading-tight">
                        Open your business workspace.
                    </h1>
                    <p class="mt-4 text-sm leading-7 text-slate-300">
                        Register your account, reserve a business URL, and let the system prepare your database and files automatically.
                    </p>
                </div>

                <div class="space-y-4">
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Business URL</p>
                        <p id="tenant-preview" class="mt-3 break-all font-mono text-sm text-white"><?= esc($tenantPreviewUrl) ?></p>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-start gap-3 rounded-2xl bg-slate-800 px-4 py-4">
                            <span class="mt-1 flex h-6 w-6 items-center justify-center rounded-full bg-blue-500 text-xs font-bold text-white">1</span>
                            <div>
                                <p class="text-sm font-semibold">Create account details</p>
                                <p class="text-xs text-slate-400">Owner profile, email, password, and company identity.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 rounded-2xl bg-slate-800 px-4 py-4">
                            <span class="mt-1 flex h-6 w-6 items-center justify-center rounded-full bg-blue-500 text-xs font-bold text-white">2</span>
                            <div>
                                <p class="text-sm font-semibold">Provision business workspace</p>
                                <p class="text-xs text-slate-400">Database, app files, and starter configuration are prepared.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 rounded-2xl bg-slate-800 px-4 py-4">
                            <span class="mt-1 flex h-6 w-6 items-center justify-center rounded-full bg-blue-500 text-xs font-bold text-white">3</span>
                            <div>
                                <p class="text-sm font-semibold">Redirect to business login</p>
                                <p class="text-xs text-slate-400">You land directly on the new business URL with a success message.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="h-full overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-xl">
                <div class="flex h-full flex-col">
                    <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Register</p>
                        <h2 class="mt-2 text-2xl font-bold text-slate-900"><?= lang('Auth.createNewAccount') ?></h2>
                        <p class="mt-1 text-sm text-slate-500">Simple setup for your business workspace.</p>
                    </div>

                    <div class="flex-1 overflow-y-auto px-5 py-4 sm:px-6">
                        <?php if (session()->has('errors')): ?>
                            <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" role="alert">
                                <?php foreach (session('errors') as $error): ?>
                                    <p><?= $error ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (session()->has('error')): ?>
                            <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" role="alert">
                                <?= session('error') ?>
                            </div>
                        <?php endif; ?>

                        <?php if (session()->has('message')): ?>
                            <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" role="alert">
                                <?= session('message') ?>
                            </div>
                        <?php endif; ?>

                        <form id="register-form" class="space-y-5" action="<?= base_url('register') ?>" method="POST">
                            <?= csrf_field() ?>

                            <div>
                                <h3 class="mb-3 text-sm font-semibold uppercase tracking-[0.16em] text-slate-500">Account Owner</h3>
                                <div class="grid gap-3 md:grid-cols-2">
                                    <label class="block">
                                        <span class="mb-1.5 block text-sm font-medium text-slate-700"><?= lang('Auth.username') ?></span>
                                        <input id="username" name="username" type="text" required value="<?= old('username') ?>"
                                            class="block w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-200"
                                            placeholder="Choose a username">
                                    </label>
                                    <label class="block">
                                        <span class="mb-1.5 block text-sm font-medium text-slate-700"><?= lang('Auth.fullName') ?></span>
                                        <input id="name" name="name" type="text" required value="<?= old('name') ?>"
                                            class="block w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-200"
                                            placeholder="Full name">
                                    </label>
                                    <label class="block md:col-span-2">
                                        <span class="mb-1.5 block text-sm font-medium text-slate-700"><?= lang('Auth.email') ?></span>
                                        <input id="email" name="email" type="email" required value="<?= old('email') ?>"
                                            class="block w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-200"
                                            placeholder="you@company.com">
                                    </label>
                                    <label class="block">
                                        <span class="mb-1.5 block text-sm font-medium text-slate-700"><?= lang('Auth.phone') ?></span>
                                        <input id="phone" name="phone" type="tel" value="<?= old('phone') ?>"
                                            class="block w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-200"
                                            placeholder="<?= lang('Auth.phoneOptional') ?>">
                                    </label>
                                    <label class="block">
                                        <span class="mb-1.5 block text-sm font-medium text-slate-700"><?= lang('Auth.password') ?></span>
                                        <input id="password" name="password" type="password" required
                                            class="block w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-200"
                                            placeholder="<?= lang('Auth.passwordMin') ?>">
                                    </label>
                                    <label class="block md:col-span-2">
                                        <span class="mb-1.5 block text-sm font-medium text-slate-700"><?= lang('Auth.confirmPassword') ?></span>
                                        <input id="password_confirm" name="password_confirm" type="password" required
                                            class="block w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-200"
                                            placeholder="Confirm your password">
                                    </label>
                                </div>
                            </div>

                            <div class="border-t border-slate-200 pt-5">
                                <h3 class="mb-3 text-sm font-semibold uppercase tracking-[0.16em] text-slate-500">Company Workspace</h3>
                                <div class="grid gap-3 md:grid-cols-2">
                                    <label class="block md:col-span-2">
                                        <span class="mb-1.5 block text-sm font-medium text-slate-700">Company Name</span>
                                        <input id="company_name" name="company_name" type="text" required value="<?= old('company_name') ?>"
                                            class="block w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-200"
                                            placeholder="Your company or store name">
                                    </label>
                                    <label class="block md:col-span-2">
                                        <span class="mb-1.5 block text-sm font-medium text-slate-700">Business Base URL</span>
                                        <input id="tenant_base_url" name="tenant_base_url" type="url" required value="<?= esc($tenantBaseUrl) ?>"
                                            class="block w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-200"
                                            placeholder="http://localhost:8080/kasbook">
                                    </label>
                                    <label class="block">
                                        <span class="mb-1.5 block text-sm font-medium text-slate-700">Company Slug</span>
                                        <input id="company_slug" name="company_slug" type="text" required value="<?= old('company_slug') ?>"
                                            class="block w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-slate-900 focus:bg-white focus:ring-2 focus:ring-slate-200"
                                            placeholder="abc-traders">
                                    </label>
                                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                        <span class="mb-1.5 block text-sm font-medium text-slate-700">Business URL</span>
                                        <p id="tenant-inline-preview" class="break-all font-mono text-sm text-slate-900"><?= esc($tenantPreviewUrl) ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-2xl bg-slate-50 px-4 py-4 text-sm text-slate-600">
                                Setup may take a short while because the system creates the database and business files before redirecting you.
                            </div>

                            <div class="flex flex-col gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-between">
                                <div class="text-sm text-slate-500">
                                    <?= lang('Auth.alreadyHaveAccount') ?>
                                    <a href="<?= base_url('login') ?>" class="font-semibold text-slate-900 hover:underline">
                                        <?= lang('Auth.loginHere') ?>
                                    </a>
                                </div>
                                <button id="register-submit" type="submit"
                                    class="inline-flex items-center justify-center gap-3 rounded-xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white transition hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-300 disabled:cursor-not-allowed disabled:opacity-70">
                                    <span id="register-submit-icon"><i class="fa-solid fa-arrow-right"></i></span>
                                    <span id="register-submit-label"><?= lang('Auth.register') ?></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<div id="registration-loading" class="fixed inset-0 z-[120] hidden">
    <div class="absolute inset-0 bg-slate-950/90"></div>
    <div class="relative flex h-full items-center justify-center p-4">
        <div class="w-full max-w-xl rounded-[28px] bg-slate-900 p-6 text-white shadow-2xl sm:p-8">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-600">
                    <i class="fa-solid fa-spinner animate-spin text-xl"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">Please Wait</p>
                    <h3 class="mt-1 text-2xl font-bold">Preparing your business workspace</h3>
                </div>
            </div>

            <p id="loading-status-text" class="mt-5 text-sm leading-6 text-slate-300">
                Creating your business database and provisioning application files.
            </p>

            <div class="mt-6 overflow-hidden rounded-full bg-slate-800">
                <div id="loading-progress-bar" class="h-2 w-[22%] rounded-full bg-blue-500 transition-all duration-700"></div>
            </div>

            <div class="mt-6 space-y-3 text-sm">
                <div id="loading-step-1" class="rounded-xl border border-slate-700 bg-slate-800 px-4 py-3 text-white">Creating business database</div>
                <div id="loading-step-2" class="rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-slate-400">Copying application files</div>
                <div id="loading-step-3" class="rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-slate-400">Configuring business environment</div>
                <div id="loading-step-4" class="rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-slate-400">Redirecting to business login</div>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        const form = document.getElementById('register-form');
        const companyNameInput = document.getElementById('company_name');
        const companySlugInput = document.getElementById('company_slug');
        const tenantBaseUrlInput = document.getElementById('tenant_base_url');
        const previewNodes = [
            document.getElementById('tenant-preview'),
            document.getElementById('tenant-inline-preview')
        ].filter(Boolean);
        const loadingOverlay = document.getElementById('registration-loading');
        const loadingStatusText = document.getElementById('loading-status-text');
        const loadingProgressBar = document.getElementById('loading-progress-bar');
        const submitButton = document.getElementById('register-submit');
        const submitLabel = document.getElementById('register-submit-label');
        const submitIcon = document.getElementById('register-submit-icon');
        const loadingSteps = [{
                element: document.getElementById('loading-step-1'),
                text: 'Creating your business database and starter records.',
                progress: '28%'
            },
            {
                element: document.getElementById('loading-step-2'),
                text: 'Copying application files for the new business workspace.',
                progress: '52%'
            },
            {
                element: document.getElementById('loading-step-3'),
                text: 'Configuring the business environment and URL.',
                progress: '76%'
            },
            {
                element: document.getElementById('loading-step-4'),
                text: 'Finalizing setup and preparing redirect.',
                progress: '94%'
            }
        ];

        let slugWasEdited = <?= old('company_slug') ? 'true' : 'false' ?>;
        let loadingIndex = 0;

        function slugify(value) {
            return value
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '')
                .replace(/-{2,}/g, '-');
        }

        function updatePreview() {
            const baseUrl = (tenantBaseUrlInput.value || 'http://localhost').replace(/\/$/, '');
            const slug = companySlugInput.value.trim() || '<slug>';
            const preview = baseUrl + '/' + slug;

            previewNodes.forEach(function(node) {
                node.textContent = preview;
            });
        }

        function setActiveLoadingStep(index) {
            loadingSteps.forEach(function(step, stepIndex) {
                if (!step.element) {
                    return;
                }

                if (stepIndex <= index) {
                    step.element.classList.remove('bg-slate-950', 'text-slate-400', 'border-slate-800');
                    step.element.classList.add('bg-slate-800', 'text-white', 'border-slate-700');
                } else {
                    step.element.classList.remove('bg-slate-800', 'text-white', 'border-slate-700');
                    step.element.classList.add('bg-slate-950', 'text-slate-400', 'border-slate-800');
                }
            });

            const current = loadingSteps[index] || loadingSteps[loadingSteps.length - 1];
            if (current) {
                loadingStatusText.textContent = current.text;
                loadingProgressBar.style.width = current.progress;
            }
        }

        function startLoadingSequence() {
            loadingOverlay.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            setActiveLoadingStep(loadingIndex);

            window.setInterval(function() {
                loadingIndex = (loadingIndex + 1) % loadingSteps.length;
                setActiveLoadingStep(loadingIndex);
            }, 1800);
        }

        if (companyNameInput && companySlugInput) {
            companyNameInput.addEventListener('input', function() {
                if (!slugWasEdited) {
                    companySlugInput.value = slugify(companyNameInput.value);
                    updatePreview();
                }
            });

            companySlugInput.addEventListener('input', function() {
                slugWasEdited = companySlugInput.value.trim() !== '';
                companySlugInput.value = slugify(companySlugInput.value);
                updatePreview();
            });
        }

        if (tenantBaseUrlInput) {
            tenantBaseUrlInput.addEventListener('input', updatePreview);
        }

        if (form) {
            form.addEventListener('submit', function() {
                if (typeof form.reportValidity === 'function' && !form.reportValidity()) {
                    return;
                }

                if (submitButton) {
                    submitButton.disabled = true;
                }

                if (submitLabel) {
                    submitLabel.textContent = 'Preparing workspace...';
                }

                if (submitIcon) {
                    submitIcon.innerHTML = '<i class="fa-solid fa-spinner animate-spin"></i>';
                }

                startLoadingSequence();
            });
        }

        updatePreview();
    })();
</script>
<?= $this->endSection() ?>