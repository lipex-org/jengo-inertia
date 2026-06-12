<?php

namespace Jengo\Inertia\Props;

use Closure;

class Once
{
    public function __construct(
        public Closure $callback
    ) {}
}
