<?php

namespace Jengo\Inertia\Props;

use Closure;

class Lazy
{
    public function __construct(
        public Closure $callback
    ) {}
}
