<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="min-h-screen bg-slate-100">
    <!-- Top Bar -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="h-16 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center shadow-md">
                        <i class="fas fa-book text-lg"></i>
                    </div>
                    <h1 class="text-xl font-bold text-gray-900"><?= esc($title) ?></h1>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <?php if (session()->has('message')): ?>
            <div class="mb-4 bg-green-50 border-l-4 border-green-500 text-green-800 rounded-lg p-4 shadow-sm">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-3 text-green-500"></i>
                    <span><?= session('message') ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if (session()->has('error')): ?>
            <div class="mb-4 bg-red-50 border-l-4 border-red-500 text-red-800 rounded-lg p-4 shadow-sm">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-3 text-red-500"></i>
                    <span><?= session('error') ?></span>
                </div>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-slate-50 to-slate-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Supplier Name</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Phone</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Opening Balance</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Current Balance</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (!empty($suppliers)): ?>
                            <?php foreach ($suppliers as $supplier): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?= esc($supplier['name']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <?= esc($supplier['phone']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <?= esc($supplier['email']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="font-medium text-gray-900">
                                            <?= number_to_currency($supplier['opening_balance'], 'PKR', 'en_PK', 2) ?>
                                        </span>
                                        <button class="ml-2 text-blue-600 hover:text-blue-800 transition-colors edit-opening-balance"
                                            data-supplier-id="<?= $supplier['id'] ?>"
                                            data-current-balance="<?= $supplier['opening_balance'] ?>"
                                            title="Edit Opening Balance">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold <?= $supplier['current_balance'] > 0 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' ?>">
                                            <?= number_to_currency($supplier['current_balance'], 'PKR', 'en_PK', 2) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                        <a href="<?= base_url('supplier-ledger/view/' . $supplier['id']) ?>"
                                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
                                            <i class="fas fa-eye mr-2"></i> View Ledger
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i>
                                        <p class="text-gray-500 text-sm">No suppliers found</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Opening Balance Modal -->
<div id="openingBalanceModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-xl bg-white">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">Update Opening Balance</h3>
            <button type="button" class="text-gray-400 hover:text-gray-600 close-modal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="mt-2">
            <form id="openingBalanceForm">
                <input type="hidden" id="supplierId" name="supplier_id">
                <div class="mb-4">
                    <label for="openingBalance" class="block text-sm font-semibold text-gray-700 mb-2">
                        Opening Balance
                    </label>
                    <input type="number" step="0.01"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        id="openingBalance" name="opening_balance" required>
                    <p class="mt-2 text-xs text-gray-500">
                        Positive value means you owe the supplier (payable).
                    </p>
                </div>
            </form>
        </div>
        <div class="flex gap-2 justify-end mt-4">
            <button type="button"
                class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 text-sm font-semibold rounded-lg transition-colors close-modal">
                Cancel
            </button>
            <button type="button" id="saveOpeningBalance"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors">
                Save
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('openingBalanceModal');

        // Show modal
        function showModal() {
            modal.classList.remove('hidden');
        }

        // Hide modal
        function hideModal() {
            modal.classList.add('hidden');
        }

        // Close buttons
        document.querySelectorAll('.close-modal').forEach(btn => {
            btn.addEventListener('click', hideModal);
        });

        // Close on outside click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                hideModal();
            }
        });

        // Edit opening balance
        document.querySelectorAll('.edit-opening-balance').forEach(btn => {
            btn.addEventListener('click', function() {
                const supplierId = this.dataset.supplierId;
                const currentBalance = this.dataset.currentBalance;

                document.getElementById('supplierId').value = supplierId;
                document.getElementById('openingBalance').value = currentBalance;

                showModal();
            });
        });

        // Save opening balance
        document.getElementById('saveOpeningBalance').addEventListener('click', function() {
            const formData = new FormData(document.getElementById('openingBalanceForm'));

            fetch('<?= base_url('supplier-ledger/update-opening-balance') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert(data.message || 'Failed to update opening balance');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating opening balance');
                });
        });
    });
</script>

<?= $this->endSection() ?>