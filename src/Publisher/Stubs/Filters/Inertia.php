<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use Jengo\Inertia\Middleware;
use function Jengo\Base\Support\arr;

class Inertia extends Middleware
{
    /**
     * @return array
     */
    public function withShare(RequestInterface $request): array
    {
        return array_merge(parent::withShare($request), [
            'csrf_token' => csrf_token(),
            'csrf_hash' => csrf_hash(),
            'auth' => [
                'user' => auth()->user()
                    ? arr(auth()->user())->only(['id', 'username', 'email'])
                    : null,
            ],
        ]);
    }
}
