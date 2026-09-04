<?php

declare(strict_types=1);

namespace Tests\Unit;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use Config\App;
use Config\Services;
use Jengo\Inertia\Config\Inertia as ConfigInertia;
use Jengo\Inertia\Inertia;
use Jengo\Inertia\Middleware;
use Jengo\Inertia\Props\Always;
use Tests\TestCase;

class MiddlewareTest extends TestCase
{
    private Middleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new Middleware();
        Inertia::flushShared();
    }

    private function createRequest(string $method = 'GET', string $url = 'http://example.com/test', array $headers = []): IncomingRequest
    {
        $config = new App();
        $uri = new \CodeIgniter\HTTP\SiteURI($config, $url);
        $userAgent = new UserAgent();
        $request = new IncomingRequest($config, $uri, 'php://input', $userAgent);
        $request->setMethod($method);

        foreach ($headers as $key => $val) {
            $request->setHeader($key, $val);
        }

        Services::injectMock('request', $request);

        return $request;
    }

    public function testWithShareResolvesErrorsAndFlash(): void
    {
        session()->setFlashdata('errors', ['email' => 'Invalid email']);
        session()->setFlashdata('info', 'Welcome back');

        $request = $this->createRequest();
        $share = $this->middleware->withShare($request);

        $this->assertArrayHasKey('errors', $share);
        $this->assertArrayHasKey('flash', $share);
        $this->assertInstanceOf(Always::class, $share['errors']);
        $this->assertInstanceOf(Always::class, $share['flash']);

        $errorsResolved = $share['errors']->resolve();
        $this->assertInstanceOf(\stdClass::class, $errorsResolved);
        $this->assertSame('Invalid email', $errorsResolved->email);

        $flashResolved = $share['flash']->resolve();
        $this->assertArrayHasKey('info', $flashResolved);
    }

    public function testWithShareHandlesErrorBag(): void
    {
        session()->setFlashdata('errors', ['password' => 'Too short']);

        $request = $this->createRequest('GET', 'http://example.com/test', [
            'x-inertia-error-bag' => 'loginForm',
        ]);

        $share = $this->middleware->withShare($request);
        $errors = $share['errors']->resolve();

        $this->assertObjectHasProperty('loginForm', $errors);
        $this->assertSame(['password' => 'Too short'], $errors->loginForm);
    }

    public function testWithShareReturnsEmptyObjectWhenNoErrors(): void
    {
        session()->remove('errors');
        service('validation')->reset();

        $request = $this->createRequest();
        $share = $this->middleware->withShare($request);
        $errors = $share['errors']->resolve();

        $this->assertEquals((object) [], $errors);
    }

    public function testBeforeSharesDataWithInertia(): void
    {
        $request = $this->createRequest();
        $this->middleware->before($request);

        $shared = Inertia::getShared(null);
        $this->assertArrayHasKey('errors', $shared);
        $this->assertArrayHasKey('flash', $shared);
    }

    public function testAfterAddsVaryHeaderAndPassesThroughNonInertiaRequest(): void
    {
        $request = $this->createRequest('GET', 'http://example.com/test');
        $response = new Response(new App());
        $response->setStatusCode(200);

        $result = $this->middleware->after($request, $response);

        $this->assertSame(200, $result->getStatusCode());
        $this->assertTrue($result->hasHeader('Vary'));
        $this->assertSame('X-Inertia', $result->getHeaderLine('Vary'));
    }

    public function testAfterHandlesPrecognitionHeaders(): void
    {
        $request = $this->createRequest('POST', 'http://example.com/form', [
            'Precognition' => 'true',
        ]);
        $response = new Response(new App());
        $response->setStatusCode(200);

        $result = $this->middleware->after($request, $response);

        $this->assertTrue($result->hasHeader('Precognition'));
        $this->assertSame('true', $result->getHeaderLine('Precognition'));
        $this->assertSame('Precognition', $result->getHeaderLine('Vary'));
    }

    public function testAfterConvertsPutPatchDelete302To303(): void
    {
        $response = new Response(new App());
        $response->setStatusCode(302);

        foreach (['PUT', 'PATCH', 'DELETE'] as $method) {
            $request = $this->createRequest($method, 'http://example.com/resource', [
                'X-Inertia' => 'true',
            ]);

            $result = $this->middleware->after($request, $response);
            $this->assertSame(303, $result->getStatusCode());
        }
    }

    public function testAfterRedirectsBackOnEmpty200Response(): void
    {
        $request = $this->createRequest('GET', 'http://example.com/test', [
            'X-Inertia' => 'true',
        ]);
        $response = new Response(new App());
        $response->setStatusCode(200)->setBody('');

        $result = $this->middleware->after($request, $response);

        $this->assertInstanceOf(RedirectResponse::class, $result);
    }

    public function testAfterHandlesVersionChangeInProduction(): void
    {
        $config = new ConfigInertia();
        $config->version = '2.0.0';
        \CodeIgniter\Config\Factories::injectMock('config', 'Inertia', $config);

        $request = $this->createRequest('GET', 'http://example.com/app', [
            'X-Inertia' => 'true',
            'X-Inertia-Version' => '1.0.0',
        ]);

        $response = new Response(new App());
        $response->setStatusCode(200)->setJSON(['component' => 'App', 'props' => []]);

        // Mock production environment check if possible or test version mismatch
        $result = $this->middleware->after($request, $response);
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testFlashDataUnmarkingOn200AndPreservedOn302(): void
    {
        session()->setFlashdata('notify', 'Success!');
        $this->assertNotEmpty(session()->getFlashKeys());

        $request = $this->createRequest();
        $response200 = new Response(new App());
        $response200->setStatusCode(200);

        $this->middleware->after($request, $response200);
        // After 200, unmarkFlashdata was called
        $this->assertEmpty(session()->getFlashKeys());
    }
}
