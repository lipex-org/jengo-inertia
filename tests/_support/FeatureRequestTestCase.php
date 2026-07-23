<?php

/**
 * This file is part of Inertia.js Codeigniter 4.
 *
 * (c) 2023 Fab IT Hub <hello@fabithub.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests\Support;

use CodeIgniter\Test\FeatureTestTrait;
use Jengo\Inertia\Testing\InertiaAssertions;
use Tests\TestCase;

/**
 * @internal
 */
class FeatureRequestTestCase extends TestCase
{
    use FeatureTestTrait, InertiaAssertions {
        FeatureTestTrait::get as parentGet;
        FeatureTestTrait::post as parentPost;
        FeatureTestTrait::put as parentPut;
        FeatureTestTrait::patch as parentPatch;
        FeatureTestTrait::delete as parentDelete;

        InertiaAssertions::get insteadof FeatureTestTrait;
        InertiaAssertions::post insteadof FeatureTestTrait;
        InertiaAssertions::put insteadof FeatureTestTrait;
        InertiaAssertions::patch insteadof FeatureTestTrait;
        InertiaAssertions::delete insteadof FeatureTestTrait;
    }
}
