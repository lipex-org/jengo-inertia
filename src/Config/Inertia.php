<?php

/**
 * This file is part of Inertia.js Codeigniter 4.
 *
 * (c) 2023 Fab IT Hub <hello@fabithub.com>
 * (c) 2026 JengoPHP <hello@jengophp.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Jengo\Inertia\Config;

use CodeIgniter\Config\BaseConfig;

class Inertia extends BaseConfig
{
    public string $rootView = 'app';
    public string|null $version = null;
    public bool $isSsrEnabled = false;
    public string $ssrUrl = 'http://127.0.0.1:13714';
    public string $filterAlias = 'inertia';
}