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
