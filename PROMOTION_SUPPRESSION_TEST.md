# Promotion Suppression Implementation Test Guide

## Overview

The `PromotionService::filterToHighestThreshold()` method now implements three suppression rules:

1. **Same-trigger-set tiered qty**: Higher quantity tiers suppress lower ones
2. **Subset suppression**: Multi-product rules suppress single-product subsets
3. **Priority tie-breaking**: When two promotions have identical trigger sets/qtys, higher priority (lower number) wins
4. **Independent promotions**: Completely unrelated promotions fire independently

---

## Test Scenarios

### Scenario 1: Same-Trigger-Set Tiered Qty Suppression

**Setup:**

- **Promotion A**: Product X qty ≥ 3 → Gift-A (priority: 100)
- **Promotion B**: Product X qty ≥ 6 → Gift-B (priority: 100)

**Test Case:**

- Sell: 6 units of Product X
- **Expected Result**: Only Gift-B fires; Gift-A is suppressed (higher tier wins)

**Rationale**: Both promotions have the same trigger product (X), but B requires qty 6 while A requires qty 3. Since qty 6 is available, B is more specific and A should not fire.

---

### Scenario 2: Subset Suppression (Multi-Product vs Single-Product)

**Setup:**

- **Promotion A**: Product X qty ≥ 5 → Gift-A (priority: 100)
- **Promotion B**: Product X qty ≥ 5 AND Product Y qty ≥ 5 → Gift-B (priority: 100)

**Test Case:**

- Sell: 5 units of Product X AND 5 units of Product Y
- **Expected Result**: Only Gift-B fires; Gift-A is suppressed (more specific multi-product rule wins)

**Rationale**: Promotion A's trigger set {X} is a strict subset of Promotion B's trigger set {X, Y}. Since both qualify but B is more specific (requires more trigger products), A should not fire.

---

### Scenario 3: Priority Tie-Breaking (Same Trigger Set, Same Qty)

**Setup:**

- **Promotion A**: Product X qty ≥ 5 → Gift-A (priority: 100)
- **Promotion B**: Product X qty ≥ 5 → Gift-B (priority: 50) ← Lower number = higher priority

**Test Case:**

- Sell: 5 units of Product X
- **Expected Result**: Only Gift-B fires; Gift-A is suppressed (higher priority wins)

**Rationale**: Both promotions have identical trigger sets and thresholds. The tie-breaking rule uses the `priority` field (lower value = higher priority), so B fires and A is suppressed.

---

### Scenario 4: Independent Unrelated Promotions (Should Both Fire)

**Setup:**

- **Promotion A**: Product X qty ≥ 5 → Gift-A (priority: 100)
- **Promotion C**: Product Z qty ≥ 3 → Gift-C (priority: 100)

**Test Case:**

- Sell: 5 units of Product X AND 3 units of Product Z
- **Expected Result**: BOTH Gift-A and Gift-C fire (they have no overlapping trigger products)

**Rationale**: These promotions have completely different trigger products {X} vs {Z}. They are unrelated and should not suppress each other.

---

## Implementation Details

### Code Changes Made

**File**: `app/Services/PromotionService.php`

#### 1. `filterToHighestThreshold()` Method (Lines 221–348)

- **Single-pass build**: Constructs `$promoQtyMaps` and `$triggerSetGroups` in one loop
- **Rule 1 Implementation**:
  - Groups promotions by sorted trigger product IDs
  - Per group, finds maximum qty for each product
  - Keeps only promotions matching all max quantities
  - If multiple promotions at max tier, sorts by priority (ascending) and keeps only the first
- **Rule 2 Implementation**:
  - For each promotion A in `$highestOnly`, checks if any promotion B in `$highestOnly` is a strict superset
  - Superset logic: B must have strictly more trigger products, and all of A's products in B with qty ≥
  - Uses `$promoQtyMaps[$idA]` / `$promoQtyMaps[$idB]` (NOT iteration values which are promotion data arrays)

#### 2. `applyToSale()` Method (Line 96)

