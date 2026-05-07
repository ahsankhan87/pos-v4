<?php

namespace App\Controllers;

use App\Models\EmployeeSalesTargetModel;
use App\Models\EmployeesModel;

class EmployeeTargets extends BaseController
{
    protected $targetModel;
    protected $employeeModel;

    public function __construct()
    {
        helper(['audit', 'form', 'permission']);
        $this->targetModel = new EmployeeSalesTargetModel();
        $this->employeeModel = new EmployeesModel();
    }

    public function index()
    {
        $storeId = (int) (session('store_id') ?? 0);
        $month = trim((string) ($this->request->getGet('month') ?? date('Y-m')));
        $employeeId = (int) ($this->request->getGet('employee_id') ?? 0);

        $builder = $this->targetModel
            ->select('pos_employee_sales_targets.*, pos_employees.name AS employee_name')
            ->join('pos_employees', 'pos_employees.id = pos_employee_sales_targets.employee_id', 'left')
            ->where('pos_employee_sales_targets.store_id', $storeId)
            ->orderBy('pos_employee_sales_targets.target_month', 'DESC')
            ->orderBy('pos_employee_sales_targets.id', 'DESC');

        if ($month !== '') {
            $builder->where('pos_employee_sales_targets.target_month', $month);
        }
        if ($employeeId > 0) {
            $builder->where('pos_employee_sales_targets.employee_id', $employeeId);
        }

        $targets = $builder->findAll();

        return view('employee_targets/index', [
            'title' => lang('EmployeeTargets.title_index'),
            'targets' => $targets,
            'employees' => $this->getEmployeesForStore($storeId),
            'selectedMonth' => $month,
            'selectedEmployeeId' => $employeeId,
        ]);
    }

    public function new()
    {
        $storeId = (int) (session('store_id') ?? 0);

        return view('employee_targets/new', [
            'title' => lang('EmployeeTargets.title_new'),
            'employees' => $this->getEmployeesForStore($storeId),
            'target' => null,
        ]);
    }

    public function create()
    {
        $payload = $this->buildPayload();
        if (! $payload['ok']) {
            return redirect()->back()->withInput()->with('error', $payload['message']);
        }

        if (! $this->canChangeTargetMonth($payload['data']['target_month'])) {
            return redirect()->back()->withInput()->with('error', lang('EmployeeTargets.error_locked_month'));
        }

        $exists = $this->targetModel
            ->forStore($payload['data']['store_id'])
            ->where('employee_id', (int) $payload['data']['employee_id'])
            ->where('target_month', (string) $payload['data']['target_month'])
            ->first();

        if ($exists) {
            return redirect()->back()->withInput()->with('error', lang('EmployeeTargets.error_duplicate_target'));
        }

        $id = $this->targetModel->insert($payload['data'], true);
        if (! $id) {
            return redirect()->back()->withInput()->with('error', lang('EmployeeTargets.error_create'));
        }

        logAction('employee_target_created', 'Employee target ID: ' . (int) $id . ', Employee ID: ' . (int) $payload['data']['employee_id'] . ', Month: ' . $payload['data']['target_month']);

        return redirect()->to(site_url('employee-targets'))->with('success', lang('EmployeeTargets.success_create'));
    }

    public function edit($id)
    {
        $target = $this->findTarget((int) $id);
        if (! $target) {
            return redirect()->to(site_url('employee-targets'))->with('error', lang('EmployeeTargets.error_not_found'));
        }

        return view('employee_targets/edit', [
            'title' => lang('EmployeeTargets.title_edit'),
            'target' => $target,
            'employees' => $this->getEmployeesForStore((int) (session('store_id') ?? 0)),
        ]);
    }

    public function update($id)
    {
        $existing = $this->findTarget((int) $id);
        if (! $existing) {
            return redirect()->to(site_url('employee-targets'))->with('error', lang('EmployeeTargets.error_not_found'));
        }

        $payload = $this->buildPayload($existing);
        if (! $payload['ok']) {
            return redirect()->back()->withInput()->with('error', $payload['message']);
        }

        if (! $this->canChangeTargetMonth($payload['data']['target_month'])) {
            return redirect()->back()->withInput()->with('error', lang('EmployeeTargets.error_locked_month'));
        }

        $duplicate = $this->targetModel
            ->forStore($payload['data']['store_id'])
            ->where('employee_id', (int) $payload['data']['employee_id'])
            ->where('target_month', (string) $payload['data']['target_month'])
            ->where('id !=', (int) $id)
            ->first();

        if ($duplicate) {
            return redirect()->back()->withInput()->with('error', lang('EmployeeTargets.error_duplicate_target'));
        }

        $this->targetModel->update((int) $id, $payload['data']);
        logAction('employee_target_updated', 'Employee target ID: ' . (int) $id . ', Employee ID: ' . (int) $payload['data']['employee_id'] . ', Month: ' . $payload['data']['target_month']);

        return redirect()->to(site_url('employee-targets'))->with('success', lang('EmployeeTargets.success_update'));
    }

