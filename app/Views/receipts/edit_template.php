<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Edit Receipt Template</h2>
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

        <form action="<?= site_url('receipts/templates/update/' . $template['id']) ?>" method="post">
            <?= csrf_field() ?>

            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Template Name</label>
                <input type="text" id="name" name="name" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    value="<?= old('name', $template['name']) ?>"
                    placeholder="e.g., Thermal 80mm Receipt">
            </div>

            <div class="mb-4">
                <label for="is_default" class="flex items-center">
                    <input type="checkbox" id="is_default" name="is_default" value="1"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        <?= old('is_default', $template['is_default']) ? 'checked' : '' ?>>
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
                    </div>
                </div>
                <textarea id="template" name="template" rows="20" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 font-mono text-sm"
                    placeholder="Enter HTML template..."><?= esc(old('template', $template['template'])) ?></textarea>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="<?= site_url('receipts/templates') ?>" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Update Template
                </button>
            </div>
        </form>

        <!-- Preview Section -->
        <div class="mt-8 border-t pt-6">
            <h3 class="text-lg font-semibold mb-4">Template Preview</h3>
            <div class="border rounded-lg p-4 bg-gray-50">
                <iframe id="preview" style="width: 100%; height: 600px; border: 1px solid #ddd; background: white; border-radius: 4px;"></iframe>
            </div>
            <button type="button" onclick="updatePreview()" class="mt-3 px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                <i class="fas fa-sync-alt mr-2"></i>Update Preview
            </button>
        </div>
    </div>
</div>

<script>
    function updatePreview() {
        const templateContent = document.getElementById('template').value;
        const previewFrame = document.getElementById('preview');

        // Replace placeholders with sample data
        let preview = templateContent;
        const sampleData = {
            '{{store_name}}': 'Sample Store',
            '{{store_address}}': '123 Main Street, City',
            '{{store_phone}}': '(555) 123-4567',
            '{{store_footer}}': 'Thank you for your business!',
            '{{receipt_number}}': 'INV-2025-001',
            '{{date}}': new Date().toLocaleString(),
            '{{cashier}}': 'John Doe',
            '{{customer}}': '<div>Customer: Jane Smith</div>',
            '{{items}}': '<tr><td>Sample Product</td><td>2</td><td>$10.00</td><td>$20.00</td></tr>',
            '{{subtotal}}': '20.00',
            '{{total_discount}}': '2.00',
            '{{tax}}': '1.80',
            '{{total}}': '19.80',
            '{{paid}}': '20.00',
            '{{change}}': '0.20'
        };

        for (const [key, value] of Object.entries(sampleData)) {
            preview = preview.replace(new RegExp(key.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g'), value);
        }

        // Write to iframe to isolate the HTML
        const iframeDoc = previewFrame.contentDocument || previewFrame.contentWindow.document;
        iframeDoc.open();
        iframeDoc.write(preview);
        iframeDoc.close();
    }

    // Load preview on page load
    window.addEventListener('load', function() {
        setTimeout(updatePreview, 300);
    });
</script>

<?= $this->endSection() ?>