<?php

namespace App\Controllers\Reports;

use App\Controllers\BaseController;
use App\Services\Reports\InventoryReports;

class Inventory extends BaseController
{
    protected $reports;

    public function __construct()
    {
        helper('business_feature');
        $this->reports = new InventoryReports();
    }

    protected function filters(): array
    {
        return [
            'start_date' => $this->request->getGet('start_date') ?? date('Y-m-01'),
            'end_date' => $this->request->getGet('end_date') ?? date('Y-m-d'),
        ];
    }

    public function index()
    {
        $data = [
            'title' => 'Inventory Reports',
            'filters' => $this->filters(),
        ];
        return view('reports/inventory_index', $data);
    }

    public function valuation()
    {
        return $this->response->setJSON($this->reports->getValuation());
    }
    public function lowStock()
    {
        return $this->response->setJSON($this->reports->getLowStock());
    }
    public function movement()
    {
        return $this->response->setJSON($this->reports->getMovementTrend($this->filters()));
    }
    public function slowMovers()
    {
        return $this->response->setJSON($this->reports->getSlowMovers($this->filters()));
    }

    public function expiry()
    {
        if (! business_feature_enabled('expiry_tracking')) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'This feature is disabled for your business profile.',
            ]);
        }

        $soonDays = (int) ($this->request->getGet('soon_days') ?? 7);
        $soonDays = $soonDays > 0 ? $soonDays : 7;

        return $this->response->setJSON($this->reports->getExpiry($soonDays));
    }

    public function expiryPrint()
    {
        if (! business_feature_enabled('expiry_tracking')) {
            return redirect()->to('/')->with('error', 'This feature is disabled for your business profile.');
        }

        $soonDays = (int) ($this->request->getGet('soon_days') ?? 7);
        $soonDays = $soonDays > 0 ? $soonDays : 7;

        $data = $this->reports->getExpiry($soonDays);

        return view('reports/expiry_print', [
            'title' => lang('Reports.expiry_report_title'),
            'expired' => $data['expired'],
            'expiring' => $data['expiring'],
            'soonDays' => $soonDays,
            'generated' => date('Y-m-d H:i:s'),
        ]);
    }
}
