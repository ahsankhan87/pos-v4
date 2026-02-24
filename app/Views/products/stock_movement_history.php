 <?= $this->extend('templates/header') ?>
 <?= $this->section('content') ?>
 <div class="container mx-auto p-4">
     <div class="flex justify-between items-center mb-6">
         <h1 class="text-2xl font-bold"><?= $title ?></h1>

         <a href="<?= base_url('products') ?>" class="text-blue-600 hover:text-blue-800"><?= lang('Products.back_to_products') ?></a>
     </div>

     <!-- Stock History -->
     <div class="mt-8 bg-white p-6 rounded-lg shadow">
         <p class="text-sm text-grey-500 mb-4"><?= lang('Products.stock_history_for_product_id') ?>: <?= esc($productId) ?></p>
         <?php if (!empty($history)): ?>
             <div class="overflow-x-auto">
                 <table class="min-w-full divide-y divide-gray-200">
                     <thead class="bg-gray-50">
                         <tr>
                             <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Products.date') ?></th>
                             <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Products.user') ?></th>
                             <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Products.type') ?></th>
                             <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Products.quantity') ?></th>
                             <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Products.notes') ?></th>
                         </tr>
                     </thead>
                     <tbody class="bg-white divide-y divide-gray-200">
                         <?php foreach ($history as $log): ?>
                             <tr>
                                 <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                     <?= date('d M Y H:i', strtotime($log['created_at'])) ?>
                                 </td>
                                 <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                     <?= $log['username'] ?>
                                 </td>
                                 <td class="px-6 py-4 whitespace-nowrap">
                                     <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?= $log['type'] === 'in' ? 'bg-green-100 text-green-800' : ($log['type'] === 'out' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') ?>">
                                         <?= lang('Products.stock_type_' . strtolower($log['type'])) ?>
                                     </span>
                                 </td>
                                 <td class="px-6 py-4 whitespace-nowrap text-sm font-medium <?= $log['type'] === 'in' ? 'text-green-600' : 'text-red-600' ?>">
                                     <?= $log['type'] === 'in' ? '+' : '-' ?><?= $log['quantity'] ?>
                                 </td>
                                 <td class="px-6 py-4 text-sm text-gray-500">
                                     <?= $log['notes'] ?? lang('Products.not_available') ?>
                                 </td>
                             </tr>
                         <?php endforeach; ?>
                     </tbody>
                 </table>
             </div>
         <?php else: ?>
             <p class="text-gray-500"><?= lang('Products.no_stock_movement_history_for_product') ?></p>
         <?php endif; ?>
     </div>
 </div>
 <?= $this->endSection() ?>