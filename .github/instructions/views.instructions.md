---
description: "Use when writing or editing PHP view templates in app/Views/. Enforces security, mobile layout, localization-safe logic, and currency consistency conventions."
applyTo: "app/Views/**/*.php"
---

# View Conventions

## Security

- Every `target="_blank"` anchor **must** include `rel="noopener noreferrer"`.
  ```php
  // ✅ correct
  <a href="..." target="_blank" rel="noopener noreferrer">...</a>
  // ❌ wrong — missing rel
  <a href="..." target="_blank">...</a>
  ```
- Always escape output with `esc($var)`. For JS attributes use `esc($var, 'js')`.

## Mobile Layout

- Wrap every multi-column table in `overflow-x-auto`, not `overflow-hidden`.
  ```html
  <div class="bg-white shadow-md rounded-lg overflow-x-auto">
    <table class="min-w-full ..."></table>
  </div>
  ```

## Currency Symbol

- Declare `$currency` **once** near the top of the template and reuse it everywhere — in both summary cards and table cells. Never call `session('currency_symbol')` inline per-cell.
  ```php
  <?php $currency = (string) (session('currency_symbol') ?? ''); ?>
  ...
  <div><?= $currency . number_format($total, 2) ?></div>
  ```

## Localization-Safe Conditional Logic

- **Never** compare translated strings to drive CSS classes or business logic. Use the raw underlying data value (percentage, numeric flag, ID).

  ```php
  // ✅ correct — use achievement_percent, not lang() string
  $tierClass = $achievementPercent >= 120 ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700';
  $statusClass = $achievementPercent >= 100 ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700';

  // ❌ wrong — brittle, breaks when translation wording changes
  $tierClass = $tier === lang('Module.tier_gold') ? 'bg-amber-100 text-amber-800' : '';
  $statusClass = $row['status'] === lang('Module.status_achieved') ? 'bg-green-100' : 'bg-yellow-100';
  ```

- Translated labels are for display output only (`esc($tier)`, `esc($row['status'])`), never for branching.

## Output Escaping Quick Reference

| Context              | Usage                      |
| -------------------- | -------------------------- |
| HTML text            | `<?= esc($var) ?>`         |
| HTML attribute       | `<?= esc($var, 'attr') ?>` |
| JavaScript / onclick | `<?= esc($var, 'js') ?>`   |
| URL parameter        | `<?= esc($var, 'url') ?>`  |

## Print Views

- Use separate view files for print pages (e.g., `achievements_print.php`).
- Ensure print-specific styles are included, either via a dedicated CSS file or inline `<style>` block within the print view.
