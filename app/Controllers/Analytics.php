<?php

namespace App\Controllers;

use App\Models\SalesAnalyticsModel;
use App\Models\M_sales;

class Analytics extends BaseController
{
    protected $analyticsModel;

    public function __construct()
    {
        $this->saleModel = new M_sales();
        $this->analyticsModel = new SalesAnalyticsModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Sales Analytics',
            'dailySales' => $this->analyticsModel->getDailySales(),
            'monthlySales' => $this->analyticsModel->getMonthlySales(),
            'topProducts' => $this->analyticsModel->getTopProducts(5, 30),
            'paymentMethods' => $this->analyticsModel->getSalesByPaymentMethod(),

        ];

        return  view('analytics/index', $data);
    }
    public function getComparativeAnalysis()
    {
        $currentStart = $this->request->getGet('current_start') ?? date('Y-m-01');
        $currentEnd = $this->request->getGet('current_end') ?? date('Y-m-d');

        // Calculate previous period (same length)
        $days = (strtotime($currentEnd) - strtotime($currentStart)) / (60 * 60 * 24);
        $previousStart = date('Y-m-d', strtotime($currentStart . " -" . ($days + 1) . " days"));
        $previousEnd = date('Y-m-d', strtotime($currentStart . " -1 day"));

        $currentData = $this->getPeriodData($currentStart, $currentEnd);
        $previousData = $this->getPeriodData($previousStart, $previousEnd);
        // return [
        //     'current' => $currentData,
        //     'previous' => $previousData,
        //     'growth' => $this->calculateGrowth($currentData, $previousData)
        // ];
        return $this->response->setJSON([
            'current' => $currentData,
            'previous' => $previousData,
            'growth' => $this->calculateGrowth($currentData, $previousData)
        ]);
    }

    protected function getPeriodData($start, $end)
    {
        return [
            'total_sales' => $this->saleModel
                ->selectSum('total')
                ->where('created_at >=', $start)
                ->where('created_at <=', $end)
                ->get()
                ->getRow()
                ->total ?? 0,
            'transaction_count' => $this->saleModel
                ->where('created_at >=', $start)
                ->where('created_at <=', $end)
                ->countAllResults(),
            'average_sale' => $this->saleModel
                ->selectAvg('total')
                ->where('created_at >=', $start)
                ->where('created_at <=', $end)
                ->get()
                ->getRow()
                ->total ?? 0
        ];
    }

    protected function calculateGrowth($current, $previous)
    {
        if ($previous['total_sales'] == 0) return 0;

        return [
            'sales' => (($current['total_sales'] - $previous['total_sales']) / $previous['total_sales']) * 100,
            'transactions' => (($current['transaction_count'] - $previous['transaction_count']) / $previous['transaction_count']) * 100,
            'average' => (($current['average_sale'] - $previous['average_sale']) / $previous['average_sale']) * 100
        ];
    }
}
