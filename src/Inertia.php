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

use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use Jengo\Inertia\Config\Services;

/**
 * Inertia.
 *
 * @method static Props\Always                       always(mixed $value)
 * @method static ResponseFactory                    clearHistory(bool $clear = true)
 * @method static Props\Mergeable                    deepMerge(mixed $value, ?string $matchOn = null)
 * @method static Props\Defer                        defer(mixed $callback, string $group = 'default', bool $rescue = false)
 * @method static void                               disableSsr(mixed $disable = true)
 * @method static ResponseFactory                    encryptHistory(bool $encrypt = true)
 * @method static ResponseFactory                    flash(array|string $data, ?string $value = null)
 * @method static void                               flushShared()
 * @method static mixed                              getShared(?string $key, $default = null)
 * @method static string|null                        getVersion()
 * @method static string                             init(array{component: string, version: string, url: string, props: array<string, mixed>} $page, bool $isHead = false, ?string $protocolVersion = null)
 * @method static Props\Lazy                         lazy(mixed $callback)
 * @method static Props\Live                         live(mixed $value, array|string $listeners = [], array|string|null $events = null, ?int $throttle = null)
 * @method static RedirectResponse|ResponseInterface location(\CodeIgniter\HTTP\IncomingRequest|string $url)
 * @method static Props\Mergeable                    merge(mixed $value, ?string $matchOn = null)
 * @method static Props\Once                         once(mixed $callback, ?int $expiresAt = null)
 * @method static Props\Optional                     optional(mixed $value)
 * @method static bool                               pageExists(string $component, ?array $customPaths = null)
 * @method static ResponseInterface                  precognitionSuccess()
 * @method static Props\Mergeable                    prepend(mixed $value, ?string $matchOn = null)
 * @method static ResponseFactory                    preserveBigIntegers(bool $preserve = true)
 * @method static ResponseFactory                    preserveFragment(bool $preserve = true)
 * @method static ResponseInterface                  redirectWithFragment(string $url)
 * @method static ResponseInterface                  render(string $component, array $props = [], array $viewData = [])
 * @method static Props\Scroll                       scroll(mixed $data, string $pageName = 'page', ?int $previousPage = null, ?int $nextPage = null, int $currentPage = 1, bool $reset = false)
 * @method static void                               share(string|array $key, $value = null)
 * @method static void                               version(\Closure|string|null $version)
 * @method static ResponseFactory                    withErrors(array|object $errors)
 * @method static ResponseFactory                    withFlash(array $flash)
 * @method static ResponseFactory                    withProtocolVersion(string|\Inertia\Protocol\ProtocolVersion $version)
 *
 * @see ResponseFactory
 */
class Inertia
{
    /**
     * @param array<int|string, mixed> $arguments
     *
     * @psalm-api
     *
     * @return mixed
     */
    public static function __callStatic(string $method, array $arguments)
    {
        return Services::inertia()->{$method}(...$arguments);
    }
}
