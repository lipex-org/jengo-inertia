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

use CodeIgniter\Config\BaseService;
use Jengo\Inertia\Extras\Gateway;
use Jengo\Inertia\ResponseFactory;
use Jengo\Inertia\Ssr\HttpGateway;

class Services extends BaseService
{
    public static function inertia(bool $getShared = true): ?ResponseFactory
    {
        if ($getShared) {
            return static::getSharedInstance('inertia');
        }

        return new ResponseFactory();
    }

    public static function httpGateway(bool $getShared = true): ?Gateway
    {
        if ($getShared) {
            return static::getSharedInstance('httpGateway');
        }

        return new HttpGateway();
    }
}