<?php

/**
 * This file is part of Inertia.js Codeigniter 4.
 *
 * (c) 2023 Fab IT Hub <hello@fabithub.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Controllers;

use Jengo\Inertia\Inertia;

class Home extends BaseController
{
    public function index()
    {
        return Inertia::render('welcome');
    }
}
