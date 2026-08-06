# Professional ZATCA-Compliant A4 Invoice Template

## Overview

This is a professional, bilingual (English/Arabic), ZATCA-compliant invoice template designed for Saudi Arabia. It includes complete ZATCA postal address information for both seller and buyer, QR code compliance section, and professional formatting suitable for A4 printing.

## Features

### 🇸🇦 Saudi Arabia Compliance

- **ZATCA Compliant**: Includes sections for ZATCA digital signature, QR code, and invoice hash
- **Bilingual Support**: Full Arabic (RTL) and English (LTR) language support
- **Professional Design**: Clean, modern design with proper brand representation
- **Complete Address Fields**: All ZATCA postal address requirements included

### 📋 Content Sections

1. **Header Section**
   - Store logo and name
   - Invoice type badge (Invoice, Credit Note, Debit Note, Pro Forma)
   - Invoice number, date, and due date
   - Store VAT registration number

2. **Seller & Buyer Information**
   - Legal names and registration details
   - VAT/Tax numbers
   - Contact information (phone, email)
   - Complete ZATCA postal addresses:
     - Street name
     - Building number
     - City subdivision/district
     - City
     - Postal code
     - Country

3. **Line Items Table**
   - Item description
   - Quantity
   - Unit price
   - Discount per line
   - Line amount

4. **Totals Section**
   - Subtotal
   - Total discount
   - Tax amount (with tax rate)
   - Grand total
   - Amount paid
   - Balance due

5. **ZATCA Compliance Section**
   - QR code for digital verification
   - Invoice hash
   - Unique invoice ID (UUID)
   - Compliance text

6. **Footer**
   - Notes/payment terms
   - Disclaimer text

## Template Variables

### Essential Variables

All variables should be passed as an array to the view/template rendering function.

#### Store/Seller Variables

```
{{store_name}}                    - Store/Company name
{{store_logo_img}}                - HTML img tag for logo
{{store_legal_name}}              - Legal company name (ZATCA)
{{store_cr_number}}               - Commercial registration number
{{store_vat_number}}              - VAT registration number
{{store_phone}}                   - Phone number
{{store_email}}                   - Email address

# ZATCA Postal Address
{{store_street_name}}             - Street name
{{store_building_number}}         - Building number
{{store_city_subdivision_name}}   - District/Subdivision name
{{store_city_name}}               - City name
{{store_postal_code}}             - Postal code
{{store_country_name}}            - Country name
```

#### Invoice Header Variables

```
{{invoice_number}}                - Invoice number (e.g., "INV-2024-001")
{{invoice_date}}                  - Invoice date (formatted)
{{due_date}}                      - Payment due date (formatted)
{{invoice_type_label}}            - Display label (Invoice, Credit Note, etc.)
{{invoice_type_class}}            - CSS class for styling (e.g., "credit-note")
```

#### Customer/Buyer Variables

```
{{customer_name}}                 - Customer name
{{customer_email}}                - Customer email
{{customer_phone}}                - Customer phone
{{customer_vat_number}}           - Customer VAT number

# ZATCA Buyer Details
{{customer_zatca_registration_name}} - Registration name (ZATCA)
{{customer_zatca_cr_number}}      - Commercial registration (ZATCA)
{{customer_street_name}}          - Street name
{{customer_building_number}}      - Building number
{{customer_city_subdivision_name}} - District/Subdivision
{{customer_city_name}}            - City name
{{customer_postal_code}}          - Postal code
{{customer_country_name}}         - Country name
```

#### Line Items Variables

```
{{invoice_items}}                 - HTML rows for line items (see format below)
```

**Line Item Row Format** (HTML):

```html
<tr>
  <td class="text-center">1</td>
  <td>Product/Service Description</td>
  <td class="text-center">2</td>
  <td class="text-right">100.00</td>
  <td class="text-right">10.00</td>
  <td class="text-right">190.00</td>
</tr>
```

#### Totals Variables

```
{{currency}}                      - Currency symbol (e.g., "SR", "$", "﷼")
{{subtotal}}                      - Subtotal amount
{{total_discount}}                - Total discount amount
{{tax_rate}}                      - Tax percentage (e.g., "15")
{{total_tax}}                     - Total tax amount
{{grand_total}}                   - Grand total (after tax)
{{amount_paid}}                   - Amount paid
{{balance_due}}                   - Remaining balance
```

#### ZATCA Compliance Variables

