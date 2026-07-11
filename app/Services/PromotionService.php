<?php

namespace App\Services;

use App\Models\M_products;
use App\Models\PromotionRuleModel;

class PromotionService
{
    protected $ruleModel;
    protected $productModel;

    public function __construct()
    {
        $this->ruleModel = new PromotionRuleModel();
        $this->productModel = new M_products();
    }

    public function applyToSale(array $saleItems, $storeId, $saleDate = null): array
    {
        $baseItems = $this->normalizeBaseItems($saleItems);
        if ($baseItems === []) {
            return [
                'ok' => true,
                'items' => [],
                'generated_gift_items' => [],
                'applied_promotions' => [],
                'errors' => [],
            ];
        }

        $productIds = array_values(array_unique(array_map(function ($item) {
            return (int) $item['product_id'];
        }, $baseItems)));

        $rules = $this->ruleModel->getActiveRulesForProducts((int) $storeId, $productIds, $saleDate);

        // Group rules by promotion_id to handle multiple trigger products per promotion
        $rulesByPromotion = [];
        $giftProductIds = [];
        foreach ($rules as $rule) {
            $promotionId = (int) ($rule['promotion_id'] ?? 0);
            if ($promotionId <= 0) {
                continue;
            }

            if (!isset($rulesByPromotion[$promotionId])) {
                $rulesByPromotion[$promotionId] = [];
            }

            $rulesByPromotion[$promotionId][] = $rule;
            $giftProductIds[] = (int) ($rule['gift_product_id'] ?? 0);
        }

        $giftProducts = [];
        $allNeededIds = array_values(array_unique(array_filter($giftProductIds)));
        if ($allNeededIds !== []) {
            foreach ($this->productModel->forStore((int) $storeId)->whereIn('id', $allNeededIds)->findAll() as $product) {
                $giftProducts[(int) $product['id']] = $product;
            }
        }

        $errors = [];
        $generatedGiftItems = [];
        $appliedPromotions = [];
        $finalItems = $baseItems;

        // Only undiscounted trigger quantities should qualify for auto-added gifts.
        $eligibleTriggerQties = $this->buildEligibleTriggerQuantities($baseItems);

        // First pass: collect all qualifying promotions
        $qualifyingPromotions = [];
        foreach ($rulesByPromotion as $promotionId => $promotionRules) {
            $triggerProductIds = array_values(array_unique(array_filter(array_map(function ($r) {
                return (int) ($r['trigger_product_id'] ?? 0);
            }, $promotionRules))));

            if (empty($triggerProductIds)) {
                continue;
            }

            $allTriggersMetDetails = $this->checkAllTriggersMet($triggerProductIds, $promotionRules, $eligibleTriggerQties);
            if (!$allTriggersMetDetails['met'] || $allTriggersMetDetails['applications'] <= 0) {
                continue;
            }

            $qualifyingPromotions[$promotionId] = [
                'rules'               => $promotionRules,
                'applications'        => $allTriggersMetDetails['applications'],
                'trigger_product_ids' => $triggerProductIds,
                'priority'            => (int) ($promotionRules[0]['priority'] ?? 100),
            ];
        }

        // For each trigger set, keep only the promotion with the highest trigger_qty threshold met.
        // This prevents lower-threshold promotions from firing when a higher one is already satisfied.
        $filteredPromotions = $this->filterToHighestThreshold($qualifyingPromotions);

        // Second pass: apply gifts for the filtered qualifying promotions ONLY
        foreach ($filteredPromotions as $promotionId => $promotionData) {
            $promotionRules     = $promotionData['rules'];
            $applicationsCount  = $promotionData['applications'];
            $triggerProductIds  = $promotionData['trigger_product_ids'];

            // Use the first rule to get gift details (all rules in a promotion share the same gift)
            $firstRule = $promotionRules[0];
            $giftProductId = (int) ($firstRule['gift_product_id'] ?? 0);
            $giftQty = (float) ($firstRule['gift_qty'] ?? 0);

            $giftProduct = $giftProducts[$giftProductId] ?? null;
            if (!$giftProduct) {
                $errors[] = 'Promotion gift product not found for promotion #' . $promotionId . '.';
                continue;
            }

            // Apply max_applications_per_invoice limit
            $maxApplications = isset($firstRule['max_applications_per_invoice']) && $firstRule['max_applications_per_invoice'] !== null
                ? (int) $firstRule['max_applications_per_invoice']
                : null;
            if ($maxApplications !== null && $maxApplications >= 0) {
                $applicationsCount = min($applicationsCount, $maxApplications);
            }

            if ($applicationsCount <= 0) {
                continue;
            }

            // Check if gift product is one of the trigger products
            if (in_array($giftProductId, $triggerProductIds, true) && (int) ($firstRule['same_product_allowed'] ?? 0) !== 1) {
                continue;
            }

            $giftLine = [
                'product_id' => $giftProductId,
                'qty' => round($applicationsCount * $giftQty, 2),
                'unit_price' => 0.0,
                'cost_price' => (float) ($giftProduct['cost_price'] ?? 0),
                'discount' => 0.0,
                'discount_type' => 'fixed',
                'is_gift' => 1,
                'promotion_id' => $promotionId,
                'promotion_rule_id' => (int) ($firstRule['id'] ?? 0),
                'source_product_id' => implode(',', $triggerProductIds),
                'qualifying_line_key' => 'promotion_' . $promotionId,
                'name' => $giftProduct['name'] ?? 'Gift item',
                'code' => $giftProduct['code'] ?? '',
                'promotion_name' => (string) ($firstRule['promotion_name'] ?? 'Promotion'),
                'promotion_text' => $this->formatPromotionTextMultiTrigger($promotionRules),
            ];

            $generatedGiftItems[] = $giftLine;
            $finalItems[] = $giftLine;

            $appliedPromotions[] = [
                'promotion_id' => $promotionId,
                'promotion_rule_id' => (int) ($firstRule['id'] ?? 0),
                'promotion_name' => (string) ($firstRule['promotion_name'] ?? ''),
                'trigger_product_ids' => $triggerProductIds,
                'gift_product_id' => $giftProductId,
                'qualifying_line_key' => 'promotion_' . $promotionId,
                'applications' => $applicationsCount,
                'gift_qty' => $giftLine['qty'],
            ];
        }

        return [
            'ok' => $errors === [],
            'items' => $finalItems,
            'generated_gift_items' => $generatedGiftItems,
            'applied_promotions' => $appliedPromotions,
            'errors' => $errors,
        ];
    }