    public function delete($id)
    {
        $target = $this->findTarget((int) $id);
        if (! $target) {
            return redirect()->to(site_url('employee-targets'))->with('error', lang('EmployeeTargets.error_not_found'));
        }

        if (! $this->canChangeTargetMonth((string) ($target['target_month'] ?? ''))) {
            return redirect()->to(site_url('employee-targets'))->with('error', lang('EmployeeTargets.error_locked_month'));
        }

        $this->targetModel->delete((int) $id);
        logAction('employee_target_deleted', 'Employee target ID: ' . (int) $id);

        return redirect()->to(site_url('employee-targets'))->with('success', lang('EmployeeTargets.success_delete'));
    }

    public function achievements()
    {
        $storeId = (int) (session('store_id') ?? 0);
        $selectedMonth = trim((string) ($this->request->getGet('month') ?? date('Y-m')));
        if (! preg_match('/^\\d{4}-(0[1-9]|1[0-2])$/', $selectedMonth)) {
            $selectedMonth = date('Y-m');
        }

        $selectedEmployeeId = (int) ($this->request->getGet('employee_id') ?? 0);

        $targetsBuilder = $this->targetModel
            ->select('pos_employee_sales_targets.*, pos_employees.name AS employee_name')
            ->join('pos_employees', 'pos_employees.id = pos_employee_sales_targets.employee_id', 'left')
            ->where('pos_employee_sales_targets.store_id', $storeId)
            ->where('pos_employee_sales_targets.target_month', $selectedMonth)
            ->orderBy('pos_employees.name', 'ASC');

        if ($selectedEmployeeId > 0) {
            $targetsBuilder->where('pos_employee_sales_targets.employee_id', $selectedEmployeeId);
        }

        $targetRows = $targetsBuilder->findAll();
        $metricsByEmployee = $this->getMonthPerformanceByEmployee($storeId, $selectedMonth, $selectedEmployeeId);

        $rows = [];
        $totals = [
            'target_amount' => 0.0,
            'achieved_amount' => 0.0,
            'variance_amount' => 0.0,
            'employee_count' => 0,
        ];

        foreach ($targetRows as $targetRow) {
            $employeeId = (int) ($targetRow['employee_id'] ?? 0);
            $metric = $metricsByEmployee[$employeeId] ?? [
                'gross_total' => 0.0,
                'returns_total' => 0.0,
                'net_total' => 0.0,
                'sales_count' => 0,
            ];

            $targetAmount = (float) ($targetRow['target_amount'] ?? 0);
            $achievedAmount = (float) ($metric['net_total'] ?? 0);
            $achievementPercent = $targetAmount > 0 ? (($achievedAmount / $targetAmount) * 100) : 0;
            $varianceAmount = $achievedAmount - $targetAmount;

            $rows[] = [
                'employee_name' => $targetRow['employee_name'] ?? lang('Reports.unassigned'),
                'target_month' => $targetRow['target_month'] ?? $selectedMonth,
                'target_amount' => $targetAmount,
                'achieved_amount' => $achievedAmount,
                'gross_amount' => (float) ($metric['gross_total'] ?? 0),
                'returns_amount' => (float) ($metric['returns_total'] ?? 0),
                'sales_count' => (int) ($metric['sales_count'] ?? 0),
                'achievement_percent' => round($achievementPercent, 2),
                'variance_amount' => $varianceAmount,
                'tier' => $this->resolveTier($achievementPercent),
                'status' => $achievementPercent >= 100 ? lang('EmployeeTargets.status_achieved') : lang('EmployeeTargets.status_pending'),
            ];

            $totals['target_amount'] += $targetAmount;
            $totals['achieved_amount'] += $achievedAmount;
            $totals['variance_amount'] += $varianceAmount;
            $totals['employee_count']++;
        }

        $totals['achievement_percent'] = $totals['target_amount'] > 0
            ? round(($totals['achieved_amount'] / $totals['target_amount']) * 100, 2)
            : 0;

        return view('employee_targets/achievements', [
            'title' => lang('EmployeeTargets.title_achievements'),
            'rows' => $rows,
            'totals' => $totals,
            'employees' => $this->getEmployeesForStore($storeId),
            'selectedMonth' => $selectedMonth,
            'selectedEmployeeId' => $selectedEmployeeId,
        ]);
    }

