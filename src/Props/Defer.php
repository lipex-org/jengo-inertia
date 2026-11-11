<?php

/**
 * This file is part of Inertia.js Codeigniter 4.
 *
 * (c) 2023 Fab IT Hub <hello@fabithub.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Jengo\Inertia\Props;

use Inertia\Protocol\Props\Defer as ProtocolDefer;

/**
 * @deprecated Use \Inertia\Protocol\Props\Defer directly. Will be removed in the official v1.0.0 release.
 */
class Defer extends ProtocolDefer
{
    public function __construct(
        string $group = 'default',
        mixed $callback = null,
        bool $rescue = false,
    ) {
        parent::__construct($callback, $group, $rescue);
    }
}
