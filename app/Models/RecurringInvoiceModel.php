<?php

namespace App\Models;

use CodeIgniter\I18n\Time;
use CodeIgniter\Model;

class RecurringInvoiceModel extends Model
{
    protected $table = 'pos_recurring_invoices';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'recurring_no',
        'store_id',
        'customer_id',
        'template_name',
        'description',
        'frequency',
        'monthly_mode',
        'day_of_month',
        'start_date',
        'end_date',
        'next_due_date',
        'last_generated_at',
        'last_sale_id',
        'payment_method',
        'status',
        'items_json',
        'subtotal',
        'total_discount',
        'total_tax',
        'total',
        'created_by',
        'updated_by',
    ];

    protected $validationRules = [
        'template_name' => 'required|min_length[3]|max_length[120]',
        'frequency' => 'required|in_list[daily,weekly,monthly,yearly]',
        'monthly_mode' => 'permit_empty|in_list[day_of_month,last_day]',
        'day_of_month' => 'permit_empty|integer|greater_than_equal_to[1]|less_than_equal_to[31]',
        'start_date' => 'required|valid_date',
        'end_date' => 'permit_empty|valid_date',
        'status' => 'required|in_list[active,paused,ended]',
        'items_json' => 'required',
        'total' => 'required|decimal',
    ];

    public function forStore($storeId = null)
    {
        if ($storeId === null) {
            $storeId = (int) (session('store_id') ?? 0);
        }

        return $this->where($this->table . '.store_id', $storeId);
    }

    public function generateRecurringNo($prefix = 'RINV')
    {
        $storeId = (int) (session('store_id') ?? 1);
        $date = date('Ymd');
        $like = $prefix . $storeId . '-' . $date . '%';
        $lastRef = $this->selectMax('recurring_no')->where('recurring_no LIKE', $like)->first();

        if (!empty($lastRef['recurring_no'])) {
            $lastNum = (int) substr($lastRef['recurring_no'], strlen($prefix . $storeId . '-' . $date . '-'));
            $newNum = $lastNum + 1;
        } else {
            $newNum = 1;
        }

        return $prefix . $storeId . '-' . $date . '-' . str_pad((string) $newNum, 4, '0', STR_PAD_LEFT);
    }

    public function findDueForGeneration($storeId = null, $limit = 100)
    {
        $now = date('Y-m-d H:i:s');

        return $this->forStore($storeId)
            ->where('status', 'active')
            ->where('next_due_date <=', $now)
            ->orderBy('next_due_date', 'ASC')
            ->findAll($limit);
    }

    public function computeNextDueDate(array $template, $fromDateTime = null)
    {
        $frequency = strtolower((string) ($template['frequency'] ?? 'monthly'));
        $monthlyMode = strtolower((string) ($template['monthly_mode'] ?? 'day_of_month'));
        $dayOfMonth = (int) ($template['day_of_month'] ?? 1);

        $base = $fromDateTime
            ? Time::parse($fromDateTime)
            : Time::parse((string) ($template['next_due_date'] ?? date('Y-m-d H:i:s')));

        switch ($frequency) {
            case 'daily':
                $next = $base->addDays(1);
                break;
            case 'weekly':
                $next = $base->addDays(7);
                break;
            case 'yearly':
                $next = $base->addYears(1);
                break;
            case 'monthly':
            default:
                $next = $this->computeMonthlyNextDate($base, $monthlyMode, $dayOfMonth);
                break;
        }

        $endDate = (string) ($template['end_date'] ?? '');
        if ($endDate !== '') {
            $endDateTime = Time::parse($endDate . ' 23:59:59');
            if ($next->toDateTimeString() > $endDateTime->toDateTimeString()) {
                return null;
            }
        }

        return $next->toDateTimeString();
    }

    private function computeMonthlyNextDate(Time $base, string $monthlyMode, int $dayOfMonth): Time
    {
        $candidate = $base->addMonths(1);

        if ($monthlyMode === 'last_day') {
            $year = (int) $candidate->format('Y');
            $month = (int) $candidate->format('m');
            $lastDay = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            return Time::createFromDate($year, $month, $lastDay)
                ->setTime((int) $base->format('H'), (int) $base->format('i'), (int) $base->format('s'));
        }

        $safeDay = max(1, min(31, $dayOfMonth));
        $year = (int) $candidate->format('Y');
        $month = (int) $candidate->format('m');
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $safeDay = min($safeDay, $daysInMonth);

        return Time::createFromDate($year, $month, $safeDay)
            ->setTime((int) $base->format('H'), (int) $base->format('i'), (int) $base->format('s'));
    }
}
