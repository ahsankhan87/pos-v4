<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-6">Draft Sales</h1>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <?php if (!empty($drafts)): ?>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created At</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($drafts as $draft): ?>
                        <tr>
                            <td class="px-6 py-4"> <?= esc($draft['invoice_no']) ?> </td>
                            <td class="px-6 py-4"> <?= esc($draft['customer_id']) ?> </td>
                            <td class="px-6 py-4"> <?= session()->get('currency_symbol') . number_format($draft['total'], 2) ?> </td>
                            <td class="px-6 py-4"> <?= esc($draft['created_at']) ?> </td>
                            <td class="px-6 py-4">
                                <a href="<?= site_url('sales/complete-draft/' . $draft['id']) ?>" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Complete</a>
                                <a href="<?= site_url('sales/receipt/' . $draft['id']) ?>" class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300 ml-2">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="p-6 text-center text-gray-500">No draft sales found.</div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>