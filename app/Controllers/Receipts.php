<?php

namespace App\Controllers;

use App\Models\ReceiptTemplateModel;

class Receipts extends BaseController
{
    protected $templateModel;

    public function __construct()
    {
        $this->templateModel = new ReceiptTemplateModel();
        $this->M_sales = new \App\Models\M_sales();
        $this->M_stores = new \App\Models\StoreModel();
    }

    public function generate($saleId)
    {
        // Get sale data from database
        $sale = $this->M_sales->getSaleData($saleId);
        if (!$sale) {
            return redirect()->back()->with('error', 'Sale not found.');
        }

        // Get receipt template
        $template = $this->templateModel->getDefaultTemplate();
        if (!$template) {
            return redirect()->back()->with('error', 'Receipt template not found.');
        }

        $loggedInStore = $this->M_stores->find(session()->get('store_id'));

        // Normalize monetary values from stored sale
        $discountAmount = (float) ($sale['total_discount'] ?? 0);
        $discountType = $sale['discount_type'] ?? 'fixed';
        $totalTax = (float) ($sale['total_tax'] ?? 0);
        $grandTotal = (float) ($sale['total'] ?? 0);
        // Compute authoritative subtotal from stored amounts
        $subtotal = max(0, $grandTotal + $discountAmount - $totalTax);
        // For optional display: derive discount percent against subtotal when relevant
        $discountPercent = ($subtotal > 0 && $discountAmount > 0)
            ? round(($discountAmount / $subtotal) * 100, 2)
            : 0;
        // Prepare replacements
        $replacements = [
            '{{store_name}}' => $loggedInStore['name'] ?? 'Your Store Name',
            '{{store_address}}' => $loggedInStore['address'] ?? '123 Main St, City',
            '{{store_phone}}' => $loggedInStore['phone'] ?? '555-1234',
            '{{store_footer}}' => 'Returns accepted within 7 days with receipt',
            '{{receipt_number}}' => $sale['invoice_no'],
            '{{date}}' => date('d/m/Y h:i A', strtotime($sale['created_at'])),
            '{{cashier}}' => $sale['cashier_name'],
            '{{customer}}' => $sale['customer_id'] ? '<div>Customer: ' . $sale['customer_name'] . '</div>' : '',
            '{{items}}' => $this->buildItemsHtml($sale['items']),
            '{{subtotal}}' => number_format($subtotal, 2),
            '{{total_discount}}' => number_format($discountAmount, 2),
            '{{discount_percent}}' => number_format($discountPercent, 2),
            '{{discount_type}}' => $discountType,
            '{{tax}}' => number_format($totalTax, 2),
            '{{total}}' => number_format($grandTotal, 2),
            '{{paid}}' => number_format($sale['amount_tendered'], 2),
            '{{change}}' => number_format($sale['change_amount'], 2),
            '{{ItemsCount}}' => count($sale['items']) ?? 0,
            '{{payment_type}}' => ($sale['payment_type'] == 'credit' ? strtoupper($sale['payment_type']) : ''),
        ];

        // Generate receipt HTML
        $receiptHtml = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $template['template']
        );

        // Return as PDF or wrapped HTML view
        if ($this->request->getGet('output') === 'pdf') {
            return $this->generatePdf($receiptHtml);
        }

