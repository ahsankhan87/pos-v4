<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-6">Point of Sale</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Product Selection -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold">Products</h2>
                <div class="relative">
                    <input type="text" id="productSearch" placeholder="Search products..."
                        class="pl-8 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <svg class="absolute left-2.5 top-2.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4" id="productGrid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card border rounded-lg p-4 hover:shadow-md transition cursor-pointer"
                        data-id="<?= $product['id'] ?>"
                        data-name="<?= htmlspecialchars($product['name']) ?>"
                        data-price="<?= $product['price'] ?>">
                        <div class="font-medium truncate"><?= $product['name'] ?></div>
                        <div class="text-sm text-gray-500"><?= $product['code'] ?></div>
                        <div class="mt-2 font-bold">$<?= number_format($product['price'], 2) ?></div>
                        <div class="text-xs text-gray-500 mt-1">Stock: <?= $product['quantity'] ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Cart Summary -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Current Sale</h2>

            <!-- Customer Selection -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                <select id="customerSelect" class="w-full border rounded p-2">
                    <option value="">Walk-in Customer</option>
                    <?php foreach ($customers as $customer): ?>
                        <option value="<?= $customer['id'] ?>">
                            <?= $customer['name'] ?> (<?= $customer['phone'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Cart Items -->
            <div class="border rounded-lg mb-4 max-h-96 overflow-y-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase"></th>
                        </tr>
                    </thead>
                    <tbody id="cartItems" class="bg-white divide-y divide-gray-200">
                        <?php foreach ($cartItems as $item): ?>
                            <tr data-id="<?= $item['product_id'] ?>">
                                <td class="px-4 py-2 whitespace-nowrap text-sm">
                                    <?= $item['product_name'] ?>
                                    <div class="text-xs text-gray-500">$<?= number_format($item['price'], 2) ?></div>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    <input type="number" value="<?= $item['quantity'] ?>" min="1"
                                        class="w-16 border rounded p-1 quantity-input">
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm">
                                    $<?= number_format($item['price'] * $item['quantity'], 2) ?>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-right">
                                    <button class="text-red-500 remove-item">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Discounts -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Discount</label>
                <select id="discountSelect" class="w-full border rounded p-2">
                    <option value="">No Discount</option>
                    <?php foreach ($discounts as $discount): ?>
                        <option value="<?= $discount['id'] ?>"
                            data-type="<?= $discount['type'] ?>"
                            data-value="<?= $discount['value'] ?>">
                            <?= $discount['name'] ?> (<?= $discount['type'] == 'percentage' ? $discount['value'] . '%' : '$' . $discount['value'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Totals -->
            <div class="space-y-2 mb-4">
                <div class="flex justify-between">
                    <span>Subtotal:</span>
                    <span id="subtotal">$0.00</span>
                </div>
                <div class="flex justify-between">
                    <span>Discount:</span>
                    <span id="discountAmount">$0.00</span>
                </div>
                <div class="flex justify-between border-t pt-2 font-bold">
                    <span>Total:</span>
                    <span id="totalAmount">$0.00</span>
                </div>
            </div>

            <!-- Payment Buttons -->
            <div class="grid grid-cols-2 gap-2">
                <button id="holdCart" class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                    Hold
                </button>
                <button id="completeSale" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Complete Sale
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        // Product search functionality
        document.getElementById('productSearch').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            document.querySelectorAll('.product-card').forEach(card => {
                const name = card.dataset.name.toLowerCase();
                const code = card.querySelector('.text-gray-500').textContent.toLowerCase();
                if (name.includes(searchTerm) || code.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Add product to cart
        document.querySelectorAll('.product-card').forEach(card => {
            card.addEventListener('click', function() {
                const productId = this.dataset.id;
                addToCart(productId, 1);
            });
        });

        // Update cart quantities
        document.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('change', function() {
                const productId = this.closest('tr').dataset.id;
                const quantity = parseInt(this.value);
                updateCartItem(productId, quantity);
            });
        });

        // Remove items from cart
        document.querySelectorAll('.remove-item').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.closest('tr').dataset.id;
                removeFromCart(productId);
            });
        });

        // Apply discount
        document.getElementById('discountSelect').addEventListener('change', function() {
            applyDiscount(this.value);
        });

        // Complete sale button
        document.getElementById('completeSale').addEventListener('click', completeSale);

        // Initialize cart totals
        updateCartTotals();
    });

    let csrfName = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';

    function addToCart(productId, quantity) {

        fetch('<?= base_url('sales/add-to-cart') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: quantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateCartDisplay(data.cart);
                } else {
                    alert(data.message || 'Error adding to cart');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert(error.message);
            });
    }


    function updateCartItem(productId, quantity) {
        const payload = {
            product_id: productId,
            quantity: quantity,
            ['<?= csrf_token() ?>']: '<?= csrf_hash() ?>'
        };
        fetch('<?= base_url('sales/add-to-cart') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateCartDisplay(data.cart);
                }
            });
    }

    function updateCartDisplay(cartItems) {
        const cartTable = document.getElementById('cartItems');
        cartTable.innerHTML = '';

        cartItems.forEach(item => {
            const row = document.createElement('tr');
            row.dataset.id = item.product_id;
            row.innerHTML = `
            <td class="px-4 py-2 whitespace-nowrap text-sm">
                ${item.name}
                <div class="text-xs text-gray-500">$${item.price.toFixed(2)}</div>
            </td>
            <td class="px-4 py-2 whitespace-nowrap">
                <input type="number" value="${item.quantity}" min="1" 
                       class="w-16 border rounded p-1 quantity-input">
            </td>
            <td class="px-4 py-2 whitespace-nowrap text-sm">
                $${(item.price * item.quantity).toFixed(2)}
            </td>
            <td class="px-4 py-2 whitespace-nowrap text-right">
                <button class="text-red-500 remove-item">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </td>
        `;
            cartTable.appendChild(row);

            // Add event listeners to new elements
            row.querySelector('.quantity-input').addEventListener('change', function() {
                updateCartItem(item.product_id, parseInt(this.value));
            });

            row.querySelector('.remove-item').addEventListener('click', function() {
                removeFromCart(item.product_id);
            });
        });

        updateCartTotals();
    }

    function updateCartTotals() {
        let subtotal = 0;
        document.querySelectorAll('#cartItems tr').forEach(row => {
            const price = parseFloat(row.querySelector('td:nth-child(1) div').textContent.replace('$', ''));
            const quantity = parseInt(row.querySelector('td:nth-child(2) input').value);
            subtotal += price * quantity;
        });

        document.getElementById('subtotal').textContent = `$${subtotal.toFixed(2)}`;

        // Apply discount if any
        const discountSelect = document.getElementById('discountSelect');
        const selectedOption = discountSelect.options[discountSelect.selectedIndex];

        if (selectedOption.value) {
            const discountType = selectedOption.dataset.type;
            const discountValue = parseFloat(selectedOption.dataset.value);

            let discountAmount = 0;
            if (discountType === 'percentage') {
                discountAmount = subtotal * (discountValue / 100);
            } else {
                discountAmount = discountValue;
            }

            document.getElementById('discountAmount').textContent = `-$${discountAmount.toFixed(2)}`;
            document.getElementById('totalAmount').textContent = `$${(subtotal - discountAmount).toFixed(2)}`;
        } else {
            document.getElementById('discountAmount').textContent = '$0.00';
            document.getElementById('totalAmount').textContent = `$${subtotal.toFixed(2)}`;
        }
    }

    function completeSale() {
        const customerId = document.getElementById('customerSelect').value;
        const discountId = document.getElementById('discountSelect').value;

        fetch('<?= base_url('sales/complete') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    customer_id: customerId,
                    discount_id: discountId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '<?= base_url('sales/receipt1') ?>/' + data.sale_id;
                } else {
                    alert(data.message || 'Error completing sale');
                }
            });
    }
</script>