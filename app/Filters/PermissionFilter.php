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

        // Check each required permission (non-admins)
        foreach ($arguments as $permission) {
            if (!$userModel->hasPermission($session->get('user_id'), $permission)) {
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
