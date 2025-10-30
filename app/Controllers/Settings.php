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

        model('SettingsModel')->saveSettings($id, $data);

        // Handle receipt template selection
        if ($this->request->getPost('receipt_template_id')) {
            $templateModel = new ReceiptTemplateModel();
            $templateModel->setDefaultTemplate($this->request->getPost('receipt_template_id'));
        }

        return redirect()->to('/settings')->with('message', 'Settings updated!');
    }
}
