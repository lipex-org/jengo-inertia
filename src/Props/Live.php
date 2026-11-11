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

use Inertia\Protocol\Props\Live as ProtocolLive;

/**
 * @deprecated Use \Inertia\Protocol\Props\Live directly. Will be removed in the official v1.0.0 release.
 */
class Live extends ProtocolLive
{
    /**
     * @param mixed                                                                                        $value     Underlying prop value
     * @param list<array{channel: array{name: string, type?: string}|string, events: list<string>|string}> $listeners
     * @param int|null                                                                                     $throttle  Throttle in milliseconds
     */
    public function __construct(
        mixed $value,
        array $listeners = [],
        ?int $throttle = null,
    ) {
        parent::__construct(
            value: $value,
            listeners: $listeners,
            throttle: $throttle,
        );
    }
}
