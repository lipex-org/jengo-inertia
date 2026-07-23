<?php

declare(strict_types=1);

namespace Jengo\Inertia\Testing;

use PHPUnit\Framework\Assert as PHPUnit;

final class AssertableInertia
{
    private bool $interactedAll = false;
    private array $interactedKeys = [];

    public function __construct(
        public readonly string $component,
        public readonly array $props,
        public readonly string $url,
        public readonly ?string $version = null,
        public readonly array $deferredProps = [],
        private ?array $flashData = null,
        private ?\Closure $requestRunner = null
    ) {
    }

    /**
     * Assert that the component name matches.
     */
    public function component(string $value, bool $shouldMatchExactly = true): self
    {
        if ($shouldMatchExactly) {
            PHPUnit::assertSame($value, $this->component, "Expected Inertia component to be [{$value}], got [{$this->component}] instead.");
        } else {
            PHPUnit::assertStringContainsString($value, $this->component, "Expected Inertia component to contain [{$value}], got [{$this->component}] instead.");
        }

        return $this;
    }

    /**
     * Assert that a prop key exists.
     * Supports existence check, array count check, and nested scoping callback.
     */
    public function has(string $key, mixed ...$args): self
    {
        $parts = explode('.', $key);
        $this->interactedKeys[] = $parts[0];

        $actual = $this->dotGet($this->props, $key);
        PHPUnit::assertNotNull($actual, "Inertia prop [{$key}] does not exist.");

        $count = null;
        $callback = null;

        foreach ($args as $arg) {
            if (is_int($arg)) {
                $count = $arg;
            } elseif (is_callable($arg)) {
                $callback = $arg;
            }
        }

        if ($count !== null) {
            PHPUnit::assertIsArray($actual, "Inertia prop [{$key}] is not an array.");
            PHPUnit::assertCount($count, $actual, "Expected array count of [{$key}] to be [{$count}], got [" . count($actual) . "] instead.");
        }

        if ($callback !== null) {
            $scopeData = $actual;
            if (is_array($actual) && array_is_list($actual) && !empty($actual)) {
                $scopeData = $actual[0];
            }

            PHPUnit::assertIsArray($scopeData, "Cannot scope assertions into non-array property [{$key}].");

            $scope = new AssertableInertia(
                $this->component,
                $scopeData,
                $this->url,
                $this->version,
                $this->deferredProps,
                $this->flashData,
                $this->requestRunner
            );
            $callback($scope);
            $scope->validateInteracted();
        }

        return $this;
    }

    /**
     * Assert that a prop key has a specific count (for arrays).
     */
    public function count(string $key, int $expectedCount): self
    {
        $parts = explode('.', $key);
        $this->interactedKeys[] = $parts[0];

        $actual = $this->dotGet($this->props, $key);
        PHPUnit::assertIsArray($actual, "Inertia prop [{$key}] is not an array.");
        PHPUnit::assertCount($expectedCount, $actual, "Expected array count of [{$key}] to be [{$expectedCount}], got [" . count($actual) . "] instead.");

        return $this;
    }

    /**
     * Assert that a prop matches a specific value.
     */
    public function where(string $key, mixed $expected): self
    {
        $parts = explode('.', $key);
        $this->interactedKeys[] = $parts[0];

        $actual = $this->dotGet($this->props, $key);
        PHPUnit::assertEquals($expected, $actual, "Inertia prop [{$key}] does not match expected value.");

        return $this;
    }

    /**
     * Assert that a prop does not exist.
     */
    public function missing(string $key): self
    {
        $parts = explode('.', $key);
        $this->interactedKeys[] = $parts[0];

        $actual = $this->dotGet($this->props, $key);
        PHPUnit::assertNull($actual, "Inertia prop [{$key}] was expected to be missing, but exists.");

        return $this;
    }

    /**
     * Tell the validator to ignore un-interacted keys.
     */
    public function etc(): self
    {
        $this->interactedAll = true;
        return $this;
    }

    /**
     * Run partial reload for the specified keys.
     */
    public function reloadOnly(string|array $keys, callable $callback): self
    {
        PHPUnit::assertNotNull($this->requestRunner, 'Cannot perform partial reload without request runner.');

        $keys = is_array($keys) ? $keys : [$keys];
        $headers = [
            'X-Inertia' => 'true',
            'X-Inertia-Partial-Component' => $this->component,
            'X-Inertia-Partial-Only' => implode(',', $keys),
        ];

        $pageData = ($this->requestRunner)($headers);

        $reloadScope = new AssertableInertia(
            (string) $pageData['component'],
            (array) $pageData['props'],
            (string) $pageData['url'],
            $pageData['version'] !== null ? (string) $pageData['version'] : null,
            (array) ($pageData['deferredProps'] ?? []),
            $this->flashData,
            $this->requestRunner
        );

        $callback($reloadScope);
        $reloadScope->validateInteracted();

        return $this;
    }

