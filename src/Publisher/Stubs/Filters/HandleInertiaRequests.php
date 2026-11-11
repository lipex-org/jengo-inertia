<?php

/**
 * This file is part of Inertia.js Codeigniter 4.
 *
 * (c) 2023 Fab IT Hub <hello@fabithub.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use Jengo\Inertia\Middleware;

use function Jengo\Base\Support\arr;

class HandleInertiaRequests extends Middleware
{
    public function withShare(RequestInterface $request): array
    {
        return array_merge(parent::withShare($request), [
            'csrf_token' => csrf_token(),
            'csrf_hash' => csrf_hash(),
            'auth' => [
                'user' => function_exists('auth')
                    ? arr(auth()->user() ?? [])
                        ->only([
                            'id',
                            'username',
                            'email',
                        ])->asNullWhenEmpty()
                    : null,
            ],
        ]);
    }
}
