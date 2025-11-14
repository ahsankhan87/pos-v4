<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Create Receipt Template</h2>
            <a href="<?= site_url('receipts/templates') ?>" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-arrow-left mr-2"></i>Back to Templates
            </a>
        </div>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= site_url('receipts/templates/store') ?>" method="post">
            <?= csrf_field() ?>

            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Template Name</label>
                <input type="text" id="name" name="name" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    value="<?= old('name') ?>"
                    placeholder="e.g., Thermal 80mm Receipt">
            </div>

            <div class="mb-4">
                <label for="is_default" class="flex items-center">
                    <input type="checkbox" id="is_default" name="is_default" value="1"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        <?= old('is_default') ? 'checked' : '' ?>>
                    <span class="ml-2 text-sm text-gray-700">Set as default template</span>
                </label>
            </div>

            <div class="mb-4">
                <label for="template" class="block text-sm font-medium text-gray-700 mb-2">HTML Template</label>
                <div class="mb-2 text-sm text-gray-600">
                    <strong>Available placeholders:</strong>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2 mt-2 p-3 bg-gray-50 rounded">
                        <code>{{store_name}}</code>
                        <code>{{store_address}}</code>
                        <code>{{store_phone}}</code>
                        <code>{{store_footer}}</code>
                        <code>{{receipt_number}}</code>
                        <code>{{date}}</code>
                        <code>{{cashier}}</code>
                        <code>{{customer}}</code>
                        <code>{{items}}</code>
                        <code>{{subtotal}}</code>
                        <code>{{total_discount}}</code>
                        <code>{{tax}}</code>
                        <code>{{total}}</code>
                        <code>{{paid}}</code>
                        <code>{{change}}</code>
                        <code>{{ItemsCount}}</code>
                        <code>{{payment_type}}</code>
                        <code>{{currency}}</code>
                        <code>{{employee}}</code>
                        <code>{{customer_name}}</code>
                        <code>{{customer_address}}</code>
                        <code>{{customer_phone}}</code>
                        <code>{{customer_balance}}</code>
                        <code>{{customer_month_sales}}</code>
                    </div>
                </div>
                <textarea id="template" name="template" rows="20" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 font-mono text-sm"
                    placeholder="Enter HTML template..."><?= esc(old('template')) ?></textarea>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="<?= site_url('receipts/templates') ?>" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Create Template
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Add syntax highlighting or preview functionality here if needed
    document.getElementById('template').addEventListener('input', function() {
        // Could add live preview functionality here
    });
</script>

<?= $this->endSection() ?>