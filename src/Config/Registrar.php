<?php

declare(strict_types=1);

namespace Jengo\Inertia\Config;

use Jengo\Inertia\Installers\InertiaInstaller;
use Jengo\Inertia\Middleware;

class Registrar
{
    public static function JengoBase(): array
    {
        return [
            'installers' => [
                InertiaInstaller::class,
            ],
        ];
    }

    public static function Filters(): array
    {
        return [
            'aliases' => [
                'inertia' => Middleware::class,
            ],
        ];
    }
}