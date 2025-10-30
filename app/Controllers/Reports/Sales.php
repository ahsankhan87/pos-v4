<?php

namespace App\Controllers\Reports;

use App\Controllers\BaseController;
use App\Services\Reports\SalesReports;

class Sales extends BaseController
{
    protected SalesReports $reports;

    public function __construct()
    {
        $this->reports = new SalesReports();
    }

    protected function filters(): array
    {
        return [
            'start_date' => $this->request->getGet('start_date') ?? date('Y-m-01'),
            'end_date' => $this->request->getGet('end_date') ?? date('Y-m-d'),
            // Always use the selected store from session
            'store_id' => session('store_id') ?? null,
            'limit' => $this->request->getGet('limit') ?? 10,
        ];
    }

    public function index()
    {
        $filters = $this->filters();
        $data = [
            'title' => 'Sales Reports',
            'filters' => $filters,
        ];
        return view('reports/sales_index', $data);
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

    public function topProducts()
    {
        return $this->response->setJSON($this->reports->getTopProducts($this->filters()));
    }

    public function byEmployee()
    {
        return $this->response->setJSON($this->reports->getSalesByEmployee($this->filters()));
    }

    public function categoryMix()
    {
        return $this->response->setJSON($this->reports->getCategoryBreakdown($this->filters()));
    }

    public function hourly()
    {
        return $this->response->setJSON($this->reports->getHourlyDistribution($this->filters()));
    }

    public function growth()
    {
        return $this->response->setJSON($this->reports->getGrowthSummary($this->filters()));
    }

    public function topCustomers()
    {
        return $this->response->setJSON($this->reports->getTopCustomers($this->filters()));
    }

    public function margin()
    {
        return $this->response->setJSON($this->reports->getMarginSummary($this->filters()));
    }

    public function discountsTrend()
    {
        return $this->response->setJSON($this->reports->getDiscountsTrend($this->filters()));
    }

    public function returnsSummary()
    {
        return $this->response->setJSON($this->reports->getReturnsSummary($this->filters()));
    }
}
