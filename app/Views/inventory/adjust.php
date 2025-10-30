<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="container mx-auto p-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold"><?= $title ?></h1>
        <a href="<?= base_url('inventory') ?>" class="text-blue-600 hover:text-blue-800">Back to Inventory</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Stock Adjustment Form -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-lg font-medium mb-4">Adjust Stock</h2>

            <?php if (session()->has('errors')): ?>
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    <?php foreach (session('errors') as $error): ?>
                        <p><?= $error ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (session()->has('message')): ?>
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    <?= session('message') ?>
                </div>
            <?php endif; ?>

            <form action="<?= base_url("inventory/update/{$product['id']}") ?>" method="post">
                <?= csrf_field() ?>
                <div class="mb-4">
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Adjustment Type</label>
                    <select name="type" id="type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="in">Stock In (Add)</option>
                        <option value="out">Stock Out (Remove)</option>
                        <option value="adjustment">Set Exact Quantity</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                    <input type="number" name="quantity" id="quantity" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>

                <div class="mb-4">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" id="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Update Stock
                    </button>
                </div>
            </form>
        </div>

        <!-- Current Stock Info -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-lg font-medium mb-4">Current Stock Information</h2>

            <div class="space-y-4">
                <div>
                    <p class="text-sm text-gray-500">Product Name</p>
                    <p class="font-medium"><?= $product['name'] ?></p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Current Stock</p>
                    <p class="text-2xl font-bold <?= $product['quantity'] <= $product['stock_alert'] ? 'text-red-600' : 'text-green-600' ?>">
                        <?= $product['quantity'] ?>
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Stock Alert Level</p>
                    <p class="font-medium"><?= $product['stock_alert'] ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock History -->
    <div class="mt-8 bg-white p-6 rounded-lg shadow">
        <h2 class="text-lg font-medium mb-4">Stock Movement History</h2>

        <?php if (!empty($history)): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
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
                                        <?= ucfirst($log['type']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium <?= $log['type'] === 'in' ? 'text-green-600' : 'text-red-600' ?>">
                                    <?= $log['type'] === 'in' ? '+' : '-' ?><?= $log['quantity'] ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?= $log['notes'] ?? '--' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-gray-500">No stock movement history for this product</p>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>