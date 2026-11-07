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
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Jengo\Base\Container\Container;
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
     * @var Config\Inertia|null
     */
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

    protected const array SYSTEM_PROP_KEYS = [
        'errors',
        'flash'
    ];

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
        $this->validateUserProps($props);

        /** @var Config\Inertia $config */
        $config = $this->getConfig();

        if (!empty($config->pages['ensure_page_exists']) && ! $this->pageExists($component)) {
            throw PageNotFoundException::forPageNotFound(
                "Inertia page component [{$component}] not found on disk."
            );
        }

        // resolve errors and flashdata
        $this->withValidationErrors()->withFlashData();

        return (new Response($component, array_merge($this->sharedProps, $props), $this->getVersion()))
            ->withSharedKeys($this->sharedKeys)->getResponse();
    }

    /**
     * Check if an Inertia page component exists on disk.
     *
     * @param string $component Component name (e.g. 'Users/Index' or 'Home')
     * @param array<int, string>|null $customPaths Optional paths override
     * @return bool
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

    public function lazy(Closure $callback): Props\Lazy
    {
        $diCallback = static fn() => Container::getInstance()->call($callback);
        return new Props\Lazy($diCallback);
    }

    public function defer(Closure $callback, string $group = 'default'): Props\Defer
    {
        $diCallback = static fn() => Container::getInstance()->call($callback);
        return new Props\Defer($group, $diCallback);
    }

    public function once(Closure $callback): Props\Once
    {
        $diCallback = static fn() => Container::getInstance()->call($callback);
        return new Props\Once($diCallback);
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
    public static function init(array $page, bool $isHead = false): string
    {
        if ($isHead) {
            return Directive::compileHead($page);
        }

        return Directive::compile($page);
    }

    /**
     * Resolves and prepares validation errors in such
     * a way that they are easier to use client-side.
     */
    private function withValidationErrors(): self
    {
        $validation = service("validation");

        $validatorErrors = $validation->getErrors();
        $flashDataErrors = session()->getFlashdata("errors") ?? [];

        $errors = array_merge($flashDataErrors, $validatorErrors);

        if (request()->hasHeader("x-inertia-error-bag")) {
            $errors = ([
                Http::getHeaderValue("x-inertia-error-bag") => $errors,
            ]);
        }

        $this->sharedProps = array_merge($this->sharedProps, ["errors" => $errors]);
        $this->sharedKeys = array_unique(array_merge($this->sharedKeys, ["errors"]));

        return $this;
    }

    /**
     * Returns the list of flashdata
     * @return ResponseFactory
     */
    private function withFlashData(): self
    {
        $allFlashData = session()->getFlashdata();

        // filter out errors flashdata, data that starts with _ci_
        $flashData = array_filter($allFlashData, function ($key) {
            return strpos($key, '_ci_') !== 0 && $key !== 'errors';
        }, ARRAY_FILTER_USE_KEY);

        $this->sharedProps = array_merge($this->sharedProps, ["flash" => $flashData]);
        $this->sharedKeys = array_unique(array_merge($this->sharedKeys, ["flash"]));

        return $this;
    }

    protected function validateUserProps(array $props): void {
        $intersection = array_intersect(self::SYSTEM_PROP_KEYS, array_keys($props));

        if (!empty($intersection)) {
            throw new \InvalidArgumentException('The following props are overwriting system props: `[' . implode(',', $intersection) . ']`. Choose different keys for these props to avoid this error.');
        }
    }
}