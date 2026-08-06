<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php if (!isset($customer) || !is_array($customer)): ?>
    <div class="max-w-3xl mx-auto p-4">
        <div class="bg-red-50 text-red-700 border border-red-200 rounded-lg p-3"><?= lang('Customers.customer_not_found') ?></div>
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
                        <h1 class="text-lg font-bold text-gray-900"><?= lang('Customers.edit_customer') ?></h1>
                    </div>
                    <a href="<?= site_url('customers') ?>" class="text-sm text-gray-600 hover:text-gray-800 flex items-center gap-1">
                        <i class="fas fa-arrow-left"></i> <?= lang('Customers.back_to_customers') ?>
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
                            <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2"><i class="fas fa-id-card text-blue-600"></i> <?= lang('Customers.customer_info') ?></h3>
                        </div>
                        <div class="p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Customers.name') ?> <span class="text-red-500">*</span></label>
                                    <input type="text" name="name" value="<?= esc($customer['name']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Customers.email') ?> </label>
                                    <input type="email" name="email" value="<?= esc($customer['email']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Customers.phone') ?></label>
                                    <input type="text" name="phone" value="<?= esc($customer['phone']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Customers.vat_number') ?></label>
                                    <input type="text" name="vat_number" value="<?= esc($customer['vat_number'] ?? '') ?>" maxlength="15" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="<?= esc(lang('Customers.vat_number_placeholder')) ?>">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Customers.area') ?></label>
                                    <input type="text" name="area" value="<?= esc($customer['area'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="<?= esc(lang('Customers.area_placeholder')) ?>">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Customers.address') ?></label>
                                    <input type="text" name="address" value="<?= esc($customer['address']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Customers.opening_balance') ?></label>
                                    <input type="number" step="0.01" name="opening_balance" value="<?= esc($customer['opening_balance'] ?? '0.00') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="0.00">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Customers.credit_limit') ?></label>
                                    <input type="number" step="0.01" name="credit_limit" value="<?= esc($customer['credit_limit'] ?? '0.00') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="0.00">
                                </div>
                            </div>

                            <?php if (!empty($isZatcaEnabled)): ?>
                                <div class="mt-6 border-t border-gray-200 pt-4">
                                    <h4 class="text-sm font-bold text-gray-900 mb-3"><?= lang('Customers.zatca_buyer_details') ?></h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Customers.zatca_registration_name') ?></label>
                                            <input type="text" name="zatca_registration_name" value="<?= esc($customer['zatca_registration_name'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="<?= esc(lang('Customers.zatca_registration_name_placeholder')) ?>">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Customers.zatca_cr_number') ?></label>
                                            <input type="text" name="zatca_cr_number" value="<?= esc($customer['zatca_cr_number'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="<?= esc(lang('Customers.zatca_cr_number_placeholder')) ?>">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Customers.zatca_street_name') ?></label>
                                            <input type="text" name="zatca_street_name" value="<?= esc($customer['zatca_street_name'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="<?= esc(lang('Customers.zatca_street_name_placeholder')) ?>">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Customers.zatca_building_number') ?></label>
                                            <input type="text" name="zatca_building_number" value="<?= esc($customer['zatca_building_number'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="<?= esc(lang('Customers.zatca_building_number_placeholder')) ?>">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Customers.zatca_city_subdivision_name') ?></label>
                                            <input type="text" name="zatca_city_subdivision_name" value="<?= esc($customer['zatca_city_subdivision_name'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="<?= esc(lang('Customers.zatca_city_subdivision_name_placeholder')) ?>">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Customers.zatca_city_name') ?></label>
                                            <input type="text" name="zatca_city_name" value="<?= esc($customer['zatca_city_name'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="<?= esc(lang('Customers.zatca_city_name_placeholder')) ?>">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Customers.zatca_postal_code') ?></label>
                                            <input type="text" name="zatca_postal_code" value="<?= esc($customer['zatca_postal_code'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="<?= esc(lang('Customers.zatca_postal_code_placeholder')) ?>">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Customers.zatca_country_code') ?></label>
                                            <input type="text" name="zatca_country_code" value="<?= esc($customer['zatca_country_code'] ?? 'SA') ?>" maxlength="2" readonly class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-gray-100 text-gray-700">
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden sticky top-16">
                        <div class="px-4 py-2 bg-gradient-to-r from-slate-50 to-slate-100 border-b border-gray-200">
                            <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2"><i class="fas fa-save text-slate-600"></i> <?= lang('Customers.actions') ?></h3>
                        </div>
                        <div class="p-4 space-y-2">
                            <button type="submit" class="btn btn-primary w-full"><i class="fas fa-check"></i> <?= lang('Customers.update') ?></button>
                            <a href="<?= site_url('customers') ?>" class="btn btn-muted w-full"><i class="fas fa-times"></i> <?= lang('Customers.cancel') ?></a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>