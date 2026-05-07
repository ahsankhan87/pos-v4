<?php

namespace App\Controllers;

use App\Models\M_products;
use App\Models\PromotionModel;
use App\Models\PromotionRuleModel;

class Promotions extends BaseController
{
    protected $promotionModel;
    protected $ruleModel;
    protected $productModel;

    public function __construct()
    {
        helper(['audit', 'form', 'permission']);
        $this->promotionModel = new PromotionModel();
        $this->ruleModel = new PromotionRuleModel();
        $this->productModel = new M_products();
    }

    public function index()
    {
        $storeId = session('store_id');

        $rows = $this->promotionModel
            ->where('pos_promotions.store_id', (int) $storeId)
            ->orderBy('pos_promotions.priority', 'DESC')
            ->orderBy('pos_promotions.id', 'DESC')
            ->findAll();

        if (! empty($rows)) {
            $promotionIds = array_map(function ($row) {
                return (int) ($row['id'] ?? 0);
            }, $rows);

            $ruleRows = $this->ruleModel
                ->select('pos_promotion_rules.*, trigger_products.name AS trigger_product_name, gift_products.name AS gift_product_name')
                ->join('pos_products AS trigger_products', 'trigger_products.id = pos_promotion_rules.trigger_product_id', 'left')
                ->join('pos_products AS gift_products', 'gift_products.id = pos_promotion_rules.gift_product_id', 'left')
                ->whereIn('pos_promotion_rules.promotion_id', $promotionIds)
                ->orderBy('pos_promotion_rules.id', 'ASC')
                ->findAll();

            $rulesByPromotion = [];
            foreach ($ruleRows as $ruleRow) {
                $promotionId = (int) ($ruleRow['promotion_id'] ?? 0);
                if (! isset($rulesByPromotion[$promotionId])) {
                    $rulesByPromotion[$promotionId] = [];
                }
                $rulesByPromotion[$promotionId][] = $ruleRow;
            }

            foreach ($rows as &$row) {
                $promotionId = (int) ($row['id'] ?? 0);
                $rules = $rulesByPromotion[$promotionId] ?? [];

                $row['rules'] = $rules;
                $row['trigger_product_names'] = array_values(array_unique(array_filter(array_map(function ($rule) {
                    return (string) ($rule['trigger_product_name'] ?? '');
                }, $rules))));
                $row['trigger_product_name'] = $row['trigger_product_names'][0] ?? null;
                $row['trigger_product_id'] = isset($rules[0]['trigger_product_id']) ? (int) $rules[0]['trigger_product_id'] : null;
                $row['trigger_qty'] = isset($rules[0]['trigger_qty']) ? (float) $rules[0]['trigger_qty'] : null;
                $row['gift_product_name'] = $rules[0]['gift_product_name'] ?? null;
                $row['gift_product_id'] = isset($rules[0]['gift_product_id']) ? (int) $rules[0]['gift_product_id'] : null;
                $row['gift_qty'] = isset($rules[0]['gift_qty']) ? (float) $rules[0]['gift_qty'] : null;
                $row['max_applications_per_invoice'] = $rules[0]['max_applications_per_invoice'] ?? null;
                $row['same_product_allowed'] = isset($rules[0]['same_product_allowed']) ? (int) $rules[0]['same_product_allowed'] : 0;
            }
            unset($row);
        }

        return view('promotions/index', [
            'title' => lang('Promotions.title_index'),
            'promotions' => $rows,
        ]);
    }

    public function new()
    {
        return view('promotions/new', [
            'title' => lang('Promotions.title_new'),
            'promotion' => null,
            'products' => $this->productModel->forStore()->orderBy('name', 'ASC')->findAll(),
        ]);
    }

