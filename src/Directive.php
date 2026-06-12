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

use Jengo\Inertia\Config\Services;
use Jengo\Inertia\Ssr\Response;

class Directive
{
    protected static ?Response $__inertiaSsr = null;

    /**
     * @param array{component: string, version: string, url: string, props: array<string, mixed>} $page
     */
    public static function compile(array $page, string $expression = ''): string
    {
        $id = trim(trim($expression), "\\'\"") ?: 'app';
        $inertiaSsr = static::withSsr($page);

        $template = '<script type="application/json" data-page="app">' . json_encode($page, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES) . '</script><div id="' . $id . '"></div>';

        if ($inertiaSsr instanceof Response) {
            $template = $inertiaSsr->body;
        }

        return implode(' ', array_map('trim', explode("\n", $template)));
    }

    /**
     * @param array{component: string, version: string, url: string, props: array<string, mixed>} $page
     */
    public static function compileHead(array $page): string
    {
        $template = '';
        $inertiaSsr = static::withSsr($page);

        if ($inertiaSsr instanceof Response) {
            $template = $inertiaSsr->head;
        }

        return implode(' ', array_map('trim', explode("\n", $template)));
    }

    /**
     * @param array{component: string, version: string, url: string, props: array<string, mixed>} $page
     */
    protected static function withSsr(array $page): ?Response
    {
        if (!isset(static::$__inertiaSsr) && empty(static::$__inertiaSsr)) {
            $__inertiaSsr = Services::httpGateway()->dispatch($page);

            static::$__inertiaSsr = $__inertiaSsr;
        }

        return static::$__inertiaSsr;
    }
}