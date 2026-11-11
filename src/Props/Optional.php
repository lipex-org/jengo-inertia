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

namespace Jengo\Inertia\Props;

use Inertia\Protocol\Props\Optional as ProtocolOptional;

/**
 * @deprecated Use \Inertia\Protocol\Props\Optional directly. Will be removed in the official v1.0.0 release.
 */
class Optional extends ProtocolOptional
{
    public function __construct(
        mixed $callback,
    ) {
        parent::__construct($callback);
    }
}
