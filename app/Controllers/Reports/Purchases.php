<?php

namespace App\Controllers\Reports;

use App\Controllers\BaseController;
use App\Services\Reports\PurchaseReports;

class Purchases extends BaseController
{
    protected $reports;

    public function __construct()
    {
        $this->reports = new PurchaseReports();
    }

    protected function filters(): array
    {
        return [
            'start_date' => $this->request->getGet('start_date') ?? date('Y-m-01'),
            'end_date' => $this->request->getGet('end_date') ?? date('Y-m-d'),
            'store_id' => session('store_id') ?? null,
            'limit' => $this->request->getGet('limit') ?? 10,
        ];
    }

    public function index()
    {
        $filters = $this->filters();
        $data = [
            'title' => 'Purchases Reports',
            'filters' => $filters,
        ];
        return view('reports/purchases_index', $data);
    }

    public function summary()
    {
        return $this->response->setJSON($this->reports->getSummary($this->filters()));
    }
    public function timeseries()
    {
        return $this->response->setJSON($this->reports->getDailyTimeseries($this->filters()));
    }
    public function paymentMix()
    {
        return $this->response->setJSON($this->reports->getPaymentBreakdown($this->filters()));
    }
    public function topSuppliers()
    {
        return $this->response->setJSON($this->reports->getTopSuppliers($this->filters()));
    }
    public function topItems()
    {
        return $this->response->setJSON($this->reports->getTopItems($this->filters()));
    }
    public function returnsSummary()
    {
        return $this->response->setJSON($this->reports->getReturnsSummary($this->filters()));
    }
}
