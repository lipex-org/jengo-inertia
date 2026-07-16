<?php

/**
 * This file is part of Inertia.js Codeigniter 4.
 *
 * (c) 2023 Fab IT Hub <hello@fabithub.com>
 * (c) 2026 JengoPHP <hello@jengophp.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Jengo\Inertia;

use Closure;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Jengo\Inertia\Extras\Arr;
use Jengo\Inertia\Extras\Http;
use function Jengo\Base\vite_version;

/**
 * @psalm-api
 */
class ResponseFactory
{
    /**
     * @var array<string, mixed>
     */
    protected $sharedProps = [];

    /**
     * @var array<string>
     */
    protected $sharedKeys = [];

    /**
     * @var Closure|string|null
     */
    protected $version;

    /**
     * @param array<string, mixed>|string $key
     * @param mixed                       $value
     *
     * @psalm-api
     */
    public function share(string|array $key, $value = null): void
    {
        if (is_array($key)) {
            $this->sharedProps = array_merge($this->sharedProps, $key);
            $this->sharedKeys = array_unique(array_merge($this->sharedKeys, array_keys($key)));
        } else {
            Arr::set($this->sharedProps, $key, $value);
            $this->sharedKeys[] = $key;
        }
    }

    /**
     * @param mixed $default
     *
     * @return array<string, mixed>
     *
     * @psalm-api
     */
    public function getShared(?string $key, $default = null)
    {
        if ($key) {
            return Arr::get($this->sharedProps, $key, $default);
        }

        return $this->sharedProps;
    }

    /**
     * @return array<string>
     */
    public function getSharedKeys(): array
    {
        return $this->sharedKeys;
    }

    /**
     * @psalm-api
     */
    public function flushShared(): void
    {
        $this->sharedProps = [];
        $this->sharedKeys = [];
    }

    /**
     * @param Closure|string|null $version
     *
     * @psalm-api
     */
    public function version($version): void
    {
        $this->version = $version;
    }

    /**
     * @psalm-api
     */
    public function getVersion(): string
    {
        /** @var Config\Inertia $config */
        $config = config('Inertia');

        if (isset($config->version) && $config->version !== null) {
            return $config->version;
        }

        return (string) vite_version();
    }

    /**
     * @psalm-api
     *
     * @param array<string, mixed> $props
     */
    public function render(string $component, array $props = []): ResponseInterface
    {
        return (new Response($component, array_merge($this->sharedProps, $props), $this->getVersion()))
            ->withSharedKeys($this->sharedKeys)->getResponse();
    }

    public function lazy(Closure $callback): Props\Lazy
    {
        return new Props\Lazy($callback);
    }

    public function defer(Closure $callback, string $group = 'default'): Props\Defer
    {
        return new Props\Defer($group, $callback);
    }

    public function once(Closure $callback): Props\Once
    {
        return new Props\Once($callback);
    }

    public function merge(mixed $value): Props\Mergeable
    {
        return new Props\Mergeable($value);
    }

    public function prepend(mixed $value): Props\Mergeable
    {
        return new Props\Mergeable($value, prepend: true);
    }

    public function deepMerge(mixed $value): Props\Mergeable
    {
        return new Props\Mergeable($value, deep: true);
    }

    public function always(mixed $value): Props\Always
    {
        return new Props\Always($value);
    }

    /**
     * @psalm-api
     */
    public function location(RequestInterface|string $url): ResponseInterface
    {
        if ($url instanceof RequestInterface) {
            $url = (string) $url->getUri();
        }

        if (Http::isInertiaRequest()) {
            session()->set('_ci_previous_url', $url);

            $response = \response()->setStatusCode(\response()::HTTP_CONFLICT);

            if (str_contains($url, '#')) {
                return $response->setHeader('X-Inertia-Redirect', $url);
            }

            return $response->setHeader('X-Inertia-Location', $url);
        }

        return \redirect()->to($url, \response()::HTTP_SEE_OTHER);
    }

    /**
     * Set flashData
     * @param mixed[]|string $data
     * @param mixed $value
     * @return ResponseFactory
     */
    public function flash(array|string $data, ?string $value = null): self
    {
        session()->setFlashdata($data, $value);
        return $this;
    }

    /**
     * @param array{component: string, version: string, url: string, props: array<string, mixed>} $page
     *
     * @psalm-api
     */
    public static function init(array $page, bool $isHead = false): string
    {
        if ($isHead) {
            return Directive::compileHead($page);
        }

        return Directive::compile($page);
    }
}