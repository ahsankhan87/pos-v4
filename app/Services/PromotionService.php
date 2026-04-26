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
        $rulesByTrigger = [];
        $giftProductIds = [];
        foreach ($rules as $rule) {
            $triggerProductId = (int) ($rule['trigger_product_id'] ?? 0);
            if ($triggerProductId <= 0) {
                continue;
            }

            $rulesByTrigger[$triggerProductId][] = $rule;
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
        $finalItems = [];

        foreach ($baseItems as $item) {
            $finalItems[] = $item;

            $applicableRules = $rulesByTrigger[(int) $item['product_id']] ?? [];
            if ($applicableRules === []) {
                continue;
            }

            $selectedRule = null;
            foreach ($applicableRules as $candidate) {
                $giftProductId = (int) ($candidate['gift_product_id'] ?? 0);
                if ($giftProductId === (int) $item['product_id'] && (int) ($candidate['same_product_allowed'] ?? 0) !== 1) {
                    continue;
                }

                $selectedRule = $candidate;
                break;
            }

            if (!$selectedRule) {
                continue;
            }

            $triggerQty = (float) ($selectedRule['trigger_qty'] ?? 0);
            $giftQty = (float) ($selectedRule['gift_qty'] ?? 0);
            if ($triggerQty <= 0 || $giftQty <= 0) {
                continue;
            }

            $applications = (int) floor(((float) $item['qty']) / $triggerQty);
            $maxApplications = isset($selectedRule['max_applications_per_invoice']) && $selectedRule['max_applications_per_invoice'] !== null
                ? (int) $selectedRule['max_applications_per_invoice']
                : null;
            if ($maxApplications !== null && $maxApplications >= 0) {
                $applications = min($applications, $maxApplications);
            }

            if ($applications <= 0) {
                continue;
            }

            $giftProductId = (int) ($selectedRule['gift_product_id'] ?? 0);
            $giftProduct = $giftProducts[$giftProductId] ?? null;
            if (!$giftProduct) {
                $errors[] = 'Promotion gift product not found for rule #' . (int) ($selectedRule['id'] ?? 0) . '.';
                continue;
            }

            $giftLine = [
                'product_id' => $giftProductId,
                'qty' => round($applications * $giftQty, 2),
                'unit_price' => 0.0,
                'cost_price' => (float) ($giftProduct['cost_price'] ?? 0),
                'discount' => 0.0,
                'discount_type' => 'fixed',
                'is_gift' => 1,
                'promotion_id' => (int) ($selectedRule['promotion_id'] ?? 0),
                'promotion_rule_id' => (int) ($selectedRule['id'] ?? 0),
                'source_product_id' => (int) $item['product_id'],
                'qualifying_line_key' => (string) $item['qualifying_line_key'],
                'name' => $giftProduct['name'] ?? 'Gift item',
                'code' => $giftProduct['code'] ?? '',
                'promotion_name' => (string) ($selectedRule['promotion_name'] ?? 'Promotion'),
                'promotion_text' => $this->formatPromotionText($selectedRule),
            ];

            $generatedGiftItems[] = $giftLine;
            $finalItems[] = $giftLine;
            $appliedPromotions[] = [
                'promotion_id' => (int) ($selectedRule['promotion_id'] ?? 0),
                'promotion_rule_id' => (int) ($selectedRule['id'] ?? 0),
                'promotion_name' => (string) ($selectedRule['promotion_name'] ?? ''),
                'trigger_product_id' => (int) ($selectedRule['trigger_product_id'] ?? 0),
                'gift_product_id' => $giftProductId,
                'qualifying_line_key' => (string) $item['qualifying_line_key'],
                'applications' => $applications,
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

    protected function formatPromotionText(array $rule): string
    {
        $triggerQty = rtrim(rtrim(number_format((float) ($rule['trigger_qty'] ?? 0), 2, '.', ''), '0'), '.');
        $giftQty = rtrim(rtrim(number_format((float) ($rule['gift_qty'] ?? 0), 2, '.', ''), '0'), '.');

        return 'Buy ' . $triggerQty . ' Get ' . $giftQty;
    }
}
