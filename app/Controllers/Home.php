<?php

namespace App\Controllers;

class Home extends BaseController
{
    protected function isValidRole($role, $method)
    {
        return true;
    }

    public function index(): string
    {
        return view('welcome_message');
    }
}
