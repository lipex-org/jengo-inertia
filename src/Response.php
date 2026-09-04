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

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponsableInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\View\View;
use Config\View as ConfigView;
use Inertia\Protocol\ProtocolEngine;
use Jengo\Base\Inertia\CI4RequestAdapter;

class Response implements ResponsableInterface
{
    /**
     * @var array<string, mixed>
     */
    protected array $props = [];

    /**
     * @var array<string, mixed>
     */
    protected array $viewData = [];

    protected string $version = '';
    protected string $component = '';
    protected bool $encryptHistory = false;
    protected bool $clearHistory = false;
    protected bool $preserveFragment = false;
    protected array $sharedKeys = [];
    protected array $scrollProps = [];

    /**
     * @param array<string, mixed> $props
     */
    public function __construct(string $component, array $props, string $version = '')
    {
        $this->withComponent($component)->with($props)->withVersion($version);
    }

    /**
     * @param array<string, mixed>|string $key
     * @param mixed                       $value
     *
     * @return $this
     */
    public function with($key, $value = null): self
    {
        if (is_array($key)) {
            $this->props = array_merge($this->props, $key);
        } else {
            $this->props[$key] = $value;
        }

        return $this;
    }

    public function withComponent(string $component): static
    {
        $this->component = $component;

        return $this;
    }

    public function withVersion(string $version): static
    {
        $this->version = $version;

        return $this;
    }

    public function withSharedKeys(array $keys): static
    {
        $this->sharedKeys = $keys;

        return $this;
    }

    public function scrollProps(array $props): static
    {
        $this->scrollProps = $props;

        return $this;
    }

    public function encryptHistory(bool $encrypt = true): static
    {
        $this->encryptHistory = $encrypt;

        return $this;
    }

    public function clearHistory(bool $clear = true): static
    {
        $this->clearHistory = $clear;

        return $this;
    }

    public function preserveFragment(bool $preserve = true): static
    {
        $this->preserveFragment = $preserve;

        return $this;
    }

    public function __toString(): string
    {
        $response = $this->toResponse();

        if ($response instanceof View) {
            /** @var Config\Inertia $config */
            $config = \config('Inertia');

            return $response->render($config->rootView);
        }

        return (string) $response->getJSON();
    }

    public function toResponse(?RequestInterface $request = null): View|ResponseInterface
    {
        $request ??= request();
        $adapter = new CI4RequestAdapter($request);

        $engine = new ProtocolEngine();
        $decision = $engine->evaluate(
            request: $adapter,
            component: $this->component,
            props: $this->props,
            version: $this->version,
            options: [
                'sharedKeys'       => $this->sharedKeys,
                'scrollProps'      => $this->scrollProps,
                'encryptHistory'   => $this->encryptHistory,
                'clearHistory'     => $this->clearHistory,
                'preserveFragment' => $this->preserveFragment,
            ]
        );

        $page = $decision->pageObject->toArray();

        if ($decision->isJson()) {
            $res = \response()->setJSON($page, true);
            foreach ($decision->headers as $name => $value) {
                $res->setHeader($name, $value);
            }

            return $res;
        }

        $view = new View(new ConfigView(), '');
        $view->setData($this->viewData + ['page' => $page], 'raw');

        return $view;
    }

    public function getResponse(): ResponseInterface
    {
        $response = $this->toResponse();

        if ($response instanceof View) {
            /** @var Config\Inertia $config */
            $config = \config('Inertia');

            return \response()->setBody(view($config->rootView, $response->getData()))->setHeader('Content-Type', 'text/html');
        }

        return $response;
    }
}