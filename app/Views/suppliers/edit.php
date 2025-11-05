<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php if (!isset($supplier) || !is_array($supplier)): ?>
    <div class="max-w-3xl mx-auto p-4">
        <div class="bg-red-50 text-red-700 border border-red-200 rounded-lg p-3">Supplier not found.</div>
    </div>
<?php else: ?>

    <div class="min-h-screen bg-slate-100">
        <!-- Alerts -->
        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 rounded-lg p-3 text-sm">
                <div class="font-semibold mb-1 flex items-center gap-2"><i class="fas fa-exclamation-triangle"></i> Please fix the errors below</div>
                <?= session()->getFlashdata('error') ?>
                <?= validation_list_errors() ?>
            </div>
        <?php endif; ?>
        <?= validation_list_errors() ?>
        <?php if (session()->getFlashdata('success')) : ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline"><?= session()->getFlashdata('success') ?></span>
            </div>
        <?php endif; ?>
        <!-- Top Bar -->
        <div class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-6xl mx-auto px-4">
                <div class="h-12 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded bg-gradient-to-br from-emerald-500 to-green-600 text-white flex items-center justify-center shadow">
                            <i class="fas fa-truck"></i>
                        </div>
                        <h1 class="text-lg font-bold text-gray-900">Edit Supplier</h1>
                    </div>
                    <a href="<?= site_url('suppliers') ?>" class="text-sm text-gray-600 hover:text-gray-800 flex items-center gap-1">
                        <i class="fas fa-arrow-left"></i> Back to suppliers
                    </a>
                </div>
            </div>
        </div>

        <div class="max-w-6xl mx-auto px-4 py-4">
            <form method="post" action="<?= site_url('suppliers/update/' . $supplier['id']) ?>" class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <?= csrf_field() ?>

                <div class="lg:col-span-2 space-y-4">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-4 py-2 bg-gradient-to-r from-emerald-50 to-green-50 border-b border-gray-200">
                            <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2"><i class="fas fa-id-card text-emerald-600"></i> Supplier Info</h3>
                        </div>
                        <div class="p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="name" value="<?= esc($supplier['name']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                                    <input type="email" name="email" value="<?= esc($supplier['email']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Phone</label>
                                    <input type="text" name="phone" value="<?= esc($supplier['phone']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Address</label>
                                    <input type="text" name="address" value="<?= esc($supplier['address']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden sticky top-16">
                        <div class="px-4 py-2 bg-gradient-to-r from-slate-50 to-slate-100 border-b border-gray-200">
                            <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2"><i class="fas fa-save text-slate-600"></i> Actions</h3>
                        </div>
                        <div class="p-4 space-y-2">
                            <button type="submit" class="btn btn-primary w-full"><i class="fas fa-check"></i> Update</button>
                            <a href="<?= site_url('suppliers') ?>" class="btn btn-muted w-full"><i class="fas fa-times"></i> Cancel</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>