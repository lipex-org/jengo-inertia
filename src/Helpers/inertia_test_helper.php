<?php

declare(strict_types=1);

use Jengo\Inertia\Testing\InertiaTestHelper;
use Jengo\Inertia\Testing\TestResponse;

if (!function_exists('inertia_test')) {
    /**
     * Wrap a CI4 TestResponse or ResponseInterface into an Inertia TestResponse wrapper.
     */
    function inertia_test(mixed $response, ?object $testCase = null): TestResponse
    {
        return InertiaTestHelper::wrap($response, $testCase);
    }
}
