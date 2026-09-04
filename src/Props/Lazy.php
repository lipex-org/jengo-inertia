<?php

namespace Jengo\Inertia\Props;

use Inertia\Protocol\Props\Lazy as ProtocolLazy;

class Lazy extends ProtocolLazy
{
    public function __construct(
        mixed $callback
    ) {
        parent::__construct($callback);
    }
}
