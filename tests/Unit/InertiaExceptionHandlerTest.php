<?php

declare(strict_types=1);

/**
 * This file is part of Inertia.js Codeigniter 4.
 *
 * (c) 2026 JengoPHP <hello@jengophp.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests\Unit;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\SiteURI;
use CodeIgniter\HTTP\UserAgent;
use Config\App;
use Config\Exceptions as ExceptionsConfig;
use Config\Services;
use Jengo\Inertia\Config\Inertia as InertiaConfig;
use Jengo\Inertia\Debug\InertiaExceptionHandler;
use Jengo\Inertia\Exceptions\InertiaExceptionHandler as InertiaExceptionHandlerAlias;
use Jengo\Inertia\Inertia;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;
use Tests\TestCase;

class InertiaExceptionHandlerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Inertia::flushShared();
    }

    private function createIncomingRequest(
        string $method = 'GET',
        string $url = 'http://example.com/test',
        array $headers = []
    ): IncomingRequest {
        $config = new App();
        $uri = new SiteURI($config, $url);
        $userAgent = new UserAgent();
        $request = new IncomingRequest($config, $uri, 'php://input', $userAgent);
        $request->setMethod($method);

        foreach ($headers as $name => $value) {
            $request->setHeader($name, $value);
        }

        Services::injectMock('request', $request);

        return $request;
    }

    private function createResponse(): Response
    {
        $response = new Response(new App());
        Services::injectMock('response', $response);

        return $response;
    }

    public function testConfigResolveErrorPage(): void
    {
        $config = new InertiaConfig();

        // Matches by integer status code in $errorPages
        $this->assertSame('error', $config->resolveErrorPage(404));
        $this->assertSame('error', $config->resolveErrorPage(500));

        // Matches by string key
        $config->errorPages['404'] = 'errors/404';
        $this->assertSame('errors/404', $config->resolveErrorPage(404));

        // Custom error pages
        $config->errorPages = [
            403 => 'errors/403',
            'error' => 'errors/generic',
        ];
        $this->assertSame('errors/403', $config->resolveErrorPage(403));
        $this->assertSame('errors/generic', $config->resolveErrorPage(500));

        // Matches in $pages property
        $config->errorPages = [];
        $config->pages = [
            404 => 'Pages/NotFound',
            'default' => 'Pages/Fallback',
        ];
        $this->assertSame('Pages/NotFound', $config->resolveErrorPage(404));
        $this->assertSame('Pages/Fallback', $config->resolveErrorPage(500));

        // Null when nothing matches
        $config->pages = [];
        $this->assertNull($config->resolveErrorPage(404));
    }

    public function testHandleInertiaRequestRendersInertiaJsonResponse(): void
    {
        $request = $this->createIncomingRequest('GET', 'http://example.com/not-found', [
            'X-Inertia' => 'true',
            'Accept' => 'text/html, application/xhtml+xml',
        ]);
        $response = $this->createResponse();

        $exceptionsConfig = new ExceptionsConfig();
        $inertiaConfig = new InertiaConfig();
        $inertiaConfig->errorPages = [404 => 'errors/404'];

        $handler = new InertiaExceptionHandler($exceptionsConfig, $inertiaConfig);
        $exception = new PageNotFoundException('Custom route not found');

        $handler->handle($exception, $request, $response, 404, 1);

        $handledResponse = $handler->getHandledResponse();
        $this->assertNotNull($handledResponse);
        $this->assertSame(404, $handledResponse->getStatusCode());
        $this->assertTrue($handledResponse->hasHeader('X-Inertia'));

        $body = json_decode((string) $handledResponse->getBody(), true);
        $this->assertIsArray($body);
        $this->assertSame('errors/404', $body['component']);
        $this->assertSame(404, $body['props']['status']);
        $this->assertSame('Custom route not found', $body['props']['message']);
    }

    public function testHandleHtmlRequestRendersInertiaHtmlResponse(): void
    {
        $request = $this->createIncomingRequest('GET', 'http://example.com/server-error', [
            'Accept' => 'text/html',
        ]);
        $response = $this->createResponse();

        $exceptionsConfig = new ExceptionsConfig();
        $inertiaConfig = new InertiaConfig();
        $inertiaConfig->showDebugInDevelopment = false;
        $inertiaConfig->errorPages = [500 => 'errors/500'];

        $handler = new InertiaExceptionHandler($exceptionsConfig, $inertiaConfig);
        $exception = new RuntimeException('Database unreachable');

        $handler->handle($exception, $request, $response, 500, 1);

        $handledResponse = $handler->getHandledResponse();
        $this->assertNotNull($handledResponse);
        $this->assertSame(500, $handledResponse->getStatusCode());
        $this->assertStringContainsString('text/html', $handledResponse->getHeaderLine('Content-Type'));

        $body = (string) $handledResponse->getBody();
        $this->assertStringContainsString('<div id="app" data-page=', $body);
        $this->assertStringContainsString('errors/500', $body);
    }

    public function testHandleNonHtmlApiRequestRendersJson(): void
    {
        $request = $this->createIncomingRequest('POST', 'http://example.com/api/items', [
            'Accept' => 'application/json',
        ]);
        $response = $this->createResponse();

        $exceptionsConfig = new ExceptionsConfig();
        $inertiaConfig = new InertiaConfig();

        $handler = new InertiaExceptionHandler($exceptionsConfig, $inertiaConfig);
        $exception = new RuntimeException('Invalid payload');

        $handler->handle($exception, $request, $response, 400, 1);

        $handledResponse = $handler->getHandledResponse();
        $this->assertNotNull($handledResponse);
        $this->assertSame(400, $handledResponse->getStatusCode());
    }

    public function testHandleCliRequestRendersCliError(): void
    {
        $config = new App();
        $cliRequest = new CLIRequest($config);
        $response = $this->createResponse();

        $exceptionsConfig = new ExceptionsConfig();
        $handler = new InertiaExceptionHandler($exceptionsConfig);
        $exception = new RuntimeException('CLI failure');

        ob_start();
        $handler->handle($exception, $cliRequest, $response, 500, 1);
        $output = ob_get_clean();

        $this->assertIsString($output);
    }

    public function testInvalidStatusCodeWorkaround(): void
    {
        $request = $this->createIncomingRequest('GET', 'http://example.com/invalid-status', [
            'X-Inertia' => 'true',
            'Accept' => 'text/html',
        ]);
        $response = $this->createResponse();

        $exceptionsConfig = new ExceptionsConfig();
        $inertiaConfig = new InertiaConfig();
        $inertiaConfig->errorPages = [500 => 'error'];

        $handler = new InertiaExceptionHandler($exceptionsConfig, $inertiaConfig);
        $exception = new RuntimeException('Invalid status code thrown');

        $handler->handle($exception, $request, $response, 9999, 1);

        $handledResponse = $handler->getHandledResponse();
        $this->assertNotNull($handledResponse);
        $this->assertSame(500, $handledResponse->getStatusCode());
    }

    public function testDisplayErrorsIncludesExceptionVarsInProps(): void
    {
        $request = $this->createIncomingRequest('GET', 'http://example.com/debug-error', [
            'X-Inertia' => 'true',
            'Accept' => 'text/html',
        ]);
        $response = $this->createResponse();

        $exceptionsConfig = new ExceptionsConfig();
        $inertiaConfig = new InertiaConfig();
        $inertiaConfig->showDebugInDevelopment = false;
        $inertiaConfig->errorPages = [500 => 'error'];

        $handler = new class($exceptionsConfig, $inertiaConfig) extends InertiaExceptionHandler {
            protected function isDisplayErrorsEnabled(): bool
            {
                return true;
            }
        };

        $exception = new RuntimeException('Debug error message');
        $handler->handle($exception, $request, $response, 500, 1);

        $handledResponse = $handler->getHandledResponse();
        $this->assertNotNull($handledResponse);

        $body = json_decode((string) $handledResponse->getBody(), true);
        $this->assertArrayHasKey('exception', $body['props']);
        $this->assertSame('Debug error message', $body['props']['exception']['message']);
        $this->assertSame(RuntimeException::class, $body['props']['exception']['type']);
    }

    public function testShowDebugInDevelopmentRendersDebugViewFor500(): void
    {
        $request = $this->createIncomingRequest('GET', 'http://example.com/debug-500', [
            'Accept' => 'text/html',
        ]);
        $response = $this->createResponse();

        $exceptionsConfig = new ExceptionsConfig();
        $inertiaConfig = new InertiaConfig();
        $inertiaConfig->showDebugInDevelopment = true;
        $inertiaConfig->errorPages = [500 => 'error'];

        $handler = new class($exceptionsConfig, $inertiaConfig) extends InertiaExceptionHandler {
            protected function isDisplayErrorsEnabled(): bool
            {
                return true;
            }
        };

        $exception = new RuntimeException('Crash in development');

        ob_start();
        $handler->handle($exception, $request, $response, 500, 1);
        $output = ob_get_clean();

        $this->assertStringContainsString('RuntimeException', $output);
    }

    public function testFallbackWhenNoInertiaComponentMatched(): void
    {
        $request = $this->createIncomingRequest('GET', 'http://example.com/no-match', [
            'Accept' => 'text/html',
        ]);
        $response = $this->createResponse();

        $exceptionsConfig = new ExceptionsConfig();
        $inertiaConfig = new InertiaConfig();
        $inertiaConfig->errorPages = [];
        $inertiaConfig->pages = [];

        $handler = new InertiaExceptionHandler($exceptionsConfig, $inertiaConfig);
        $exception = new PageNotFoundException('Missing page');

        ob_start();
        $handler->handle($exception, $request, $response, 404, 1);
        $output = ob_get_clean();

        $this->assertStringContainsString('404', $output);
    }

    public function testSanitizeDataRemovesNonJsonValues(): void
    {
        $exceptionsConfig = new ExceptionsConfig();
        $handler = new InertiaExceptionHandler($exceptionsConfig);

        $method = new ReflectionMethod($handler, 'sanitizeData');
        $method->setAccessible(true);

        $closure = fn () => 'hello';
        $resource = fopen('php://memory', 'r');

        $obj = new \stdClass();
        $obj->name = 'Jengo';
        $obj->self = $obj;

        $input = [
            'string' => 'valid',
            'closure' => $closure,
            'resource' => $resource,
            'object' => $obj,
            'nested' => [
                'int' => 42,
            ],
        ];

        $sanitized = $method->invoke($handler, $input);
        fclose($resource);

        $this->assertSame('valid', $sanitized['string']);
        $this->assertSame('[Closure]', $sanitized['closure']);
        $this->assertStringStartsWith('[Resource #', $sanitized['resource']);
        $this->assertSame(42, $sanitized['nested']['int']);
        $this->assertSame('Jengo', $sanitized['object']['name']);
        $this->assertStringContainsString('RECURSION', $sanitized['object']['self']);
    }

    public function testRenderInertiaWithCustomViewFile(): void
    {
        $viewsDir = APPPATH . 'Views';
        if (!is_dir($viewsDir)) {
            mkdir($viewsDir, 0777, true);
        }
        $customAppView = $viewsDir . '/custom_app.php';
        file_put_contents($customAppView, '<!DOCTYPE html><html><body><?= $page["component"] ?></body></html>');

        $request = $this->createIncomingRequest('GET', 'http://example.com/custom-view', [
            'Accept' => 'text/html',
        ]);
        $response = $this->createResponse();

        $exceptionsConfig = new ExceptionsConfig();
        $inertiaConfig = new InertiaConfig();
        $inertiaConfig->rootView = 'custom_app';
        $inertiaConfig->errorPages = [404 => 'errors/404'];

        $handler = new InertiaExceptionHandler($exceptionsConfig, $inertiaConfig);
        $exception = new PageNotFoundException('Custom route not found');

        $handler->handle($exception, $request, $response, 404, 1);

        $handledResponse = $handler->getHandledResponse();
        $this->assertNotNull($handledResponse);
        $this->assertStringContainsString('errors/404', (string) $handledResponse->getBody());

        @unlink($customAppView);
    }

    public function testDetermineViewReturnsCustomStatusCodeView(): void
    {
        $exceptionsConfig = new ExceptionsConfig();
        $handler = new InertiaExceptionHandler($exceptionsConfig);
        $ref = new ReflectionMethod($handler, 'determineView');
        $ref->setAccessible(true);

        $tempDir = sys_get_temp_dir() . '/ci_test_errors_' . uniqid();
        mkdir($tempDir, 0777, true);
        file_put_contents($tempDir . '/error_418.php', 'Teapot');

        $view = $ref->invoke($handler, new \Exception('Teapot'), $tempDir, 418);
        $this->assertSame('error_418.php', $view);

        @unlink($tempDir . '/error_418.php');
        @rmdir($tempDir);
    }

    public function testExceptionsAliasClass(): void
    {
        $exceptionsConfig = new ExceptionsConfig();
        $aliasHandler = new InertiaExceptionHandlerAlias($exceptionsConfig);
        $this->assertInstanceOf(InertiaExceptionHandler::class, $aliasHandler);
    }
}
