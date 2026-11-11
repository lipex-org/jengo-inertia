<?php

/**
 * This file is part of Inertia.js Codeigniter 4.
 *
 * (c) 2023 Fab IT Hub <hello@fabithub.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use Jengo\Inertia\Inertia;

/**
 * This file is part of Inertia.js Codeigniter 4.
 *
 * (c) 2023 Fab IT Hub <hello@fabithub.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */
if (!function_exists('inertia')) {
    /**
     * Method inertia
     *
     * @param string $component $component Componenet Name
     * @param array  $props     $props Component props
     */
    function inertia(string $component, array $props = [])
    {
        return Inertia::render($component, $props);
    }
}
