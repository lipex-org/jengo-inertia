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

use Closure;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Validation\ValidationInterface;
use Jengo\Inertia\Extras\Http;

/**
 * @psalm-api
 */
class Middleware implements FilterInterface
{
    /**
     * @psalm-return array{alert: Closure():?string, errors: Closure():object, flash: Closure():array{success: ?string, error: ?string}}
     * @return array{alert: Closure():?string, errors: Closure():object, flash: Closure():array{success: ?string, error: ?string}}
     */
    public function withShare(RequestInterface $request): array
    {
        return [
            'errors' => fn() => $this->resolveValidationErrors($request),
            'flash' => static fn() => session()->getFlashdata(),
        ];
    }

    /**
     * @param array<int|string, mixed> $arguments
     */
    public function before(RequestInterface $request, $arguments = null): void
    {
        Inertia::share($this->withShare($request));
    }

    /**
     * Handle the incoming request.
     *
     * @param array|null $arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // clear all flashData
        $flashKeys = array_keys(session()->getFlashdata());
        session()->unmarkFlashdata($flashKeys);

        $response->setHeader('Vary', 'X-Inertia');

        if ($request->hasHeader('Precognition')) {
            $response->setHeader('Vary', 'Precognition');
            $response->setHeader('Precognition', 'true');
        }

        if (!$request->hasHeader('X-Inertia')) {
            return $response;
        }

        if (request()->isCLI()) {
            return $response;
        }

        // Only check version in production to avoid full page reloads during development
        if (ENVIRONMENT !== 'development' && request()->is('get')) {
            if (Http::getHeaderValue('X-Inertia-Version') !== Inertia::getVersion()) {
                $response = $this->onVersionChange($request);
            }
        }

        if ($response->getStatusCode() === $response::HTTP_OK && empty($response->getJSON())) {
            $response = $this->onEmptyResponse();
        }

        if (
            $response->getStatusCode() === $response::HTTP_FOUND
            && (request()->is('put') || request()->is('patch') || request()->is('delete'))
        ) {
            $response->setStatusCode($response::HTTP_SEE_OTHER);
        }

        return $response;
    }

    private function onEmptyResponse(): RedirectResponse
    {
        return \redirect()->back();
    }

    private function onVersionChange(RequestInterface $request): RedirectResponse|ResponseInterface
    {
        \session()->regenerate(true);

        return Inertia::location($request->getUri());
    }

    /**
     * Resolves and prepares validation errors in such
     * a way that they are easier to use client-side.
     */
    private function resolveValidationErrors(RequestInterface $request): object
    {
        service('session');

        /** @var ValidationInterface */
        $validation = service('validation');

        $errors = session()->getFlashdata('errors') ?? $validation->getErrors();

        if (!$errors) {
            return (object) [];
        }

        if ($request->hasHeader('x-inertia-error-bag')) {
            return (object) [Http::getHeaderValue('x-inertia-error-bag') => $errors];
        }

        return (object) $errors;
    }
}