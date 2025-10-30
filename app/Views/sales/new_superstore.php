<div class="max-w-5xl mx-auto bg-white p-8 rounded shadow-lg mt-8">
    <h2 class="text-3xl font-bold mb-6 text-blue-700">Super Store POS</h2>
    <?php if (session()->get('error')): ?>
        <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4"><?= session()->get('error') ?></div>
    <?php endif; ?>
    <form method="post" action="<?= site_url('sales/create') ?>">
        <?= csrf_field() ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- Barcode & Product Search -->
            <div>
                <label class="block mb-1 font-semibold">Scan Barcode</label>
                <input type="text" id="barcode-input" class="w-full border rounded px-3 py-2 mb-4" placeholder="Scan or enter barcode">
                <label class="block mb-1 font-semibold">Add Product</label>
                <select id="product-search" class="w-full border rounded px-3 py-2"></select>
            </div>
            <!-- Customer & Payment -->
            <div>
                <label class="block mb-1 font-semibold">Customer</label>
                <select name="customer_id" id="customer-select" class="w-full border rounded px-3 py-2 mb-4" required>
                    <option value="">Select Customer</option>
                    <?php foreach ($customers as $customer): ?>
                        <option value="<?= $customer['id'] ?>"><?= esc($customer['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <label class="block mb-1 font-semibold">Payment Method</label>
                <select name="payment_method" class="w-full border rounded px-3 py-2" required>
                    <option value="cash">Cash</option>
                    <option value="card">Card</option>
                    <option value="upi">UPI</option>
                    <option value="wallet">Wallet</option>
                </select>
            </div>
            <!-- Discount & Totals -->
            <div>
                <!-- Discounts -->
                <label class="block mb-1 font-semibold">Discount</label>
                <select id="discountSelect" name="discount_id" class="w-full border rounded p-2">
                    <option value="">No Discount</option>
                    <?php foreach ($discounts as $discount): ?>
                        <option value="<?= $discount['id'] ?>"
                            data-type="<?= $discount['type'] ?>"
                            data-value="<?= $discount['value'] ?>">
                            <?= $discount['name'] ?> (<?= $discount['type'] == 'percentage' ? $discount['value'] . '%' : '$' . $discount['value'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="total_discount" id="total_discount" value="0" min="0" class="w-full border rounded px-3 py-2 mb-4">

                <label class="block mb-1 font-semibold">Tax (%)</label>
                <input type="number" id="taxRate" name="tax_rate" value="15" min="0" max="100" class="w-full border rounded px-3 py-2 mb-4">
                <input type="hidden" id="total_tax" name="total_tax" value="" class="w-full border rounded px-3 py-2 mb-4">
            </div>
            <div>
                <div class="bg-gray-50 rounded p-4 mt-2">
                    <div class="flex justify-between mb-2">
                        <span class="font-semibold">Subtotal:</span>
                        <span id="subtotal" class="font-bold text-gray-700">0</span>
                    </div>
                    <div class="flex justify-between mb-2">
                        <span class="font-semibold">Discount:</span>
                        <span id="discountAmount" class="font-bold text-yellow-600">0</span>
                    </div>
                    <div class="flex justify-between mb-2">
                        <span class="font-semibold">Tax:</span>
                        <span id="taxAmount" class="font-bold text-green-600">0</span>
                    </div>
                    <div class="flex justify-between border-t pt-2">
                        <span class="font-bold text-lg">Total:</span>
                        <span id="cart-total" class="font-bold text-lg text-blue-700">0</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- Cart Table -->
        <div class="overflow-x-auto mb-6">
            <table id="cart-table" class="min-w-full bg-gray-50 rounded shadow">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="py-2 px-4">Product</th>
                        <th class="py-2 px-4">Price</th>
                        <th class="py-2 px-4">Qty</th>
                        <th class="py-2 px-4">Subtotal</th>
                        <th class="py-2 px-4">Remove</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        <input type="hidden" name="cart_data" id="cart-data">
        <div class="flex justify-end gap-4">
            <button type="button" onclick="window.location.reload()" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300">Clear</button>
            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 font-bold">Complete Sale</button>
        </div>
    </form>
</div>

<!-- Select2 CDN -->
<script src="<?php echo base_url() ?>assets/js/select2/select2.min.js"></script>
<link href="<?php echo base_url() ?>assets/js/select2/select2.min.css" rel="stylesheet" />
<script>
    $('#barcode-input').focus();

    $('#customer-select').select2({
        placeholder: 'Select Customer',
        allowClear: true
    });

    // Initialize Select2 for product search
    $('#product-search').select2({
        placeholder: 'Search for a product',
        minimumInputLength: 0,
        ajax: {
            url: '<?= site_url('api/products/search') ?>',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    q: params.term || ''
                };
            },
            processResults: function(data) {
                return {
                    results: data
                };
            }
        }
    });

    $('#product-search').on('select2:opening', function() {
        $('.select2-search__field').val('').trigger('input');
    });

    // Cart logic
    let cart = [];
    $('#product-search').on('select2:select', function(e) {
        const product = e.params.data;
        let existing = cart.find(item => item.id == product.id);
        if (existing) {
            if (existing.quantity < existing.stock) {
                existing.quantity += 1;
                renderCart();
            } else {
                alert('Only ' + existing.stock + ' in stock!');
            }
        } else {
            cart.push({
                id: product.id,
                name: product.text,
                price: product.price,
                quantity: 1,
                stock: product.quantity
            });
            renderCart();
        }
        $('#product-search').val(null).trigger('change');
    });

    $('#barcode-input').on('change', function() {
        var barcode = $(this).val();
        if (barcode) {
            $.get('<?= site_url('api/products/barcode') ?>', {
                barcode: barcode
            }, function(product) {
                if (product && product.id) {
                    let existing = cart.find(item => item.id == product.id);
                    if (existing) {
                        if (existing.quantity < existing.stock) {
                            existing.quantity += 1;
                            renderCart();
                        } else {
                            alert('Only ' + existing.stock + ' in stock!');
                        }
                    } else {
                        if (product.quantity > 0) {
                            cart.push({
                                id: product.id,
                                name: product.name,
                                price: product.price,
                                quantity: 1,
                                stock: product.quantity
                            });
                            renderCart();
                        } else {
                            alert('Product out of stock!');
                        }
                    }
                } else {
                    alert('Product not found!');
                }
                $('#barcode-input').val('');
                $('#barcode-input').focus();
            }, 'json');
        }
    });
    $('#barcode-input').on('keypress', function(e) {
        if (e.which == 13) {
            e.preventDefault();
            $(this).trigger('change');
        }
    });

    // Render cart
    function renderCart() {
        let tbody = '';
        let total = 0;
        cart.forEach((item, idx) => {
            let subtotal = item.price * item.quantity;
            total += subtotal;
            tbody += `<tr>
                <td class="py-2 px-4">${item.name}</td>
                <td class="py-2 px-4">${item.price}</td>
                <td class="py-2 px-4">
                    <input type="number" min="1" value="${item.quantity}" onchange="updateQty(${idx}, this.value)" class="w-16 border rounded px-2 py-1">
                </td>
                <td class="py-2 px-4">${subtotal}</td>
                <td class="py-2 px-4">
                    <button type="button" onclick="removeItem(${idx})" class="text-red-600 hover:underline">Remove</button>
                </td>
            </tr>`;
        });
        let discount = 0;
        let grandTotal = 0;
        let discountAmount = 0;
        // Apply discount if any
        const discountSelect = document.getElementById('discountSelect');
        const selectedOption = discountSelect.options[discountSelect.selectedIndex];

        if (selectedOption.value) {
            const discountType = selectedOption.dataset.type;
            const discountValue = parseFloat(selectedOption.dataset.value);


            if (discountType === 'percentage') {
                discountAmount = total * (discountValue / 100);
            } else {
                discountAmount = discountValue;
            }

            discount = `-${discountAmount}`;
            //grandTotal = `${(total - discountAmount)}`;
        } else {
            discount = '0.00';
            //grandTotal = `${total}`;
        }

        // Tax
        let taxRate = parseFloat(document.getElementById('taxRate').value) || 0;
        let taxableAmount = total - discountAmount;
        let taxAmount = taxableAmount * (taxRate / 100);

        grandTotal = taxableAmount + taxAmount;

        $('#total_discount').val(discountAmount.toFixed(2));
        $('#cart-table tbody').html(tbody);
        $('#subtotal').text(total.toFixed(2));
        $('#discountAmount').text(parseFloat(discount).toFixed(2));
        $('#taxAmount').text(taxAmount.toFixed(2));
        $('#total_tax').val(taxAmount.toFixed(2));
        $('#cart-total').text(grandTotal > 0 ? grandTotal.toFixed(2) : '0.00');
        $('#cart-data').val(JSON.stringify(cart));
    }

    // Update cart on discount or tax change
    $('#discountSelect, #taxRate').on('change input', renderCart);

    window.updateQty = function(idx, qty) {
        qty = parseInt(qty);
        if (qty < 1) qty = 1;
        if (qty > cart[idx].stock) {
            alert('Only ' + cart[idx].stock + ' in stock!');
            qty = cart[idx].stock;
        }
        cart[idx].quantity = qty;
        renderCart();
    }

    window.removeItem = function(idx) {
        cart.splice(idx, 1);
        renderCart();
    }

    // Initial render
    renderCart();
</script>