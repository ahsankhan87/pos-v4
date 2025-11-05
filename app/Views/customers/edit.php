<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php if (!isset($customer) || !is_array($customer)): ?>
    <div class="max-w-3xl mx-auto p-4">
        <div class="bg-red-50 text-red-700 border border-red-200 rounded-lg p-3">Customer not found.</div>
    </div>
<?php else: ?>
    <div class="min-h-screen bg-slate-100">
        <!-- Top Bar -->
        <div class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-6xl mx-auto px-4">
                <div class="h-12 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded bg-gradient-to-br from-indigo-500 to-blue-600 text-white flex items-center justify-center shadow">
                            <i class="fas fa-user-edit"></i>
                        </div>
                        <h1 class="text-lg font-bold text-gray-900">Edit Customer</h1>
                    </div>
                    <a href="<?= site_url('customers') ?>" class="text-sm text-gray-600 hover:text-gray-800 flex items-center gap-1">
                        <i class="fas fa-arrow-left"></i> Back to customers
                    </a>
                </div>
            </div>
        </div>

        <div class="max-w-6xl mx-auto px-4 py-4">
            <form method="post" action="<?= site_url('customers/update/' . $customer['id']) ?>" class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <?= csrf_field() ?>

                <div class="lg:col-span-2 space-y-4">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-4 py-2 bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-gray-200">
                            <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2"><i class="fas fa-id-card text-blue-600"></i> Customer Info</h3>
                        </div>
                        <div class="p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="name" value="<?= esc($customer['name']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Email </label>
                                    <input type="email" name="email" value="<?= esc($customer['email']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Phone</label>
                                    <input type="text" name="phone" value="<?= esc($customer['phone']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Address</label>
                                    <input type="text" name="address" value="<?= esc($customer['address']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
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
                            <a href="<?= site_url('customers') ?>" class="btn btn-muted w-full"><i class="fas fa-times"></i> Cancel</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>