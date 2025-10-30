<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    protected $publicRoutes = [
        'login',
        'register',
        'forgot-password',
        'reset-password',
        'logout',
        'api',
    ];

    public function before(RequestInterface $request, $arguments = null)
    {
        $uri = service('uri');
        $route = $uri->getSegment(1);

        // Skip authentication for public routes
        if (in_array($route, $this->publicRoutes)) {
            return;
        }

        // Check if user is logged in
        if (!session()->has('is_logged_in')) {
            return redirect()->to('/login')
                ->with('error', 'Please login to access this page');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
