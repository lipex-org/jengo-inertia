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

namespace Jengo\Inertia\Extras;

use CodeIgniter\HTTP\RequestInterface;

class Http
{
    public static function isInertiaRequest(?RequestInterface $request = null): bool
    {
        $request ??= request();

        return $request->hasHeader('X-Inertia');
    }

    public static function isPartialReload(?RequestInterface $request = null): bool
    {
        $request ??= request();

        return self::isInertiaRequest($request) && $request->hasHeader('X-Inertia-Partial-Component');
    }

    /**
     * @return list<string>
     */
    public static function getPartialData(?RequestInterface $request = null): array
    {
        $value = self::getHeaderValue('X-Inertia-Partial-Data', '', $request);
        return array_filter(explode(',', is_array($value) ? implode(',', $value) : $value));
    }

    /**
     * @return list<string>
     */
    public static function getPartialExcept(?RequestInterface $request = null): array
    {
        $value = self::getHeaderValue('X-Inertia-Partial-Except', '', $request);
        return array_filter(explode(',', is_array($value) ? implode(',', $value) : $value));
    }

    /**
     * @return list<string>
     */
    public static function getResetProps(?RequestInterface $request = null): array
    {
        $value = self::getHeaderValue('X-Inertia-Reset', '', $request);
        return array_filter(explode(',', is_array($value) ? implode(',', $value) : $value));
    }

    /**
     * @return list<string>
     */
    public static function getExceptOnceProps(?RequestInterface $request = null): array
    {
        $value = self::getHeaderValue('X-Inertia-Except-Once-Props', '', $request);
        return array_filter(explode(',', is_array($value) ? implode(',', $value) : $value));
    }

    /**
     * @return list<list<string>|string>|string
     * @psalm-return array<int|string, array<string, string>|string>|string
     */
    public static function getHeaderValue(string $header, string $default = '', ?RequestInterface $request = null): array |string
    {
        $request ??= request();

        if ($request->hasHeader($header)) {
            return $request->header($header)->getValue();
        }

        return $default;
    }
}