        // Wrap inside a view with actions (back, print, pdf)
        return view('receipts/show', [
            'title' => 'Receipt # ' . ($sale['invoice_no'] ?? ''),
            'receiptHtml' => $receiptHtml,
            'sale' => $sale
        ]);
    }

    protected function buildItemsHtml($items)
    {
        $html = '';
        foreach ($items as $item) {
            $html .= '<tr>';
            $html .= '<td>' . $item['name'] . '</td>';
            $html .= '<td style="text-align: center;">' . $item['quantity'] . '</td>';
            $html .= '<td style="text-align: right;">' . number_format($item['price'], 2) . '</td>';
            $html .= '<td style="text-align: right;">' . number_format($item['quantity'] * $item['price'], 2) . '</td>';
            $html .= '</tr>';
        }
        return $html;
    }

    protected function generatePdf($html)
    {
        // For manual installation without composer, you can use direct TCPDF download
        // Download TCPDF from https://tcpdf.org/ and place in app/ThirdParty/tcpdf
        require_once APPPATH . 'Libraries/tcpdf/tcpdf.php';

        // Small-width receipt page (80mm wide), height flexible with page breaks
        $pdf = new \TCPDF('P', 'mm', [80, 200], true, 'UTF-8', false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(4, 4, 4);
        $pdf->SetAutoPageBreak(true, 6);
        $pdf->setImageScale(1.25);
        $pdf->SetFont('dejavusans', '', 9, '', true);

        // Minimal stylesheet to improve readability in PDF
        $style = '<style>
            html,body{font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 10px;}
            table{width:100%; border-collapse: collapse;}
            td,th{padding:2px 1px; border-bottom: 0.1mm solid #eee;}
            .text-right{text-align:right}
            .text-center{text-align:center}
        </style>';

        $pdf->AddPage();
        $pdf->writeHTML($style . $html, true, false, true, false, '');

        return $pdf->Output('receipt.pdf', 'I');
    }

    // List all templates
    public function templates()
    {
        $templates = $this->templateModel->findAll();

        $data = [
            'title' => 'Receipt Templates',
            'templates' => $templates
        ];

        return view('receipts/templates', $data);
    }

    // Show form to create new template
    public function createTemplate()
    {
        $data = [
            'title' => 'Create Receipt Template'
        ];

        return view('receipts/create_template', $data);
    }

    // Store new template
    public function storeTemplate()
    {
        $rules = [
            'name' => 'required|min_length[3]',
            'template' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'template' => $this->request->getPost('template'),
            'is_default' => $this->request->getPost('is_default') ? 1 : 0
        ];

        if ($data['is_default']) {
            // Reset other defaults
            $this->templateModel->where('is_default', 1)->set(['is_default' => 0])->update();
        }

        $this->templateModel->insert($data);

        return redirect()->to('/receipts/templates')->with('success', 'Template created successfully');
    }

    // Show form to edit template
    public function editTemplate($id)
    {
        $template = $this->templateModel->find($id);

        if (!$template) {
            return redirect()->to('/receipts/templates')->with('error', 'Template not found');
        }

        $data = [
            'title' => 'Edit Receipt Template',
            'template' => $template
        ];

        return view('receipts/edit_template', $data);
    }

    // Update template
    public function updateTemplate($id)
    {
        $template = $this->templateModel->find($id);

        if (!$template) {
            return redirect()->to('/receipts/templates')->with('error', 'Template not found');
        }

        $rules = [
            'name' => 'required|min_length[3]',
            'template' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'template' => $this->request->getPost('template'),
            'is_default' => $this->request->getPost('is_default') ? 1 : 0
        ];

        if ($data['is_default']) {
            // Reset other defaults
            $this->templateModel->where('is_default', 1)->set(['is_default' => 0])->update();
        }

        $this->templateModel->update($id, $data);

        return redirect()->to('/receipts/templates')->with('success', 'Template updated successfully');
    }

    // Set default template
    public function setDefault($id)
    {
        $this->templateModel->setDefaultTemplate($id);
        return redirect()->to('/receipts/templates')->with('success', 'Default template updated');
    }

    // Delete template
    public function deleteTemplate($id)
    {
        $template = $this->templateModel->find($id);

        if (!$template) {
            return redirect()->to('/receipts/templates')->with('error', 'Template not found');
        }

        // Don't allow deleting if it's the only template
        if ($this->templateModel->countAllResults() <= 1) {
            return redirect()->to('/receipts/templates')->with('error', 'Cannot delete the only template');
        }

        $this->templateModel->delete($id);

        return redirect()->to('/receipts/templates')->with('success', 'Template deleted successfully');
    }

    // Add methods for managing templates (index, create, edit, etc.)
}
