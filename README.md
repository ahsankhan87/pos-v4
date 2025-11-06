# Kasbook POS v4

Kasbook POS v4 is a modern Point-of-Sale & basic ERP style inventory management system built on **CodeIgniter 4**. It provides fast in-browser workflows for product maintenance, purchasing, sales processing, stock visibility, and multi–role access control.

## ✨ Core Features

- Product management: names, codes, units, carton (pack) sizes, stock alerts, descriptions
- Automatic barcode generation (EAN-13 style with checksum) and on-demand barcode image rendering
- Dynamic margin / price helper: enter cost + margin % or override price directly
- Inventory adjustments via purchases & sales with quantity tracking (including carton -> pieces conversion)
- Customer & Employee management; assign sales staff to transactions
- Discount handling (fixed / percentage) with server-side validation & capping
- Tax calculation at sale level (configurable default tax rate)
- Multiple payment methods (cash, credit card, bank transfer, check, other) and tendered / change computation
- Roles & permissions (access filtering in routes/controllers)
- Audit logs, backups, analytics modules (controllers present)
- Sales drafts (recently refactored: session/cart persistence removed for simplicity – cart now in-memory)
- Barcode & product search (AJAX endpoints) optimized for quick scan + Enter workflow
- Purchase workflow with carton-aware quantity and cost updates

## 🛠 Tech Stack

- PHP 8.1+ (CodeIgniter 4 Framework distribution)
- MySQL/MariaDB (typical deployment)
- Composer for dependency management
- Tailwind-like utility classes (custom styling in views) + FontAwesome icons
- JavaScript (vanilla + jQuery + Select2 for searchable dropdowns)

## 📂 Key Directories (App Layer)

`app/Controllers` – Business & request logic (Products, Sales, Purchases, Inventory, etc.)  
`app/Models` – Database models (products, sales, sale items, customers, inventory, etc.)  
`app/Views` – Blade-like PHP view templates (CodeIgniter view engine)  
`system/` – Core CodeIgniter framework  
`writable/` – Logs, cache, sessions, uploads (ensure proper permissions in production)

## 🚀 Installation (Windows + XAMPP)

1. Clone or copy this repository into `xampp/htdocs/kasbook/pos-v4` (already done if you see this).
2. From the project root run:
   ```powershell
   composer install
   ```
3. Configure database credentials: create `.env` (or edit existing `env`) and set:
   ```ini
   database.default.hostname = localhost
   database.default.database = your_db_name
   database.default.username = your_db_user
   database.default.password = your_db_pass
   database.default.DBDriver = MySQLi
   database.default.charset = utf8mb4
   database.default.collation = utf8mb4_general_ci
   ```
4. Create the database in MySQL / MariaDB.
5. Run migrations (if provided – adapt to actual migration set):
   ```powershell
   php spark migrate
   ```
6. (Optional) Import sample receipt templates:
   - File: `writable/sample_receipt_templates.sql`
   - Use phpMyAdmin or `mysql` CLI.
7. Point Apache/Nginx document root to `public/` (DO NOT expose project root). In XAMPP httpd-vhosts config:
   ```
   DocumentRoot "C:/xampp/htdocs/kasbook/pos-v4/public"
   <Directory "C:/xampp/htdocs/kasbook/pos-v4/public">
   	 AllowOverride All
   	 Require all granted
   </Directory>
   ```
8. Browse to: `http://localhost/` (or your virtual host) and log in.

## ⚙️ Configuration Highlights

- Tax Rate: Stored in settings model (`tax_rate`) and injected into sales & purchase forms.
- Currency Symbol: Pulled from session (e.g. `$`, configurable per store).
- Barcode Generation: Endpoint `products/generate-barcode` returns JSON `{ barcode: "XXXXXXXXXXXXX" }` and uses `generateUniqueBarcode()` with EAN-13 checksum.
- Barcode Image: `products/barcode_image/{code}` returns rendered barcode (ensure GD / image libs enabled if required).
- Permissions: Route filters like `permission:products.create` protect endpoints – verify `app/Config/Filters.php` & roles setup.

## 🧾 Sales Cart Behavior (Current)

The sales cart was previously persisted in session per tab (`sale_session_id`). This logic has been removed:

- Cart exists only in memory while the page is open.
- Reloading or navigating away clears the cart.
- A `beforeunload` warning prompts if items are present and sale not submitted.
- To reintroduce persistence: restore `saveCart()` and `clearCart()` endpoints in `Sales` controller and related AJAX calls.

## 📦 Common Tasks

Add Product:

1. Navigate to Products > New
2. Fill cost price; optionally enter margin % to auto-calc retail price.
3. Generate barcode or leave blank for auto-generation on save.

