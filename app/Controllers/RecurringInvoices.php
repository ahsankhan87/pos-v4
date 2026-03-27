<?php

namespace App\Controllers;

use App\Models\M_customers;
use App\Models\M_products;
use App\Models\RecurringInvoiceModel;
use App\Services\RecurringInvoiceService;

class RecurringInvoices extends BaseController
{
    private $recurringModel;
    private $customerModel;
    private $productModel;

    public function __construct()
    {
        helper('audit');
        $this->recurringModel = new RecurringInvoiceModel();
        $this->customerModel = new M_customers();
        $this->productModel = new M_products();
    }

    public function index()
    {
        $status = strtolower((string) ($this->request->getGet('status') ?? 'active'));
        if (!in_array($status, ['active', 'paused', 'ended', 'all'], true)) {
            $status = 'active';
        }

        $builder = $this->recurringModel
            ->select('pos_recurring_invoices.*, pos_customers.name AS customer_name')
            ->join('pos_customers', 'pos_customers.id = pos_recurring_invoices.customer_id', 'left')
            ->forStore();

        if ($status !== 'all') {
            $builder->where('pos_recurring_invoices.status', $status);
        }

        $rows = $builder->orderBy('pos_recurring_invoices.next_due_date', 'ASC')->findAll();

        return view('recurring_invoices/index', [
            'title' => lang('RecurringInvoices.title_index'),
            'templates' => $rows,
            'activeStatus' => $status,
        ]);
    }

    public function new()
    {
        return view('recurring_invoices/new', [
            'title' => lang('RecurringInvoices.title_new'),
            'customers' => $this->customerModel->forStore()->findAll(),
            'products' => $this->productModel->forStore()->findAll(),
            'template' => null,
        ]);
    }

    public function create()
    {
        $payload = $this->buildTemplatePayload();
        if (!$payload['ok']) {
            return redirect()->back()->withInput()->with('error', $payload['message']);
        }

        $data = $payload['data'];
        $data['recurring_no'] = $this->recurringModel->generateRecurringNo();
        $data['store_id'] = (int) (session('store_id') ?? 0);
        $data['created_by'] = (int) (session('user_id') ?? 0);
        $data['updated_by'] = (int) (session('user_id') ?? 0);

        $insertId = $this->recurringModel->insert($data, true);
        if (!$insertId) {
            return redirect()->back()->withInput()->with('error', lang('RecurringInvoices.create_failed'));
        }

        logAction('recurring_invoice_created', 'Recurring template ID: ' . $insertId . ', Name: ' . $data['template_name']);

        return redirect()->to(site_url('recurring-invoices'))->with('success', lang('RecurringInvoices.create_success'));
    }

    public function edit($id)
    {
        $template = $this->recurringModel->forStore()->find((int) $id);
        if (!$template) {
            return redirect()->to(site_url('recurring-invoices'))->with('error', lang('RecurringInvoices.not_found'));
        }

        return view('recurring_invoices/edit', [
            'title' => lang('RecurringInvoices.title_edit'),
            'customers' => $this->customerModel->forStore()->findAll(),
            'products' => $this->productModel->forStore()->findAll(),
            'template' => $template,
        ]);
    }

    public function update($id)
    {
        $template = $this->recurringModel->forStore()->find((int) $id);
        if (!$template) {
            return redirect()->to(site_url('recurring-invoices'))->with('error', lang('RecurringInvoices.not_found'));
        }

        $payload = $this->buildTemplatePayload($template);
        if (!$payload['ok']) {
            return redirect()->back()->withInput()->with('error', $payload['message']);
        }

        $data = $payload['data'];
        $data['updated_by'] = (int) (session('user_id') ?? 0);

        $updated = $this->recurringModel->update((int) $id, $data);
        if (!$updated) {
            return redirect()->back()->withInput()->with('error', lang('RecurringInvoices.update_failed'));
        }

        logAction('recurring_invoice_updated', 'Recurring template ID: ' . $id . ', Name: ' . ($data['template_name'] ?? ''));

        return redirect()->to(site_url('recurring-invoices'))->with('success', lang('RecurringInvoices.update_success'));
    }

