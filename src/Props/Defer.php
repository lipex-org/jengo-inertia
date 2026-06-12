<?php

namespace Jengo\Inertia\Props;

use Closure;

class Defer
{
    public function __construct(
        public string $group = 'default',
        public ?Closure $callback = null,
    ) {}
}
