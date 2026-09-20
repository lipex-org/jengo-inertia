<?php

declare(strict_types=1);

namespace Jengo\Inertia\Config;

use Jengo\Inertia\Installers\InertiaInstaller;

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
}