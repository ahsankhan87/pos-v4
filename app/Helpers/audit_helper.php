<?php

if (!function_exists('logAction')) {
    function logAction($action, $details = '', array $context = [])
    {
        try {
            $session = session();
            $request = service('request');

            $userId = isset($context['user_id']) ? (int) $context['user_id'] : (int) ($session->get('user_id') ?? 0);
            $storeId = isset($context['store_id']) ? (int) $context['store_id'] : (int) ($session->get('store_id') ?? 0);

            if ($storeId <= 0 && $userId > 0) {
                $userModel = new \App\Models\UserModel();
                $userStores = $userModel->getUserStores($userId);

                foreach ($userStores as $store) {
                    if (!empty($store['is_default'])) {
                        $storeId = (int) ($store['id'] ?? 0);
                        break;
                    }
                }

                if ($storeId <= 0 && !empty($userStores[0]['id'])) {
                    $storeId = (int) $userStores[0]['id'];
                }
            }

            $model = new \App\Models\M_audit_logs();
            $model->insert([
                'user_id' => $userId > 0 ? $userId : null,
                'action' => (string) $action,
                'details' => (string) $details,
                'ip_address' => $request->getIPAddress(),
                'user_agent' => $request->getUserAgent()->getAgentString(),
                'store_id' => $storeId > 0 ? $storeId : 0,
            ]);

            return true;
        } catch (\Throwable $e) {
            log_message('error', 'Audit logging failed for action "{action}": {message}', [
                'action' => (string) $action,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
