<?php

declare(strict_types=1);

/**
 * This file is part of Inertia.js Codeigniter 4.
 *
 * (c) 2023 Fab IT Hub <hello@fabithub.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Jengo\Inertia;

use CodeIgniter\HTTP\RequestInterface as CI4RequestInterface;
use Inertia\Protocol\Contracts\RequestInterface as ProtocolRequestInterface;

class CI4RequestAdapter implements ProtocolRequestInterface
{
    public function __construct(protected CI4RequestInterface $request)
    {
    }

    public function getHeaderLine(string $name): string
    {
        return (string) $this->request->getHeaderLine($name);
    }

    public function hasHeader(string $name): bool
    {
        return $this->request->hasHeader($name) && $this->request->getHeaderLine($name) !== '';
    }

    public function getMethod(): string
    {
        return strtoupper((string) $this->request->getMethod());
    }

    public function getUrl(): string
    {
        $uri = $this->request->getUri();
        $path = '/' . ltrim($uri->getPath(), '/');
        $query = $uri->getQuery();
        $fragment = $uri->getFragment();

        return $path . ($query ? '?' . $query : '') . ($fragment ? '#' . $fragment : '');
    }

    public function getQueryParam(string $name, mixed $default = null): mixed
    {
        return $this->request->getGet($name) ?? $default;
    }

    public function getCi4Request(): CI4RequestInterface
    {
        return $this->request;
    }
}
