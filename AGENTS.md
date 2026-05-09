# Kasbook POS v4 — Agent Instructions

## Project Overview

CodeIgniter 4 (PHP 8.1+) Point-of-Sale & basic ERP system.  
Multi-store, multi-tenant, role-based permissions, English + Arabic RTL support, user-based subscription.  
Setup and key features: [README.md](README.md)

---

## Commands

```bash
composer install          # Install PHP dependencies
php spark migrate         # Run database migrations
php spark migrate:rollback
composer test             # Run PHPUnit tests (phpunit.xml.dist)
php spark serve           # Dev server (or use XAMPP: public/ as docroot)
```

Tests live in `tests/` and bootstrap via `system/Test/bootstrap.php`.  
Coverage targets `./app` (excludes Views and Routes.php).

---

## Architecture

```
app/Controllers/    — Route handlers; extend BaseController
app/Models/         — DB models (all use pos_ table prefix)
app/Views/          — PHP templates (CI4 extend/section/endSection)
app/Services/       — Business logic decoupled from controllers
app/Filters/        — Auth, Permission, Store, Feature guards
app/Helpers/        — permission_helper, audit_helper, locale_helper
app/Language/en/    — English i18n strings
app/Language/ar/    — Arabic RTL strings
app/Config/         — Routes.php, Filters.php, Database.php, App.php
app/Database/Migrations/  — Date-prefixed migration files
```

---

## Key Conventions

### Multi-Store Scope (ALWAYS use)

Every model query that retrieves store data **must** use the `forStore()` scope:

```php
$rows = $this->productModel->forStore()->findAll();
// Adds: WHERE store_id = {session('store_id')}
```

Omitting this leaks data across stores.

### Database Tables

Prefix: `pos_` — e.g. `pos_products`, `pos_sales`, `pos_users`, `pos_audit_logs`.

### Permissions

```php
can('sales.view')              // Single check
canAny(['sales.create', 'sales.edit'])
canAll(['reports.view', 'reports.export'])
```

Role ID 1 or role name `admin` bypasses all permission checks.  
Routes use: `['filter' => 'permission:module.action']`.

### Audit Logging

Call after any create/update/delete:

```php
logAction('product_created', 'Product ID: ' . $id);
```

### Naming

| Element           | Pattern             | Example                                      |
| ----------------- | ------------------- | -------------------------------------------- |
| Controller        | PascalCase          | `EmployeeTargets`                            |
| Route URL         | kebab-case          | `/employee-targets/achievements`             |
| Controller method | camelCase           | `achievements()`, `achievementsPrint()`      |
| DB table          | `pos_` + snake_case | `pos_employee_sales_targets`                 |
| View folder       | kebab-case          | `employee_targets/`                          |
| View file         | snake_case          | `achievements.php`, `achievements_print.php` |
| Language key      | `Module.key`        | `lang('EmployeeTargets.title_achievements')` |

### Views

```php
<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
    <!-- content here -->
<?= $this->endSection() ?>
```

Use separate views file for print pages

Always escape output: `<?= esc($var) ?>`. Never echo unescaped user data.  
Currency symbol comes from session, never hardcoded: `session('currency_symbol')`.  
Prefer storing it once per template: `$currency = (string) (session('currency_symbol') ?? '');`

### Flash Messages

Set in controller: `redirect()->to(site_url('path'))->with('success', lang('Module.success_create'))`  
Display in view via `session()->getFlashdata('success')`.

### Localization

All UI strings go through `lang('Module.key')`.  
Tier/status classification in views must use **data values** (percentages, flags), not translated strings, as the basis for CSS classes — translated labels are for display only.

### Security

- All `target="_blank"` links must include `rel="noopener noreferrer"`.
- Use `esc($var, 'js')` inside `onclick`/inline JS attributes.
- CSRF token in AJAX headers: `meta[name=csrf-token]` → `content=csrf_hash()`.

### URL Helpers

```php
site_url('sales/new')     // Internal app links
base_url('assets/app.js') // Static assets
```

### Responsive Tables

Wrap all multi-column tables in `overflow-x-auto` for mobile compatibility:

```html
<div class="bg-white shadow-md rounded-lg overflow-x-auto">
  <table class="min-w-full ..."></table>
</div>
```

## Use DataTable for all tables.

## Session Keys (available everywhere)

| Key               | Type   | Description           |
| ----------------- | ------ | --------------------- |
| `store_id`        | int    | Active store ID       |
| `store_name`      | string | Active store name     |
| `user_id`         | int    | Logged-in user ID     |
| `role_id`         | int    | Role (1 = admin)      |
| `role_name`       | string | Role label            |
| `currency_symbol` | string | Store currency symbol |
| `is_logged_in`    | bool   | Auth check            |

---

## Common Services

```php
service('promotionService')          // PromotionService – promo/gift engine
service('recurringInvoiceService')   // RecurringInvoiceService
```

---

## Pitfalls

- **Never** compare translated label strings to drive CSS or business logic — use the underlying data (percentages, IDs, flags).
- **Never** skip `forStore()` on any model query unless you have an explicit multi-store admin reason.
- Language files for new modules must be added to both `app/Language/en/` and `app/Language/ar/`.
- Models use mixed naming (legacy `M_products`, newer `UserModel` / `EmployeeTargetsModel`) — follow the newer PascalCase for any new model.
- Document root must point to `public/`, not the project root.
