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

use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use Jengo\Inertia\Config\Services;

/**
 * Inertia.
 *
 * @method static void                               flushShared()
 * @method static mixed                              getShared(?string $key, $default = null)
 * @method static string                             getVersion()
 * @method static string                             init(array{component: string, version: string, url: string, props: array<string, mixed>} $page, bool $isHead = false)
 * @method static RedirectResponse|ResponseInterface location((Request | string) $url)                                                                             :
 * @method static ResponseInterface                  render(string $component, array $props = [], array $viewData = [])
 * @method static void                               share(string|array $key, $value = null)
 * @method static void                               version((Closure | string | null) $version)
 * @method static Props\Lazy                         lazy(Closure $callback)
 * @method static Props\Defer                        defer(Closure $callback, string $group = 'default')
 * @method static Props\Once                         once(Closure $callback)
 * @method static Props\Mergeable                    merge(mixed $value)
 * @method static Props\Mergeable                    prepend(mixed $value)
 * @method static Props\Mergeable                    deepMerge(mixed $value)
 * @method static Props\Always                       always(mixed $value)
 * @method static ResponseFactory                    flash(array|string $data, ?string $value = null)
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