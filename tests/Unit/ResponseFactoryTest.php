<?php

declare(strict_types=1);

namespace Tests\Unit;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use Config\App;
use Config\Services;
use Jengo\Inertia\Config\Inertia as ConfigInertia;
use Jengo\Inertia\Inertia;
use Jengo\Inertia\Props\Always;
use Jengo\Inertia\Props\Defer;
use Jengo\Inertia\Props\Lazy;
use Jengo\Inertia\Props\Live;
use Jengo\Inertia\Props\Mergeable;
use Jengo\Inertia\Props\Once;
use Jengo\Inertia\Props\Optional;
use Jengo\Inertia\Props\Scroll;
use Jengo\Inertia\ResponseFactory;
use Tests\TestCase;

class ResponseFactoryTest extends TestCase
{
    private ResponseFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new ResponseFactory();
        Inertia::flushShared();
    }

    private function createRequest(string $url = 'http://example.com/test', array $headers = []): IncomingRequest
    {
        $config = new App();
        $uri = new URI($url);
        $userAgent = new UserAgent();
        $request = new IncomingRequest($config, $uri, 'php://input', $userAgent);

        foreach ($headers as $key => $val) {
            $request->setHeader($key, $val);
        }

        Services::injectMock('request', $request);

        return $request;
    }

    public function testShareAndGetShared(): void
    {
        $this->factory->share('appName', 'JengoApp');
        $this->factory->share(['user' => ['name' => 'Alice'], 'theme' => 'dark']);

        $this->assertSame('JengoApp', $this->factory->getShared('appName'));
        $this->assertSame(['name' => 'Alice'], $this->factory->getShared('user'));
        $this->assertSame('default_val', $this->factory->getShared('non_existent', 'default_val'));

        $allShared = $this->factory->getShared(null);
        $this->assertArrayHasKey('appName', $allShared);
        $this->assertArrayHasKey('user', $allShared);
        $this->assertArrayHasKey('theme', $allShared);

        $keys = $this->factory->getSharedKeys();
        $this->assertContains('appName', $keys);
        $this->assertContains('user', $keys);
        $this->assertContains('theme', $keys);

        $this->factory->flushShared();
        $this->assertEmpty($this->factory->getShared(null));
        $this->assertEmpty($this->factory->getSharedKeys());
    }

    public function testVersionManagement(): void
    {
        $this->factory->version('2.5.0');
        
        $config = new ConfigInertia();
        $config->version = '3.0.0';
        \CodeIgniter\Config\Factories::injectMock('config', 'Inertia', $config);

        $this->assertSame('3.0.0', $this->factory->getVersion());

        $config->version = null;
        \CodeIgniter\Config\Factories::injectMock('config', 'Inertia', $config);
        $this->assertIsString($this->factory->getVersion());
    }

    public function testPropCreationHelpers(): void
    {
        $this->assertInstanceOf(Always::class, $this->factory->always('val'));
        $this->assertInstanceOf(Lazy::class, $this->factory->lazy(fn() => 'lazy'));
        $this->assertInstanceOf(Defer::class, $this->factory->defer(fn() => 'defer', 'custom_group'));
        $this->assertInstanceOf(Once::class, $this->factory->once(fn() => 'once'));
        
        $merge = $this->factory->merge(['a']);
        $this->assertInstanceOf(Mergeable::class, $merge);
        $this->assertFalse($merge->prepend);
        $this->assertFalse($merge->deep);

        $prepend = $this->factory->prepend(['b']);
        $this->assertInstanceOf(Mergeable::class, $prepend);
        $this->assertTrue($prepend->prepend);

        $deepMerge = $this->factory->deepMerge(['c']);
        $this->assertInstanceOf(Mergeable::class, $deepMerge);
        $this->assertTrue($deepMerge->deep);

        $optional = $this->factory->optional('opt');
        $this->assertInstanceOf(Optional::class, $optional);

        $scroll = $this->factory->scroll(['item1']);
        $this->assertInstanceOf(Scroll::class, $scroll);

        $live = $this->factory->live('live_val', 'channel_1');
        $this->assertInstanceOf(Live::class, $live);
    }

    public function testLocationOnInertiaRequest(): void
    {
        $this->createRequest('http://example.com/checkout', ['X-Inertia' => 'true']);

        $response = $this->factory->location('https://external.com/billing');

        $this->assertSame(409, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('X-Inertia-Location'));
        $this->assertSame('https://external.com/billing', $response->getHeaderLine('X-Inertia-Location'));
        $this->assertSame('https://external.com/billing', session()->get('_ci_previous_url'));
    }

    public function testLocationWithFragmentOnInertiaRequest(): void
    {
        $this->createRequest('http://example.com/faq', ['X-Inertia' => 'true']);

        $response = $this->factory->location('http://example.com/faq#pricing');

        $this->assertSame(409, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('X-Inertia-Redirect'));
        $this->assertSame('http://example.com/faq#pricing', $response->getHeaderLine('X-Inertia-Redirect'));
    }

    public function testLocationOnStandardRequest(): void
    {
        $this->createRequest('http://example.com/checkout');

        $response = $this->factory->location('https://external.com/billing');

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(303, $response->getStatusCode());
    }

    public function testLocationWithRequestInterfaceArgument(): void
    {
        $req = $this->createRequest('http://example.com/orders', ['X-Inertia' => 'true']);

        $response = $this->factory->location($req);

        $this->assertSame(409, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('X-Inertia-Location'));
    }

    public function testFlash(): void
    {
        $res = $this->factory->flash('success_msg', 'Profile updated!');
        $this->assertSame($this->factory, $res);
        $this->assertSame('Profile updated!', session()->getFlashdata('success_msg'));
    }

    public function testDisableSsr(): void
    {
        $config = new ConfigInertia();
        $config->isSsrEnabled = true;
        \CodeIgniter\Config\Factories::injectMock('config', 'Inertia', $config);

        $this->factory->disableSsr(true);
        $this->assertFalse($config->isSsrEnabled);

        $this->factory->disableSsr(fn() => false);
        $this->assertTrue($config->isSsrEnabled);
    }

    public function testInitCompilesPageDirectives(): void
    {
        $page = [
            'component' => 'Dashboard',
            'props'     => ['user' => 'Bob'],
            'url'       => '/dashboard',
            'version'   => '1.0',
        ];

        $html = ResponseFactory::init($page, false);
        $this->assertStringContainsString('data-page="app"', $html);
        $this->assertStringContainsString('<div id="app"></div>', $html);

        $head = ResponseFactory::init($page, true);
        $this->assertIsString($head);
    }

    public function testInertiaFacadeStaticProxy(): void
    {
        Inertia::share('site', 'My Site');
        $this->assertSame('My Site', Inertia::getShared('site'));

        $this->assertInstanceOf(Always::class, Inertia::always('val'));
        $this->assertInstanceOf(Lazy::class, Inertia::lazy(fn() => 'lazy'));
        $this->assertInstanceOf(Defer::class, Inertia::defer(fn() => 'defer'));
        $this->assertInstanceOf(Once::class, Inertia::once(fn() => 'once'));
        $this->assertInstanceOf(Mergeable::class, Inertia::merge(['a']));
        $this->assertInstanceOf(Mergeable::class, Inertia::prepend(['b']));
        $this->assertInstanceOf(Mergeable::class, Inertia::deepMerge(['c']));
        $this->assertInstanceOf(Optional::class, Inertia::optional('opt'));
        $this->assertInstanceOf(Scroll::class, Inertia::scroll(['item1']));
        $this->assertInstanceOf(Live::class, Inertia::live('live_val', 'channel_1'));
    }

    public function testRedirectWithFragment(): void
    {
        $response = $this->factory->redirectWithFragment('http://example.com/items#section-2');

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(409, $response->getStatusCode());
        $this->assertSame('http://example.com/items#section-2', $response->getHeaderLine('X-Inertia-Redirect'));
    }

    public function testPrecognitionSuccess(): void
    {
        $response = $this->factory->precognitionSuccess();

        $this->assertSame(204, $response->getStatusCode());
        $this->assertSame('true', $response->getHeaderLine('Precognition-Success'));
    }

    public function testDirectiveCompileProtocolVersions(): void
    {
        $page = [
            'component' => 'Dashboard',
            'props'     => ['user' => 'Bob'],
            'url'       => '/dashboard',
            'version'   => '1.0',
        ];

        // V3 (default)
        $v3Html = ResponseFactory::init($page, false, '3.0');
        $this->assertStringContainsString('<script type="application/json" data-page="app">', $v3Html);
        $this->assertStringContainsString('<div id="app"></div>', $v3Html);

        // V2
        $v2Html = ResponseFactory::init($page, false, '2.0');
        $this->assertStringContainsString('<div id="app" data-page=', $v2Html);
        $this->assertStringNotContainsString('<script', $v2Html);
    }

    public function testRenderResolvesValidationErrorsAndFlashData(): void
    {
        $this->createRequest('http://example.com/test', ['X-Inertia' => 'true']);

        session()->setFlashdata('errors', ['email' => 'Invalid email address']);
        session()->setFlashdata('status', 'Profile saved successfully');

        $response = $this->factory->render('Dashboard', ['user' => 'John']);

        $this->assertSame(200, $response->getStatusCode());
        $shared = $this->factory->getShared(null);
        $this->assertArrayHasKey('errors', $shared);
        $this->assertArrayHasKey('flash', $shared);
        $this->assertSame(['email' => 'Invalid email address'], $shared['errors']);
        $this->assertSame(['status' => 'Profile saved successfully'], $shared['flash']);
    }

    public function testRenderHandlesErrorBagHeader(): void
    {
        $this->createRequest('http://example.com/test', [
            'X-Inertia' => 'true',
            'x-inertia-error-bag' => 'loginForm',
        ]);

        session()->setFlashdata('errors', ['password' => 'Too short']);

        $this->factory->render('Login', []);

        $shared = $this->factory->getShared(null);
        $this->assertArrayHasKey('errors', $shared);
        $this->assertSame(['loginForm' => ['password' => 'Too short']], $shared['errors']);
    }

    public function testRenderThrowsExceptionWhenOverwritingSystemProps(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The following props are overwriting system props: `[errors]`. Choose different keys for these props to avoid this error.');

        $this->factory->render('Dashboard', ['errors' => ['custom' => 'error']]);
    }
}
