<?php

namespace Jengo\Inertia\Props;

use Inertia\Protocol\Props\Always as ProtocolAlways;

class Always extends ProtocolAlways
{
    public function __construct(
        public mixed $value
    ) {
        parent::__construct($value);
    }
}
