<?php

declare(strict_types=1);

namespace Jengo\Inertia\Testing;

use CodeIgniter\Test\TestResponse as CITestResponse;
use PHPUnit\Framework\Assert as PHPUnit;

class TestResponse
{
    public function __construct(
        public readonly CITestResponse $response,
        protected readonly object $testCase
    ) {
    }

    /**
     * Delegate standard assertions and calls to the original response.
     */
    public function __call(string $name, array $arguments)
    {
        $result = $this->response->{$name}(...$arguments);

        if ($result instanceof CITestResponse) {
            return new self($result, $this->testCase);
        }

        return $result;
    }

    /**
     * Retrieve all props or a specific prop from the Inertia response.
     */
    public function inertiaProps(?string $key = null): mixed
    {
        $body = $this->response->response()->getBody();
        $page = null;

        if ($this->response->response()->hasHeader('X-Inertia')) {
            $page = json_decode($body, true);
        } else {
            if (preg_match('/<script[^>]*data-page="[^"]+"[^>]*>(.*?)<\/script>/s', $body, $matches)) {
                $page = json_decode(html_entity_decode($matches[1]), true);
            }
        }

        PHPUnit::assertNotNull($page, 'The response is not a valid Inertia response.');
        $props = $page['props'] ?? [];

        if ($key === null) {
            return $props;
        }

        return $this->dotGet($props, $key);
    }

    /**
     * Fluent Inertia assertions.
     */
    public function assertInertia(callable $callback): self
    {
        $body = $this->response->response()->getBody();
        $page = null;

        if ($this->response->response()->hasHeader('X-Inertia')) {
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

        // Build request runner for reload and deferred props follow-up requests
        $requestRunner = function (array $headers) use ($page) {
            $request = service('request');
            $method = strtolower($request->getMethod());
            $uri = (string) $request->getUri();

            $path = parse_url($uri, PHP_URL_PATH);
            $path = str_replace('/index.php', '', $path);
            $query = parse_url($uri, PHP_URL_QUERY);
            if ($query) {
                $path .= '?' . $query;
            }

            // Execute request
            $res = $this->testCase->callInertiaRequest($method, $path, $_POST, $headers);

            // Extract payload from follow-up response
            $body = $res->response()->getBody();
            $pageData = null;

            if ($res->response()->hasHeader('X-Inertia')) {
                $pageData = json_decode($body, true);
            } else {
                if (preg_match('/<script[^>]*data-page="[^"]+"[^>]*>(.*?)<\/script>/s', $body, $matches)) {
                    $pageData = json_decode(html_entity_decode($matches[1]), true);
                }
            }

            PHPUnit::assertNotNull($pageData, 'The follow-up response is not a valid Inertia response.');
            return $pageData;
        };

        $assertable = new AssertableInertia(
            (string) $page['component'],
            (array) $page['props'],
            (string) $page['url'],
            $page['version'] !== null ? (string) $page['version'] : null,
            (array) ($page['deferredProps'] ?? []),
            null,
            $requestRunner
        );

        $callback($assertable);
        $assertable->validateInteracted();

        return $this;
    }

    /**
     * Assert flash data exists on a redirect response and optionally matches expected value.
     */
    public function assertInertiaFlash(string $key, mixed $expected = null): self
    {
        $flash = session()->getFlashdata() ?: [];
        $actual = $this->dotGet($flash, $key);

        PHPUnit::assertNotNull($actual, "Flash data [{$key}] does not exist.");

        if ($expected !== null) {
            PHPUnit::assertEquals($expected, $actual, "Flash data [{$key}] does not match expected value.");
        }

        return $this;
    }

    /**
     * Assert flash data does not exist on a redirect response.
     */
    public function assertInertiaFlashMissing(string $key): self
    {
        $flash = session()->getFlashdata() ?: [];
        $actual = $this->dotGet($flash, $key);

        PHPUnit::assertNull($actual, "Flash data [{$key}] was expected to be missing, but exists.");

        return $this;
    }

    private function dotGet(array $array, string $key): mixed
    {
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        if (!str_contains($key, '.')) {
            return $array[$key] ?? null;
        }

        foreach (explode('.', $key) as $segment) {
            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return null;
            }
        }

        return $array;
    }
}
