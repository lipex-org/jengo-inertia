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

use ArrayAccess;
use Closure;
use Inertia\Protocol\Support\Arr as ProtocolArr;

class Arr
{
    public static function accessible(mixed $value): bool
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }

    public static function exists(mixed $array, int|string $key): bool
    {
        if ($array instanceof ArrayAccess) {
            return $array->offsetExists($key);
        }

        if (is_array($array)) {
            return array_key_exists($key, $array);
        }

        return false;
    }

    public static function get(mixed $array, mixed $key, mixed $default = null): mixed
    {
        if (!static::accessible($array)) {
            return static::value($default);
        }

        if (null === $key) {
            return $array;
        }

        if (static::exists($array, $key)) {
            return $array[$key];
        }

        if (!is_string($key) || !str_contains($key, '.')) {
            return $array[$key] ?? static::value($default);
        }

        foreach (explode('.', $key) as $segment) {
            if (static::accessible($array) && static::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return static::value($default);
            }
        }

        return $array;
    }

    public static function set(mixed &$array, mixed $key, mixed $value): mixed
    {
        if (null === $key) {
            return $array = $value;
        }

        $keys = explode('.', (string) $key);

        foreach ($keys as $i => $segment) {
            if (count($keys) === 1) {
                break;
            }

            unset($keys[$i]);

            if (!isset($array[$segment]) || !is_array($array[$segment])) {
                $array[$segment] = [];
            }

            $array = &$array[$segment];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }

    public static function only(array $array, array|string $keys): array
    {
        return array_intersect_key($array, array_flip((array) $keys));
    }

    public static function value(mixed $value, mixed ...$args): mixed
    {
        return ProtocolArr::value($value, ...$args);
    }
}