    public function achievementsPrint()
    {
        $storeId = (int) (session('store_id') ?? 0);
        $selectedMonth = trim((string) ($this->request->getGet('month') ?? date('Y-m')));
        if (! preg_match('/^\\d{4}-(0[1-9]|1[0-2])$/', $selectedMonth)) {
            $selectedMonth = date('Y-m');
        }

        $selectedEmployeeId = (int) ($this->request->getGet('employee_id') ?? 0);

        $targetsBuilder = $this->targetModel
            ->select('pos_employee_sales_targets.*, pos_employees.name AS employee_name')
            ->join('pos_employees', 'pos_employees.id = pos_employee_sales_targets.employee_id', 'left')
            ->where('pos_employee_sales_targets.store_id', $storeId)
            ->where('pos_employee_sales_targets.target_month', $selectedMonth)
            ->orderBy('pos_employees.name', 'ASC');

        if ($selectedEmployeeId > 0) {
            $targetsBuilder->where('pos_employee_sales_targets.employee_id', $selectedEmployeeId);
        }

        $targetRows = $targetsBuilder->findAll();
        $metricsByEmployee = $this->getMonthPerformanceByEmployee($storeId, $selectedMonth, $selectedEmployeeId);

        $rows = [];
        $totals = [
            'target_amount' => 0.0,
            'achieved_amount' => 0.0,
            'variance_amount' => 0.0,
            'employee_count' => 0,
        ];

        foreach ($targetRows as $targetRow) {
            $employeeId = (int) ($targetRow['employee_id'] ?? 0);
            $metric = $metricsByEmployee[$employeeId] ?? [
                'gross_total' => 0.0,
                'returns_total' => 0.0,
                'net_total' => 0.0,
                'sales_count' => 0,
            ];

            $targetAmount = (float) ($targetRow['target_amount'] ?? 0);
            $achievedAmount = (float) ($metric['net_total'] ?? 0);
            $achievementPercent = $targetAmount > 0 ? (($achievedAmount / $targetAmount) * 100) : 0;
            $varianceAmount = $achievedAmount - $targetAmount;

            $rows[] = [
                'employee_name' => $targetRow['employee_name'] ?? lang('Reports.unassigned'),
                'target_amount' => $targetAmount,
                'achieved_amount' => $achievedAmount,
                'achievement_percent' => round($achievementPercent, 2),
                'variance_amount' => $varianceAmount,
                'tier' => $this->resolveTier($achievementPercent),
                'status' => $achievementPercent >= 100 ? lang('EmployeeTargets.status_achieved') : lang('EmployeeTargets.status_pending'),
            ];

            $totals['target_amount'] += $targetAmount;
            $totals['achieved_amount'] += $achievedAmount;
            $totals['variance_amount'] += $varianceAmount;
            $totals['employee_count']++;
        }

        $totals['achievement_percent'] = $totals['target_amount'] > 0
            ? round(($totals['achieved_amount'] / $totals['target_amount']) * 100, 2)
            : 0;

        $storeName = session('store_name') ?? '';

        return view('employee_targets/achievements_print', [
            'title' => lang('EmployeeTargets.title_achievements'),
            'rows' => $rows,
            'totals' => $totals,
            'selectedMonth' => $selectedMonth,
            'storeName' => $storeName,
        ]);
    }

    protected function buildPayload($existing = null)
    {
        $storeId = (int) (session('store_id') ?? 0);
        $employeeId = (int) ($this->request->getPost('employee_id') ?? 0);
        $targetMonth = trim((string) ($this->request->getPost('target_month') ?? ''));
        $targetAmount = (float) ($this->request->getPost('target_amount') ?? 0);
        $notes = trim((string) ($this->request->getPost('notes') ?? ''));

        if ($employeeId <= 0) {
            return ['ok' => false, 'message' => lang('EmployeeTargets.error_employee_required')];
        }

        if (! preg_match('/^\\d{4}-(0[1-9]|1[0-2])$/', $targetMonth)) {
            return ['ok' => false, 'message' => lang('EmployeeTargets.error_month_invalid')];
        }

        if ($targetAmount <= 0) {
            return ['ok' => false, 'message' => lang('EmployeeTargets.error_target_amount_required')];
        }

        $employee = $this->employeeModel->forStore($storeId)->find($employeeId);
        if (! $employee) {
            return ['ok' => false, 'message' => lang('EmployeeTargets.error_employee_not_found')];
        }

        return [
            'ok' => true,
            'data' => [
                'store_id' => $storeId,
                'employee_id' => $employeeId,
                'target_month' => $targetMonth,
                'target_amount' => round($targetAmount, 2),
                'notes' => $notes !== '' ? $notes : null,
                'created_by' => (int) ($existing['created_by'] ?? session('user_id') ?? 0),
                'updated_by' => (int) (session('user_id') ?? 0),
            ],
        ];
    }

