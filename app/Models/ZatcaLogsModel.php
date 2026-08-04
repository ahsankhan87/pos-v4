<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * ZATCA Logs Model
 * 
 * Audit trail for all ZATCA operations (API calls, invoice generation, errors)
 */
class ZatcaLogsModel extends Model
{
    protected $table = 'pos_zatca_logs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'invoice_id',
        'action',
        'level',
        'message',
        'context',
        'created_at',
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';

    // Validation
    protected $validationRules = [
        'action' => 'required|string|max_length[50]',
        'level' => 'required|in_list[info,warning,error]',
        'message' => 'required|string',
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;

    /**
     * Log a ZATCA action
     * 
     * @param string $action Action name (generate_xml, sign, submit_report, etc.)
     * @param string $message Log message
     * @param string $level Log level (info, warning, error)
     * @param int|null $invoiceId Related invoice ID
     * @param array $context Additional context data
     * @return int|bool Insert ID or false
     */
    public function logAction(
        string $action,
        string $message,
        string $level = 'info',
        $invoiceId = null,
        array $context = []
    ) {
        return $this->insert([
            'invoice_id' => $invoiceId,
            'action' => $action,
            'level' => $level,
            'message' => $message,
            'context' => !empty($context) ? json_encode($context) : null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Get logs for specific invoice
     * 
     * @param int $invoiceId
     * @return array
     */
    public function getInvoiceLogs(int $invoiceId): array
    {
        return $this->where('invoice_id', $invoiceId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get recent error logs
     * 
     * @param int $limit
     * @return array
     */
    public function getRecentErrors(int $limit = 50): array
    {
        return $this->where('level', 'error')
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get logs by action type
     * 
     * @param string $action
     * @param int $limit
     * @return array
     */
    public function getLogsByAction(string $action, int $limit = 100): array
    {
        return $this->where('action', $action)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Clean up old logs (for maintenance)
     * 
     * @param int $daysOld Delete logs older than X days
     * @return int Number of deleted rows
     */
    public function cleanOldLogs(int $daysOld = 90): int
    {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$daysOld} days"));
        return $this->where('created_at <', $cutoffDate)->delete();
    }
}
