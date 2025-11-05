<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="min-h-screen bg-slate-100">
    <!-- Top Bar -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-6xl mx-auto px-4">
            <div class="h-12 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded bg-gradient-to-br from-indigo-500 to-blue-600 text-white flex items-center justify-center shadow">
                        <i class="fas fa-user"></i>
                    </div>
                    <h1 class="text-lg font-bold text-gray-900">Customer Details</h1>
                </div>
                <div class="flex items-center gap-2">
                    <a href="<?= site_url('customers/edit/' . $customer['id']) ?>" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i> Edit</a>
                    <a href="<?= site_url('customers') ?>" class="btn btn-muted btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 py-4 grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-4 py-2 bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-gray-200">
                    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2"><i class="fas fa-id-card text-blue-600"></i> Customer Info</h3>
                </div>
                <div class="p-4 text-sm text-gray-800">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="text-xs text-gray-500">Name</div>
                            <div class="font-semibold"><?= esc($customer['name']) ?></div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Email</div>
                            <div class="font-semibold"><?= esc($customer['email']) ?></div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Phone</div>
                            <div class="font-semibold"><?= esc($customer['phone']) ?></div>
                        </div>
                        <div class="md:col-span-2">
                            <div class="text-xs text-gray-500">Address</div>
                            <div class="font-semibold"><?= esc($customer['address']) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-4 py-2 bg-gradient-to-r from-amber-50 to-yellow-50 border-b border-gray-200">
                    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2"><i class="fas fa-star text-amber-600"></i> Loyalty & Meta</h3>
                </div>
                <div class="p-4 text-sm text-gray-800 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <div class="text-xs text-gray-500">Customer ID</div>
                        <div class="font-semibold">#<?= (int) $customer['id'] ?></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Loyalty Points</div>
                        <div class="font-semibold"><?= number_format((float) ($customer['points'] ?? 0)) ?></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Created</div>
                        <div class="font-semibold"><?= esc($customer['created_at']) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-4 py-2 bg-gradient-to-r from-slate-50 to-slate-100 border-b border-gray-200">
                    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2"><i class="fas fa-ellipsis-h text-slate-600"></i> Actions</h3>
                </div>
                <div class="p-4 space-y-2">
                    <a href="<?= site_url('customers/edit/' . $customer['id']) ?>" class="btn btn-primary w-full"><i class="fas fa-edit"></i> Edit Customer</a>
                    <a href="<?= site_url('customers') ?>" class="btn btn-muted w-full"><i class="fas fa-arrow-left"></i> Back to List</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>