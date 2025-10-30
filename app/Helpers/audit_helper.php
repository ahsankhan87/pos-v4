<?php
function logAction($action, $details = '')
{
    $model = new \App\Models\M_audit_logs();
    $model->insert([
        'user_id' => session('user_id'),
        'action' => $action,
        'details' => $details,
        'ip_address' => service('request')->getIPAddress(),
        'user_agent' => service('request')->getUserAgent(),
        'store_id' => session('store_id'),
    ]);
}
