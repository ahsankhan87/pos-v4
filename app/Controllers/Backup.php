<?php

namespace App\Controllers;

class Backup extends BaseController
{
    public function index()
    {
        $data['title'] = 'Backup';
        return view('backup/download', $data);
    }

    public function download()
    {
        $method = $this->request->getGet('method') ?? 'php';

        switch ($method) {
            case 'mysqldump':
                return $this->downloadWithMysqldump();
            case 'php':
            default:
                return $this->downloadWithPhp();
        }
    }

    private function downloadWithMysqldump()
    {
        $defaultGroup = config('Database')->defaultGroup;
        $config = config('Database')->$defaultGroup;

        $filename = 'backup_mysqldump_' . date('Y-m-d_H-i-s') . '.sql';

        // Build command
        $command = "mysqldump --host={$config['hostname']} " .
            "--user={$config['username']} " .
            "--password={$config['password']} " .
            "{$config['database']} 2>&1";

        // Execute command
        $output = shell_exec($command);

        if (empty($output)) {
            return redirect()->back()->with('error', 'mysqldump failed or returned empty output');
        }

        return $this->response->download($filename, $output);
    }

    private function downloadWithPhp()
    {
        $defaultGroup = config('Database')->defaultGroup;
        $config = config('Database')->$defaultGroup;

        $filename = 'backup_php_' . date('Y-m-d_H-i-s') . '.sql';
        $content = $this->generatePhpBackup();

        return $this->response->download($filename, $content);
    }

    private function generatePhpBackup()
    {
        $db = \Config\Database::connect();
        $tables = $db->listTables();

        $output = "-- PHP Generated Database Backup\n";
        $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";

        foreach ($tables as $table) {
            $output .= $this->backupTable($db, $table);
        }

        return $output;
    }

    private function backupTable($db, $table)
    {
        $output = "--\n-- Table: {$table}\n--\n\n";

        // Get create table syntax
        $query = $db->query("SHOW CREATE TABLE `{$table}`");
        if ($query) {
            $row = $query->getRowArray();
            $output .= $row['Create Table'] . ";\n\n";
        }

        // Get table data
        $data = $db->table($table)->get();
        if ($data) {
            $output .= "--\n-- Data for table: {$table}\n--\n\n";

            foreach ($data->getResultArray() as $row) {
                $values = [];
                foreach ($row as $value) {
                    $values[] = $value === null ? 'NULL' : "'" . addslashes($value) . "'";
                }
                $output .= "INSERT INTO `{$table}` VALUES (" . implode(', ', $values) . ");\n";
            }
            $output .= "\n";
        }

        return $output;
    }
}
