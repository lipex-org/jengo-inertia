<?php

declare(strict_types=1);

namespace Tests\Unit;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use Config\App;
use Config\Filters;
use Jengo\Inertia\Config\Inertia as ConfigInertia;
use Jengo\Inertia\Inertia;
use Jengo\Inertia\Modifiers\InertiaModifier;
use Tests\TestCase;

class DummyFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null) {}

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response->setHeader('X-Filter-Executed', 'true');
    }
}

class InertiaModifierTest extends TestCase
{
    public function testModifyValidationFailed(): void
    {
        $modifier = new InertiaModifier();

        $config = new App();
        $uri = new URI('http://example.com/form');
        $userAgent = new UserAgent();
        $request = new IncomingRequest($config, $uri, 'php://input', $userAgent);

        $errors = ['email' => 'Invalid email address'];

        $response = $modifier->modifyValidationFailed($errors, $request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame($errors, session()->getFlashdata('errors'));
    }

    public function testModifyValidationFailedWithFilterHook(): void
    {
        $inertiaConfig = new ConfigInertia();
        $inertiaConfig->filterAlias = 'custom_inertia_filter';
        \CodeIgniter\Config\Factories::injectMock('config', 'Inertia', $inertiaConfig);

        $filtersConfig = new Filters();
        $filtersConfig->aliases['custom_inertia_filter'] = DummyFilter::class;
        \CodeIgniter\Config\Factories::injectMock('config', 'Filters', $filtersConfig);

        $modifier = new InertiaModifier();

        $config = new App();
        $uri = new URI('http://example.com/form');
        $userAgent = new UserAgent();
        $request = new IncomingRequest($config, $uri, 'php://input', $userAgent);

        $errors = ['name' => 'Name is required'];
        $response = $modifier->modifyValidationFailed($errors, $request);

        $this->assertTrue($response->hasHeader('X-Filter-Executed'));
        $this->assertSame('true', $response->getHeaderLine('X-Filter-Executed'));
    }
}
