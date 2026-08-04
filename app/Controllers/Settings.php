<?php

namespace App\Controllers;

use App\Models\ReceiptTemplateModel;

class Settings extends BaseController
{
    public function index()
    {
        $settings = model('SettingsModel')->getSettings();
        $templateModel = new ReceiptTemplateModel();
        $templates = $templateModel->findAll();

        return view('settings/index', [
            'settings' => $settings,
            'templates' => $templates,
            'title' => 'App Settings'
        ]);
    }

    public function update()
    {
        $id = $this->request->getPost('id') ?? 1;
        $data = $this->request->getPost();

        if (isset($data['zatca_invoice_type'])) {
            $mode = strtolower(trim((string) $data['zatca_invoice_type']));
            $data['zatca_invoice_type'] = in_array($mode, ['simplified', 'standard', 'both'], true) ? $mode : 'both';
        }

        // Validate ZATCA fields if e-invoicing is enabled
        if (!empty($data['einvoicing_enabled'])) {
            $errors = [];

            // Validate environment
            $validEnvironments = ['sandbox', 'simulation', 'production'];
            if (!empty($data['zatca_environment']) && !in_array($data['zatca_environment'], $validEnvironments, true)) {
                $errors[] = lang('Zatca.invalid_environment');
            }

            // Validate invoice type
            if (!empty($data['zatca_invoice_type']) && !in_array($data['zatca_invoice_type'], ['simplified', 'standard', 'both'], true)) {
                $errors[] = lang('Zatca.invalid_invoice_type');
            }

            // Validate store IDs format if provided
            if (!empty($data['zatca_enabled_store_ids'])) {
                $storeIds = json_decode($data['zatca_enabled_store_ids'], true);
                if ($storeIds === null && json_last_error() !== JSON_ERROR_NONE) {
                    $errors[] = lang('Zatca.invalid_store_ids');
                }
            }

            // If validation errors, redirect back with errors
            if (!empty($errors)) {
                return redirect()->back()->withInput()->with('error', implode('<br>', $errors));
            }
        }

        model('SettingsModel')->saveSettings($id, $data);

        // Handle receipt template selection
        if ($this->request->getPost('receipt_template_id')) {
            $templateModel = new ReceiptTemplateModel();
            $templateModel->setDefaultTemplate($this->request->getPost('receipt_template_id'));
        }

        return redirect()->to('/settings')->with('message', 'Settings updated!');
    }
}
