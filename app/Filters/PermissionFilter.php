<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class PermissionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        $userModel = new \App\Models\UserModel();

        // Check if user is logged in
        if (!$session->has('user_id')) {
            return redirect()->to('/login')->with('error', 'Please login first');
        }

        // Admin bypass: users with Admin role have full access
        $role = $userModel->getRole($session->get('user_id'));
        if ($role && (strtolower($role['name'] ?? '') === 'admin' || (int)($role['id'] ?? 0) === 1)) {
            return; // allow
        }

        $arguments = is_array($arguments) ? $arguments : [];

        // Check required permissions (non-admins)
        // Supports:
        // - "perm.name" (required)
        // - "any|perm.a|perm.b|perm.c" (any-of)
        // - "any:perm.a|perm.b|perm.c" (legacy any-of)
        foreach ($arguments as $arg) {
            $arg = trim((string) $arg);
            if ($arg === '') {
                continue;
            }

            $allowed = false;

            if (strpos($arg, 'any|') === 0 || strpos($arg, 'any:') === 0) {
                $list = trim(substr($arg, 4));
                $candidates = array_values(array_filter(array_map('trim', explode('|', $list))));
                foreach ($candidates as $permission) {
                    if ($userModel->hasPermission($session->get('user_id'), $permission)) {
                        $allowed = true;
                        break;
                    }
                }
            } else {
                $allowed = $userModel->hasPermission($session->get('user_id'), $arg);
            }

            if (!$allowed) {
                $req = service('request');
                if ($req && method_exists($req, 'isAJAX') && $req->isAJAX()) {
                    return service('response')->setJSON([
                        'error' => 'You are not authorized to perform this action'
                    ])->setStatusCode(403);
                }
                return redirect()->to(site_url('no-access'));
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
