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

use Inertia\Protocol\Props\Always as ProtocolAlways;

/**
 * @deprecated Use \Inertia\Protocol\Props\Always directly. Will be removed in the official v1.0.0 release.
 */
class Always extends ProtocolAlways
{
    public function __construct(
        public mixed $value,
    ) {
        parent::__construct($value);
    }
}
