<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class StoreFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // Skip store selection for these paths
        $skipPaths = [
            'stores/select',
            'stores/switch',
            'login',
            'logout',
            'register',
            'forgot-password',
            'reset-password',
            'api',
        ];

        // Get the current URI path (correct way)
        $currentPath = $request->getUri()->getPath();

        // Skip auth-related pages and store selection pages
        foreach ($skipPaths as $path) {
            if (strpos($currentPath, $path) === 0) {
                return;
            }
        }

        // Also skip if user isn't logged in (let AuthFilter handle it)
        if (!$session->has('is_logged_in')) {
            return;
        }

        // Only check store selection for logged-in users
        if (!$session->has('store_id') && !$request->isAJAX()) {
            return redirect()->to('stores/select');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
