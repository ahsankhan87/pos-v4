<?php
/**
 * INVOICE TEMPLATE IMPLEMENTATION REFERENCE
 * 
 * This file shows how to integrate the professional ZATCA-compliant invoice template
 * with your CodeIgniter Sales controller.
 * 
 * Copy and adapt the methods shown here into your existing Sales controller.
 */

// ============================================================================
// Add to your Sales.php controller
// ============================================================================

public function printInvoice($invoiceId = null)
{
    if (!$invoiceId) {
        return redirect()->to('sales')->with('error', lang('Sales.invoice_not_found'));
    }

    // Verify permission
    if (!can('sales.view')) {
        return redirect()->to('sales')->with('error', lang('Auth.permission_denied'));
    }

    // Get locale for RTL/LTR support
    $locale = session('locale') ?? 'en';
    $direction = $locale === 'ar' ? 'rtl' : 'ltr';
    
    // Load models
    $saleModel = new M_sales();
    $customerModel = new M_customers();
    $storeModel = new StoreModel();
    $saleDetailsModel = new M_sale_details();

    // Get invoice/sale data
    $sale = $saleModel->forStore()->find($invoiceId);
    if (!$sale) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    // Get customer data
    $customer = $customerModel->forStore()->find($sale['customer_id']);
    if (!$customer) {
        return redirect()->to('sales')->with('error', lang('Sales.customer_not_found'));
    }

    // Get store data (from session)
    $storeId = (int) (session('store_id') ?? 0);
    $store = $storeModel->find($storeId);
    if (!$store) {
        return redirect()->to('sales')->with('error', lang('Sales.store_not_found'));
    }

    // Get store logo
    $storeLogo = '';
    if (!empty($store['logo']) && file_exists('uploads/logos/' . $store['logo'])) {
        $storeLogo = '<img src="' . base_url('uploads/logos/' . $store['logo']) . '" alt="Logo" style="max-height: 60px;">';
    } else {
        $storeLogo = '<div style="font-size: 18px; font-weight: bold; color: #1a472a;">' . esc($store['name']) . '</div>';
    }

    // Prepare store/seller info
    $storeData = [
        'store_name' => $store['name'],
        'store_logo_img' => $storeLogo,
        'store_legal_name' => !empty($store['zatca_seller_legal_name']) ? $store['zatca_seller_legal_name'] : $store['name'],
        'store_cr_number' => $store['cr_number'] ?? 'N/A',
        'store_vat_number' => $store['zatca_seller_vat_number'] ?? 'N/A',
        'store_phone' => $store['phone'] ?? '',
        'store_email' => $store['email'] ?? '',
        
        // ZATCA Postal Address
        'store_street_name' => $store['zatca_street_name'] ?? '',
        'store_building_number' => $store['zatca_building_number'] ?? '',
        'store_city_subdivision_name' => $store['zatca_city_subdivision_name'] ?? '',
        'store_city_name' => $store['zatca_city_name'] ?? '',
        'store_postal_code' => $store['zatca_postal_code'] ?? '',
        'store_country_name' => 'المملكة العربية السعودية', // Saudi Arabia in Arabic
    ];

    // Prepare invoice header info
    $invoiceData = [
        'invoice_number' => $sale['invoice_number'] ?? $sale['id'],
        'invoice_date' => date('d-m-Y', strtotime($sale['created_at'] ?? date('Y-m-d'))),
        'due_date' => !empty($sale['due_date']) ? date('d-m-Y', strtotime($sale['due_date'])) : date('d-m-Y', strtotime('+30 days')),
        'invoice_type_label' => lang('Invoice.invoice'),
        'invoice_type_class' => '',
    ];

    // Prepare customer/buyer info
    $customerData = [
        'customer_name' => $customer['name'],
        'customer_phone' => $customer['phone'] ?? '',
        'customer_email' => $customer['email'] ?? '',
        'customer_vat_number' => $customer['vat_number'] ?? '',
        
        // ZATCA Buyer Details
        'customer_zatca_registration_name' => $customer['zatca_registration_name'] ?? $customer['name'],
        'customer_zatca_cr_number' => $customer['zatca_cr_number'] ?? '',
        'customer_street_name' => $customer['zatca_street_name'] ?? '',
        'customer_building_number' => $customer['zatca_building_number'] ?? '',
        'customer_city_subdivision_name' => $customer['zatca_city_subdivision_name'] ?? '',
        'customer_city_name' => $customer['zatca_city_name'] ?? '',
        'customer_postal_code' => $customer['zatca_postal_code'] ?? '',
        'customer_country_name' => 'المملكة العربية السعودية',
    ];

    // Calculate totals
    $saleDetails = $saleDetailsModel->where('sale_id', $invoiceId)->findAll();
    $subtotal = 0;
    $totalDiscount = 0;
    foreach ($saleDetails as $detail) {
        $subtotal += ($detail['quantity'] * $detail['unit_price']);
        $totalDiscount += $detail['discount'] ?? 0;
    }
    
    $taxRate = 15; // Standard Saudi VAT
    $taxableAmount = $subtotal - $totalDiscount;
    $taxAmount = $taxableAmount * ($taxRate / 100);
    $grandTotal = $taxableAmount + $taxAmount;

    $totalsData = [
        'currency' => $store['currency_symbol'] ?? 'SR',
        'subtotal' => number_format($subtotal, 2),
        'total_discount' => number_format($totalDiscount, 2),
        'tax_rate' => $taxRate,
        'total_tax' => number_format($taxAmount, 2),
        'grand_total' => number_format($grandTotal, 2),
        'amount_paid' => number_format($sale['paid_amount'] ?? 0, 2),
        'balance_due' => number_format($grandTotal - ($sale['paid_amount'] ?? 0), 2),
    ];

    // Prepare line items
    $invoiceItems = $this->generateInvoiceItemsHTML($saleDetails);

    // ZATCA QR Code and digital signature
    $zatcaData = [
        'zatca_qr_code' => $this->generateZATCAQRCode($sale, $store, $customer, $grandTotal),
        'invoice_hash' => $sale['digital_signature'] ?? 'PENDING_SIGNATURE',
        'invoice_uuid' => $sale['uuid'] ?? $this->generateUUID(),
    ];

    // Additional info
    $additionalData = [
        'direction' => $direction,
        'invoice_notes' => $sale['notes'] ?? '',
        'payment_terms' => $sale['payment_terms'] ?? lang('Invoice.payment_terms_label') . ': Net 30',
        'footer_text' => lang('Invoice.thank_you'),
        'invoice_items' => $invoiceItems,
    ];

    // Merge all data
    $data = array_merge($storeData, $invoiceData, $customerData, $totalsData, $zatcaData, $additionalData);

    // Render professional invoice template
    return view('invoices/professional_invoice_a4', $data);
}

