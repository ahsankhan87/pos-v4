# Code Review: `app/Services/PromotionService.php`

**Reviewed:** 2026-05-14  
**Reviewer:** GitHub Copilot

---

## 🔴 CRITICAL

**1. Raw string concatenated into error messages — no sanitization**
In `applyToSale`:

```php
$errors[] = 'Promotion gift product not found for promotion #' . $promotionId . '.';
```

`$promotionId` is already cast to `int` here so it's safe, but this pattern should be noted — if ever changed to use a non-cast value, it becomes an injection vector.

---

## 🟡 WARNING

**2. `forStore()` called on shared model instance — query state mutation risk**
`$this->productModel` is a shared instance. Calling `forStore()` on it may mutate its internal query builder state. If this model is reused elsewhere in the same request, leftover `WHERE` clauses could corrupt subsequent queries. Each query should use a fresh model instance or chain from a clean builder.

**3. `filterToHighestThreshold` can incorrectly suppress multi-trigger bundle promotions**
If Promotion 1 requires A×5 + B×2 → Gift-A, and Promotion 2 requires A×10 → Gift-B, both qualify. Since Product A's max threshold is 10, Promotion 1 (A×5) gets suppressed — even though it is a fundamentally different multi-product bundle. The filter should only suppress promotions that share **all** the same trigger product set, not just one overlapping product.

**4. `checkAllTriggersMet` has an ambiguous early-exit when all rules are invalid**
If every rule in `$promotionRules` has `trigger_product_id <= 0` or `trigger_qty <= 0`, the loop exits without setting `$minApplications` and the final `PHP_INT_MAX` guard returns `met: false`. This is correct but relies on an implicit fallthrough. An explicit early guard before the loop (checking that at least one valid rule exists) would make intent clear and prevent subtle bugs if the logic is modified later.

**5. Gift product IDs are fetched eagerly before the qualifying/filtering step**
`$giftProductIds` is populated from all active rules matching cart products — including promotions that will later be suppressed by `filterToHighestThreshold`. The bulk `whereIn` query fetches rows that are never used. For stores with many promotions this wastes DB I/O. The gift product fetch should happen after filtering.

---

## 🔵 INFO

**6. `formatPromotionTextMultiTrigger` omits product names — output is not user-friendly**
The resulting string `"Buy Qty 5 + Qty 10 Get 1"` loses product name context. If shown on the sale line, it is ambiguous. The rule rows already contain `trigger_product_name` and `gift_product_name` when fetched with joins — consider including them.

**7. No explicit guard on `$promotionRules[0]` access**

```php
$firstRule = $promotionRules[0];
```

This is implicitly safe because `checkAllTriggersMet` requires at least one valid rule, but adding `if (empty($promotionRules)) continue;` before this line makes the invariant explicit and protects against future refactors breaking the assumption.

**8. `$appliedRuleIds` deduplication removed — no guard against duplicate rule IDs**
The previous implementation tracked applied rule IDs to prevent double-application. After the two-pass refactor this was removed. If two promotions share the same `promotion_rule_id` due to DB inconsistency, both would fire. A lightweight check on `promotion_rule_id` in the second pass would be a safe safeguard.

**9. `$finalItems = $baseItems` — verify callers always set `is_gift` on gift rows**
`normalizeBaseItems` correctly strips gift items via `is_gift` flag, preventing re-processing. However, if any caller passes previously-enriched sale items without the `is_gift` flag set, gifts would be re-processed. This should be verified at all call sites.

**10. Performance: no caching for high-frequency real-time recalculation**
`applyToSale` runs a full DB rule query on every call. For AJAX-driven real-time sale updates (triggered on every qty change), this can generate significant DB load. A short-lived cache keyed on `storeId + sorted productIds` would reduce repeated identical queries within a single sale session.

---

## ✅ GOOD

- **Two-pass architecture** (collect qualifiers → filter → apply) is clean, readable, and testable.
- **`forStore()` is properly scoped** on all product lookups — no cross-store data leakage.
- **`normalizeBaseItems` correctly excludes gift items** from re-processing, preventing infinite re-application loops.
- **Side-effect-free service** — `applyToSale` returns a data structure rather than writing to DB, making it safe to call speculatively and easy to unit test.
- **`max_applications_per_invoice` cap** is applied correctly after the applications count is calculated.
- **Consistent type casting** throughout — all IDs are `(int)`, all quantities are `(float)`.
- **`filterToHighestThreshold`** correctly handles the common case of a single trigger product appearing in multiple promotions at different qty thresholds.
