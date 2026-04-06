<?php

namespace App\Services\Tenancy;

use App\Models\CompanyTenantModel;

class TenantProvisioningService
{
    protected $tenantModel;
    protected $db;

    public function __construct()
    {
        $this->tenantModel = new CompanyTenantModel();
        $this->db = \Config\Database::connect();
    }

    public function normalizeSlug($slug)
    {
        $slug = strtolower(trim((string) $slug));
        $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim((string) $slug, '-');
    }

    public function validateSlug($slug)
    {
        $slug = $this->normalizeSlug($slug);
        if ($slug === '' || strlen($slug) < 3 || strlen($slug) > 50) {
            return [false, 'Company slug must be between 3 and 50 characters'];
        }

        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            return [false, 'Company slug format is invalid'];
        }

        $reserved = ['www', 'admin', 'api', 'app', 'login', 'register', 'billing', 'dashboard', 'help', 'support'];
        if (in_array($slug, $reserved, true)) {
            return [false, 'Company slug is reserved'];
        }

        if ($this->tenantModel->findBySlug($slug)) {
            return [false, 'Company slug is already in use'];
        }

        return [true, $slug];
    }

    public function buildDatabaseName($slug)
    {
        $prefix = getenv('TENANT_DB_PREFIX') ?: 'pos_tenant_';
        return $prefix . str_replace('-', '_', $slug);
    }

    public function provision($storeId, $companyName, $slug, $createdBy = null, $tenantBaseUrl = null)
    {
        $timeoutSeconds = $this->getProvisioningTimeoutSeconds();
        $this->extendExecutionTimeout($timeoutSeconds);
        $this->configureConnectionTimeouts($this->db, $timeoutSeconds);

        $check = $this->validateSlug($slug);
        if (!$check[0]) {
            return [false, $check[1]];
        }

        $slug = $check[1];

        if ($this->tenantModel->findByStore($storeId)) {
            return [false, 'Tenant configuration already exists for this company'];
        }

        $dbHost = getenv('TENANT_DB_HOST') ?: (getenv('database.default.hostname') ?: 'localhost');
        $dbPort = (int) (getenv('TENANT_DB_PORT') ?: (getenv('database.default.port') ?: 3306));
        $dbUser = getenv('TENANT_DB_USER') ?: (getenv('database.default.username') ?: '');
        $dbPass = getenv('TENANT_DB_PASS');
        if ($dbPass === false || $dbPass === null || $dbPass === '') {
            $dbPass = getenv('database.default.password') ?: '';
        }

        $dbName = $this->buildDatabaseName($slug);
        $appPath = $this->buildAppPath($slug);
        $appUrl = $this->buildAppUrl($slug, $tenantBaseUrl);

        if ($this->databaseExists($dbName)) {
            return [false, 'Tenant database already exists'];
        }

        if ($appPath !== '' && is_dir($appPath)) {
            return [false, 'Tenant app path already exists: ' . $appPath];
        }

        $this->db->query('CREATE DATABASE `' . str_replace('`', '``', $dbName) . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');

        $templateDb = getenv('TENANT_DB_TEMPLATE') ?: '';
        if ($templateDb !== '') {
            $this->cloneDatabaseFromTemplate($templateDb, $dbName);
        } else {
            $sourceDb = (string) $this->db->getDatabase();
            if ($sourceDb === '') {
                throw new \RuntimeException('Unable to determine source database for tenant schema initialization');
            }
            $this->cloneDatabaseStructureFromSource($sourceDb, $dbName);
        }

        $this->syncBootstrapDataToTenantDatabase((int) $storeId, $createdBy !== null ? (int) $createdBy : null, [
            'hostname' => $dbHost,
            'port' => $dbPort,
            'database' => $dbName,
            'username' => $dbUser,
            'password' => $dbPass,
        ]);

        if ($appPath !== '') {
            $this->provisionAppCopy($appPath);
            $this->configureTenantAppEnvironment($appPath, [
                'hostname' => $dbHost,
                'port' => $dbPort,
                'database' => $dbName,
                'username' => $dbUser,
                'password' => $dbPass,
            ], $appUrl);
        }

        $tenantData = [
            'store_id' => (int) $storeId,
            'company_name' => (string) $companyName,
            'slug' => $slug,
            'app_path' => $appPath !== '' ? $appPath : null,
            'app_url' => $appUrl !== '' ? $appUrl : null,
            'db_host' => $dbHost,
            'db_port' => $dbPort,
            'db_name' => $dbName,
            'db_user' => $dbUser,
            'db_pass' => $dbPass,
            'status' => 'active',
            'created_by' => $createdBy !== null ? (int) $createdBy : null,
        ];

        $this->tenantModel->insert($tenantData);

        return [true, $tenantData];
    }

    protected function databaseExists($dbName)
    {
        $sql = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?";
        $row = $this->db->query($sql, [$dbName])->getRowArray();
        return !empty($row);
    }

    protected function cloneDatabaseFromTemplate($templateDb, $targetDb)
    {
        if (!$this->databaseExists($templateDb)) {
            throw new \RuntimeException('Template database does not exist: ' . $templateDb);
        }

        $tablesResult = $this->db->query('SHOW TABLES FROM `' . str_replace('`', '``', $templateDb) . '`')->getResultArray();
        foreach ($tablesResult as $row) {
            $tableName = array_values($row)[0] ?? null;
            if (!$tableName) {
                continue;
            }

            $safeTable = str_replace('`', '``', $tableName);
            $this->db->query('CREATE TABLE `' . str_replace('`', '``', $targetDb) . '`.`' . $safeTable . '` LIKE `' . str_replace('`', '``', $templateDb) . '`.`' . $safeTable . '`');
            $this->db->query('INSERT INTO `' . str_replace('`', '``', $targetDb) . '`.`' . $safeTable . '` SELECT * FROM `' . str_replace('`', '``', $templateDb) . '`.`' . $safeTable . '`');
        }
    }

    protected function cloneDatabaseStructureFromSource($sourceDb, $targetDb)
    {
        if (!$this->databaseExists($sourceDb)) {
            throw new \RuntimeException('Source database does not exist: ' . $sourceDb);
        }

        $tablesResult = $this->db->query('SHOW TABLES FROM `' . str_replace('`', '``', $sourceDb) . '`')->getResultArray();
        foreach ($tablesResult as $row) {
            $tableName = array_values($row)[0] ?? null;
            if (!$tableName) {
                continue;
            }

            $safeTable = str_replace('`', '``', $tableName);
            $this->db->query('CREATE TABLE `' . str_replace('`', '``', $targetDb) . '`.`' . $safeTable . '` LIKE `' . str_replace('`', '``', $sourceDb) . '`.`' . $safeTable . '`');
        }
    }

    protected function buildAppPath($slug)
    {
        $baseDir = trim((string) (getenv('TENANT_APP_BASE_DIR') ?: ''));
        if ($baseDir === '') {
            $baseDir = dirname(dirname(rtrim(ROOTPATH, "\\/")));
        }

        return rtrim($baseDir, "\\/") . DIRECTORY_SEPARATOR . $slug;
    }

    protected function buildAppUrl($slug, $tenantBaseUrl = null)
    {
        $tenantBaseUrl = trim((string) $tenantBaseUrl);
        if ($tenantBaseUrl !== '') {
            return rtrim($tenantBaseUrl, '/') . '/' . $slug;
        }

        $template = trim((string) (getenv('TENANT_APP_URL_TEMPLATE') ?: ''));
        if ($template !== '') {
            return str_replace('{slug}', $slug, $template);
        }

        $base = trim((string) (getenv('app.baseURL') ?: 'http://localhost/'));
        if ($base === '') {
            $base = 'http://localhost/';
        }

        return rtrim($base, '/') . '/' . $slug;
    }

    protected function provisionAppCopy($targetPath)
    {
        if (!is_dir($targetPath) && !@mkdir($targetPath, 0775, true) && !is_dir($targetPath)) {
            throw new \RuntimeException('Unable to create tenant app path: ' . $targetPath);
        }

        $sourcePath = rtrim(ROOTPATH, "\\/");
        $this->copyDirectoryRecursively($sourcePath, $targetPath, [
            '.git',
            '.github',
            'node_modules',
            'tenants',
            'writable',
        ]);

        $this->ensureWritableStructure($targetPath);
    }

    protected function copyDirectoryRecursively($source, $target, array $excludeNames = [])
    {
        $items = @scandir($source);
        if ($items === false) {
            throw new \RuntimeException('Unable to read directory: ' . $source);
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            if (in_array($item, $excludeNames, true)) {
                continue;
            }

            $from = $source . DIRECTORY_SEPARATOR . $item;
            $to = $target . DIRECTORY_SEPARATOR . $item;

            if (is_dir($from)) {
                if (!is_dir($to) && !@mkdir($to, 0775, true) && !is_dir($to)) {
                    throw new \RuntimeException('Unable to create tenant app directory: ' . $to);
                }
                $this->copyDirectoryRecursively($from, $to, $excludeNames);
                continue;
            }

            if (!@copy($from, $to)) {
                throw new \RuntimeException('Unable to copy file to tenant app: ' . $to);
            }
        }
    }

    protected function ensureWritableStructure($targetPath)
    {
        $dirs = [
            $targetPath . DIRECTORY_SEPARATOR . 'writable',
            $targetPath . DIRECTORY_SEPARATOR . 'writable' . DIRECTORY_SEPARATOR . 'cache',
            $targetPath . DIRECTORY_SEPARATOR . 'writable' . DIRECTORY_SEPARATOR . 'debugbar',
            $targetPath . DIRECTORY_SEPARATOR . 'writable' . DIRECTORY_SEPARATOR . 'logs',
            $targetPath . DIRECTORY_SEPARATOR . 'writable' . DIRECTORY_SEPARATOR . 'session',
            $targetPath . DIRECTORY_SEPARATOR . 'writable' . DIRECTORY_SEPARATOR . 'temp',
            $targetPath . DIRECTORY_SEPARATOR . 'writable' . DIRECTORY_SEPARATOR . 'uploads',
        ];

        foreach ($dirs as $dir) {
            if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
                throw new \RuntimeException('Unable to create writable directory for tenant app: ' . $dir);
            }
        }
    }

    protected function syncBootstrapDataToTenantDatabase($storeId, $userId, array $tenantDb)
    {
        $sourceStore = $this->db->table('pos_stores')->where('id', (int) $storeId)->get()->getRowArray();
        if (empty($sourceStore)) {
            throw new \RuntimeException('Source store not found for tenant bootstrap sync');
        }

        $sourceUser = null;
        if ((int) $userId > 0) {
            $sourceUser = $this->db->table('pos_users')->where('id', (int) $userId)->get()->getRowArray();
        }

        $sourceMap = null;
        if ((int) $userId > 0) {
            $sourceMap = $this->db->table('pos_user_stores')
                ->where('user_id', (int) $userId)
                ->where('store_id', (int) $storeId)
                ->get()
                ->getRowArray();
        }

        $sourceSubscription = null;
        if ((int) $userId > 0) {
            $sourceSubscription = $this->db->table('subscriptions')
                ->where('user_id', (int) $userId)
                ->where('store_id', (int) $storeId)
                ->orderBy('id', 'DESC')
                ->get()
                ->getRowArray();

            if (empty($sourceSubscription)) {
                $sourceSubscription = $this->db->table('subscriptions')
                    ->where('user_id', (int) $userId)
                    ->orderBy('id', 'DESC')
                    ->get()
                    ->getRowArray();
            }
        }

        $tenantConn = \Config\Database::connect([
            'DSN' => '',
            'hostname' => (string) ($tenantDb['hostname'] ?? 'localhost'),
            'username' => (string) ($tenantDb['username'] ?? ''),
            'password' => (string) ($tenantDb['password'] ?? ''),
            'database' => (string) ($tenantDb['database'] ?? ''),
            'DBDriver' => 'MySQLi',
            'DBPrefix' => '',
            'pConnect' => false,
            'DBDebug' => true,
            'charset' => 'utf8mb4',
            'DBCollat' => 'utf8mb4_general_ci',
            'swapPre' => '',
            'encrypt' => false,
            'compress' => false,
            'strictOn' => false,
            'failover' => [],
            'port' => (int) ($tenantDb['port'] ?? 3306),
            'numberNative' => false,
            'foundRows' => false,
            'dateFormat' => [
                'date' => 'Y-m-d',
                'datetime' => 'Y-m-d H:i:s',
                'time' => 'H:i:s',
            ],
        ], false);
        $this->configureConnectionTimeouts($tenantConn, $this->getProvisioningTimeoutSeconds());

        $tenantDbName = (string) ($tenantDb['database'] ?? '');
        $tenantConn->transBegin();

        $this->syncAccessControlDataToTenantDatabase($tenantConn, $tenantDbName);

        $storeRow = $this->filterRowToExistingColumns($tenantConn, $tenantDbName, 'pos_stores', $sourceStore);
        if (empty($storeRow)) {
            throw new \RuntimeException('Target tenant table pos_stores is missing expected columns');
        }
        $tenantConn->table('pos_stores')->where('id', (int) $storeId)->delete();
        if (!$tenantConn->table('pos_stores')->insert($storeRow)) {
            $error = $tenantConn->error();
            throw new \RuntimeException('Failed inserting store into tenant DB: ' . (($error['message'] ?? 'unknown error')));
        }

        if (!empty($sourceUser)) {
            $userRow = $this->filterRowToExistingColumns($tenantConn, $tenantDbName, 'pos_users', $sourceUser);
            $tenantConn->table('pos_users')->where('id', (int) $userId)->delete();
            if (!empty($userRow) && !$tenantConn->table('pos_users')->insert($userRow)) {
                $error = $tenantConn->error();
                throw new \RuntimeException('Failed inserting user into tenant DB: ' . (($error['message'] ?? 'unknown error')));
            }
        }

        if (!empty($sourceMap)) {
            $mapRow = $this->filterRowToExistingColumns($tenantConn, $tenantDbName, 'pos_user_stores', $sourceMap);
            $tenantConn->table('pos_user_stores')
                ->where('user_id', (int) $sourceMap['user_id'])
                ->where('store_id', (int) $sourceMap['store_id'])
                ->delete();
            if (!empty($mapRow) && !$tenantConn->table('pos_user_stores')->insert($mapRow)) {
                $error = $tenantConn->error();
                throw new \RuntimeException('Failed inserting user-store mapping into tenant DB: ' . (($error['message'] ?? 'unknown error')));
            }
        }

        if (!empty($sourceSubscription)) {
            $subRow = $this->filterRowToExistingColumns($tenantConn, $tenantDbName, 'subscriptions', $sourceSubscription);
            if (!empty($subRow)) {
                $tenantConn->table('subscriptions')->where('id', (int) $sourceSubscription['id'])->delete();
                if (!$tenantConn->table('subscriptions')->insert($subRow)) {
                    $error = $tenantConn->error();
                    throw new \RuntimeException('Failed inserting subscription into tenant DB: ' . (($error['message'] ?? 'unknown error')));
                }
            }
        }

        if ($tenantConn->transStatus() === false) {
            $tenantConn->transRollback();
            throw new \RuntimeException('Unable to seed bootstrap records in tenant database');
        }

        $tenantConn->transCommit();
    }

    protected function syncAccessControlDataToTenantDatabase($tenantConn, $tenantDbName)
    {
        if ($tenantDbName === '') {
            throw new \RuntimeException('Tenant database name is required for access control sync');
        }

        $sourceRoles = $this->db->table('pos_roles')->get()->getResultArray();
        $sourcePermissions = $this->db->table('pos_permissions')->get()->getResultArray();
        $sourceRolePermissions = $this->db->table('pos_role_permissions')->get()->getResultArray();

        foreach ($sourceRoles as $role) {
            $roleRow = $this->filterRowToExistingColumns($tenantConn, $tenantDbName, 'pos_roles', $role);
            $roleId = (int) ($role['id'] ?? 0);
            if ($roleId <= 0 || empty($roleRow)) {
                continue;
            }

            $tenantConn->table('pos_roles')->where('id', $roleId)->delete();
            if (!$tenantConn->table('pos_roles')->insert($roleRow)) {
                $error = $tenantConn->error();
                throw new \RuntimeException('Failed syncing roles to tenant DB: ' . (($error['message'] ?? 'unknown error')));
            }
        }

        foreach ($sourcePermissions as $permission) {
            $permissionRow = $this->filterRowToExistingColumns($tenantConn, $tenantDbName, 'pos_permissions', $permission);
            $permissionId = (int) ($permission['id'] ?? 0);
            if ($permissionId <= 0 || empty($permissionRow)) {
                continue;
            }

            $tenantConn->table('pos_permissions')->where('id', $permissionId)->delete();
            if (!$tenantConn->table('pos_permissions')->insert($permissionRow)) {
                $error = $tenantConn->error();
                throw new \RuntimeException('Failed syncing permissions to tenant DB: ' . (($error['message'] ?? 'unknown error')));
            }
        }

        // Replace mappings to match central role-permission matrix.
        $tenantConn->table('pos_role_permissions')->truncate();
        foreach ($sourceRolePermissions as $rolePermission) {
            $mapRow = $this->filterRowToExistingColumns($tenantConn, $tenantDbName, 'pos_role_permissions', $rolePermission);
            if (empty($mapRow)) {
                continue;
            }

            if (!$tenantConn->table('pos_role_permissions')->insert($mapRow)) {
                $error = $tenantConn->error();
                throw new \RuntimeException('Failed syncing role-permission mappings to tenant DB: ' . (($error['message'] ?? 'unknown error')));
            }
        }
    }

    protected function filterRowToExistingColumns($conn, $dbName, $table, array $row)
    {
        if ($dbName === '' || empty($row)) {
            return [];
        }

        $sql = 'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?';
        $columnsRows = $conn->query($sql, [$dbName, $table])->getResultArray();
        if (empty($columnsRows)) {
            return [];
        }

        $allowed = [];
        foreach ($columnsRows as $col) {
            $name = (string) ($col['COLUMN_NAME'] ?? '');
            if ($name !== '') {
                $allowed[$name] = true;
            }
        }

        $filtered = [];
        foreach ($row as $key => $value) {
            if (isset($allowed[$key])) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    protected function configureTenantAppEnvironment($appPath, array $tenantDb, $appUrl)
    {
        $envTemplatePath = rtrim(ROOTPATH, "\\/") . DIRECTORY_SEPARATOR . 'env';
        $envTargetPath = rtrim($appPath, "\\/") . DIRECTORY_SEPARATOR . '.env';

        $content = '';
        if (is_file($envTemplatePath)) {
            $content = (string) file_get_contents($envTemplatePath);
        }

        $content = $this->upsertEnvLine($content, 'database.default.hostname', (string) ($tenantDb['hostname'] ?? 'localhost'));
        $content = $this->upsertEnvLine($content, 'database.default.port', (string) ((int) ($tenantDb['port'] ?? 3306)));
        $content = $this->upsertEnvLine($content, 'database.default.database', (string) ($tenantDb['database'] ?? ''));
        $content = $this->upsertEnvLine($content, 'database.default.username', (string) ($tenantDb['username'] ?? ''));
        $content = $this->upsertEnvLine($content, 'database.default.password', (string) ($tenantDb['password'] ?? ''));
        $content = $this->upsertEnvLine($content, 'app.baseURL', $this->normalizeBaseUrl($this->sanitizeAppUrl((string) $appUrl)));

        if (@file_put_contents($envTargetPath, $content) === false) {
            throw new \RuntimeException('Unable to write tenant app .env file: ' . $envTargetPath);
        }
    }

    protected function upsertEnvLine($content, $key, $value)
    {
        $line = $key . ' = ' . $this->formatEnvValue($value);
        $pattern = '/^\s*#?\s*' . preg_quote($key, '/') . '\s*=.*$/m';

        if (preg_match($pattern, $content)) {
            return (string) preg_replace($pattern, $line, $content, 1);
        }

        $content = rtrim((string) $content) . PHP_EOL;
        return $content . $line . PHP_EOL;
    }

    protected function formatEnvValue($value)
    {
        $value = (string) $value;
        $value = str_replace('"', '\\"', $value);
        return '"' . $value . '"';
    }

    protected function getProvisioningTimeoutSeconds()
    {
        $timeout = (int) (getenv('TENANT_PROVISION_TIMEOUT') ?: 600);
        return $timeout > 0 ? $timeout : 600;
    }

    protected function extendExecutionTimeout($seconds)
    {
        $seconds = max(120, (int) $seconds);

        if (function_exists('set_time_limit')) {
            @set_time_limit($seconds);
        }

        @ini_set('max_execution_time', (string) $seconds);
        @ini_set('default_socket_timeout', (string) $seconds);
        @ini_set('mysql.connect_timeout', (string) $seconds);
    }

    protected function configureConnectionTimeouts($connection, $seconds)
    {
        $seconds = max(120, (int) $seconds);

        if (!$connection) {
            return;
        }

        $queries = [
            'SET SESSION wait_timeout = ' . $seconds,
            'SET SESSION interactive_timeout = ' . $seconds,
            'SET SESSION net_read_timeout = ' . $seconds,
            'SET SESSION net_write_timeout = ' . $seconds,
            'SET SESSION innodb_lock_wait_timeout = ' . min($seconds, 600),
        ];

        foreach ($queries as $sql) {
            try {
                $connection->query($sql);
            } catch (\Throwable $e) {
            }
        }
    }

    protected function normalizeBaseUrl($url)
    {
        $url = trim((string) $url);
        if ($url === '') {
            $url = trim((string) (getenv('app.baseURL') ?: 'http://localhost/'));
        }

        if (!$this->isAbsoluteUrl($url)) {
            $base = trim((string) (getenv('app.baseURL') ?: 'http://localhost/'));
            if ($base === '') {
                $base = 'http://localhost/';
            }
            $url = rtrim($base, '/') . '/' . ltrim($url, '/');
        }

        return rtrim($url, '/') . '/';
    }

    protected function sanitizeAppUrl($url)
    {
        return preg_replace('#/public/?$#i', '', trim((string) $url)) ?? trim((string) $url);
    }

    protected function isAbsoluteUrl($url)
    {
        return (bool) preg_match('#^https?://#i', (string) $url);
    }
}