    protected function normalizeBaseItems(array $saleItems): array
    {
        $normalized = [];

        foreach ($saleItems as $index => $item) {
            if (!empty($item['is_gift'])) {
                continue;
            }

            $productId = (int) ($item['product_id'] ?? $item['id'] ?? 0);
            $qty = (float) ($item['qty'] ?? $item['quantity'] ?? 0);
            if ($productId <= 0 || $qty <= 0) {
                continue;
            }

            $discountType = strtolower((string) ($item['discount_type'] ?? 'fixed'));
            if (!in_array($discountType, ['fixed', 'percentage'], true)) {
                $discountType = 'fixed';
            }

            $normalized[] = [
                'product_id' => $productId,
                'qty' => $qty,
                'unit_price' => (float) ($item['unit_price'] ?? $item['price'] ?? 0),
                'cost_price' => (float) ($item['cost_price'] ?? 0),
                'discount' => (float) ($item['discount'] ?? 0),
                'discount_type' => $discountType,
                'is_gift' => 0,
                'promotion_id' => null,
                'promotion_rule_id' => null,
                'source_product_id' => null,
                'qualifying_line_key' => (string) ($item['qualifying_line_key'] ?? ('line_' . $index)),
                'name' => $item['name'] ?? null,
                'code' => $item['code'] ?? null,
            ];
        }

        return $normalized;
    }

