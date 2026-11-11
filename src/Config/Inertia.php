<?php

/**
 * This file is part of Inertia.js Codeigniter 4.
 *
 * (c) 2023 Fab IT Hub <hello@fabithub.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Jengo\Inertia\Config;

use CodeIgniter\Config\BaseConfig;

class Inertia extends BaseConfig
{
    /**
     * Wire protocol version: '3.0' (default) or '2.0'.
     */
    public string $protocolVersion = '3.0';

    public string $rootView = 'app';
    public ?string $version = null;
    public bool $isSsrEnabled = false;
    public string $ssrUrl = 'http://127.0.0.1:13714';
    public string $filterAlias = 'inertia';

    /**
     * Wrap 64-bit integers outside JS safe range (-2^53+1 to 2^53-1) as {"$bigint": "<digits>"}.
     */
    public bool $preserveBigIntegers = false;

    /**
     * Map of HTTP status codes or error keys to Inertia page components.
     *
     * Example:
     * [
     *     401 => 'error',
     *     403 => 'error',
     *     404 => 'error',
     *     419 => 'error',
     *     500 => 'error',
     *     503 => 'error',
     *     'error' => 'error',
     * ]
     *
     * @var array<int|string, string>
     */
    public array $errorPages = [
        401 => 'error',
        403 => 'error',
        404 => 'error',
        419 => 'error',
        500 => 'error',
        503 => 'error',
        'error' => 'error',
    ];

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

    /**
     * Whether to show CI4 HTML debug trace view in development for 500 errors.
     * When false (default), the configured Inertia error page is rendered
     * with detailed exception info passed in the props if display_errors is on.
     */
    public bool $showDebugInDevelopment = false;

    public function __construct()
    {
        parent::__construct();

        if (empty($this->pages['pages'])) {
            $this->pages['pages'] = [
                function_exists('resource_path') ? resource_path('js/inertia/pages') : (defined('RESOURCEPATH') ? RESOURCEPATH . 'js/inertia/pages' : 'resources/js/inertia/pages'),
                function_exists('resource_path') ? resource_path('js/Pages') : (defined('RESOURCEPATH') ? RESOURCEPATH . 'js/Pages' : 'resources/js/Pages'),
                function_exists('resource_path') ? resource_path('js/pages') : (defined('RESOURCEPATH') ? RESOURCEPATH . 'js/pages' : 'resources/js/pages'),
            ];
        }
    }

    /**
     * Resolve the Inertia component name for a given HTTP status code.
     */
    public function resolveErrorPage(int|string $statusCode): ?string
    {
        if (isset($this->errorPages[$statusCode])) {
            return (string) $this->errorPages[$statusCode];
        }

        if (isset($this->pages[$statusCode])) {
            return (string) $this->pages[$statusCode];
        }

        return $this->errorPages['error']
            ?? $this->pages['error']
            ?? $this->errorPages['default']
            ?? $this->pages['default']
            ?? null;
    }
}
