---
name: review-and-fix-page
description: "Review a PHP view, controller, or language file for security issues, convention violations, and UX inconsistencies specific to this CodeIgniter 4 POS codebase. Produces a categorized findings report and then applies all fixable issues directly in the source file. Use when: reviewing a page, auditing a view, checking a controller, fixing security issues, checking localization, mobile layout review."
argument-hint: "Path to the file to review, e.g. app/Views/sales/index.php"
---

# Review and Fix Page

## What This Skill Produces

1. A categorized findings report saved as `<filename>-review.md` in the project root.
2. Direct code fixes applied to the source file for all fixable issues.
3. A PHP lint validation confirming no syntax errors were introduced.

---

## Procedure

### Step 1 — Gather Context

Load the target file and its related files in parallel:

- The view file itself
- The controller method that renders it (grep for the view name)
- The language file(s) it uses (`app/Language/en/<Module>.php`)
- The print variant if one exists (e.g. `achievements_print.php`)

### Step 2 — Audit Against Checklist

Check every item in [./references/checklist.md](./references/checklist.md) and record each finding.

### Step 3 — Produce the Report

Save a report file as `<filename>-review.md` in the project root using this structure:

```markdown
# Review Findings: <Page Title>

Reviewed file: <relative path>

## 🔴 CRITICAL

- ...

## 🟡 WARNING

1. <title>
   - Reference: <file>:<line>
   - Why it matters: ...

## 🔵 INFO

1. ...

## ✅ GOOD

1. ...
```

### Step 4 — Fix All Fixable Issues

Apply fixes directly to the source file for every WARNING and fixable INFO item. Do **not** ask for permission — fix them.

Common fixes from this codebase:

| Finding                                                  | Fix                                                                                              |
| -------------------------------------------------------- | ------------------------------------------------------------------------------------------------ |
| Missing `rel="noopener noreferrer"` on `target="_blank"` | Add it inline                                                                                    |
| `overflow-hidden` on table wrapper                       | Change to `overflow-x-auto`                                                                      |
| CSS class driven by `lang()` string comparison           | Replace with numeric/flag data comparison                                                        |
| `session('currency_symbol')` called per-cell             | Hoist to `$currency = (string)(session('currency_symbol') ?? '');` once at top, reuse everywhere |
| Unescaped output                                         | Wrap with `esc($var)` or `esc($var, 'js')` for inline JS                                         |
| Summary cards missing currency symbol                    | Prepend `$currency`                                                                              |

### Step 5 — Validate

Run PHP lint on the modified file:

```bash
php -l <path-to-file>
```

If lint fails, fix the syntax error before finishing.

### Step 6 — Update the Report

Mark fixed items in the report with a `— ✅ Fixed` suffix on their heading line.

---

## Severity Definitions

| Level       | Meaning                                                                       |
| ----------- | ----------------------------------------------------------------------------- |
| 🔴 CRITICAL | Security vulnerability or data leak (XSS, missing store scope, open redirect) |
| 🟡 WARNING  | Bug risk, convention violation, or accessibility/security hardening gap       |
| 🔵 INFO     | UX inconsistency or minor divergence from project conventions                 |
| ✅ GOOD     | Patterns done correctly — always include at least 2–3 to balance the report   |
