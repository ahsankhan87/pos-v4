---
description: "Use when writing or editing backend PHP in this CodeIgniter 4 app (controllers, models, services, filters, helpers, commands, config). Enforces CI4-safe request handling, validation, response patterns, query safety, and project naming/style conventions."
applyTo:
  - "app/Controllers/**/*.php"
  - "app/Models/**/*.php"
  - "app/Services/**/*.php"
  - "app/Filters/**/*.php"
  - "app/Helpers/**/*.php"
  - "app/Commands/**/*.php"
  - "app/Config/**/*.php"
---

# PHP + CodeIgniter 4 Standards

This instruction covers shared backend standards.  
For area-specific rules, also follow:

- `.github/instructions/controllers.instructions.md`
- `.github/instructions/views.instructions.md`
- `.github/instructions/language.instructions.md`

## 1) Class and Namespace Structure

- Use PSR-4 namespaces under `App\`.
- Keep one class per file.
- Class names use PascalCase.
- Method names use camelCase.
- Prefer explicit property/method visibility (`public`, `protected`, `private`).

```php
namespace App\Services;

class PromotionService
{
    public function applyToSale(array $items): array
    {
        // ...
    }
}
```

## 2) Request Input Handling

- Always cast and normalize request input before use.
- Validate month/date/ID and numeric fields before queries/writes.
- Never trust raw request values in arithmetic or branching.

```php
$employeeId = (int) ($this->request->getGet('employee_id') ?? 0);
$targetMonth = trim((string) ($this->request->getPost('target_month') ?? ''));
$amount = (float) ($this->request->getPost('amount') ?? 0);
```

## 3) Validation First, Write Later

- Validate all required fields before insert/update/delete logic.
- Use guard clauses with early returns for invalid input.
- For form flows: `redirect()->back()->withInput()->with('error', lang(...))`.

```php
if ($employeeId <= 0) {
    return redirect()->back()->withInput()->with('error', lang('Module.error_employee_required'));
}
```

## 4) Response Patterns

- Web requests: redirect with flash messages after mutations.
- AJAX/API requests: return structured JSON with explicit status codes.
- Do not mix HTML and JSON response patterns in the same endpoint path.

```php
return $this->response->setStatusCode(403)->setJSON([
    'success' => false,
    'message' => 'Forbidden',
]);
```

## 5) Query Safety and Data Access

- Use Query Builder / Model methods; avoid string-built SQL.
- Always scope store-owned data to the active store (see controller rules).
- Never interpolate unescaped request values into raw SQL.

```php
$rows = $this->model
    ->where('store_id', $storeId)
    ->where('employee_id', $employeeId)
    ->findAll();
```

## 6) CI4 Helper and Service Usage

- Use CI4 helpers for app links and assets:
  - `site_url()` for internal routes
  - `base_url()` for static assets
- Use `service('name')` for shared services registered in CI4 service container.
- Load project helpers when needed: `helper(['permission', 'audit', 'form', 'locale'])`.

## 7) Time, Locale, and Session Conventions

- Read context from session keys already used by the app (`store_id`, `user_id`, `role_id`, `role_name`, `currency_symbol`).
- Do not hardcode currency symbols, role labels, or locale-specific text.
- Keep user-visible text in language files and access with `lang('Module.key')`.

## 8) Naming and Module Consistency

- Controllers: PascalCase class names, kebab-case route URLs.
- Tables: `pos_` prefix + snake_case.
- Keep module naming consistent across Controller / Model / language file / route segment.

## 9) Error Handling and Side Effects

- Use guard clauses for not-found and unauthorized cases.
- Log meaningful audit events for successful create/update/delete actions.
- Do not log success events for failed operations.

## 10) Keep Changes Minimal and Targeted

- Preserve existing behavior unless the task explicitly requires behavior changes.
- Avoid unrelated refactors in bug-fix tasks.
- Follow existing formatting and coding style of the touched file.
