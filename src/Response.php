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

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\View\View;
use Config\View as ConfigView;
use Inertia\Protocol\InertiaResponse;
use Inertia\Protocol\ProtocolVersion;
use Stringable;

class Response extends InertiaResponse implements Stringable
{
    /**
     * @var array<string, mixed>
     */
    protected array $viewData = [];

    /**
     * @param array<string, mixed> $props
     */
    public function __construct(
        string $component,
        array $props = [],
        mixed $version = '',
    ) {
        parent::__construct($component, $props, $version);
    }

    public function withComponent(string $component): static
    {
        $this->component = $component;

        return $this;
    }

    /**
     * @param array<string, mixed> $props
     */
    public function scrollProps(array $props): static
    {
        $this->options['scrollProps'] = $props;

        return $this;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function viewData(array $data): static
    {
        $this->viewData = array_merge($this->viewData, $data);

        return $this;
    }

    public function __toString(): string
    {
        $response = $this->toResponse();

        if ($response instanceof View) {
            /** @var Config\Inertia $config */
            $config = config('Inertia');

            return $response->render($config->rootView);
        }

        return (string) $response->getJSON();
    }

    public function toResponse(?RequestInterface $request = null): ResponseInterface|View
    {
        $request ??= request();
        $adapter = new CI4RequestAdapter($request);

        /** @var Config\Inertia $config */
        $config = config('Inertia') ?? new Config\Inertia();

        // Extract reserved 'errors' and 'flash' from user props if present
        if (isset($this->props['errors'])) {
            $this->options['errors'] = $this->props['errors'];
            unset($this->props['errors']);
        }
        if (isset($this->props['flash'])) {
            $this->options['flash'] = $this->props['flash'];
            unset($this->props['flash']);
        }

        if (!isset($this->options['protocolVersion'])) {
            $this->options['protocolVersion'] = ProtocolVersion::fromMixed($config->protocolVersion ?? '3.0');
        }

        if (!isset($this->options['preserveBigIntegers']) && !empty($config->preserveBigIntegers)) {
            $this->options['preserveBigIntegers'] = true;
        }

        $decision = $this->toDecision($adapter);
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
            $config = config('Inertia');

            return \response()->setBody(view($config->rootView, $response->getData()))->setHeader('Content-Type', 'text/html');
        }

        return $response;
    }
}
