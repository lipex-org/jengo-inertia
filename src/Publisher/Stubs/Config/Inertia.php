<?php

namespace Config;

use Jengo\Inertia\Config\Inertia as BaseInertia;

class Inertia extends BaseInertia
{
    /**
     * The root template that's loaded on the first page visit.
     */
    public string $rootView = 'app';

    /**
     * Determines the current asset version.
     */
    public string|null $version = null;

    /**
     * Inertia page component configuration.
     *
     * @var array<string, mixed>
     */
    public array $pages = [
        'ensure_page_exists' => true,
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
