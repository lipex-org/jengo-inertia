<?php

/**
 * This file is part of Inertia.js Codeigniter 4.
 *
 * (c) 2023 Fab IT Hub <hello@fabithub.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Config;

use Jengo\Inertia\Config\Inertia as BaseInertia;

class Inertia extends BaseInertia
{
    /**
     * The root template that's loaded on the first page visit.
     */
    public string $rootView = 'app';

    /**
     * Wire protocol version: '3.0' (default) or '2.0'.
     */
    public string $protocolVersion = '3.0';

    /**
     * Determines the current asset version.
     */
    public ?string $version = null;

    /**
     * Wrap 64-bit integers outside JS safe range (-2^53+1 to 2^53-1) as {"$bigint": "<digits>"}.
     */
    public bool $preserveBigIntegers = false;

    /**
     * Inertia page component configuration.
     *
     * @var array<string, mixed>
     */
    public array $pages = [
        'ensure_page_exists' => false,
        'pages' => [],
        'extensions' => ['vue', 'jsx', 'tsx', 'svelte', 'js', 'ts'],
        'error' => 'error',
    ];

    /**
     * Testing specific configurations for Inertia assertions.
     *
     * @var array<string, mixed>
     */
    public array $testing = [
        'ensure_page_exists' => false,
        'page_paths' => [],
    ];

    public function __construct()
    {
        parent::__construct();

        $this->pages['pages'] = [
            resource_path('js/inertia/pages'),
            resource_path('js/Pages'),
            resource_path('js/pages'),
        ];

        $this->testing['page_paths'] = [
            resource_path('js/inertia/pages'),
            resource_path('js/Pages'),
            resource_path('js/pages'),
        ];
    }
}