```
{{zatca_qr_code}}                 - HTML img tag with QR code
{{invoice_hash}}                  - Invoice digital signature hash
{{invoice_uuid}}                  - Unique invoice identifier (UUID)
```

#### Notes & Footer Variables

```
{{invoice_notes}}                 - Notes/comments on invoice
{{payment_terms}}                 - Payment terms text
{{footer_text}}                   - Footer message/thank you note
```

#### Language Labels (Auto-localized from language files)

```
{{invoice_number_label}}
{{invoice_date_label}}
{{due_date_label}}
{{seller_label}}
{{buyer_label}}
{{legal_name_label}}
{{cr_number_label}}
{{vat_label}}
{{phone_label}}
{{email_label}}
{{postal_address_label}}
{{street_label}}
{{building_label}}
{{city_subdivision_label}}
{{city_label}}
{{postal_code_label}}
{{country_label}}
{{name_label}}
{{registration_name_label}}
{{item_no_label}}
{{description_label}}
{{qty_label}}
{{unit_price_label}}
{{discount_label}}
{{amount_label}}
{{subtotal_label}}
{{tax_label}}
{{grand_total_label}}
{{paid_label}}
{{balance_label}}
{{notes_label}}
{{payment_terms_label}}
{{zatca_compliance_label}}
{{zatca_compliance_text}}
{{invoice_id_label}}
{{uuid_label}}
{{footer_disclaimer}}
```

## Language Support

### English (LTR)

- File: `app/Language/en/Invoice.php`
- Direction: Left-to-Right
- Loaded automatically with language selection

### Arabic (RTL)

- File: `app/Language/ar/Invoice.php`
- Direction: Right-to-Left
- Loaded automatically with language selection

To support bilingual rendering, pass `{{direction}}` as either `ltr` or `rtl`.

## Print Settings

### Recommended Print Settings

- **Paper Size**: A4
- **Orientation**: Portrait
- **Margins**: 0.5cm all sides
- **Scaling**: 100% (no scaling)
- **Print Background**: Enabled (for table backgrounds and colors)
- **Headers/Footers**: Disabled

### CSS Media Queries

The template automatically adapts to:

- **Print Media** (@media print)
- **Screen Media** (@media screen)
- **Mobile Devices** (max-width: 768px)

## Color Scheme (Saudi Arabia Themed)

- **Primary Green**: `#1a472a` (Saudi green)
- **Accent Yellow**: `#ffc107` (payment terms highlight)
- **Background**: White with light gray (`#fafafa`)
- **Text**: Dark gray/black for readability
- **Borders**: Light gray (`#ddd`)

## Integration with CodeIgniter

### Controller Example