    /**
     * Filter qualifying promotions to fire only the most specific/highest-tier one per trigger context.
     *
     * Two suppression rules are applied:
     * 1. Same trigger set, tiered qty: if promotions A and B share the same set of trigger products
     *    but B requires higher quantities, only B fires (highest tier wins). If both have the same
     *    trigger qty for all products, the one with higher priority fires (lower numeric priority value = higher priority).
     * 2. Subset suppression: if promotion A's trigger products are a strict subset of promotion B's,
     *    A is suppressed when B also qualifies (the more specific multi-product rule wins).
     * Completely unrelated promotions (no shared trigger products) fire independently.
     *
     * @param  array $qualifyingPromotions  [promotionId => ['rules', 'applications', 'trigger_product_ids', 'priority']]
     * @return array Filtered subset with the same structure
     */
    protected function filterToHighestThreshold(array $qualifyingPromotions): array
    {
        if (count($qualifyingPromotions) <= 1) {
            return $qualifyingPromotions;
        }

        // Build trigger qty maps for all qualifying promotions (single pass)
        $promoQtyMaps     = [];
        $triggerSetGroups = [];
        foreach ($qualifyingPromotions as $promotionId => $promotionData) {
            $qtyMap = $this->buildTriggerQtyMap($promotionData['rules'] ?? []);
            if ($qtyMap === []) {
                continue;
            }
            $triggerIds = array_keys($qtyMap);
            sort($triggerIds, SORT_NUMERIC);
            $triggerKey = implode(',', $triggerIds);
            $triggerSetGroups[$triggerKey][$promotionId] = $qtyMap;
            $promoQtyMaps[$promotionId] = $qtyMap;
        }

        // Rule 1: Same trigger set → keep only the highest-quantity tier(s), with priority tie-breaking
        $highestOnly = [];
        foreach ($triggerSetGroups as $promos) {
            $maxQtyMap = [];
            foreach ($promos as $qtyMap) {
                foreach ($qtyMap as $pid => $qty) {
                    if (!isset($maxQtyMap[$pid]) || $qty > $maxQtyMap[$pid]) {
                        $maxQtyMap[$pid] = $qty;
                    }
                }
            }
            // Collect all promotions matching max qty tier
            $candidatesForTier = [];
            foreach ($promos as $promotionId => $qtyMap) {
                $isMax = true;
                foreach ($maxQtyMap as $pid => $maxQty) {
                    if (!isset($qtyMap[$pid]) || $qtyMap[$pid] < $maxQty) {
                        $isMax = false;
                        break;
                    }
                }
                if ($isMax) {
                    $candidatesForTier[$promotionId] = $qualifyingPromotions[$promotionId];
                }
            }
            // If multiple promotions at max tier, use priority (lower number = higher priority)
            if (count($candidatesForTier) > 1) {
                uasort($candidatesForTier, function ($a, $b) {
                    $priorityA = (int) ($a['priority'] ?? 100);
                    $priorityB = (int) ($b['priority'] ?? 100);
                    return $priorityA - $priorityB;
                });
                // Keep only the first (highest priority) from this tier group
                $highestOnly[array_key_first($candidatesForTier)] = reset($candidatesForTier);
            } else {
                // Preserve numeric promotion IDs as keys; array_merge() reindexes numeric keys.
                foreach ($candidatesForTier as $promotionId => $promotionData) {
                    $highestOnly[$promotionId] = $promotionData;
                }
            }
        }

        // Rule 2: Subset suppression — suppress A if B's trigger set is a strict superset of A's.
        // IMPORTANT: use $promoQtyMaps[$idA] / $promoQtyMaps[$idB] here, NOT the $highestOnly values
        // (which are promotion data arrays, not qty maps).
        $suppressed = [];
        foreach ($highestOnly as $idA => $dataA) {
            $qtyMapA = $promoQtyMaps[$idA] ?? [];
            if (empty($qtyMapA)) {
                continue;
            }
            foreach ($highestOnly as $idB => $dataB) {
                if ($idA === $idB) {
                    continue;
                }
                $qtyMapB = $promoQtyMaps[$idB] ?? [];
                if (empty($qtyMapB)) {
                    continue;
                }
                // B must have strictly more trigger products than A to be a superset
                if (count($qtyMapB) <= count($qtyMapA)) {
                    continue;
                }
                // Every trigger product in A must exist in B with qty >= A's qty
                $isSuperset = true;
                foreach ($qtyMapA as $pid => $qtyA) {
                    if (!isset($qtyMapB[$pid]) || $qtyMapB[$pid] < $qtyA) {
                        $isSuperset = false;
                        break;
                    }
                }
                if ($isSuperset) {
                    $suppressed[$idA] = true;
                    break;
                }
            }
        }

        $filtered = [];
        foreach ($highestOnly as $promotionId => $promotionData) {
            if (!isset($suppressed[$promotionId])) {
                $filtered[$promotionId] = $promotionData;
            }
        }

        return $filtered;
    }