    public function toggle($id)
    {
        $template = $this->recurringModel->forStore()->find((int) $id);
        if (!$template) {
            return redirect()->to(site_url('recurring-invoices'))->with('error', lang('RecurringInvoices.not_found'));
        }

        $next = ($template['status'] ?? 'active') === 'active' ? 'paused' : 'active';
        if (($template['status'] ?? '') === 'ended') {
            return redirect()->to(site_url('recurring-invoices'))->with('error', lang('RecurringInvoices.ended_cannot_resume'));
        }

        $this->recurringModel->update((int) $id, [
            'status' => $next,
            'updated_by' => (int) (session('user_id') ?? 0),
        ]);

        logAction('recurring_invoice_status_changed', 'Recurring template ID: ' . $id . ', Status: ' . $next);

        return redirect()->to(site_url('recurring-invoices'))->with('success', lang('RecurringInvoices.status_updated'));
    }

    public function cloneTemplate($id)
    {
        $template = $this->recurringModel->forStore()->find((int) $id);
        if (!$template) {
            return redirect()->to(site_url('recurring-invoices'))->with('error', lang('RecurringInvoices.not_found'));
        }

        unset($template['id']);
        $template['recurring_no'] = $this->recurringModel->generateRecurringNo();
        $template['template_name'] = trim((string) ($template['template_name'] ?? 'Template')) . ' (Copy)';
        $template['status'] = 'paused';
        $template['last_generated_at'] = null;
        $template['last_sale_id'] = null;
        $template['created_by'] = (int) (session('user_id') ?? 0);
        $template['updated_by'] = (int) (session('user_id') ?? 0);

        $newId = $this->recurringModel->insert($template, true);
        if (!$newId) {
            return redirect()->to(site_url('recurring-invoices'))->with('error', lang('RecurringInvoices.clone_failed'));
        }

        logAction('recurring_invoice_cloned', 'Recurring template cloned from ID: ' . (int) $id . ' to ID: ' . (int) $newId);

        return redirect()->to(site_url('recurring-invoices/edit/' . (int) $newId))
            ->with('success', lang('RecurringInvoices.clone_success'));
    }

    public function delete($id)
    {
        $template = $this->recurringModel->forStore()->find((int) $id);
        if (!$template) {
            return redirect()->to(site_url('recurring-invoices'))->with('error', lang('RecurringInvoices.not_found'));
        }

        $deleted = $this->recurringModel->delete((int) $id);
        if (!$deleted) {
            return redirect()->to(site_url('recurring-invoices'))->with('error', lang('RecurringInvoices.delete_failed'));
        }

        logAction('recurring_invoice_deleted', 'Recurring template ID: ' . (int) $id . ' deleted.');

        return redirect()->to(site_url('recurring-invoices'))->with('success', lang('RecurringInvoices.delete_success'));
    }

    public function generateNow($id)
    {
        $template = $this->recurringModel->forStore()->find((int) $id);
        if (!$template) {
            return redirect()->to(site_url('recurring-invoices'))->with('error', lang('RecurringInvoices.not_found'));
        }

        $service = new RecurringInvoiceService();
        $result = $service->generateNow((int) $id, true);

        if (!$result['ok']) {
            return redirect()->to(site_url('recurring-invoices'))->with('error', $result['message']);
        }

        return redirect()->to(site_url('sales/receipt/' . (int) $result['sale_id']))
            ->with('success', lang('RecurringInvoices.generate_success'));
    }