    public function create()
    {
        $payload = $this->buildPayload();
        if (! $payload['ok']) {
            return redirect()->back()->withInput()->with('error', $payload['message']);
        }

        $promotionData = $payload['promotion'];
        $rulesData = $payload['rules'];

        $db = db_connect();
        $db->transStart();

        try {
            $promotionId = $this->promotionModel->insert($promotionData, true);
            if (! $promotionId) {
                throw new \RuntimeException(lang('Promotions.create_failed'));
            }

            foreach ($rulesData as $ruleData) {
                $ruleData['promotion_id'] = (int) $promotionId;
                if (! $this->ruleModel->insert($ruleData, true)) {
                    throw new \RuntimeException(lang('Promotions.create_failed'));
                }
            }

            $db->transComplete();
            if (! $db->transStatus()) {
                throw new \RuntimeException(lang('Promotions.create_failed'));
            }

            logAction('promotion_created', 'Promotion ID: ' . (int) $promotionId . ', Name: ' . ($promotionData['name'] ?? ''));

            return redirect()->to(site_url('promotions'))->with('success', lang('Promotions.create_success'));
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        $promotion = $this->findPromotion((int) $id);
        if (! $promotion) {
            return redirect()->to(site_url('promotions'))->with('error', lang('Promotions.not_found'));
        }

        return view('promotions/edit', [
            'title' => lang('Promotions.title_edit'),
            'promotion' => $promotion,
            'products' => $this->productModel->forStore()->orderBy('name', 'ASC')->findAll(),
        ]);
    }

    public function update($id)
    {
        $promotion = $this->findPromotion((int) $id);
        if (! $promotion) {
            return redirect()->to(site_url('promotions'))->with('error', lang('Promotions.not_found'));
        }

        $payload = $this->buildPayload($promotion);
        if (! $payload['ok']) {
            return redirect()->back()->withInput()->with('error', $payload['message']);
        }

        $db = db_connect();
        $db->transStart();

        try {
            $this->promotionModel->update((int) $id, $payload['promotion']);
            $this->ruleModel->where('promotion_id', (int) $id)->delete();

            foreach ($payload['rules'] as $ruleData) {
                $ruleData['promotion_id'] = (int) $id;
                $this->ruleModel->insert($ruleData, true);
            }

            $db->transComplete();
            if (! $db->transStatus()) {
                throw new \RuntimeException(lang('Promotions.update_failed'));
            }

            logAction('promotion_updated', 'Promotion ID: ' . (int) $id . ', Name: ' . ($payload['promotion']['name'] ?? ''));

            return redirect()->to(site_url('promotions'))->with('success', lang('Promotions.update_success'));
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function toggle($id)
    {
        $promotion = $this->findPromotion((int) $id);
        if (! $promotion) {
            return redirect()->to(site_url('promotions'))->with('error', lang('Promotions.not_found'));
        }

        $nextStatus = ($promotion['status'] ?? 'active') === 'active' ? 'paused' : 'active';
        $this->promotionModel->update((int) $id, [
            'status' => $nextStatus,
            'updated_by' => (int) (session('user_id') ?? 0),
        ]);

        logAction('promotion_status_changed', 'Promotion ID: ' . (int) $id . ', Status: ' . $nextStatus);

        return redirect()->to(site_url('promotions'))->with('success', lang('Promotions.status_updated'));
    }

    public function printAll()
    {
        $storeId = session('store_id');

        $rows = $this->promotionModel
            ->where('pos_promotions.store_id', (int) $storeId)
            ->orderBy('pos_promotions.priority', 'DESC')
            ->orderBy('pos_promotions.id', 'DESC')
            ->findAll();

        if (! empty($rows)) {
            $promotionIds = array_map(function ($row) {
                return (int) ($row['id'] ?? 0);
            }, $rows);

            $ruleRows = $this->ruleModel
                ->select('pos_promotion_rules.*, trigger_products.name AS trigger_product_name, gift_products.name AS gift_product_name')
                ->join('pos_products AS trigger_products', 'trigger_products.id = pos_promotion_rules.trigger_product_id', 'left')
                ->join('pos_products AS gift_products', 'gift_products.id = pos_promotion_rules.gift_product_id', 'left')
                ->whereIn('pos_promotion_rules.promotion_id', $promotionIds)
                ->orderBy('pos_promotion_rules.id', 'ASC')
                ->findAll();

            $rulesByPromotion = [];
            foreach ($ruleRows as $ruleRow) {
                $pid = (int) ($ruleRow['promotion_id'] ?? 0);
                $rulesByPromotion[$pid][] = $ruleRow;
            }

            foreach ($rows as &$row) {
                $pid = (int) ($row['id'] ?? 0);
                $rules = $rulesByPromotion[$pid] ?? [];
                $row['rules'] = $rules;
                $row['trigger_product_names'] = array_values(array_unique(array_filter(array_map(function ($r) {
                    return (string) ($r['trigger_product_name'] ?? '');
                }, $rules))));
                $row['trigger_qty_list'] = array_map(function ($r) {
                    return (float) ($r['trigger_qty'] ?? 0);
                }, $rules);
                $row['gift_product_name'] = $rules[0]['gift_product_name'] ?? '-';
                $row['gift_qty'] = isset($rules[0]['gift_qty']) ? (float) $rules[0]['gift_qty'] : 0;
                $row['max_applications_per_invoice'] = $rules[0]['max_applications_per_invoice'] ?? null;
            }
            unset($row);
        }

        return view('promotions/print', [
            'title'      => lang('Promotions.print_title'),
            'promotions' => $rows ?? [],
            'printed_at' => date('Y-m-d H:i'),
            'storeName'  => session('store_name') ?? '',
        ]);
    }

    public function delete($id)
    {
        $promotion = $this->findPromotion((int) $id);
        if (! $promotion) {
            return redirect()->to(site_url('promotions'))->with('error', lang('Promotions.not_found'));
        }

        $db = db_connect();
        $db->transStart();

        try {
            $this->ruleModel->where('promotion_id', (int) $id)->delete();
            $this->promotionModel->delete((int) $id);

            $db->transComplete();
            if (! $db->transStatus()) {
                throw new \RuntimeException(lang('Promotions.delete_failed'));
            }

            logAction('promotion_deleted', 'Promotion ID: ' . (int) $id);

            return redirect()->to(site_url('promotions'))->with('success', lang('Promotions.delete_success'));
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->to(site_url('promotions'))->with('error', $e->getMessage());
        }
    }

    protected function findPromotion($id)
    {
        $promotion = $this->promotionModel
            ->select('pos_promotions.*')
            ->forStore()
            ->where('pos_promotions.id', (int) $id)
            ->first();

        if (! $promotion) {
            return null;
        }

        $rules = $this->ruleModel
            ->where('promotion_id', (int) $id)
            ->orderBy('id', 'ASC')
            ->findAll();

        $promotion['rules'] = $rules;
        $promotion['trigger_product_ids'] = array_values(array_unique(array_map(function ($rule) {
            return (int) ($rule['trigger_product_id'] ?? 0);
        }, $rules)));

        if (! empty($rules)) {
            $promotion['rule_id'] = $rules[0]['id'] ?? null;
            $promotion['trigger_product_id'] = $rules[0]['trigger_product_id'] ?? null;
            $promotion['trigger_qty'] = $rules[0]['trigger_qty'] ?? null;
            $promotion['gift_product_id'] = $rules[0]['gift_product_id'] ?? null;
            $promotion['gift_qty'] = $rules[0]['gift_qty'] ?? null;
            $promotion['max_applications_per_invoice'] = $rules[0]['max_applications_per_invoice'] ?? null;
            $promotion['same_product_allowed'] = $rules[0]['same_product_allowed'] ?? null;
        }

        return $promotion;
    }

    protected function buildPayload($existing = null)
    {
        $name = trim((string) $this->request->getPost('name'));
        $status = strtolower((string) ($this->request->getPost('status') ?? 'active'));
        $startDate = trim((string) ($this->request->getPost('start_date') ?? ''));
        $endDate = trim((string) ($this->request->getPost('end_date') ?? ''));
        $priority = (int) ($this->request->getPost('priority') ?? 100);
        $autoApply = (int) ($this->request->getPost('auto_apply') ?? 1) === 1 ? 1 : 0;

        // Handle new form structure with trigger_rules
        $triggerRulesRaw = $this->request->getPost('trigger_rules');
        $triggerRules = [];

        if (is_array($triggerRulesRaw)) {
            foreach ($triggerRulesRaw as $rule) {
                $triggerProductId = (int) ($rule['trigger_product_id'] ?? 0);
                $triggerQty = (float) ($rule['trigger_qty'] ?? 0);

                if ($triggerProductId > 0 && $triggerQty > 0) {
                    $triggerRules[] = [
                        'trigger_product_id' => $triggerProductId,
                        'trigger_qty' => $triggerQty,
                    ];
                }
            }
        }

        // Fallback for old form structure (backward compatibility)
        if (empty($triggerRules)) {
            $triggerProductIdsRaw = $this->request->getPost('trigger_product_ids');
            $triggerProductIds = [];
            if (is_array($triggerProductIdsRaw)) {
                $triggerProductIds = array_values(array_unique(array_filter(array_map('intval', $triggerProductIdsRaw))));
            }
            if ($triggerProductIds === []) {
                $fallbackTriggerProductId = (int) ($this->request->getPost('trigger_product_id') ?? 0);
                if ($fallbackTriggerProductId > 0) {
                    $triggerProductIds = [$fallbackTriggerProductId];
                }
            }

            $triggerQty = (float) ($this->request->getPost('trigger_qty') ?? 0);
            if ($triggerQty > 0) {
                foreach ($triggerProductIds as $productId) {
                    $triggerRules[] = [
                        'trigger_product_id' => $productId,
                        'trigger_qty' => $triggerQty,
                    ];
                }
            }
        }

        $giftProductId = (int) ($this->request->getPost('gift_product_id') ?? 0);
        $giftQty = (float) ($this->request->getPost('gift_qty') ?? 0);
        $maxApplications = trim((string) ($this->request->getPost('max_applications_per_invoice') ?? ''));
        $sameProductAllowed = (int) ($this->request->getPost('same_product_allowed') ?? 0) === 1 ? 1 : 0;

        if ($name === '') {
            return ['ok' => false, 'message' => lang('Promotions.validation_name_required')];
        }
        if (! in_array($status, ['active', 'paused'], true)) {
            $status = 'active';
        }
        if (empty($triggerRules) || $giftProductId <= 0) {
            return ['ok' => false, 'message' => lang('Promotions.validation_products_required')];
        }
        if ($giftQty <= 0) {
            return ['ok' => false, 'message' => lang('Promotions.validation_quantities_required')];
        }
        if ($startDate !== '' && $endDate !== '' && strtotime($endDate) < strtotime($startDate)) {
            return ['ok' => false, 'message' => lang('Promotions.validation_dates')];
        }

        // Validate all trigger products
        foreach ($triggerRules as $rule) {
            if ($rule['trigger_qty'] <= 0) {
                return ['ok' => false, 'message' => lang('Promotions.validation_quantities_required')];
            }

            if ($rule['trigger_product_id'] === $giftProductId && $sameProductAllowed !== 1) {
                return ['ok' => false, 'message' => lang('Promotions.validation_same_product')];
            }
        }

        $storeId = (int) (session('store_id') ?? 0);
        $giftProduct = (new M_products())->forStore($storeId)->find($giftProductId);
        if (! $giftProduct) {
            return ['ok' => false, 'message' => lang('Promotions.validation_product_missing')];
        }

        foreach ($triggerRules as $rule) {
            $triggerProduct = (new M_products())->forStore($storeId)->find($rule['trigger_product_id']);
            if (! $triggerProduct) {
                return ['ok' => false, 'message' => lang('Promotions.validation_product_missing')];
            }
        }

        // Create rules for database storage
        $rulesData = [];
        foreach ($triggerRules as $rule) {
            $rulesData[] = [
                'trigger_product_id' => $rule['trigger_product_id'],
                'trigger_qty' => $rule['trigger_qty'],
                'gift_product_id' => $giftProductId,
                'gift_qty' => $giftQty,
                'max_applications_per_invoice' => $maxApplications !== '' ? (int) $maxApplications : null,
                'same_product_allowed' => $sameProductAllowed,
            ];
        }

        return [
            'ok' => true,
            'promotion' => [
                'store_id' => $storeId,
                'name' => $name,
                'status' => $status,
                'start_date' => $startDate !== '' ? $startDate : null,
                'end_date' => $endDate !== '' ? $endDate : null,
                'priority' => $priority,
                'auto_apply' => $autoApply,
                'created_by' => (int) ($existing['created_by'] ?? session('user_id') ?? 0),
                'updated_by' => (int) (session('user_id') ?? 0),
            ],
            'rules' => $rulesData,
        ];
    }
}
