<?php

namespace App\Controllers;

use App\Models\M_audit_logs;

class AuditLogs extends BaseController
{

    public function index()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('audit_logs');
        $storeId = session('store_id');

        if ($storeId !== null) {
            $builder->where('store_id', $storeId);
        }

        $totalLogs = $builder->countAllResults();

        return view('audit_logs/index', [
            'title' => 'Audit Logs',
            'totalLogs' => $totalLogs,
        ]);
    }

    public function datatable()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request.']);
        }

        $draw = (int) ($this->request->getVar('draw') ?? 0);
        $start = max(0, (int) ($this->request->getVar('start') ?? 0));
        $length = (int) ($this->request->getVar('length') ?? 25);
        $length = $length > 0 ? min($length, 200) : 25;

        $search = $this->request->getVar('search')['value'] ?? '';
        $orderRequest = $this->request->getVar('order')[0] ?? null;

        $columns = [
            'a.user_id',
            'a.action',
            'a.details',
            'a.created_at',
        ];

        $db = \Config\Database::connect();
        $storeId = session('store_id');

        $baseBuilder = $db->table('audit_logs a');
        if ($storeId !== null) {
            $baseBuilder->where('a.store_id', $storeId);
        }
        $totalRecords = (clone $baseBuilder)->countAllResults();

        $filteredBuilder = $db->table('audit_logs a')
            ->join('pos_users u', 'u.id = a.user_id', 'left');

        if ($storeId !== null) {
            $filteredBuilder->where('a.store_id', $storeId);
        }

        if ($search !== '') {
            $filteredBuilder->groupStart()
                ->like('u.name', $search)
                ->orLike('a.action', $search)
                ->orLike('a.details', $search)
                ->orLike('a.ip_address', $search)
                ->orLike('a.user_id', $search)
                ->groupEnd();
        }

        $totalFiltered = (clone $filteredBuilder)->countAllResults();

        $filteredBuilder->select(
            'a.id, a.user_id, a.action, a.details, a.created_at, a.ip_address, a.user_agent,' .
                " COALESCE(NULLIF(u.name, ''), CONCAT('User #', a.user_id), 'System') AS user_name"
        );

        if ($orderRequest) {
            $orderColumnIndex = (int) ($orderRequest['column'] ?? 3);
            $orderColumn = $columns[$orderColumnIndex] ?? 'a.created_at';
            $orderDir = strtolower($orderRequest['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
            $filteredBuilder->orderBy($orderColumn, $orderDir);
        } else {
            $filteredBuilder->orderBy('a.created_at', 'DESC');
        }

        $filteredBuilder->limit($length, $start);

        $logs = $filteredBuilder->get()->getResultArray();

        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data' => $logs,
        ]);
    }
    /**
     * Log an action in the audit logs.
     *
     * @param string $action The action performed.
     * @param string $details Additional details about the action.
     */
    public function logAction($action, $details = '')
    {
        $model = new \App\Models\M_audit_logs();
        $model->insert([
            'user_id' => session('user_id'),
            'action' => $action,
            'details' => $details,
            'ip_address' => $this->request->getIPAddress(),
            'created_at' => date('Y-m-d H:i:s'),
            'store_id' => session('store_id'), // Assuming you have store_id in session
            'user_agent' => $this->request->getUserAgent()->getAgentString()
        ]);
    }
}