- Added `'priority' => (int) ($promotionRules[0]['priority'] ?? 100)` to `qualifyingPromotions` array
- Priority is extracted from the first rule (all rules in a promotion share the same priority from `pos_promotions.priority`)

#### 3. Removed Dead Code

- Deleted `dominatesTriggerQtyMap()` method (no longer used after refactoring)

#### 4. Database Schema

- **No changes needed** — existing schema fully supports the new rules
- `pos_promotion_rules.trigger_qty` and `pos_promotions.priority` already provide necessary data

---

## Data Structures

### Input: `$qualifyingPromotions`

```php
[
    promotionId => [
        'rules'               => [/* rule data from pos_promotion_rules */],
        'applications'        => (int) count,
        'trigger_product_ids' => [productId, ...],
        'priority'            => (int) /* from pos_promotions.priority */,
    ],
    ...
]
```

### Internal: `$promoQtyMaps`

```php
[
    promotionId => [
        productId => (float) trigger_qty,
        ...
    ],
    ...
]
```

### Internal: `$triggerSetGroups`

```php
[
    'triggerKey' => [  // e.g., '12,45' for products 12 and 45
        promotionId => [productId => qty, ...],
        ...
    ],
    ...
]
```

---

## Edge Cases & Behavior

| Scenario                                      | Behavior                | Notes                                                        |
| --------------------------------------------- | ----------------------- | ------------------------------------------------------------ |
| 0 or 1 qualifying promotion                   | Return as-is            | No suppression needed                                        |
| All promotions unrelated                      | All fire                | No suppression applied                                       |
| Tied promotions (same trigger, qty, priority) | First in iteration wins | Deterministic but arbitrary; assign priorities to avoid ties |
| Promotion missing priority field              | Defaults to 100         | Safe fallback                                                |
| Subset with identical quantities              | Subset suppressed       | More trigger products = more specific                        |
| Subset with higher quantities in superset     | Subset suppressed       | Superset must have qty ≥ all of subset's products            |
| Subset with lower quantities in superset      | No suppression          | Not a true superset if any product qty is lower              |

---

## Testing the Implementation

### Manual Testing in UI

1. Navigate to Promotions module
2. Create test promotions matching the scenarios above
3. Process a sale with the specified products/quantities
4. Verify that only expected gifts appear in the sale line items
5. Check `pos_sale_items.is_gift=1` and `promotion_id` fields for gift tracking

### Code-Level Testing

The implementation can be tested via:

```php
// In a test or controller
$service = service('promotionService');
$result = $service->applyToSale($saleItems, $storeId, $saleDate);

// Verify $result['generated_gift_items'] contains only expected gifts
// Verify $result['applied_promotions'] lists only expected promotions
```

---

## Known Limitations

- **Circular superset relationships**: If somehow Promotion A is a subset of B and B is a subset of A, the algorithm suppresses A. Prevent via data validation.
- **Tie-breaking determinism**: Without explicit priority assignment, ties are broken by first occurrence in filtered list (deterministic but arbitrary). Recommend assigning distinct priorities.
- **Gift product availability**: If gift product doesn't exist or is out of stock, no suppression is applied to qualifying promotions (error logged instead).

---

## Verification Checklist

- [x] Syntax: No PHP errors (`php -l` passes)
- [x] Same-trigger-set tiered qty suppression logic implemented
- [x] Subset suppression logic implemented with correct qty map references
- [x] Priority field extracted from rules and used for tie-breaking
- [x] `dominatesTriggerQtyMap()` removed (dead code)
- [x] Single-pass build of `$promoQtyMaps` and `$triggerSetGroups` (no duplication)
- [x] Database schema supports all rules (no changes needed)
- [ ] Manual UI test: Scenario 1 (same-trigger tiered)
- [ ] Manual UI test: Scenario 2 (subset suppression)
- [ ] Manual UI test: Scenario 3 (priority tie-breaking)
- [ ] Manual UI test: Scenario 4 (independent promotions)
