<?php

namespace App\Controllers;

class NoAccess extends BaseController
{
    public function index()
    {
        return view('errors/no_access', ['title' => 'No Access']);
    }
}
