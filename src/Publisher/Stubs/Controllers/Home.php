<?php

namespace App\Controllers;

use Jengo\Inertia\Inertia;

class Home extends BaseController
{
    public function index()
    {
        return Inertia::render('welcome');
    }
}