<?php

namespace App\Controllers;

class Api extends BaseController
{
    /**
     * Refresh CSRF token to prevent expiration during long POS sessions
     * This endpoint is called periodically from the POS interface
     */
    public function csrfRefresh()
    {
        // Generate a new CSRF token
        $csrf = csrf_hash();

        // Return the new token as JSON
        return $this->response->setJSON([
            'success' => true,
            'token' => $csrf,
            'token_name' => csrf_token(),
            'timestamp' => time()
        ]);
    }

    /**
     * Keep session alive - simple ping endpoint
     */
    public function keepAlive()
    {
        return $this->response->setJSON([
            'success' => true,
            'time' => date('Y-m-d H:i:s'),
            'session_active' => session()->has('user_id')
        ]);
    }
}
