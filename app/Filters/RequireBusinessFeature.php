<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RequireBusinessFeature implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $feature = is_array($arguments) && isset($arguments[0]) ? trim((string) $arguments[0]) : '';
        if ($feature === '') {
            return null;
        }

        helper('business_feature');

        if (business_feature_enabled($feature)) {
            return null;
        }

        if (method_exists($request, 'isAJAX') && $request->isAJAX()) {
            return service('response')->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'This feature is disabled for your business profile.',
            ]);
        }

        return redirect()->to('/')->with('error', 'This feature is disabled for your business profile.');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // no-op
    }
}