```php
<?php

namespace App\Controllers;

use App\Models\SaleModel;
use App\Models\CustomerModel;
use App\Models\StoreModel;

class Invoices extends BaseController
{
    public function generateInvoice($invoiceId = null)
    {
        if (!$invoiceId) {
            return redirect()->to('invoices')->with('error', 'Invoice not found');
        }

        // Load invoice data
        $saleModel = new SaleModel();
        $invoice = $saleModel->find($invoiceId);

        if (!$invoice) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Load related data
        $customerModel = new CustomerModel();
        $storeModel = new StoreModel();

        $customer = $customerModel->find($invoice['customer_id']);
        $store = $storeModel->find($invoice['store_id']);

        // Prepare template variables
        $data = [
            'direction' => session('locale') === 'ar' ? 'rtl' : 'ltr',

            // Store/Seller Info
            'store_name' => $store['name'],
            'store_logo_img' => '<img src="' . base_url('uploads/logos/' . $store['logo']) . '" alt="Logo">',
            'store_legal_name' => $store['zatca_seller_legal_name'],
            'store_cr_number' => $store['cr_number'],
            'store_vat_number' => $store['zatca_seller_vat_number'],
            'store_phone' => $store['phone'],
            'store_email' => $store['email'],

            // Store ZATCA Address
            'store_street_name' => $store['zatca_street_name'],
            'store_building_number' => $store['zatca_building_number'],
            'store_city_subdivision_name' => $store['zatca_city_subdivision_name'],
            'store_city_name' => $store['zatca_city_name'],
            'store_postal_code' => $store['zatca_postal_code'],
            'store_country_name' => 'Saudi Arabia',

            // Invoice Info
            'invoice_number' => $invoice['invoice_number'],
            'invoice_date' => date('d-m-Y', strtotime($invoice['created_at'])),
            'due_date' => date('d-m-Y', strtotime($invoice['due_date'])),
            'invoice_type_label' => lang('Invoice.invoice'),
            'invoice_type_class' => '',

            // Customer/Buyer Info
            'customer_name' => $customer['name'],
            'customer_phone' => $customer['phone'],
            'customer_email' => $customer['email'],
            'customer_vat_number' => $customer['vat_number'],

            // Customer ZATCA Details
            'customer_zatca_registration_name' => $customer['zatca_registration_name'],
            'customer_zatca_cr_number' => $customer['zatca_cr_number'],
            'customer_street_name' => $customer['zatca_street_name'],
            'customer_building_number' => $customer['zatca_building_number'],
            'customer_city_subdivision_name' => $customer['zatca_city_subdivision_name'],
            'customer_city_name' => $customer['zatca_city_name'],
            'customer_postal_code' => $customer['zatca_postal_code'],
            'customer_country_name' => 'Saudi Arabia',

            // Totals
            'currency' => $store['currency_symbol'],
            'subtotal' => number_format($invoice['subtotal'], 2),
            'total_discount' => number_format($invoice['discount_total'], 2),
            'tax_rate' => '15', // Standard Saudi VAT
            'total_tax' => number_format($invoice['tax_amount'], 2),
            'grand_total' => number_format($invoice['total'], 2),
            'amount_paid' => number_format($invoice['paid'], 2),
            'balance_due' => number_format($invoice['total'] - $invoice['paid'], 2),

            // ZATCA Info
            'zatca_qr_code' => '<img src="' . base_url('uploads/qr-codes/' . $invoice['qr_code']) . '" alt="QR Code">',
            'invoice_hash' => $invoice['digital_signature'],
            'invoice_uuid' => $invoice['uuid'],

            // Notes
            'invoice_notes' => $invoice['notes'] ?? '',
            'payment_terms' => $invoice['payment_terms'] ?? 'Net 30',
            'footer_text' => lang('Invoice.thank_you'),
        ];

        // Get line items
        $data['invoice_items'] = $this->getInvoiceItemsHTML($invoiceId);

        // Render template
        return view('invoices/professional_invoice_a4', $data);
    }

    private function getInvoiceItemsHTML($invoiceId)
    {
        $saleDetailModel = new SaleDetailModel();
        $items = $saleDetailModel->where('sale_id', $invoiceId)->findAll();

        $html = '';
        $itemNo = 1;
        foreach ($items as $item) {
            $html .= '<tr>';
            $html .= '<td class="text-center">' . $itemNo . '</td>';
            $html .= '<td>' . esc($item['description']) . '</td>';
            $html .= '<td class="text-center">' . number_format($item['quantity'], 2) . '</td>';
            $html .= '<td class="text-right">' . number_format($item['unit_price'], 2) . '</td>';
            $html .= '<td class="text-right">' . number_format($item['discount'], 2) . '</td>';
            $html .= '<td class="text-right">' . number_format($item['line_total'], 2) . '</td>';
            $html .= '</tr>';
            $itemNo++;
        }

        return $html;
    }
}
```

## Browser Compatibility

- Chrome/Chromium (Recommended for printing)
- Firefox
- Safari
- Edge
- Mobile browsers (responsive view)

## Print to PDF

Use browser's Print to PDF feature or:

- Chrome: Print → Save as PDF
- Firefox: Print → Save to PDF

## Features Checklist

✅ Professional design  
✅ ZATCA compliant  
✅ Bilingual (English/Arabic)  
✅ Complete ZATCA postal addresses  
✅ QR code section  
✅ Digital signature/hash display  
✅ Responsive layout  
✅ Print-optimized  
✅ Mobile-friendly  
✅ Customizable colors  
✅ Logo support  
✅ Multiple invoice types  
✅ Notes and payment terms  
✅ Complete line item details

## Notes

1. **Logo Placement**: Provide logo as HTML img tag or raw image
2. **QR Code**: Generate using a QR code library and pass as img tag
3. **Date Format**: Ensure consistent date formatting (d-m-Y recommended)
4. **Currency**: Use 3-letter ISO code (SAR, USD, etc.) or symbol
5. **Language Files**: Load using `lang()` helper for localization
6. **ZATCA Compliance**: Ensure all required fields are populated before printing

## Future Enhancements

- Support for multiple line breaks in descriptions
- Barcode generation for SKU tracking
- Payment method badges
- Installment payment plans display
- Multi-currency conversion
- Digital signature verification UI
