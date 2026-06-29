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
use CodeIgniter\HTTP\ResponsableInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\View\View;
use Config\View as ConfigView;
use Jengo\Inertia\Extras\Arr;
use Jengo\Inertia\Extras\Http;

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
            /** @var Config\Inertia */
            $config = \config('Inertia');
            return $response->render($config->rootView);
        }

        return (string) $response->getJSON();
    }

    public function toResponse(?RequestInterface $request = null): View|ResponseInterface
    {
        $request ??= request();

        $partialData = Http::getPartialData($request);
        $partialExcept = Http::getPartialExcept($request);
        $isPartial = Http::isPartialReload($request) && Http::getHeaderValue('X-Inertia-Partial-Component', '', $request) === $this->component;

        $exceptOnce = Http::getExceptOnceProps($request);

        $resolvedProps = [];
        $deferredProps = [];
        $rescuedProps = [];
        $mergeProps = [];
        $prependProps = [];
        $deepMergeProps = [];
        $matchPropsOn = [];
        $onceProps = [];

        foreach ($this->props as $key => $value) {
            // Handle "Always" props
            if ($value instanceof Props\Always) {
                $resolvedProps[$key] = Arr::value($value->value);
                continue;
            }

            // Partial reload logic
            if ($isPartial) {
                if ($partialData && !in_array($key, $partialData, true)) {
                    continue;
                }
                if ($partialExcept && in_array($key, $partialExcept, true)) {
                    continue;
                }
            } elseif ($value instanceof Props\Lazy) {
                // Lazy props are only included in partial reloads
                continue;
            }

            // Handle "Once" props
            if ($value instanceof Props\Once) {
                $onceProps[$key] = ['prop' => $key, 'expiresAt' => null];
                if (in_array($key, $exceptOnce, true)) {
                    continue;
                }
                $resolvedProps[$key] = Arr::value($value->callback);
                continue;
            }

            // Handle "Defer" props
            if ($value instanceof Props\Defer) {
                if (!$isPartial || !in_array($key, $partialData, true)) {
                    $deferredProps[$value->group][] = $key;
                    continue;
                }
                try {
                    $resolvedProps[$key] = Arr::value($value->callback);
                } catch (\Throwable $e) {
                    $rescuedProps[] = $key;
                }
                continue;
            }

            // Handle "Mergeable" props
            if ($value instanceof Props\Mergeable) {
                if ($value->deep) {
                    $deepMergeProps[] = $key;
                } elseif ($value->prepend) {
                    $prependProps[] = $key;
                } else {
                    $mergeProps[] = $key;
                }

                if ($value->matchOn) {
                    $matchPropsOn[] = "{$key}.{$value->matchOn}";
                }

                $resolvedProps[$key] = Arr::value($value->value);
                continue;
            }

            // Regular props
            $resolvedProps[$key] = Arr::value($value);
        }

        $fragment = $request->getUri()->getFragment();
        $query = $request->getUri()->getQuery();
        $url = $request->getUri()->getPath() . ($fragment ? "#{$fragment}" : "") . ($query ? "?{$query}" : "");

        $page = [
            'component' => $this->component,
            'props' => $resolvedProps,
            'url' => $url,
            'version' => $this->version,
        ];

        if ($this->encryptHistory)
            $page['encryptHistory'] = true;
        if ($this->clearHistory)
            $page['clearHistory'] = true;
        if ($this->preserveFragment)
            $page['preserveFragment'] = true;
        if ($deferredProps)
            $page['deferredProps'] = $deferredProps;
        if ($rescuedProps)
            $page['rescuedProps'] = $rescuedProps;
        if ($mergeProps)
            $page['mergeProps'] = $mergeProps;
        if ($prependProps)
            $page['prependProps'] = $prependProps;
        if ($deepMergeProps)
            $page['deepMergeProps'] = $deepMergeProps;
        if ($matchPropsOn)
            $page['matchPropsOn'] = $matchPropsOn;
        if ($onceProps)
            $page['onceProps'] = $onceProps;
        if ($this->sharedKeys)
            $page['sharedProps'] = $this->sharedKeys;
        if ($this->scrollProps)
            $page['scrollProps'] = $this->scrollProps;

        if (Http::isInertiaRequest($request)) {
            return \response()->setJSON($page, true)->setHeader('Vary', 'X-Inertia')->setHeader('X-Inertia', 'true');
        }

        $view = new View(new ConfigView());
        $view->setData($this->viewData + ['page' => $page], 'raw');

        return $view;
    }

    public function getResponse(): ResponseInterface
    {
        $response = $this->toResponse();

        if ($response instanceof View) {
            /** @var Config\Inertia */
            $config = \config('Inertia');
            return \response()->setBody($response->render($config->rootView))->setHeader('Content-Type', 'text/html');
        }

        return $response;
    }
}