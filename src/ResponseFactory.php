<?php

/**
 * This file is part of Inertia.js Codeigniter 4.
 *
 * (c) 2023 Fab IT Hub <hello@fabithub.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Jengo\Inertia;

use Closure;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Inertia\Protocol\PageObject;
use Inertia\Protocol\ProtocolVersion;
use Inertia\Protocol\Ssr\SsrGatewayInterface;
use Inertia\Protocol\Support\Arr;
use Inertia\Protocol\Support\InertiaHeaders;
use InvalidArgumentException;
use Jengo\Base\Container\Container;
use Jengo\Inertia\Config\Services;

use function Jengo\Base\vite_version;

/**
 * @psalm-api
 */
class ResponseFactory
{
    protected const array SYSTEM_PROP_KEYS = [
        'errors',
        'flash',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $sharedProps = [];

    /**
     * @var list<string>
     */
    protected $sharedKeys = [];

    protected ?Config\Inertia $config = null;

    /**
     * @var Closure|string|null
     */
    protected $version;

    public function setConfig(Config\Inertia $config): self
    {
        $this->config = $config;

        return $this;
    }

    public function getConfig(): Config\Inertia
    {
        return $this->config ?? config('Inertia') ?? new Config\Inertia();
    }

    /**
     * @param array<string, mixed>|string $key
     * @param mixed                       $value
     *
     * @psalm-api
     */
    public function share(array|string $key, $value = null): void
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
     * @return list<string>
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
        $this->validateUserProps($props);

        /** @var Config\Inertia $config */
        $config = $this->getConfig();

        if (!empty($config->pages['ensure_page_exists']) && !$this->pageExists($component)) {
            throw PageNotFoundException::forPageNotFound(
                "Inertia page component [{$component}] not found on disk.",
            );
        }

        // resolve errors and flashdata
        $this->withValidationErrors()->withFlashData();

        $errors = $this->sharedProps['errors'] ?? [];
        $flash = $this->sharedProps['flash'] ?? [];

        // Exclude reserved errors and flash from user props to prevent ReservedPropException
        $userSharedProps = $this->sharedProps;
        unset($userSharedProps['errors'], $userSharedProps['flash']);

        $userSharedKeys = array_values(array_filter($this->sharedKeys, static fn ($k) => !in_array($k, ['errors', 'flash'], true)));

        return (new Response($component, array_merge($userSharedProps, $props), $this->getVersion()))
            ->withSharedKeys($userSharedKeys)
            ->withErrors($errors)
            ->withFlash($flash)
            ->withProtocolVersion($config->protocolVersion ?? '3.0')
            ->preserveBigIntegers(!empty($config->preserveBigIntegers))
            ->getResponse();
    }

    /**
     * Check if an Inertia page component exists on disk.
     *
     * @param string                  $component   Component name (e.g. 'Users/Index' or 'Home')
     * @param array<int, string>|null $customPaths Optional paths override
     */
    public function pageExists(string $component, ?array $customPaths = null): bool
    {
        /** @var Config\Inertia $config */
        $config = $this->getConfig();

        $paths = $customPaths ?? (array) ($config->pages['pages'] ?? []);
        $extensions = (array) ($config->pages['extensions'] ?? ['vue', 'jsx', 'tsx', 'svelte', 'js', 'ts']);

        // Normalize component name and directory separators
        $cleanComponent = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, trim($component));
        $cleanComponent = ltrim($cleanComponent, DIRECTORY_SEPARATOR);

        if ($cleanComponent === '') {
            return false;
        }

        // Derive component variants: e.g. "users/index" -> "Users/Index"
        $parts = explode(DIRECTORY_SEPARATOR, $cleanComponent);
        $pascalParts = array_map('ucfirst', $parts);
        $pascalComponent = implode(DIRECTORY_SEPARATOR, $pascalParts);

        $variants = array_unique([
            $cleanComponent,
            $pascalComponent,
        ]);

