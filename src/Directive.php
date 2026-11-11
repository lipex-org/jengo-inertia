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

use Inertia\Protocol\PageObject;
use Inertia\Protocol\ProtocolVersion;
use Jengo\Inertia\Config\Services;
use Jengo\Inertia\Ssr\Response;

/**
 * @deprecated Use \Inertia\Protocol\PageObject::toHtml() or \Jengo\Inertia\Inertia::init() directly. Will be removed in the official v1.0.0 release.
 */
class Directive
{
    protected static ?Response $__inertiaSsr = null;

    /**
     * @deprecated Use \Inertia\Protocol\PageObject::toHtml() or \Jengo\Inertia\Inertia::init() directly. Will be removed in the official v1.0.0 release.
     *
     * @param array{component: string, version: string, url: string, props: array<string, mixed>} $page
     */
    public static function compile(array $page, string $expression = '', ?string $protocolVersion = null): string
    {
        $id = trim(trim($expression), "\\'\"") ?: 'app';
        $inertiaSsr = static::withSsr($page);

        if ($inertiaSsr instanceof Response) {
            return implode(' ', array_map('trim', explode("\n", $inertiaSsr->body)));
        }

        $version = $protocolVersion ?? config('Inertia')->protocolVersion ?? '3.0';
        $protoVer = ProtocolVersion::fromMixed($version);

        $pageObject = PageObject::fromArray($page, $protoVer);

        if ($protoVer->isV2()) {
            return $pageObject->toHtmlDiv($id);
        }

        return '<script type="application/json" data-page="app">' . $pageObject->toHtmlJson() . '</script>' . $pageObject->toHtmlTag($id);
    }

    /**
     * @deprecated Use \Inertia\Protocol\Ssr\HttpSsrGateway directly. Will be removed in the official v1.0.0 release.
     *
     * @param array{component: string, version: string, url: string, props: array<string, mixed>} $page
     */
    public static function compileHead(array $page): string
    {
        $template = '';
        $inertiaSsr = static::withSsr($page);

        if ($inertiaSsr instanceof Response) {
            $template = is_array($inertiaSsr->head) ? implode("\n", $inertiaSsr->head) : (string) $inertiaSsr->head;
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
