<?php

namespace App\Commands;

use App\Models\RecurringInvoiceModel;
use App\Services\RecurringInvoiceService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class GenerateRecurringInvoices extends BaseCommand
{
    protected $group       = 'Sales';
    protected $name        = 'sales:generate-recurring-invoices';
    protected $description = 'Generate due recurring invoices for the current store context.';

    public function run(array $params)
    {
        $limit = (int) (CLI::getOption('limit') ?? 100);
        if ($limit < 1) {
            $limit = 100;
        }

        $storeId = (int) (CLI::getOption('store') ?? (session('store_id') ?? 1));

        $model = new RecurringInvoiceModel();
        $service = new RecurringInvoiceService();

        $rows = $model->findDueForGeneration($storeId, $limit);
        if ($rows === []) {
            CLI::write('No due recurring templates found.', 'yellow');
            return;
        }

        $ok = 0;
        $failed = 0;

        foreach ($rows as $row) {
            $result = $service->generateNow((int) $row['id'], false);
            if ($result['ok']) {
                $ok++;
                CLI::write('Generated: template #' . $row['id'] . ' -> invoice ' . ($result['invoice_no'] ?? ''), 'green');
            } else {
                $failed++;
                CLI::write('Failed: template #' . $row['id'] . ' -> ' . ($result['message'] ?? 'Unknown error'), 'red');
            }
        }

        CLI::write('Summary: generated=' . $ok . ', failed=' . $failed . ', total=' . count($rows), 'yellow');
    }
}
