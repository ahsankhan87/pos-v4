<?php

namespace App\Controllers\Reports;

use App\Controllers\BaseController;
use App\Services\Reports\InventoryReports;

class Inventory extends BaseController
{
    protected $reports;

    public function __construct()
    {
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
}
