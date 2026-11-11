<?php

/**
 * This file is part of Inertia.js Codeigniter 4.
 *
 * (c) 2023 Fab IT Hub <hello@fabithub.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Jengo\Inertia\Extras;

use CodeIgniter\HTTP\RequestInterface;
use Inertia\Protocol\Support\InertiaHeaders;

/**
 * @deprecated Use \Inertia\Protocol\Support\InertiaHeaders or \Inertia\Protocol\Contracts\RequestInterface directly. Will be removed in the official v1.0.0 release.
 */
class Http
{
    public static function isInertiaRequest(?RequestInterface $request = null): bool
    {
        $request ??= request();

        return $request->hasHeader(InertiaHeaders::HEADER_INERTIA);
    }

    public static function isPartialReload(?RequestInterface $request = null): bool
    {
        $request ??= request();

        return self::isInertiaRequest($request) && $request->hasHeader(InertiaHeaders::HEADER_PARTIAL_COMPONENT);
    }

    /**
     * @return list<string>
     */
    public static function getPartialData(?RequestInterface $request = null): array
    {
        $value = self::getHeaderValue(InertiaHeaders::HEADER_PARTIAL_DATA, '', $request);

        return array_filter(explode(',', is_array($value) ? implode(',', $value) : $value));
    }

    /**
     * @return list<string>
     */
    public static function getPartialExcept(?RequestInterface $request = null): array
    {
        $value = self::getHeaderValue(InertiaHeaders::HEADER_PARTIAL_EXCEPT, '', $request);

        return array_filter(explode(',', is_array($value) ? implode(',', $value) : $value));
    }

    /**
     * @return list<string>
     */
    public static function getResetProps(?RequestInterface $request = null): array
    {
        $value = self::getHeaderValue(InertiaHeaders::HEADER_RESET, '', $request);

        return array_filter(explode(',', is_array($value) ? implode(',', $value) : $value));
    }

    /**
     * @return list<string>
     */
    public static function getExceptOnceProps(?RequestInterface $request = null): array
    {
        $value = self::getHeaderValue(InertiaHeaders::HEADER_EXCEPT_ONCE_PROPS, '', $request);

        return array_filter(explode(',', is_array($value) ? implode(',', $value) : $value));
    }

    public static function getHeaderValue(string $key, mixed $default = '', ?RequestInterface $request = null): mixed
    {
        $request ??= request();

        if ($request->hasHeader($key)) {
            $value = $request->getHeaderLine($key);

            return '' !== $value ? $value : $default;
        }

        return $default;
    }
}