    /**
     * Run partial reload excluding the specified keys.
     */
    public function reloadExcept(string|array $keys, callable $callback): self
    {
        PHPUnit::assertNotNull($this->requestRunner, 'Cannot perform partial reload without request runner.');

        $keys = is_array($keys) ? $keys : [$keys];
        $headers = [
            'X-Inertia' => 'true',
            'X-Inertia-Partial-Component' => $this->component,
            'X-Inertia-Partial-Except' => implode(',', $keys),
        ];

        $pageData = ($this->requestRunner)($headers);

        $reloadScope = new AssertableInertia(
            (string) $pageData['component'],
            (array) $pageData['props'],
            (string) $pageData['url'],
            $pageData['version'] !== null ? (string) $pageData['version'] : null,
            (array) ($pageData['deferredProps'] ?? []),
            $this->flashData,
            $this->requestRunner
        );

        $callback($reloadScope);
        $reloadScope->validateInteracted();

        return $this;
    }

    /**
     * Run partial reload to resolve deferred properties.
     */
    public function loadDeferredProps(mixed $groupsOrCallback, ?callable $callback = null): self
    {
        PHPUnit::assertNotNull($this->requestRunner, 'Cannot load deferred props without request runner.');

        $groups = [];
        $actualCallback = null;

        if (is_callable($groupsOrCallback)) {
            $actualCallback = $groupsOrCallback;
            $groups = array_keys($this->deferredProps);
        } else {
            $groups = is_array($groupsOrCallback) ? $groupsOrCallback : [$groupsOrCallback];
            $actualCallback = $callback;
        }

        PHPUnit::assertNotNull($actualCallback, 'Deferred props loader requires a callback.');

        // Collect keys to reload
        $keys = [];
        foreach ($groups as $group) {
            if (isset($this->deferredProps[$group])) {
                $keys = array_merge($keys, $this->deferredProps[$group]);
            }
        }

        if (empty($keys)) {
            $actualCallback($this);
            return $this;
        }

        $headers = [
            'X-Inertia' => 'true',
            'X-Inertia-Partial-Component' => $this->component,
            'X-Inertia-Partial-Only' => implode(',', $keys),
        ];

        $pageData = ($this->requestRunner)($headers);

        $reloadScope = new AssertableInertia(
            (string) $pageData['component'],
            (array) $pageData['props'],
            (string) $pageData['url'],
            $pageData['version'] !== null ? (string) $pageData['version'] : null,
            (array) ($pageData['deferredProps'] ?? []),
            $this->flashData,
            $this->requestRunner
        );

        $actualCallback($reloadScope);
        $reloadScope->validateInteracted();

        return $this;
    }

    /**
     * Check if a flash data key exists and optionally matches expected value.
     */
    public function hasFlash(string $key, mixed $expected = null): self
    {
        if ($this->flashData === null) {
            $this->flashData = session()->getFlashdata() ?: [];
        }

        $actual = $this->dotGet($this->flashData, $key);
        PHPUnit::assertNotNull($actual, "Flash data [{$key}] does not exist.");

        if ($expected !== null) {
            PHPUnit::assertEquals($expected, $actual, "Flash data [{$key}] does not match expected value.");
        }

        return $this;
    }

    /**
     * Check that a flash data key does not exist.
     */
    public function missingFlash(string $key): self
    {
        if ($this->flashData === null) {
            $this->flashData = session()->getFlashdata() ?: [];
        }

        $actual = $this->dotGet($this->flashData, $key);
        PHPUnit::assertNull($actual, "Flash data [{$key}] was expected to be missing, but exists.");

        return $this;
    }

    /**
     * Assert that every key in the scope was interacted with.
     */
    public function validateInteracted(): void
    {
        if ($this->interactedAll) {
            return;
        }

        $allKeys = array_keys($this->props);
        $diff = array_diff($allKeys, $this->interactedKeys);

        if (!empty($diff)) {
            PHPUnit::fail(
                sprintf(
                    "Inertia scope has unexpected properties: [%s]. Use etc() to ignore them.",
                    implode(', ', $diff)
                )
            );
        }
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
