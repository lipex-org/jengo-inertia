<?php

declare(strict_types=1);

namespace Tests\Unit;

use Jengo\Inertia\Config\Registrar;
use Jengo\Inertia\Installers\InertiaInstaller;
use Tests\TestCase;

class RegistrarTest extends TestCase
{
    public function testRegistrarProvidesInstaller(): void
    {
        $reg = Registrar::JengoBase();
        $this->assertArrayHasKey('installers', $reg);
        $this->assertContains(InertiaInstaller::class, $reg['installers']);
    }
}
