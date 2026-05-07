<?php

namespace App\Models;

use CodeIgniter\Model;

class PromotionRuleModel extends Model
{
    protected $table = 'pos_promotion_rules';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'promotion_id',
        'trigger_product_id',
        'trigger_qty',
        'gift_product_id',
        'gift_qty',
        'max_applications_per_invoice',
        'same_product_allowed',
        'created_at',
        'updated_at',
    ];

    public function getActiveRulesForProducts($storeId, array $productIds, $saleDate = null)
    {
        $productIds = array_values(array_unique(array_filter(array_map('intval', $productIds))));
        if ($productIds === []) {
            return [];
        }

        $date = $saleDate ? date('Y-m-d', strtotime((string) $saleDate)) : date('Y-m-d');

        $matchingPromotionIds = $this->select('pos_promotion_rules.promotion_id')
            ->join('pos_promotions', 'pos_promotions.id = pos_promotion_rules.promotion_id', 'inner')
            ->where('pos_promotions.store_id', (int) $storeId)
            ->where('pos_promotions.auto_apply', 1)
            ->where('pos_promotions.status', 'active')
            ->whereIn('pos_promotion_rules.trigger_product_id', $productIds)
            ->groupStart()
            ->where('pos_promotions.start_date <=', $date)
            ->orWhere('pos_promotions.start_date', null)
            ->groupEnd()
            ->groupStart()
            ->where('pos_promotions.end_date >=', $date)
            ->orWhere('pos_promotions.end_date', null)
            ->groupEnd()
            ->groupBy('pos_promotion_rules.promotion_id')
            ->findColumn('promotion_id');

        $matchingPromotionIds = array_values(array_unique(array_filter(array_map('intval', $matchingPromotionIds ?? []))));
        if ($matchingPromotionIds === []) {
            return [];
        }

        return $this->select('pos_promotion_rules.*, pos_promotions.name AS promotion_name, pos_promotions.priority, pos_promotions.auto_apply, pos_promotions.status AS promotion_status, pos_promotions.start_date, pos_promotions.end_date')
            ->join('pos_promotions', 'pos_promotions.id = pos_promotion_rules.promotion_id', 'inner')
            ->where('pos_promotions.store_id', (int) $storeId)
            ->where('pos_promotions.auto_apply', 1)
            ->where('pos_promotions.status', 'active')
            ->whereIn('pos_promotion_rules.promotion_id', $matchingPromotionIds)
            ->groupStart()
            ->where('pos_promotions.start_date <=', $date)
            ->orWhere('pos_promotions.start_date', null)
            ->groupEnd()
            ->groupStart()
            ->where('pos_promotions.end_date >=', $date)
            ->orWhere('pos_promotions.end_date', null)
            ->groupEnd()
            ->orderBy('pos_promotions.priority', 'DESC')
            ->orderBy('pos_promotion_rules.id', 'ASC')
            ->findAll();
    }

    /**
     * Returns all product IDs configured as gift items in active promotions for the given store.
     * These products should not be sold as standalone items.
     */
    public function getActiveGiftProductIds(int $storeId): array
    {
        $date = date('Y-m-d');
        $ids = $this->select('pos_promotion_rules.gift_product_id')
            ->join('pos_promotions', 'pos_promotions.id = pos_promotion_rules.promotion_id', 'inner')
            ->where('pos_promotions.store_id', $storeId)
            ->where('pos_promotions.status', 'active')
            ->groupStart()
            ->where('pos_promotions.start_date <=', $date)
            ->orWhere('pos_promotions.start_date', null)
            ->groupEnd()
            ->groupStart()
            ->where('pos_promotions.end_date >=', $date)
            ->orWhere('pos_promotions.end_date', null)
            ->groupEnd()
            ->findColumn('gift_product_id');

        return array_values(array_unique(array_filter(array_map('intval', $ids ?? []))));
    }
}
