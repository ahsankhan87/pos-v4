<?php

namespace App\Controllers;

use App\Models\EmployeeSalesTargetModel;
use App\Models\EmployeeCategoryTargetModel;
use App\Models\EmployeesModel;
use App\Models\CategoriesModel;

class EmployeeTargets extends BaseController
{
    protected $targetModel;
    protected $categoryTargetModel;
    protected $employeeModel;
    protected $categoryModel;

    public function __construct()
    {
        helper(['audit', 'form', 'permission']);
        $this->targetModel = new EmployeeSalesTargetModel();
        $this->categoryTargetModel = new EmployeeCategoryTargetModel();
        $this->employeeModel = new EmployeesModel();
        $this->categoryModel = new CategoriesModel();
    }

    public function index()
    {
        $storeId = (int) (session('store_id') ?? 0);
        $month = trim((string) ($this->request->getGet('month') ?? date('Y-m')));
        $employeeId = (int) ($this->request->getGet('employee_id') ?? 0);

        $builder = $this->categoryTargetModel
            ->select('MAX(pos_employee_category_targets.id) AS id, pos_employee_category_targets.store_id, pos_employee_category_targets.employee_id, pos_employee_category_targets.target_month, SUM(pos_employee_category_targets.target_amount) AS target_amount, MAX(pos_employee_category_targets.notes) AS notes, MAX(pos_employee_category_targets.updated_at) AS updated_at, MAX(pos_employee_category_targets.created_at) AS created_at, pos_employees.name AS employee_name')
            ->join('pos_employees', 'pos_employees.id = pos_employee_category_targets.employee_id', 'left')
            ->where('pos_employee_category_targets.store_id', $storeId)
            ->groupBy('pos_employee_category_targets.employee_id, pos_employee_category_targets.target_month')
            ->orderBy('pos_employee_category_targets.target_month', 'DESC')
            ->orderBy('pos_employees.name', 'ASC');

        if ($month !== '') {
            $builder->where('pos_employee_category_targets.target_month', $month);
        }
        if ($employeeId > 0) {
            $builder->where('pos_employee_category_targets.employee_id', $employeeId);
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
            'categories' => $this->getCategoriesForStore($storeId),
            'target' => null,
        ]);
    }

    public function create()
    {
        $payload = $this->buildPayload();
        if (! $payload['ok']) {
            return redirect()->back()->withInput()->with('error', $payload['message']);
        }

        if (! $this->canChangeTargetMonth($payload['targetMonth'])) {
            return redirect()->back()->withInput()->with('error', lang('EmployeeTargets.error_locked_month'));
        }

        // Delete old category targets for this employee-month if replacing
        $this->categoryTargetModel->deleteByEmployeeMonth(
            $payload['employeeId'],
            $payload['targetMonth'],
            $payload['storeId']
        );

        // Insert category targets
        foreach ($payload['categoryTargets'] as $categoryId => $targetAmount) {
            if ((float) $targetAmount <= 0) {
                continue;
            }

            $this->categoryTargetModel->insert([
                'store_id' => $payload['storeId'],
                'employee_id' => $payload['employeeId'],
                'category_id' => $categoryId,
                'target_month' => $payload['targetMonth'],
                'target_amount' => round((float) $targetAmount, 2),
                'notes' => $payload['notes'] ?? null,
                'created_by' => (int) (session('user_id') ?? 0),
                'updated_by' => (int) (session('user_id') ?? 0),
            ]);
        }

        logAction('employee_target_created', 'Employee ID: ' . (int) $payload['employeeId'] . ', Month: ' . $payload['targetMonth'] . ', Categories: ' . count($payload['categoryTargets']));

        return redirect()->to(site_url('employee-targets'))->with('success', lang('EmployeeTargets.success_create'));
    }

    public function edit($id)
    {
        $storeId = (int) (session('store_id') ?? 0);
        // Get first category target for this employee-month to get the header info
        $categoryTargets = $this->categoryTargetModel
            ->forStore($storeId)
            ->where('id', (int) $id)
            ->findAll();

        if (empty($categoryTargets)) {
            return redirect()->to(site_url('employee-targets'))->with('error', lang('EmployeeTargets.error_not_found'));
        }

        $firstTarget = $categoryTargets[0];
        $employeeId = (int) ($firstTarget['employee_id'] ?? 0);
        $targetMonth = (string) ($firstTarget['target_month'] ?? '');

        // Get all category targets for this employee-month
        $allCategoryTargets = $this->categoryTargetModel
            ->forStore($storeId)
            ->where('employee_id', $employeeId)
            ->where('target_month', $targetMonth)
            ->findAll();

        return view('employee_targets/edit', [
            'title' => lang('EmployeeTargets.title_edit'),
            'categoryTargetId' => (int) $id,
            'categoryTargets' => $allCategoryTargets,
            'employees' => $this->getEmployeesForStore($storeId),
            'categories' => $this->getCategoriesForStore($storeId),
            'selectedEmployeeId' => $employeeId,
            'selectedMonth' => $targetMonth,
        ]);
    }

    public function update($id)
    {
        $storeId = (int) (session('store_id') ?? 0);

        // Find the target record to get employee-month info
        $existingTarget = $this->categoryTargetModel
            ->forStore($storeId)
            ->where('id', (int) $id)
            ->first();

        if (! $existingTarget) {
            return redirect()->to(site_url('employee-targets'))->with('error', lang('EmployeeTargets.error_not_found'));
        }

        $payload = $this->buildPayload($existingTarget);
        if (! $payload['ok']) {
            return redirect()->back()->withInput()->with('error', $payload['message']);
        }

        if (! $this->canChangeTargetMonth($payload['targetMonth'])) {
            return redirect()->back()->withInput()->with('error', lang('EmployeeTargets.error_locked_month'));
        }

        // Delete old category targets for this employee-month
        $this->categoryTargetModel->deleteByEmployeeMonth(
            $payload['employeeId'],
            $payload['targetMonth'],
            $payload['storeId']
        );

        // Insert updated category targets
        foreach ($payload['categoryTargets'] as $categoryId => $targetAmount) {
            if ((float) $targetAmount <= 0) {
                continue;
            }

            $this->categoryTargetModel->insert([
                'store_id' => $payload['storeId'],
                'employee_id' => $payload['employeeId'],
                'category_id' => $categoryId,
                'target_month' => $payload['targetMonth'],
                'target_amount' => round((float) $targetAmount, 2),
                'notes' => $payload['notes'] ?? null,
                'created_by' => (int) ($existingTarget['created_by'] ?? session('user_id') ?? 0),
                'updated_by' => (int) (session('user_id') ?? 0),
            ]);
        }

        logAction('employee_target_updated', 'Employee ID: ' . (int) $payload['employeeId'] . ', Month: ' . $payload['targetMonth'] . ', Categories: ' . count($payload['categoryTargets']));

        return redirect()->to(site_url('employee-targets'))->with('success', lang('EmployeeTargets.success_update'));
    }

    public function delete($id)
    {
        $storeId = (int) (session('store_id') ?? 0);
        $target = $this->categoryTargetModel
            ->forStore($storeId)
            ->where('id', (int) $id)
            ->first();

        if (! $target) {
            return redirect()->to(site_url('employee-targets'))->with('error', lang('EmployeeTargets.error_not_found'));
        }

        if (! $this->canChangeTargetMonth((string) ($target['target_month'] ?? ''))) {
            return redirect()->to(site_url('employee-targets'))->with('error', lang('EmployeeTargets.error_locked_month'));
        }

        $employeeId = (int) ($target['employee_id'] ?? 0);
        $targetMonth = (string) ($target['target_month'] ?? '');

        // Delete all category targets for this employee-month
        $this->categoryTargetModel->deleteByEmployeeMonth($employeeId, $targetMonth, $storeId);
        logAction('employee_target_deleted', 'Employee ID: ' . $employeeId . ', Month: ' . $targetMonth);

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

        $report = $this->buildAchievementReport($storeId, $selectedMonth, $selectedEmployeeId);

        return view('employee_targets/achievements', [
            'title' => lang('EmployeeTargets.title_achievements'),
            'rows' => $report['rows'],
            'totals' => $report['totals'],
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
        $report = $this->buildAchievementReport($storeId, $selectedMonth, $selectedEmployeeId);
        $storeName = session('store_name') ?? '';

        return view('employee_targets/achievements_print', [
            'title' => lang('EmployeeTargets.title_achievements'),
            'rows' => $report['rows'],
            'totals' => $report['totals'],
            'selectedMonth' => $selectedMonth,
            'storeName' => $storeName,
        ]);
    }

    public function achievementsCategories()
    {
        $storeId = (int) (session('store_id') ?? 0);
        $selectedMonth = trim((string) ($this->request->getGet('month') ?? date('Y-m')));
        if (! preg_match('/^\\d{4}-(0[1-9]|1[0-2])$/', $selectedMonth)) {
            $selectedMonth = date('Y-m');
        }

        $selectedEmployeeId = (int) ($this->request->getGet('employee_id') ?? 0);
        $report = $this->buildCategoryAchievementReport($storeId, $selectedMonth, $selectedEmployeeId);

        return view('employee_targets/achievements_categories', [
            'title' => lang('EmployeeTargets.title_achievements_categories'),
            'rows' => $report['rows'],
            'categories' => $report['categories'],
            'totals' => $report['totals'],
            'categoryTotalsPercent' => $report['categoryTotalsPercent'],
            'employees' => $this->getEmployeesForStore($storeId),
            'selectedMonth' => $selectedMonth,
            'selectedEmployeeId' => $selectedEmployeeId,
        ]);
    }

    public function achievementsCategoriesPrint()
    {
        $storeId = (int) (session('store_id') ?? 0);
        $selectedMonth = trim((string) ($this->request->getGet('month') ?? date('Y-m')));
        if (! preg_match('/^\\d{4}-(0[1-9]|1[0-2])$/', $selectedMonth)) {
            $selectedMonth = date('Y-m');
        }

        $selectedEmployeeId = (int) ($this->request->getGet('employee_id') ?? 0);
        $report = $this->buildCategoryAchievementReport($storeId, $selectedMonth, $selectedEmployeeId);
        $storeName = session('store_name') ?? '';

        return view('employee_targets/achievements_categories_print', [
            'title' => lang('EmployeeTargets.title_achievements_categories'),
            'rows' => $report['rows'],
            'categories' => $report['categories'],
            'totals' => $report['totals'],
            'categoryTotalsPercent' => $report['categoryTotalsPercent'],
            'selectedMonth' => $selectedMonth,
            'selectedEmployeeId' => $selectedEmployeeId,
            'storeName' => $storeName,
        ]);
    }

    protected function buildPayload($existing = null)
    {
        $storeId = (int) (session('store_id') ?? 0);
        $employeeId = (int) ($this->request->getPost('employee_id') ?? 0);
        $targetMonth = trim((string) ($this->request->getPost('target_month') ?? ''));
        $notes = trim((string) ($this->request->getPost('notes') ?? ''));
        $categoryTargets = $this->request->getPost('category_targets') ?? [];
        $totalTargetAmount = (float) ($this->request->getPost('total_target_amount') ?? 0);

        if ($employeeId <= 0) {
            return ['ok' => false, 'message' => lang('EmployeeTargets.error_employee_required')];
        }

        if (! preg_match('/^\\d{4}-(0[1-9]|1[0-2])$/', $targetMonth)) {
            return ['ok' => false, 'message' => lang('EmployeeTargets.error_month_invalid')];
        }

        $employee = $this->employeeModel->forStore($storeId)->find($employeeId);
        if (! $employee) {
            return ['ok' => false, 'message' => lang('EmployeeTargets.error_employee_not_found')];
        }

        // If total target is provided, spread evenly across all categories
        if ($totalTargetAmount > 0) {
            $categories = $this->categoryModel->forStore($storeId)->findAll();
            if (empty($categories)) {
                return ['ok' => false, 'message' => lang('EmployeeTargets.error_no_categories')];
            }

            $amountPerCategory = $totalTargetAmount / count($categories);
            $categoryTargets = [];
            foreach ($categories as $category) {
                $categoryId = (int) ($category['id'] ?? 0);
                $categoryTargets[$categoryId] = $amountPerCategory;
            }
        }

        // Validate at least one category has a target
        $hasValidTarget = false;
        $processedTargets = [];
        foreach ($categoryTargets as $categoryId => $targetAmount) {
            $categoryId = (int) $categoryId;
            $targetAmount = (float) ($targetAmount ?? 0);
            if ($targetAmount > 0) {
                $hasValidTarget = true;
                $processedTargets[$categoryId] = $targetAmount;
            }
        }

        if (! $hasValidTarget) {
            return ['ok' => false, 'message' => lang('EmployeeTargets.error_target_amount_required')];
        }

        return [
            'ok' => true,
            'storeId' => $storeId,
            'employeeId' => $employeeId,
            'targetMonth' => $targetMonth,
            'categoryTargets' => $processedTargets,
            'notes' => $notes !== '' ? $notes : null,
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

    protected function getCategoriesForStore($storeId)
    {
        return $this->categoryModel
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

    protected function buildAchievementReport(int $storeId, string $selectedMonth, int $selectedEmployeeId = 0): array
    {
        // Get all employees with category targets for this month
        $targetBuilder = $this->categoryTargetModel
            ->select('pos_employees.id AS employee_id, pos_employees.name AS employee_name')
            ->join('pos_employees', 'pos_employees.id = pos_employee_category_targets.employee_id', 'inner')
            ->where('pos_employee_category_targets.store_id', $storeId)
            ->where('pos_employee_category_targets.target_month', $selectedMonth)
            ->groupBy('pos_employee_category_targets.employee_id, pos_employees.id, pos_employees.name')
            ->orderBy('pos_employees.name', 'ASC');

        if ($selectedEmployeeId > 0) {
            $targetBuilder->where('pos_employee_category_targets.employee_id', $selectedEmployeeId);
        }

        $employees = $targetBuilder->findAll();
        $metricsByEmployee = $this->getMonthPerformanceByEmployee($storeId, $selectedMonth, $selectedEmployeeId);
        $categoryTargetsByEmployee = $this->getEmployeeCategoryTargets($storeId, $selectedMonth, $selectedEmployeeId);

        $rows = [];
        $totals = [
            'target_amount' => 0.0,
            'achieved_amount' => 0.0,
            'variance_amount' => 0.0,
            'employee_count' => 0,
        ];

        foreach ($employees as $employee) {
            $employeeId = (int) ($employee['employee_id'] ?? 0);
            $employeeName = (string) ($employee['employee_name'] ?? lang('Reports.unassigned'));

            $categoryTargets = $categoryTargetsByEmployee[$employeeId] ?? [];
            $totalTarget = array_sum($categoryTargets);

            $metric = $metricsByEmployee[$employeeId] ?? [
                'gross_total' => 0.0,
                'returns_total' => 0.0,
                'net_total' => 0.0,
                'sales_count' => 0,
            ];

            $achievedAmount = (float) ($metric['net_total'] ?? 0);
            $achievementPercent = $totalTarget > 0 ? (($achievedAmount / $totalTarget) * 100) : 0;
            $varianceAmount = $achievedAmount - $totalTarget;

            $rows[] = [
                'employee_name' => $employeeName,
                'target_amount' => $totalTarget,
                'achieved_amount' => $achievedAmount,
                'gross_amount' => (float) ($metric['gross_total'] ?? 0),
                'returns_amount' => (float) ($metric['returns_total'] ?? 0),
                'sales_count' => (int) ($metric['sales_count'] ?? 0),
                'achievement_percent' => round($achievementPercent, 2),
                'variance_amount' => $varianceAmount,
                'tier' => $this->resolveTier($achievementPercent),
                'status' => $achievementPercent >= 100 ? lang('EmployeeTargets.status_achieved') : lang('EmployeeTargets.status_pending'),
                'category_breakdown' => $categoryTargets,
            ];

            $totals['target_amount'] += $totalTarget;
            $totals['achieved_amount'] += $achievedAmount;
            $totals['variance_amount'] += $varianceAmount;
            $totals['employee_count']++;
        }

        $totals['achievement_percent'] = $totals['target_amount'] > 0
            ? round(($totals['achieved_amount'] / $totals['target_amount']) * 100, 2)
            : 0;

        return [
            'rows' => $rows,
            'totals' => $totals,
        ];
    }

    protected function getEmployeeCategoryTargets(int $storeId, string $selectedMonth, int $selectedEmployeeId = 0): array
    {
        $builder = $this->categoryTargetModel
            ->select('employee_id, category_id, target_amount')
            ->where('store_id', $storeId)
            ->where('target_month', $selectedMonth);

        if ($selectedEmployeeId > 0) {
            $builder->where('employee_id', $selectedEmployeeId);
        }

        $targets = $builder->findAll();
        $result = [];

        foreach ($targets as $target) {
            $empId = (int) ($target['employee_id'] ?? 0);
            $catId = (int) ($target['category_id'] ?? 0);
            $amount = (float) ($target['target_amount'] ?? 0);

            if (! isset($result[$empId])) {
                $result[$empId] = [];
            }

            $result[$empId][$catId] = $amount;
        }

        return $result;
    }

    protected function buildCategoryAchievementReport(int $storeId, string $selectedMonth, int $selectedEmployeeId = 0): array
    {
        $targetsBuilder = $this->categoryTargetModel
            ->select('pos_employees.id AS employee_id, pos_employees.name AS employee_name')
            ->join('pos_employees', 'pos_employees.id = pos_employee_category_targets.employee_id', 'inner')
            ->where('pos_employee_category_targets.store_id', $storeId)
            ->where('pos_employee_category_targets.target_month', $selectedMonth)
            ->groupBy('pos_employee_category_targets.employee_id, pos_employees.id, pos_employees.name')
            ->orderBy('pos_employees.name', 'ASC');

        if ($selectedEmployeeId > 0) {
            $targetsBuilder->where('pos_employee_category_targets.employee_id', $selectedEmployeeId);
        }

        $targetRows = $targetsBuilder->findAll();
        $categoryMetrics = $this->getMonthPerformanceByEmployeeCategory($storeId, $selectedMonth, $selectedEmployeeId);
        $categoryTargetsByEmployee = $this->getEmployeeCategoryTargets($storeId, $selectedMonth, $selectedEmployeeId);

        $categoryMeta = [];
        $categoryRows = $this->categoryModel->forStore($storeId)->orderBy('name', 'ASC')->findAll();
        foreach ($categoryRows as $categoryRow) {
            $categoryId = (int) ($categoryRow['id'] ?? 0);
            if ($categoryId <= 0) {
                continue;
            }
            $categoryMeta[$categoryId] = [
                'id' => $categoryId,
                'name' => (string) ($categoryRow['name'] ?? lang('Reports.uncategorized')),
            ];
        }

        foreach (($categoryMetrics['categoryNames'] ?? []) as $categoryId => $categoryName) {
            $cid = (int) $categoryId;
            if (isset($categoryMeta[$cid])) {
                continue;
            }
            $categoryMeta[$cid] = [
                'id' => $cid,
                'name' => (string) $categoryName,
            ];
        }

        $categories = array_values($categoryMeta);
        usort($categories, static function ($a, $b) use ($categoryMetrics) {
            $ac = (int) ($a['id'] ?? 0);
            $bc = (int) ($b['id'] ?? 0);
            $at = (float) ($categoryMetrics['categoryTotals'][$ac] ?? 0);
            $bt = (float) ($categoryMetrics['categoryTotals'][$bc] ?? 0);
            if ($at === $bt) {
                return strcmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''));
            }
            return ($at < $bt) ? 1 : -1;
        });

        $rows = [];
        $totals = [
            'target_amount' => 0.0,
            'achieved_amount' => 0.0,
            'variance_amount' => 0.0,
            'employee_count' => 0,
        ];
        $categoryTotalsAchieved = [];

        foreach ($targetRows as $targetRow) {
            $employeeId = (int) ($targetRow['employee_id'] ?? 0);
            $byCategory = $categoryMetrics['byEmployee'][$employeeId] ?? [];
            $categoryTargets = $categoryTargetsByEmployee[$employeeId] ?? [];
            $targetAmount = array_sum($categoryTargets);
            $achievedAmount = (float) array_sum($byCategory);
            $achievementPercent = $targetAmount > 0 ? (($achievedAmount / $targetAmount) * 100) : 0;
            $varianceAmount = $achievedAmount - $targetAmount;

            $categoryPercents = [];
            $categoryBreakdown = [];
            foreach ($categories as $category) {
                $categoryId = (int) ($category['id'] ?? 0);
                $categoryTarget = (float) ($categoryTargets[$categoryId] ?? 0);
                $categoryAchieved = (float) ($byCategory[$categoryId] ?? 0);
                $categoryPercents[$categoryId] = $categoryTarget > 0 ? (($categoryAchieved / $categoryTarget) * 100) : 0;
                $categoryBreakdown[$categoryId] = [
                    'target' => $categoryTarget,
                    'achieved' => $categoryAchieved,
                    'percent' => round($categoryPercents[$categoryId], 2),
                ];
                $categoryTotalsAchieved[$categoryId] = (float) ($categoryTotalsAchieved[$categoryId] ?? 0) + $categoryAchieved;
            }

            $rows[] = [
                'employee_name' => $targetRow['employee_name'] ?? lang('Reports.unassigned'),
                'target_amount' => $targetAmount,
                'achieved_amount' => $achievedAmount,
                'achievement_percent' => round($achievementPercent, 2),
                'category_breakdown' => $categoryBreakdown,
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

        $categoryTotalsTarget = [];
        $categoryTotalsPercent = [];
        foreach ($categories as $category) {
            $categoryId = (int) ($category['id'] ?? 0);
            $categoryTotalsTarget[$categoryId] = 0.0;

            // Calculate total target for this category across all employees
            foreach ($categoryTargetsByEmployee as $empTargets) {
                $categoryTotalsTarget[$categoryId] += (float) ($empTargets[$categoryId] ?? 0);
            }

            $categoryTotalsPercent[$categoryId] = $totals['target_amount'] > 0
                ? round((((float) ($categoryTotalsAchieved[$categoryId] ?? 0)) / $totals['target_amount']) * 100, 2)
                : 0.0;
        }

        return [
            'rows' => $rows,
            'categories' => $categories,
            'totals' => $totals,
            'categoryTotalsTarget' => $categoryTotalsTarget,
            'categoryTotalsPercent' => $categoryTotalsPercent,
        ];
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

    protected function getMonthPerformanceByEmployeeCategory(int $storeId, string $month, int $employeeId = 0): array
    {
        $from = $month . '-01 00:00:00';
        $to = date('Y-m-t', strtotime($month . '-01')) . ' 23:59:59';
        $db = db_connect();

        $salesBuilder = $db->table('pos_sale_items')
            ->select('pos_sales.employee_id, COALESCE(pos_products.category_id, 0) AS category_id, pos_categories.name AS category_name, SUM(pos_sale_items.subtotal) AS gross_total')
            ->join('pos_sales', 'pos_sales.id = pos_sale_items.sale_id', 'inner')
            ->join('pos_products', 'pos_products.id = pos_sale_items.product_id', 'left')
            ->join('pos_categories', 'pos_categories.id = pos_products.category_id', 'left')
            ->where('pos_sales.store_id', $storeId)
            ->where('pos_sales.created_at >=', $from)
            ->where('pos_sales.created_at <=', $to)
            ->where('pos_sales.employee_id >', 0)
            ->groupBy('pos_sales.employee_id, COALESCE(pos_products.category_id, 0), pos_categories.name');

        if ($employeeId > 0) {
            $salesBuilder->where('pos_sales.employee_id', $employeeId);
        }

        $salesRows = $salesBuilder->get()->getResultArray();

        $returnsBuilder = $db->table('pos_sales_returns')
            ->select('pos_sales.employee_id, COALESCE(pos_products.category_id, 0) AS category_id, pos_categories.name AS category_name, SUM(pos_sales_returns.return_amount) AS returns_total')
            ->join('pos_sales', 'pos_sales.id = pos_sales_returns.sale_id', 'inner')
            ->join('pos_products', 'pos_products.id = pos_sales_returns.product_id', 'left')
            ->join('pos_categories', 'pos_categories.id = pos_products.category_id', 'left')
            ->where('pos_sales.store_id', $storeId)
            ->where('pos_sales.created_at >=', $from)
            ->where('pos_sales.created_at <=', $to)
            ->where('pos_sales.employee_id >', 0)
            ->groupBy('pos_sales.employee_id, COALESCE(pos_products.category_id, 0), pos_categories.name');

        if ($employeeId > 0) {
            $returnsBuilder->where('pos_sales.employee_id', $employeeId);
        }

        $returnRows = $returnsBuilder->get()->getResultArray();

        $result = [];
        $categoryTotals = [];
        $categoryNames = [];

        foreach ($salesRows as $row) {
            $eid = (int) ($row['employee_id'] ?? 0);
            $categoryId = (int) ($row['category_id'] ?? 0);
            $amount = (float) ($row['gross_total'] ?? 0);

            $result[$eid][$categoryId] = (float) ($result[$eid][$categoryId] ?? 0) + $amount;
            $categoryTotals[$categoryId] = (float) ($categoryTotals[$categoryId] ?? 0) + $amount;
            if (! isset($categoryNames[$categoryId])) {
                $name = trim((string) ($row['category_name'] ?? ''));
                $categoryNames[$categoryId] = $name !== '' ? $name : lang('Reports.uncategorized');
            }
        }

        foreach ($returnRows as $row) {
            $eid = (int) ($row['employee_id'] ?? 0);
            $categoryId = (int) ($row['category_id'] ?? 0);
            $returns = (float) ($row['returns_total'] ?? 0);

            $result[$eid][$categoryId] = (float) ($result[$eid][$categoryId] ?? 0) - $returns;
            $categoryTotals[$categoryId] = (float) ($categoryTotals[$categoryId] ?? 0) - $returns;
            if (! isset($categoryNames[$categoryId])) {
                $name = trim((string) ($row['category_name'] ?? ''));
                $categoryNames[$categoryId] = $name !== '' ? $name : lang('Reports.uncategorized');
            }
        }

        $knownCategoryRows = $this->categoryModel->forStore($storeId)->select('id, name')->findAll();
        foreach ($knownCategoryRows as $categoryRow) {
            $cid = (int) ($categoryRow['id'] ?? 0);
            if ($cid <= 0) {
                continue;
            }
            $name = trim((string) ($categoryRow['name'] ?? ''));
            if ($name !== '') {
                $categoryNames[$cid] = $name;
            }
        }

        return [
            'byEmployee' => $result,
            'categoryTotals' => $categoryTotals,
            'categoryNames' => $categoryNames,
        ];
    }
}
