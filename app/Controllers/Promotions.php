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
            ->select('pos_promotions.*, pos_promotion_rules.id AS rule_id, pos_promotion_rules.trigger_product_id, pos_promotion_rules.trigger_qty, pos_promotion_rules.gift_product_id, pos_promotion_rules.gift_qty, pos_promotion_rules.max_applications_per_invoice, pos_promotion_rules.same_product_allowed, trigger_products.name AS trigger_product_name, gift_products.name AS gift_product_name')
            ->join('pos_promotion_rules', 'pos_promotion_rules.promotion_id = pos_promotions.id', 'left')
            ->join('pos_products AS trigger_products', 'trigger_products.id = pos_promotion_rules.trigger_product_id', 'left')
            ->join('pos_products AS gift_products', 'gift_products.id = pos_promotion_rules.gift_product_id', 'left')
            ->where('pos_promotions.store_id', (int) $storeId)
            ->orderBy('pos_promotions.priority', 'DESC')
            ->orderBy('pos_promotions.id', 'DESC')
            ->findAll();

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
        if (!$payload['ok']) {
            return redirect()->back()->withInput()->with('error', $payload['message']);
        }

        $promotionData = $payload['promotion'];
        $ruleData = $payload['rule'];

        $db = db_connect();
        $db->transStart();

        try {
            $promotionId = $this->promotionModel->insert($promotionData, true);
            if (!$promotionId) {
                throw new \RuntimeException(lang('Promotions.create_failed'));
            }

            $ruleData['promotion_id'] = (int) $promotionId;
            if (!$this->ruleModel->insert($ruleData, true)) {
                throw new \RuntimeException(lang('Promotions.create_failed'));
            }

            $db->transComplete();
            if (!$db->transStatus()) {
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
        if (!$promotion) {
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
        if (!$promotion) {
            return redirect()->to(site_url('promotions'))->with('error', lang('Promotions.not_found'));
        }

        $payload = $this->buildPayload($promotion);
        if (!$payload['ok']) {
            return redirect()->back()->withInput()->with('error', $payload['message']);
        }

        $db = db_connect();
        $db->transStart();

        try {
            $this->promotionModel->update((int) $id, $payload['promotion']);
            $this->ruleModel->where('promotion_id', (int) $id)->delete();
            $payload['rule']['promotion_id'] = (int) $id;
            $this->ruleModel->insert($payload['rule'], true);

            $db->transComplete();
            if (!$db->transStatus()) {
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
        if (!$promotion) {
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

    public function delete($id)
    {
        $promotion = $this->findPromotion((int) $id);
        if (!$promotion) {
            return redirect()->to(site_url('promotions'))->with('error', lang('Promotions.not_found'));
        }

        $db = db_connect();
        $db->transStart();

        try {
            $this->ruleModel->where('promotion_id', (int) $id)->delete();
            $this->promotionModel->delete((int) $id);

            $db->transComplete();
            if (!$db->transStatus()) {
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
        return $this->promotionModel
            ->select('pos_promotions.*, pos_promotion_rules.id AS rule_id, pos_promotion_rules.trigger_product_id, pos_promotion_rules.trigger_qty, pos_promotion_rules.gift_product_id, pos_promotion_rules.gift_qty, pos_promotion_rules.max_applications_per_invoice, pos_promotion_rules.same_product_allowed')
            ->join('pos_promotion_rules', 'pos_promotion_rules.promotion_id = pos_promotions.id', 'left')
            ->forStore()
            ->where('pos_promotions.id', (int) $id)
            ->first();
    }

    protected function buildPayload($existing = null)
    {
        $name = trim((string) $this->request->getPost('name'));
        $status = strtolower((string) ($this->request->getPost('status') ?? 'active'));
        $startDate = trim((string) ($this->request->getPost('start_date') ?? ''));
        $endDate = trim((string) ($this->request->getPost('end_date') ?? ''));
        $priority = (int) ($this->request->getPost('priority') ?? 100);
        $autoApply = (int) ($this->request->getPost('auto_apply') ?? 1) === 1 ? 1 : 0;
        $triggerProductId = (int) ($this->request->getPost('trigger_product_id') ?? 0);
        $triggerQty = (float) ($this->request->getPost('trigger_qty') ?? 0);
        $giftProductId = (int) ($this->request->getPost('gift_product_id') ?? 0);
        $giftQty = (float) ($this->request->getPost('gift_qty') ?? 0);
        $maxApplications = trim((string) ($this->request->getPost('max_applications_per_invoice') ?? ''));
        $sameProductAllowed = (int) ($this->request->getPost('same_product_allowed') ?? 0) === 1 ? 1 : 0;

        if ($name === '') {
            return ['ok' => false, 'message' => lang('Promotions.validation_name_required')];
        }
        if (!in_array($status, ['active', 'paused'], true)) {
            $status = 'active';
        }
        if ($triggerProductId <= 0 || $giftProductId <= 0) {
            return ['ok' => false, 'message' => lang('Promotions.validation_products_required')];
        }
        if ($triggerQty <= 0 || $giftQty <= 0) {
            return ['ok' => false, 'message' => lang('Promotions.validation_quantities_required')];
        }
        if ($startDate !== '' && $endDate !== '' && strtotime($endDate) < strtotime($startDate)) {
            return ['ok' => false, 'message' => lang('Promotions.validation_dates')];
        }
        if ($triggerProductId === $giftProductId && $sameProductAllowed !== 1) {
            return ['ok' => false, 'message' => lang('Promotions.validation_same_product')];
        }

        $storeId = (int) (session('store_id') ?? 0);
        $triggerProduct = (new M_products())->forStore($storeId)->find($triggerProductId);
        $giftProduct = (new M_products())->forStore($storeId)->find($giftProductId);
        if (!$triggerProduct || !$giftProduct) {
            return ['ok' => false, 'message' => lang('Promotions.validation_product_missing')];
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
            'rule' => [
                'trigger_product_id' => $triggerProductId,
                'trigger_qty' => $triggerQty,
                'gift_product_id' => $giftProductId,
                'gift_qty' => $giftQty,
                'max_applications_per_invoice' => $maxApplications !== '' ? (int) $maxApplications : null,
                'same_product_allowed' => $sameProductAllowed,
            ],
        ];
    }
}
