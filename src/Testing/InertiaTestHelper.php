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

namespace Jengo\Inertia\Testing;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Test\TestResponse as CITestResponse;

class InertiaTestHelper
{
    /**
     * Wrap any CI4 response or TestResponse in an Inertia TestResponse wrapper.
     */
    public static function wrap(mixed $response, ?object $testCase = null): TestResponse
    {
        if ($response instanceof TestResponse) {
            return $response;
        }

        if ($response instanceof CITestResponse) {
            return new TestResponse($response, $testCase);
        }

        if ($response instanceof ResponseInterface) {
            return new TestResponse(new CITestResponse($response), $testCase);
        }

        return new TestResponse($response, $testCase);
    }
}