        foreach ($paths as $basePath) {
            $base = rtrim($basePath, '\\/');

            if (!str_starts_with($base, '/') && !preg_match('/^[A-Za-z]:[\\\\\/]/', $base)) {
                $root = defined('ROOTPATH') ? rtrim(ROOTPATH, '\\/') : getcwd();
                $base = $root . DIRECTORY_SEPARATOR . $base;
            }

            if (!is_dir($base)) {
                continue;
            }

            foreach ($variants as $variant) {
                // 1. Direct file match (if variant already includes extension)
                $directPath = $base . DIRECTORY_SEPARATOR . $variant;
                if (is_file($directPath)) {
                    return true;
                }

                // 2. Extension match (e.g. Component.vue, Component.jsx)
                foreach ($extensions as $ext) {
                    $ext = ltrim($ext, '.');
                    $filePath = $base . DIRECTORY_SEPARATOR . $variant . '.' . $ext;
                    if (is_file($filePath)) {
                        return true;
                    }

                    // 3. Index file within component folder (e.g. Users -> Users/Index.vue, Users/index.tsx)
                    $indexPath = $base . DIRECTORY_SEPARATOR . $variant . DIRECTORY_SEPARATOR . 'index.' . $ext;
                    if (is_file($indexPath)) {
                        return true;
                    }

                    $indexUpperPath = $base . DIRECTORY_SEPARATOR . $variant . DIRECTORY_SEPARATOR . 'Index.' . $ext;
                    if (is_file($indexUpperPath)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function lazy(mixed $callback): Props\Lazy
    {
        $resolved = $callback instanceof Closure
            ? static fn () => Container::getInstance()->call($callback)
            : $callback;

        return new Props\Lazy($resolved);
    }

    public function optional(mixed $callback): Props\Optional
    {
        $resolved = $callback instanceof Closure
            ? static fn () => Container::getInstance()->call($callback)
            : $callback;

        return new Props\Optional($resolved);
    }

    public function defer(mixed $callback, string $group = 'default', bool $rescue = false): Props\Defer
    {
        $resolved = $callback instanceof Closure
            ? static fn () => Container::getInstance()->call($callback)
            : $callback;

        return new Props\Defer($group, $resolved, $rescue);
    }

    public function once(mixed $callback, ?int $expiresAt = null): Props\Once
    {
        $resolved = $callback instanceof Closure
            ? static fn () => Container::getInstance()->call($callback)
            : $callback;

        return new Props\Once($resolved, $expiresAt);
    }

    public function merge(mixed $value, ?string $matchOn = null): Props\Mergeable
    {
        return new Props\Mergeable(value: $value, deep: false, prepend: false, matchOn: $matchOn);
    }

    public function prepend(mixed $value, ?string $matchOn = null): Props\Mergeable
    {
        return new Props\Mergeable(value: $value, deep: false, prepend: true, matchOn: $matchOn);
    }

    public function deepMerge(mixed $value, ?string $matchOn = null): Props\Mergeable
    {
        return new Props\Mergeable(value: $value, deep: true, prepend: false, matchOn: $matchOn);
    }

    public function always(mixed $value): Props\Always
    {
        return new Props\Always($value);
    }

    public function scroll(
        mixed $data,
        string $pageName = 'page',
        ?int $previousPage = null,
        ?int $nextPage = null,
        int $currentPage = 1,
        bool $reset = false,
    ): Props\Scroll {
        return new Props\Scroll($data, $pageName, $previousPage, $nextPage, $currentPage, $reset);
    }

    /**
     * @param array<string, mixed>|list<array{channel: array{name: string, type?: string}|string, events: list<string>|string}>|string $listeners
     * @param list<string>|string|null                                                                                                 $events
     */
    public function live(mixed $value, array|string $listeners = [], array|string|null $events = null, ?int $throttle = null): Props\Live
    {
        if (is_string($listeners)) {
            $live = new Props\Live($value, [], $throttle);
            if ($events !== null) {
                $live->listen($listeners, $events);
            }

            return $live;
        }

        if (isset($listeners['channel'])) {
            $listeners = [$listeners];
        }

        return new Props\Live($value, $listeners, $throttle);
    }

    /**
     * Generate an external location visit response decision (409 Conflict).
     */
    public function redirectWithFragment(string $url): ResponseInterface
    {
        return \response()->setStatusCode(409)->setHeader('X-Inertia-Redirect', $url);
    }

    /**
     * Generate a Precognition validation success response decision (204 No Content).
     */
    public function precognitionSuccess(): ResponseInterface
    {
        return \response()->setStatusCode(204)
            ->setHeader('Precognition', 'true')
            ->setHeader('Precognition-Success', 'true')
            ->setHeader('Vary', 'Precognition');
    }

    /**
     * @psalm-api
     */
    public function location(RequestInterface|string $url): ResponseInterface
    {
        if ($url instanceof RequestInterface) {
            $url = (string) $url->getUri();
        }

        if (request()->hasHeader(InertiaHeaders::HEADER_INERTIA)) {
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
     *
     * @param list<mixed>|string $data
     * @param mixed              $value
     */
    public function flash(array|string $data, $value = null): self
    {
        session()->setFlashdata($data, $value);

        return $this;
    }

    /**
     * Disable SSR for tests.
     */
    public function disableSsr(mixed $disable = true): void
    {
        $resolved = is_callable($disable) ? $disable() : $disable;
        $config = config('Inertia');
        if ($config) {
            $config->isSsrEnabled = !$resolved;
        }
    }

    /**
     * @param array{component: string, version: string, url: string, props: array<string, mixed>} $page
     *
     * @psalm-api
     */
    public static function init(array $page, bool $isHead = false, ?string $protocolVersion = null): string
    {
        $version = $protocolVersion ?? config('Inertia')->protocolVersion ?? '3.0';
        $protoVer = ProtocolVersion::fromMixed($version);

        $pageObject = PageObject::fromArray($page, $protoVer);

        // SSR handling
        $config = config('Inertia');
        if (!empty($config?->isSsrEnabled)) {
            $gateway = Services::httpGateway();
            if ($gateway instanceof SsrGatewayInterface) {
                $ssr = $gateway->render($pageObject);
                if ($ssr) {
                    return $isHead ? implode("\n", $ssr->head) : $ssr->body;
                }
            } elseif ($gateway !== null && method_exists($gateway, 'dispatch')) {
                $ssr = $gateway->dispatch($page);
                if ($ssr) {
                    $head = is_array($ssr->head) ? implode("\n", $ssr->head) : (string) $ssr->head;

                    return $isHead ? $head : (string) $ssr->body;
                }
            }
        }

        if ($isHead) {
            return '';
        }

        if ($protoVer->isV2()) {
            return $pageObject->toHtmlDiv();
        }

        return '<script type="application/json" data-page="app">' . $pageObject->toHtmlJson() . '</script>' . $pageObject->toHtmlTag();
    }

    /**
     * Resolves and prepares validation errors in such
     * a way that they are easier to use client-side.
     */
    private function withValidationErrors(): self
    {
        $validation = service('validation');

        $validatorErrors = $validation->getErrors();
        $flashDataErrors = session()->getFlashdata('errors') ?? [];

        $errors = array_merge($flashDataErrors, $validatorErrors);

        if (request()->hasHeader(InertiaHeaders::HEADER_ERROR_BAG)) {
            $errors = [
                request()->getHeaderLine(InertiaHeaders::HEADER_ERROR_BAG) => $errors,
            ];
        }

        $this->sharedProps = array_merge($this->sharedProps, ['errors' => $errors]);
        $this->sharedKeys = array_unique(array_merge($this->sharedKeys, ['errors']));

        return $this;
    }

    /**
     * Returns the list of flashdata
     */
    private function withFlashData(): self
    {
        $allFlashData = session()->getFlashdata();

        // filter out errors flashdata, data that starts with _ci_
        $flashData = array_filter($allFlashData, static fn ($key) => !str_starts_with($key, '_ci_')   && $key !== 'errors', ARRAY_FILTER_USE_KEY);

        $this->sharedProps = array_merge($this->sharedProps, ['flash' => $flashData]);
        $this->sharedKeys = array_unique(array_merge($this->sharedKeys, ['flash']));

        return $this;
    }

    protected function validateUserProps(array $props): void
    {
        $intersection = array_intersect(self::SYSTEM_PROP_KEYS, array_keys($props));

        if (!empty($intersection)) {
            throw new InvalidArgumentException('The following props are overwriting system props: `[' . implode(',', $intersection) . ']`. Choose different keys for these props to avoid this error.');
        }
    }
}
