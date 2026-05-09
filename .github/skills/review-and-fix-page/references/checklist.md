# Page Review Checklist

## 🔴 CRITICAL — Data & Security

- [ ] All output is escaped with `esc($var)`. No raw `echo` or `<?= $var ?>` without `esc()`.
- [ ] Inline JS attributes use `esc($var, 'js')`.
- [ ] Every model query on store-scoped data calls `forStore()`. No bare `findAll()` / `find()` without scope.
- [ ] No unvalidated user input used in SQL (`db_connect()->query()` raw calls).
- [ ] CSRF token present in forms and AJAX headers.

## 🟡 WARNING — Convention & Security Hardening

- [ ] Every `target="_blank"` anchor includes `rel="noopener noreferrer"`.
- [ ] Table wrappers use `overflow-x-auto`, never `overflow-hidden`, for mobile scrollability.
- [ ] CSS badge/status classes are driven by **raw data values** (percentages, IDs, flags) — never by comparing `lang()` translated strings.
- [ ] Currency symbol declared **once** per template as `$currency = (string)(session('currency_symbol') ?? '');` and reused everywhere (summary cards and table cells).
- [ ] Flash messages use `session()->getFlashdata('success'|'error')` — never hardcoded inline strings.
- [ ] All UI strings go through `lang('Module.key')` — no hardcoded English/Arabic text in views.

## 🔵 INFO — UX & Consistency

- [ ] Summary/card totals include the currency symbol (same as table rows).
- [ ] Empty-state `<td colspan="N">` matches the actual column count.
- [ ] Print link (if present) preserves active filters in the query string.
- [ ] Filters preserve their selected state after submit (check `selected`/`value` bindings).
- [ ] Page title uses `esc($title)` from controller, not a hardcoded string.

## Controller (if reviewing a controller)

- [ ] `logAction()` called after every successful insert/update/delete.
- [ ] Every route has a `permission:module.action` filter.
- [ ] Redirects after mutations use `->with('success', lang(...))` — no direct view render after write.
- [ ] `buildPayload()` / validation runs before any DB write.

## Language Files (if reviewing a language addition)

- [ ] Key added to **both** `app/Language/en/<Module>.php` and `app/Language/ar/<Module>.php`.
- [ ] Key name is `snake_case` (no camelCase, no dots within the key).
- [ ] File name matches module name in PascalCase in both locale directories.