// ============================================================================
// Helper method to generate invoice items HTML
// ============================================================================

private function generateInvoiceItemsHTML($saleDetails)
{
    $html = '';
    $itemNo = 1;
    
    foreach ($saleDetails as $detail) {
        $lineTotal = ($detail['quantity'] * $detail['unit_price']) - ($detail['discount'] ?? 0);
        
        $html .= '<tr>';
        $html .= '<td class="text-center">' . $itemNo . '</td>';
        $html .= '<td><strong>' . esc($detail['product_name'] ?? 'Item #' . $detail['product_id']) . '</strong><br>';
        
        // Add description if available
        if (!empty($detail['description'])) {
            $html .= '<small style="color: #666;">' . esc($detail['description']) . '</small>';
        }
        
        $html .= '</td>';
        $html .= '<td class="text-center">' . number_format($detail['quantity'], 2) . '</td>';
        $html .= '<td class="text-right">' . number_format($detail['unit_price'], 2) . '</td>';
        $html .= '<td class="text-right">' . number_format($detail['discount'] ?? 0, 2) . '</td>';
        $html .= '<td class="text-right"><strong>' . number_format($lineTotal, 2) . '</strong></td>';
        $html .= '</tr>';
        
        $itemNo++;
    }
    
    return $html;
}

// ============================================================================
// Helper method to generate ZATCA QR Code
// ============================================================================

private function generateZATCAQRCode($sale, $store, $customer, $totalAmount)
{
    // This is a simplified example. In production, you would:
    // 1. Use the saleh7/php-zatca-xml library to generate proper ZATCA QR code
    // 2. Include digital signature and timestamp
    // 3. Store QR code image and reference it
    
    // For now, generate a simple QR code with basic invoice info
    $qrData = [
        'seller' => $store['zatca_seller_legal_name'] ?? $store['name'],
        'vat' => $store['zatca_seller_vat_number'] ?? '',
        'invoice' => $sale['invoice_number'] ?? $sale['id'],
        'date' => $sale['created_at'] ?? date('Y-m-d'),
        'amount' => $totalAmount,
    ];
    
    // Encode as JSON and generate QR code
    // Using an external QR code service or library
    $qrText = json_encode($qrData);
    
    // Example using QR code API (replace with your preferred QR library)
    $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=' . urlencode($qrText);
    
    return '<img src="' . $qrCodeUrl . '" alt="ZATCA QR Code" style="border: 1px solid #ddd; padding: 5px;">';
}

// ============================================================================
// Helper method to generate UUID for invoice
// ============================================================================

private function generateUUID()
{
    // Generate RFC 4122 compliant UUID v4
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

// ============================================================================
// Route configuration (add to app/Config/Routes.php)
// ============================================================================

// $routes->get('sales/print-invoice/(:num)', 'Sales::printInvoice/$1', ['filter' => 'permission:sales.view']);
// $routes->get('sales/print-invoice/(:num)/pdf', 'Sales::printInvoicePDF/$1', ['filter' => 'permission:sales.view']);

// ============================================================================
// Usage in Controller
// ============================================================================

/*
    // In your Sales controller show() or detail() method:
    public function show($id)
    {
        $sale = $this->saleModel->find($id);
        if (!$sale) {
            throw PageNotFoundException::forPageNotFound();
        }
        
        // Display sale details
        $data['sale'] = $sale;
        $data['printInvoiceUrl'] = site_url('sales/print-invoice/' . $id);
        
        return view('sales/show', $data);
    }
    
    // In your view (sales/show.php):
    <a href="<?= $printInvoiceUrl ?>" class="btn btn-primary" target="_blank">
        <i class="fas fa-print"></i> <?= lang('Sales.print_invoice') ?>
    </a>
*/
