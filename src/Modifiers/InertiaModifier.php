<?php

declare(strict_types=1);

namespace Jengo\Inertia\Modifiers;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Filters;
use Jengo\Base\Contracts\ResponseModifierInterface;
use Jengo\Inertia\Inertia;

class InertiaModifier implements ResponseModifierInterface
{
    public function modifyValidationFailed(array $errors, RequestInterface $request, array $options = []): ResponseInterface
    {
        // 1. Set errors as Inertia flashdata
        Inertia::flash('errors', $errors);

        // 2. Build redirect response through Inertia middleware if configured
        $config = config('Inertia');
        $filtersConfig = config('Filters');

        $redirect = redirect()->back()->withInput()->with('errors', $errors);

        $filterAlias = $config->filterAlias ?? 'inertia';
        $filterClass = $filtersConfig->aliases[$filterAlias] ?? null;

        if ($filterClass && class_exists($filterClass)) {
            $instance = new $filterClass();
            if ($instance instanceof FilterInterface) {
                return $instance->after($request, $redirect);
            }
        }

        return $redirect;
    }
}
