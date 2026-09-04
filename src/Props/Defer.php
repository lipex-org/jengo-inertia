<?php

namespace Jengo\Inertia\Props;

use Inertia\Protocol\Props\Defer as ProtocolDefer;

class Defer extends ProtocolDefer
{
    public function __construct(
        string $group = 'default',
        mixed $callback = null,
        bool $rescue = false
    ) {
        parent::__construct($callback, $group, $rescue);
    }
}
