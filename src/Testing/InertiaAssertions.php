<?php

declare(strict_types=1);

namespace Jengo\Inertia\Testing;

use PHPUnit\Framework\Assert as PHPUnit;

trait InertiaAssertions
{
    /**
     * Public proxy to run HTTP requests for follow-up testing (e.g. reload and deferred props).
     */
    public function callInertiaRequest(string $method, string $path, array $data = [], array $headers = []): TestResponse
    {
        if (!empty($headers) && method_exists($this, 'withHeaders')) {
            $this->withHeaders($headers);
        }

        $method = strtolower($method);
        $result = $this->{$method}($path, $data);

        return InertiaTestHelper::wrap($result, $this);
    }

    /**
     * Assert that the response is a valid Inertia response.
     */
    protected function assertInertia(mixed $response, ?callable $callback = null): void
    {
        $wrapped = InertiaTestHelper::wrap($response, $this);

        if ($callback !== null) {
            $wrapped->assertInertia($callback);
            return;
        }

        $body = $wrapped->response->response()->getBody();
        $page = null;

        if ($wrapped->response->response()->hasHeader('X-Inertia')) {
            $page = json_decode($body, true);
        } else {
            if (preg_match('/<script[^>]*data-page="[^"]+"[^>]*>(.*?)<\/script>/s', $body, $matches)) {
                $page = json_decode(html_entity_decode($matches[1]), true);
            }
        }

        PHPUnit::assertNotNull($page, 'The response is not a valid Inertia response.');
        PHPUnit::assertArrayHasKey('component', $page, 'The Inertia response is missing the "component" key.');
        PHPUnit::assertArrayHasKey('props', $page, 'The Inertia response is missing the "props" key.');
        PHPUnit::assertArrayHasKey('url', $page, 'The Inertia response is missing the "url" key.');
        PHPUnit::assertArrayHasKey('version', $page, 'The Inertia response is missing the "version" key.');
    }

    /**
     * Wrap a test response in an Inertia TestResponse wrapper for fluent assertions.
     */
    protected function inertia(mixed $response): TestResponse
    {
        return InertiaTestHelper::wrap($response, $this);
    }
}
