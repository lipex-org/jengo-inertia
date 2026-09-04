<?php

declare(strict_types=1);

namespace Tests\Unit;

use CodeIgniter\HTTP\CURLRequest;
use CodeIgniter\HTTP\Response as HTTPResponse;
use Config\App;
use Config\Services;
use Jengo\Inertia\Config\Inertia as ConfigInertia;
use Jengo\Inertia\Directive;
use Jengo\Inertia\Ssr\HttpGateway;
use Jengo\Inertia\Ssr\Response as SsrResponse;
use Tests\TestCase;

class SsrTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        $ref = new \ReflectionClass(Directive::class);
        $prop = $ref->getProperty('__inertiaSsr');
        $prop->setAccessible(true);
        $prop->setValue(null, null);
    }

    public function testSsrResponseDto(): void
    {
        $response = new SsrResponse('<title>Hello</title>', '<div>App</div>');
        $this->assertSame('<title>Hello</title>', $response->head);
        $this->assertSame('<div>App</div>', $response->body);
    }

    public function testHttpGatewayDisabledReturnsNull(): void
    {
        $config = new ConfigInertia();
        $config->isSsrEnabled = false;
        \CodeIgniter\Config\Factories::injectMock('config', 'Inertia', $config);

        $gateway = new HttpGateway();
        $result = $gateway->dispatch(['component' => 'Home', 'props' => []]);

        $this->assertNull($result);
    }

    public function testHttpGatewayDispatchSuccess(): void
    {
        $config = new ConfigInertia();
        $config->isSsrEnabled = true;
        $config->ssrUrl = 'http://127.0.0.1:13714';
        \CodeIgniter\Config\Factories::injectMock('config', 'Inertia', $config);

        $mockHttp = new HTTPResponse(new App());
        $mockHttp->setBody(json_encode([
            'head' => ['<title>SSR Page</title>', '<meta name="description" content="test">'],
            'body' => '<div id="app" data-server-rendered="true"><h1>SSR Content</h1></div>',
        ]));

        $mockClient = $this->createMock(CURLRequest::class);
        $mockClient->method('setJSON')->willReturnSelf();
        $mockClient->method('post')->willReturn($mockHttp);

        Services::injectMock('curlRequest', $mockClient);

        $gateway = new HttpGateway();
        $result = $gateway->dispatch(['component' => 'Home', 'props' => []]);

        $this->assertInstanceOf(SsrResponse::class, $result);
        $this->assertStringContainsString('<title>SSR Page</title>', $result->head);
        $this->assertStringContainsString('SSR Content', $result->body);
    }

    public function testHttpGatewayHandlesExceptionsGracefully(): void
    {
        $config = new ConfigInertia();
        $config->isSsrEnabled = true;
        \CodeIgniter\Config\Factories::injectMock('config', 'Inertia', $config);

        $mockClient = $this->createMock(CURLRequest::class);
        $mockClient->method('setJSON')->willReturnSelf();
        $mockClient->method('post')->willThrowException(new \RuntimeException('Connection refused'));

        Services::injectMock('curlRequest', $mockClient);

        $gateway = new HttpGateway();
        $result = $gateway->dispatch(['component' => 'Home', 'props' => []]);

        $this->assertNull($result);
    }

    public function testHttpGatewayReturnsNullWhenResponseIsInvalid(): void
    {
        $config = new ConfigInertia();
        $config->isSsrEnabled = true;
        \CodeIgniter\Config\Factories::injectMock('config', 'Inertia', $config);

        $mockHttp = new HTTPResponse(new App());
        $mockHttp->setBody('not valid json');

        $mockClient = $this->createMock(CURLRequest::class);
        $mockClient->method('setJSON')->willReturnSelf();
        $mockClient->method('post')->willReturn($mockHttp);

        Services::injectMock('curlRequest', $mockClient);

        $gateway = new HttpGateway();
        $this->assertNull($gateway->dispatch(['component' => 'Home', 'props' => []]));
    }

    public function testDirectiveCompileWithoutSsr(): void
    {
        $config = new ConfigInertia();
        $config->isSsrEnabled = false;
        \CodeIgniter\Config\Factories::injectMock('config', 'Inertia', $config);

        $page = [
            'component' => 'Users/Index',
            'props'     => ['count' => 10],
            'url'       => '/users',
            'version'   => '1.0',
        ];

        $compiled = Directive::compile($page, 'custom-app');
        $this->assertStringContainsString('data-page="app"', $compiled);
        $this->assertStringContainsString('<div id="custom-app"></div>', $compiled);

        $head = Directive::compileHead($page);
        $this->assertSame('', $head);
    }

    public function testDirectiveCompileWithSsr(): void
    {
        $config = new ConfigInertia();
        $config->isSsrEnabled = true;
        \CodeIgniter\Config\Factories::injectMock('config', 'Inertia', $config);

        $mockGateway = new class implements \Jengo\Inertia\Extras\Gateway {
            public function dispatch(array $page): ?SsrResponse
            {
                return new SsrResponse('<title>SSR Title</title>', '<div id="ssr-rendered">Content</div>');
            }
        };

        \Jengo\Inertia\Config\Services::injectMock('httpGateway', $mockGateway);

        $page = [
            'component' => 'About',
            'props'     => [],
            'url'       => '/about',
            'version'   => '1.0',
        ];

        $compiledBody = Directive::compile($page);
        $this->assertSame('<div id="ssr-rendered">Content</div>', $compiledBody);

        $compiledHead = Directive::compileHead($page);
        $this->assertSame('<title>SSR Title</title>', $compiledHead);
    }
}
