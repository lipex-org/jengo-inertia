<?php

declare(strict_types=1);

namespace Jengo\Inertia\Testing;

use PHPUnit\Framework\Assert as PHPUnit;

trait InertiaAssertions
{
    /**
     * Public proxy to run protected HTTP requests from other classes (e.g. TestResponse).
     */
    public function callInertiaRequest(string $method, string $path, array $data = [], array $headers = []): TestResponse
    {
        if (!empty($headers)) {
            $this->withHeaders($headers);
        }

        $method = strtolower($method);
        if ($method === 'get') {
            return $this->get($path);
        }

        return $this->{$method}($path, $data);
    }

    /**
     * Assert that the response is a valid Inertia response.
     */
    protected function assertInertia(mixed $response, ?callable $callback = null): void
    {
        $wrapped = $response instanceof TestResponse ? $response : new TestResponse($response, $this);

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
     * Override standard HTTP GET method to return Jengo's custom TestResponse wrapper.
     */
    protected function get(string $path, array $headers = []): TestResponse
    {
        return new TestResponse($this->parentGet($path, $headers), $this);
    }

    /**
     * Override standard HTTP POST method.
     */
    protected function post(string $path, array $data = [], array $headers = []): TestResponse
    {
        return new TestResponse($this->parentPost($path, $data, $headers), $this);
    }

    /**
     * Override standard HTTP PUT method.
     */
    protected function put(string $path, array $data = [], array $headers = []): TestResponse
    {
        return new TestResponse($this->parentPut($path, $data, $headers), $this);
    }

    /**
     * Override standard HTTP PATCH method.
     */
    protected function patch(string $path, array $data = [], array $headers = []): TestResponse
    {
        return new TestResponse($this->parentPatch($path, $data, $headers), $this);
    }

    /**
     * Override standard HTTP DELETE method.
     */
    protected function delete(string $path, array $data = [], array $headers = []): TestResponse
    {
        return new TestResponse($this->parentDelete($path, $data, $headers), $this);
    }
}
