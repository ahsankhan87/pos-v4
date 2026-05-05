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

        // Create a map of product quantities for easier lookup
        $cartProductQties = [];
        foreach ($baseItems as $item) {
            $productId = (int) $item['product_id'];
            if (!isset($cartProductQties[$productId])) {
                $cartProductQties[$productId] = 0;
            }
            $cartProductQties[$productId] += (float) $item['qty'];
        }

        // Track which rules have been applied to avoid duplicate applications
        $appliedRuleIds = [];

        // Process each promotion's rules together
        foreach ($rulesByPromotion as $promotionId => $promotionRules) {
            // Get all unique trigger products for this promotion
            $triggerProductIds = [];
            foreach ($promotionRules as $rule) {
                $triggerProductIds[] = (int) ($rule['trigger_product_id'] ?? 0);
            }
            $triggerProductIds = array_values(array_unique(array_filter($triggerProductIds)));

            if (empty($triggerProductIds)) {
                continue;
            }

            // Check if ALL trigger products are present in cart with required quantities
            $allTriggersMetDetails = $this->checkAllTriggersMet($triggerProductIds, $promotionRules, $cartProductQties);
            if (!$allTriggersMetDetails['met']) {
                continue;
            }

            // Apply gifts for this promotion
            $applicationsCount = $allTriggersMetDetails['applications'];
            if ($applicationsCount <= 0) {
                continue;
            }

            // Use the first rule to get gift details (all rules in a promotion have the same gift)
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
