<?php

namespace Jengo\Inertia\Props;

use Inertia\Protocol\Props\Once as ProtocolOnce;

class Once extends ProtocolOnce
{
    public function __construct(
        mixed $callback,
        ?int $expiresAt = null
    ) {
        parent::__construct($callback, $expiresAt);
    }
}
