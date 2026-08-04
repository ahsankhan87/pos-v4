<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php $isZatcaEnabled = !empty($isZatcaEnabled); ?>
<?php $storeProfile = is_array($storeProfile ?? null) ? $storeProfile : []; ?>
<div class="min-h-screen bg-slate-100">
    <!-- Top Bar -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-6xl mx-auto px-4">
            <div class="h-12 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded bg-gradient-to-br from-indigo-500 to-blue-600 text-white flex items-center justify-center shadow">
                        <i class="fas fa-user"></i>
                    </div>
                    <h1 class="text-lg font-bold text-gray-900"><?= lang('Customers.customer_details') ?></h1>
                </div>
                <div class="flex items-center gap-2">
                    <a href="<?= site_url('customers/edit/' . $customer['id']) ?>" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i> <?= lang('Customers.edit') ?></a>
                    <a href="<?= site_url('customers') ?>" class="btn btn-muted btn-sm"><i class="fas fa-arrow-left"></i> <?= lang('Customers.back') ?></a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 py-4 grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-4 py-2 bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-gray-200">
                    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2"><i class="fas fa-id-card text-blue-600"></i> <?= lang('Customers.customer_info') ?></h3>
                </div>
                <div class="p-4 text-sm text-gray-800">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="text-xs text-gray-500"><?= lang('Customers.name') ?></div>
                            <div class="font-semibold"><?= esc($customer['name']) ?></div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500"><?= lang('Customers.email') ?></div>
                            <div class="font-semibold"><?= esc($customer['email']) ?></div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500"><?= lang('Customers.phone') ?></div>
                            <div class="font-semibold"><?= esc($customer['phone']) ?></div>
                        </div>
                        <?php if ($isZatcaEnabled): ?>
                            <div>
                                <div class="text-xs text-gray-500"><?= lang('Customers.vat_number') ?></div>
                                <div class="font-semibold"><?= esc($customer['vat_number'] ?? '') ?: '-' ?></div>
                            </div>
                        <?php endif; ?>
                        <div>
                            <div class="text-xs text-gray-500"><?= lang('Customers.area') ?></div>
                            <div class="font-semibold">
                                <?php if (!empty($customer['area'])): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-50 text-blue-700">
                                        <i class="fas fa-map-marker-alt mr-1"></i><?= esc($customer['area']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-400"><?= lang('Customers.not_set') ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <div class="text-xs text-gray-500"><?= lang('Customers.address') ?></div>
                            <div class="font-semibold"><?= esc($customer['address']) ?></div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500"><?= lang('Customers.opening_balance') ?></div>
                            <div class="font-semibold text-green-600"><?= number_format($customer['opening_balance'] ?? 0, 2) ?></div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500"><?= lang('Customers.credit_limit') ?></div>
                            <div class="font-semibold text-blue-600"><?= number_format($customer['credit_limit'] ?? 0, 2) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($isZatcaEnabled): ?>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-4 py-2 bg-gradient-to-r from-emerald-50 to-teal-50 border-b border-gray-200">
                        <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2"><i class="fas fa-file-invoice text-emerald-600"></i> <?= lang('Customers.zatca_customer_profile') ?></h3>
                    </div>
                    <div class="p-4 text-sm text-gray-800">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <div class="text-xs text-gray-500"><?= lang('Customers.zatca_registration_name') ?></div>
                                <div class="font-semibold"><?= esc($customer['zatca_registration_name'] ?? '') ?: '-' ?></div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500"><?= lang('Customers.zatca_cr_number') ?></div>
                                <div class="font-semibold"><?= esc($customer['zatca_cr_number'] ?? '') ?: '-' ?></div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500"><?= lang('Customers.zatca_street_name') ?></div>
                                <div class="font-semibold"><?= esc($customer['zatca_street_name'] ?? '') ?: '-' ?></div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500"><?= lang('Customers.zatca_building_number') ?></div>
                                <div class="font-semibold"><?= esc($customer['zatca_building_number'] ?? '') ?: '-' ?></div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500"><?= lang('Customers.zatca_city_subdivision_name') ?></div>
                                <div class="font-semibold"><?= esc($customer['zatca_city_subdivision_name'] ?? '') ?: '-' ?></div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500"><?= lang('Customers.zatca_city_name') ?></div>
                                <div class="font-semibold"><?= esc($customer['zatca_city_name'] ?? '') ?: '-' ?></div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500"><?= lang('Customers.zatca_postal_code') ?></div>
                                <div class="font-semibold"><?= esc($customer['zatca_postal_code'] ?? '') ?: '-' ?></div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500"><?= lang('Customers.zatca_country_code') ?></div>
                                <div class="font-semibold"><?= esc($customer['zatca_country_code'] ?? 'SA') ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-4 py-2 bg-gradient-to-r from-blue-50 to-cyan-50 border-b border-gray-200">
                        <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2"><i class="fas fa-building text-blue-600"></i> <?= lang('Customers.zatca_company_profile') ?></h3>
                    </div>
                    <div class="p-4 text-sm text-gray-800">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <div class="text-xs text-gray-500"><?= lang('Customers.company_name') ?></div>
                                <div class="font-semibold"><?= esc($storeProfile['name'] ?? '') ?: '-' ?></div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500"><?= lang('Customers.vat_number') ?></div>
                                <div class="font-semibold"><?= esc($storeProfile['zatca_seller_vat_number'] ?? '') ?: '-' ?></div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500"><?= lang('Customers.zatca_registration_name') ?></div>
                                <div class="font-semibold"><?= esc($storeProfile['zatca_seller_legal_name'] ?? '') ?: '-' ?></div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500"><?= lang('Customers.zatca_street_name') ?></div>
                                <div class="font-semibold"><?= esc($storeProfile['zatca_street_name'] ?? '') ?: '-' ?></div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500"><?= lang('Customers.zatca_building_number') ?></div>
                                <div class="font-semibold"><?= esc($storeProfile['zatca_building_number'] ?? '') ?: '-' ?></div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500"><?= lang('Customers.zatca_city_subdivision_name') ?></div>
                                <div class="font-semibold"><?= esc($storeProfile['zatca_city_subdivision_name'] ?? '') ?: '-' ?></div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500"><?= lang('Customers.zatca_city_name') ?></div>
                                <div class="font-semibold"><?= esc($storeProfile['zatca_city_name'] ?? '') ?: '-' ?></div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500"><?= lang('Customers.zatca_postal_code') ?></div>
                                <div class="font-semibold"><?= esc($storeProfile['zatca_postal_code'] ?? '') ?: '-' ?></div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500"><?= lang('Customers.zatca_country_code') ?></div>
                                <div class="font-semibold"><?= esc($storeProfile['zatca_country_code'] ?? 'SA') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-4 py-2 bg-gradient-to-r from-amber-50 to-yellow-50 border-b border-gray-200">
                    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2"><i class="fas fa-star text-amber-600"></i> <?= lang('Customers.loyalty_meta') ?></h3>
                </div>
                <div class="p-4 text-sm text-gray-800 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <div class="text-xs text-gray-500"><?= lang('Customers.customer_id') ?></div>
                        <div class="font-semibold">#<?= (int) $customer['id'] ?></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500"><?= lang('Customers.loyalty_points') ?></div>
                        <div class="font-semibold"><?= number_format((float) ($customer['points'] ?? 0)) ?></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500"><?= lang('Customers.created') ?></div>
                        <div class="font-semibold"><?= esc($customer['created_at']) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-4 py-2 bg-gradient-to-r from-slate-50 to-slate-100 border-b border-gray-200">
                    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2"><i class="fas fa-ellipsis-h text-slate-600"></i> <?= lang('Customers.actions') ?></h3>
                </div>
                <div class="p-4 space-y-2">
                    <?php if (can('sales.create')): ?>
                        <a href="<?= site_url('sales/new?customer_id=' . (int) $customer['id']) ?>" class="btn btn-success w-full">
                            <i class="fas fa-shopping-bag"></i> <?= lang('Customers.new_sale_btn') ?>
                        </a>
                        <!-- <a href="<?= site_url('sales/pos?customer_id=' . (int) $customer['id']) ?>" class="btn btn-info w-full">
                            <i class="fas fa-cash-register"></i> POS Sale
                        </a> -->
                    <?php endif; ?>
                    <a href="<?= site_url('customers/edit/' . $customer['id']) ?>" class="btn btn-primary w-full"><i class="fas fa-edit"></i> <?= lang('Customers.edit_customer_btn') ?></a>
                    <a href="<?= site_url('customers') ?>" class="btn btn-muted w-full"><i class="fas fa-arrow-left"></i> <?= lang('Customers.back_to_list') ?></a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>