    protected function findTarget($id)
    {
        return $this->targetModel
            ->forStore()
            ->where('id', (int) $id)
            ->first();
    }

    protected function getEmployeesForStore($storeId)
    {
        return $this->employeeModel
            ->forStore($storeId)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    protected function canChangeTargetMonth(string $targetMonth): bool
    {
        if ($this->isAdminUser()) {
            return true;
        }

        if (! preg_match('/^\\d{4}-(0[1-9]|1[0-2])$/', $targetMonth)) {
            return false;
        }

        $firstDayOfMonth = $targetMonth . '-01';
        return $firstDayOfMonth > date('Y-m-d');
    }

    protected function isAdminUser(): bool
    {
        $roleId = (int) (session('role_id') ?? 0);
        if ($roleId === 1) {
            return true;
        }

        $roleName = strtolower((string) (session('role_name') ?? ''));
        return $roleName === 'admin';
    }

    protected function resolveTier($percentage): string
    {
        $percentage = (float) $percentage;
        if ($percentage >= 120) {
            return lang('EmployeeTargets.tier_gold');
        }
        if ($percentage >= 100) {
            return lang('EmployeeTargets.tier_silver');
        }
        if ($percentage >= 80) {
            return lang('EmployeeTargets.tier_bronze');
        }

        return lang('EmployeeTargets.tier_none');
    }

    protected function getMonthPerformanceByEmployee(int $storeId, string $month, int $employeeId = 0): array
    {
        $from = $month . '-01 00:00:00';
        $to = date('Y-m-t', strtotime($month . '-01')) . ' 23:59:59';
        $db = db_connect();

        $salesBuilder = $db->table('pos_sales')
            ->select('employee_id, COUNT(id) AS sales_count, SUM(total) AS gross_total')
            ->where('store_id', $storeId)
            ->where('created_at >=', $from)
            ->where('created_at <=', $to)
            ->where('employee_id >', 0)
            ->groupBy('employee_id');

        if ($employeeId > 0) {
            $salesBuilder->where('employee_id', $employeeId);
        }

        $salesRows = $salesBuilder->get()->getResultArray();

        $returnsBuilder = $db->table('pos_sales_returns')
            ->select('pos_sales.employee_id, SUM(pos_sales_returns.return_amount) AS returns_total')
            ->join('pos_sales', 'pos_sales.id = pos_sales_returns.sale_id', 'inner')
            ->where('pos_sales.store_id', $storeId)
            ->where('pos_sales.created_at >=', $from)
            ->where('pos_sales.created_at <=', $to)
            ->where('pos_sales.employee_id >', 0)
            ->groupBy('pos_sales.employee_id');

        if ($employeeId > 0) {
            $returnsBuilder->where('pos_sales.employee_id', $employeeId);
        }

        $returnRows = $returnsBuilder->get()->getResultArray();

        $result = [];
        foreach ($salesRows as $row) {
            $eid = (int) ($row['employee_id'] ?? 0);
            $result[$eid] = [
                'sales_count' => (int) ($row['sales_count'] ?? 0),
                'gross_total' => (float) ($row['gross_total'] ?? 0),
                'returns_total' => 0.0,
                'net_total' => (float) ($row['gross_total'] ?? 0),
            ];
        }

        foreach ($returnRows as $row) {
            $eid = (int) ($row['employee_id'] ?? 0);
            $returns = (float) ($row['returns_total'] ?? 0);
            if (! isset($result[$eid])) {
                $result[$eid] = [
                    'sales_count' => 0,
                    'gross_total' => 0.0,
                    'returns_total' => $returns,
                    'net_total' => 0.0 - $returns,
                ];
                continue;
            }

            $result[$eid]['returns_total'] = $returns;
            $result[$eid]['net_total'] = (float) $result[$eid]['gross_total'] - $returns;
        }

        return $result;
    }
}