    protected function buildTriggerQtyMap(array $rules): array
    {
        $qtyMap = [];

        foreach ($rules as $rule) {
            $productId = (int) ($rule['trigger_product_id'] ?? 0);
            $qty = (float) ($rule['trigger_qty'] ?? 0);
            if ($productId <= 0 || $qty <= 0) {
                continue;
            }

            if (! isset($qtyMap[$productId]) || $qty > $qtyMap[$productId]) {
                $qtyMap[$productId] = $qty;
            }
        }

        return $qtyMap;
    }

    protected function buildEligibleTriggerQuantities(array $baseItems): array
    {
        $eligibleTriggerQties = [];

        foreach ($baseItems as $item) {
            if ($this->isDiscountedTriggerItem($item)) {
                continue;
            }

            $productId = (int) ($item['product_id'] ?? 0);
            $qty = (float) ($item['qty'] ?? 0);
            if ($productId <= 0 || $qty <= 0) {
                continue;
            }

            if (! isset($eligibleTriggerQties[$productId])) {
                $eligibleTriggerQties[$productId] = 0.0;
            }

            $eligibleTriggerQties[$productId] += $qty;
        }

        return $eligibleTriggerQties;
    }

    protected function isDiscountedTriggerItem(array $item): bool
    {
        return (float) ($item['discount'] ?? 0) > 0;
    }


    /**
     * Check if all trigger products for a promotion are present with required quantities
     * Returns the minimum number of complete promotion applications possible
     */
    protected function checkAllTriggersMet(array $triggerProductIds, array $promotionRules, array $cartProductQties): array
    {
        if (empty($triggerProductIds)) {
            return ['met' => false, 'applications' => 0];
        }

        $minApplications = PHP_INT_MAX;

        // For each trigger product, check if it exists in cart and calculate how many complete applications
        foreach ($promotionRules as $rule) {
            $triggerProductId = (int) ($rule['trigger_product_id'] ?? 0);
            $triggerQty = (float) ($rule['trigger_qty'] ?? 0);

            if ($triggerProductId <= 0 || $triggerQty <= 0) {
                continue;
            }

            $cartQty = $cartProductQties[$triggerProductId] ?? 0;
            if ($cartQty <= 0) {
                // One of the required trigger products is missing
                return ['met' => false, 'applications' => 0];
            }

            // Calculate how many times this trigger product allows the promotion to apply
            $applications = (int) floor($cartQty / $triggerQty);
            $minApplications = min($minApplications, $applications);
        }

        if ($minApplications <= 0 || $minApplications === PHP_INT_MAX) {
            return ['met' => false, 'applications' => 0];
        }

        return ['met' => true, 'applications' => $minApplications];
    }

    protected function formatPromotionTextMultiTrigger(array $promotionRules): string
    {
        $triggerParts = [];
        foreach ($promotionRules as $rule) {
            $triggerQty = rtrim(rtrim(number_format((float) ($rule['trigger_qty'] ?? 0), 2, '.', ''), '0'), '.');
            $triggerParts[] = 'Qty ' . $triggerQty;
        }

        $giftQty = rtrim(rtrim(number_format((float) ($promotionRules[0]['gift_qty'] ?? 0), 2, '.', ''), '0'), '.');

        return 'Buy ' . implode(' + ', $triggerParts) . ' Get ' . $giftQty;
    }
}
