# Receipt Template System

## Overview

The receipt template system allows you to create and manage multiple receipt templates with HTML. You can easily switch between different templates and customize the appearance of your receipts.

## Features

- Create unlimited receipt templates
- HTML-based templates for complete customization
- Set one template as default
- Live preview when editing templates
- Easy template switching from Settings page

## Available Placeholders

Use these placeholders in your HTML templates. They will be automatically replaced with actual data:

| Placeholder          | Description                   | Example Output                   |
| -------------------- | ----------------------------- | -------------------------------- |
| `{{store_name}}`     | Store name                    | "ABC Store"                      |
| `{{store_address}}`  | Store address                 | "123 Main St, City"              |
| `{{store_phone}}`    | Store phone number            | "(555) 123-4567"                 |
| `{{store_footer}}`   | Footer message                | "Returns accepted within 7 days" |
| `{{receipt_number}}` | Receipt/Invoice number        | "INV-2025-001"                   |
| `{{date}}`           | Transaction date and time     | "25/10/2025 14:30"               |
| `{{cashier}}`        | Cashier name                  | "John Doe"                       |
| `{{customer}}`       | Customer info (if available)  | "Customer: Jane Smith"           |
| `{{items}}`          | Table rows of purchased items | Multiple `<tr>` rows             |
| `{{subtotal}}`       | Subtotal before tax/discount  | "100.00"                         |
| `{{total_discount}}` | Total discount amount         | "10.00"                          |
| `{{tax}}`            | Tax amount                    | "9.00"                           |
| `{{total}}`          | Grand total                   | "99.00"                          |
| `{{paid}}`           | Amount paid by customer       | "100.00"                         |
| `{{change}}`         | Change given                  | "1.00"                           |

## Item Format

The `{{items}}` placeholder expects table rows (`<tr>` tags). Each item row contains:

- Item name
- Quantity
- Unit price
- Line total

Example structure:

```html
<tr>
  <td>Product Name</td>
  <td>2</td>
  <td>10.00</td>
  <td>20.00</td>
</tr>
```

## How to Use

### Creating a New Template

1. Navigate to **Receipt Templates** from the sidebar menu
2. Click **Create New Template**
3. Enter a template name (e.g., "Thermal 80mm Receipt")
4. Paste or write your HTML template using the placeholders
5. Optionally check "Set as default template"
6. Click **Create Template**

### Editing a Template

1. Go to **Receipt Templates**
2. Click **Edit** next to the template you want to modify
3. Make your changes
4. Use the **Update Preview** button to see how it looks
5. Click **Update Template** to save

### Setting Default Template

Option 1: From Templates List

1. Go to **Receipt Templates**
2. Click **Set Default** next to your preferred template

Option 2: From Settings

1. Go to **Settings**
2. Select your preferred template from the "Receipt Template" dropdown
3. Click **Save Settings**

### Deleting a Template

1. Go to **Receipt Templates**
2. Click **Delete** next to the template (Note: You cannot delete the default template or if it's the only template)
3. Confirm the deletion

## Sample Template

A sample template is provided in `app/Views/receipts/sample_template.html`. You can use this as a starting point for creating your own templates.

## Best Practices

1. **Keep it simple**: Thermal printers work best with simple layouts
2. **Test your template**: Always test with real data before using in production
3. **Use fixed width**: Most thermal receipts are 80mm (300px) or 58mm (200px)
4. **Avoid heavy graphics**: Complex images may not print well on thermal printers
5. **Use web-safe fonts**: Stick to common fonts like Courier, Arial, or system fonts
6. **Include store info**: Always include store name, address, and contact info
7. **Add legal text**: Include return policy, tax ID, or other required information

## Troubleshooting

**Template not showing changes:**

- Clear browser cache
- Make sure you clicked "Update Template"
- Check if the correct template is set as default

**Placeholders not replaced:**

- Verify placeholder spelling exactly matches (case-sensitive)
- Ensure double curly braces: `{{placeholder}}`
- Check that the data exists (e.g., customer info only shows if customer is selected)

**Items not displaying:**

- Make sure you have a `<table>` structure for items
- The `{{items}}` placeholder expects to be used within a `<tbody>` tag
- Each item is a complete `<tr>` row

## Technical Details

### Database Table

Templates are stored in the `receipt_templates` table:

- `id`: Primary key
- `name`: Template name
- `template`: HTML content
- `is_default`: Boolean (1 = default, 0 = not default)
- `created_at`: Timestamp
- `updated_at`: Timestamp

### Files

- **Controller**: `app/Controllers/Receipts.php`
- **Model**: `app/Models/ReceiptTemplateModel.php`
- **Views**:
  - `app/Views/receipts/templates.php` - List templates
  - `app/Views/receipts/create_template.php` - Create form
  - `app/Views/receipts/edit_template.php` - Edit form with preview
  - `app/Views/receipts/sample_template.html` - Sample template

### Routes

- `GET /receipts/templates` - List all templates
- `GET /receipts/templates/create` - Create form
- `POST /receipts/templates/store` - Store new template
- `GET /receipts/templates/edit/{id}` - Edit form
- `POST /receipts/templates/update/{id}` - Update template
- `GET /receipts/templates/set-default/{id}` - Set as default
- `GET /receipts/templates/delete/{id}` - Delete template
- `GET /receipts/generate/{saleId}` - Generate receipt for a sale

## Support

For issues or questions, contact your system administrator.