    private function buildTemplatePayload($existing = null)
    {
        $templateName = trim((string) $this->request->getPost('template_name'));
        $customerId = (int) ($this->request->getPost('customer_id') ?? 0);
        $description = trim((string) $this->request->getPost('description'));
        $frequency = strtolower((string) ($this->request->getPost('frequency') ?? 'monthly'));
        $monthlyMode = strtolower((string) ($this->request->getPost('monthly_mode') ?? 'day_of_month'));
        $dayOfMonth = (int) ($this->request->getPost('day_of_month') ?? 1);
        $startDate = (string) ($this->request->getPost('start_date') ?? '');
        $endDate = (string) ($this->request->getPost('end_date') ?? '');
        $paymentMethod = (string) ($this->request->getPost('payment_method') ?? 'cash');

        if ($templateName === '') {
            return ['ok' => false, 'message' => lang('RecurringInvoices.validation_template_name_required')];
        }

        if (!in_array($frequency, ['daily', 'weekly', 'monthly', 'yearly'], true)) {
            return ['ok' => false, 'message' => lang('RecurringInvoices.validation_frequency_invalid')];
        }

        if ($frequency === 'monthly') {
            if (!in_array($monthlyMode, ['day_of_month', 'last_day'], true)) {
                return ['ok' => false, 'message' => lang('RecurringInvoices.validation_monthly_mode_invalid')];
            }
            if ($monthlyMode === 'day_of_month' && ($dayOfMonth < 1 || $dayOfMonth > 31)) {
                return ['ok' => false, 'message' => lang('RecurringInvoices.validation_monthly_day_range')];
            }
        } else {
            $monthlyMode = 'day_of_month';
            $dayOfMonth = null;
        }

        if ($startDate === '') {
            return ['ok' => false, 'message' => lang('RecurringInvoices.validation_start_date_required')];
        }

        if ($endDate !== '' && strtotime($endDate) < strtotime($startDate)) {
            return ['ok' => false, 'message' => lang('RecurringInvoices.validation_end_date_after_start')];
        }

        $items = $this->parseItemsFromRequest();
        if ($items === []) {
            return ['ok' => false, 'message' => lang('RecurringInvoices.validation_items_required')];
        }

        $totals = $this->calculateTotals($items);
        $nextDueDate = $existing['next_due_date'] ?? ($startDate . ' 00:00:00');

        if ($existing === null) {
            $tempTemplate = [
                'frequency' => $frequency,
                'monthly_mode' => $monthlyMode,
                'day_of_month' => $dayOfMonth,
                'end_date' => $endDate,
                'next_due_date' => $nextDueDate,
            ];

            // Roll forward to the first due date that is now/future.
            $now = date('Y-m-d H:i:s');
            while ($nextDueDate !== null && strtotime($nextDueDate) < strtotime($now)) {
                $tempTemplate['next_due_date'] = $nextDueDate;
                $nextDueDate = $this->recurringModel->computeNextDueDate($tempTemplate, $nextDueDate);
            }
        }

        return [
            'ok' => true,
            'data' => [
                'customer_id' => $customerId > 0 ? $customerId : null,
                'template_name' => $templateName,
                'description' => $description,
                'frequency' => $frequency,
                'monthly_mode' => $monthlyMode,
                'day_of_month' => $dayOfMonth,
                'start_date' => $startDate,
                'end_date' => $endDate !== '' ? $endDate : null,
                'next_due_date' => $nextDueDate,
                'payment_method' => $paymentMethod,
                'status' => (string) ($existing['status'] ?? 'active'),
                'items_json' => json_encode($items, JSON_UNESCAPED_UNICODE),
                'subtotal' => $totals['subtotal'],
                'total_discount' => $totals['total_discount'],
                'total_tax' => 0,
                'total' => $totals['total'],
            ],
        ];
    }

    private function parseItemsFromRequest(): array
    {
        $productIds = $this->request->getPost('product_id');
        $quantities = $this->request->getPost('quantity');
        $prices = $this->request->getPost('price');
        $discounts = $this->request->getPost('discount');
        $discountTypes = $this->request->getPost('discount_type');

        if (!is_array($productIds)) {
            return [];
        }

        $items = [];

        foreach ($productIds as $idx => $productIdRaw) {
            $productId = (int) $productIdRaw;
            $quantity = (float) ($quantities[$idx] ?? 0);
            $price = (float) ($prices[$idx] ?? 0);
            $discount = (float) ($discounts[$idx] ?? 0);
            $discountType = strtolower((string) ($discountTypes[$idx] ?? 'fixed'));
            if (!in_array($discountType, ['fixed', 'percentage'], true)) {
                $discountType = 'fixed';
            }

            if ($productId <= 0 || $quantity <= 0 || $price < 0) {
                continue;
            }

            $product = $this->productModel->forStore()->find($productId);
            if (!$product) {
                continue;
            }

            $items[] = [
                'product_id' => $productId,
                'product_name' => $product['name'] ?? ('Product #' . $productId),
                'quantity' => $quantity,
                'price' => $price,
                'cost_price' => (float) ($product['cost_price'] ?? 0),
                'discount' => max(0, $discount),
                'discount_type' => $discountType,
            ];
        }

        return $items;
    }

    private function calculateTotals(array $items): array
    {
        $subtotal = 0.0;
        $discountTotal = 0.0;

        foreach ($items as $item) {
            $lineBase = ((float) ($item['price'] ?? 0)) * ((float) ($item['quantity'] ?? 0));
            $lineDiscount = (float) ($item['discount'] ?? 0);
            if (($item['discount_type'] ?? 'fixed') === 'percentage') {
                $lineDiscount = $lineBase * ($lineDiscount / 100);
            }

            $lineDiscount = max(0, min($lineBase, $lineDiscount));
            $subtotal += $lineBase;
            $discountTotal += $lineDiscount;
        }

        return [
            'subtotal' => round($subtotal, 2),
            'total_discount' => round($discountTotal, 2),
            'total' => round(max(0, $subtotal - $discountTotal), 2),
        ];
    }
}
