---
description: "Use when adding, editing, or creating language files in app/Language/. Enforces that every key is added to both en/ and ar/ simultaneously, and follows the Module.key naming format."
applyTo: "app/Language/**/*.php"
---

# Language File Conventions

## Parity Rule — en/ and ar/ Must Stay in Sync

Every language file and every key **must exist in both** `app/Language/en/` and `app/Language/ar/`.  
Never add a key to only one locale.

```
app/Language/
├── en/Products.php   ← add key here
└── ar/Products.php   ← also add it here (translated)
```

When creating a **new module language file**, create both files in the same operation:

```php
// app/Language/en/MyModule.php
<?php
return [
    'title_index' => 'My Module',
    'success_create' => 'Record created successfully.',
];

// app/Language/ar/MyModule.php
<?php
return [
    'title_index' => 'وحدتي',
    'success_create' => 'تم إنشاء السجل بنجاح.',
];
```

> **Known gap**: `ar/` is currently missing `EmployeeTargets.php`, `SalesOrders.php`, and `Validation.php`. Create these files when working in those modules.

## File Naming

Language filenames must be **PascalCase** and match the module name exactly in both locales:

| Module             | en/ file                | ar/ file                |
| ------------------ | ----------------------- | ----------------------- |
| Employee Targets   | `EmployeeTargets.php`   | `EmployeeTargets.php`   |
| Sales Orders       | `SalesOrders.php`       | `SalesOrders.php`       |
| Expense Categories | `ExpenseCategories.php` | `ExpenseCategories.php` |

## Key Naming — `Module.key` Format

Keys use `snake_case`. They are referenced in code as `lang('Module.key')`.

```php
// ✅ correct key names
'title_index'      => '...'
'success_create'   => '...'
'error_not_found'  => '...'
'confirm_delete'   => '...'

// ❌ wrong — camelCase, dot notation, or inconsistent casing
'titleIndex'       => '...'
'title.index'      => '...'
'Title_Index'      => '...'
```

### Standard Key Categories

Follow these naming patterns for common key types:

| Category          | Pattern           | Example                                              |
| ----------------- | ----------------- | ---------------------------------------------------- |
| Page titles       | `title_*`         | `title_index`, `title_new`, `title_edit`             |
| Success messages  | `success_*`       | `success_create`, `success_update`, `success_delete` |
| Error messages    | `error_*`         | `error_not_found`, `error_create`, `error_duplicate` |
| Labels / headings | bare noun         | `employee`, `target_amount`, `status`                |
| Actions / buttons | verb or verb_noun | `apply_filters`, `reset_filters`, `confirm_delete`   |

## File Structure

```php
<?php

return [
    // titles
    'title_index'  => '...',
    'title_new'    => '...',
    'title_edit'   => '...',

    // labels
    'name'         => '...',
    'status'       => '...',

    // actions
    'save'         => '...',
    'cancel'       => '...',
    'delete'       => '...',

    // messages
    'success_create'   => '...',
    'success_update'   => '...',
    'success_delete'   => '...',
    'error_not_found'  => '...',
    'error_create'     => '...',
];
```

## Usage in Code

```php
// controllers / helpers
lang('Products.success_create')
lang('EmployeeTargets.error_not_found')

// views
<?= lang('Products.title_index') ?>
<?= lang('EmployeeTargets.apply_filters') ?>
```

**Never hardcode UI strings in views or controllers.** Always go through `lang()`.
