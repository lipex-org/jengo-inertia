<?php

declare(strict_types=1);

namespace Tests\Unit;

use CodeIgniter\Events\Events;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use Config\App;
use Jengo\Base\Validation\FormFailedResponseHolder;
use Jengo\Inertia\Config\Registrar;
use Jengo\Inertia\Inertia;
use Jengo\Inertia\Installers\InertiaInstaller;
use Tests\TestCase;

class EventsAndRegistrarTest extends TestCase
{
    public function testRegistrarProvidesInstaller(): void
    {
        $reg = Registrar::JengoBase();
        $this->assertArrayHasKey('installers', $reg);
        $this->assertContains(InertiaInstaller::class, $reg['installers']);
    }

    public function testFormFailedEventListenerFlashesErrors(): void
    {
        $config = new App();
        $uri = new URI('http://example.com/test');
        $userAgent = new UserAgent();
        $request = new IncomingRequest($config, $uri, 'php://input', $userAgent);

        $holder = new FormFailedResponseHolder(['username' => 'Required field'], $request);

        Events::trigger('jengo.form.failed', $holder);

        $this->assertSame(['username' => 'Required field'], session()->getFlashdata('errors'));
    }
}