Process Sale:

1. Open New Sale page
2. Scan barcode or search product (F1 focuses barcode, F2 opens search)
3. Adjust quantities (supports carton unit switching if defined)
4. Enter discount (fixed or %) and tax rate
5. Input tendered amount; system computes change/due
6. Submit (F9 or form button) – sale recorded and inventory decremented.

Record Purchase:

1. Open Create Purchase
2. Select supplier, scan or search product
3. Enter quantities (pieces or cartons)
4. Adjust cost price if needed
5. Apply discount/shipping/tax
6. Save – inventory incremented.

## 🔐 Security & Operational Notes

- Always deploy with `public/` as web root to avoid exposing system/ & app/ code.
- Enable HTTPS in production (configure virtual host + cert).
- Keep PHP up to date (>= 8.1). Watch EOL dates.
- Enforce strong database credentials; never commit real secrets.
- Logs & cache in `writable/` can grow – rotate or prune periodically.
- Consider adding rate limiting for API endpoints (barcode/product search) if exposed publicly.

## 🧪 Testing

Project includes `phpunit.xml.dist`. To run tests (if any are present):

```powershell
php vendor\bin\phpunit
```

If you add models/controllers logic, create tests under `tests/` following CodeIgniter 4 testing guide.

## 📚 Extending

- Add persistence layer for sales drafts: reintroduce session or database table (e.g., `pos_sale_drafts`).
- Implement WebSocket or polling for multi-terminal synchronization.
- Add reporting dashboards (daily sales, low-stock, top products).
- Integrate receipt printing (thermal printer ESC/POS library) & PDF invoices.

## 📝 License

Framework code under CodeIgniter’s license; custom application code is proprietary or as you choose (define clearly here if MIT/GPL/etc.). See `LICENSE` for details.

## 🤝 Contributing

1. Fork repository
2. Create feature branch: `git checkout -b feature/your-feature`
3. Commit changes with clear messages
4. Open Pull Request describing intent & test coverage

## ❓ Support

Open issues for bugs or enhancement ideas. For framework-specific questions consult the [CodeIgniter User Guide](https://codeigniter.com/user_guide/).

## Contact Us

## You can contact on khybersoft.com

> This README augments the default CodeIgniter distribution notes below.

# CodeIgniter 4 Framework

## What is CodeIgniter?

CodeIgniter is a PHP full-stack web framework that is light, fast, flexible and secure.
More information can be found at the [official site](https://codeigniter.com).

This repository holds the distributable version of the framework.
It has been built from the
[development repository](https://github.com/codeigniter4/CodeIgniter4).

More information about the plans for version 4 can be found in [CodeIgniter 4](https://forum.codeigniter.com/forumdisplay.php?fid=28) on the forums.

You can read the [user guide](https://codeigniter.com/user_guide/)
corresponding to the latest version of the framework.

## Important Change with index.php

`index.php` is no longer in the root of the project! It has been moved inside the _public_ folder,
for better security and separation of components.

This means that you should configure your web server to "point" to your project's _public_ folder, and
not to the project root. A better practice would be to configure a virtual host to point there. A poor practice would be to point your web server to the project root and expect to enter _public/..._, as the rest of your logic and the
framework are exposed.

**Please** read the user guide for a better explanation of how CI4 works!

## Repository Management

We use GitHub issues, in our main repository, to track **BUGS** and to track approved **DEVELOPMENT** work packages.
We use our [forum](http://forum.codeigniter.com) to provide SUPPORT and to discuss
FEATURE REQUESTS.

This repository is a "distribution" one, built by our release preparation script.
Problems with it can be raised on our forum, or as issues in the main repository.

## Contributing

We welcome contributions from the community.

Please read the [_Contributing to CodeIgniter_](https://github.com/codeigniter4/CodeIgniter4/blob/develop/CONTRIBUTING.md) section in the development repository.

## Server Requirements

PHP version 8.1 or higher is required, with the following extensions installed:

- [intl](http://php.net/manual/en/intl.requirements.php)
- [mbstring](http://php.net/manual/en/mbstring.installation.php)

> [!WARNING]
>
> - The end of life date for PHP 7.4 was November 28, 2022.
> - The end of life date for PHP 8.0 was November 26, 2023.
> - If you are still using PHP 7.4 or 8.0, you should upgrade immediately.
> - The end of life date for PHP 8.1 will be December 31, 2025.

Additionally, make sure that the following extensions are enabled in your PHP:

- json (enabled by default - don't turn it off)
- [mysqlnd](http://php.net/manual/en/mysqlnd.install.php) if you plan to use MySQL
- [libcurl](http://php.net/manual/en/curl.requirements.php) if you plan to use the HTTP\CURLRequest library
