<?php

namespace Jengo\Inertia\Props;

class Mergeable
{
    public function __construct(
        public mixed $value,
        public bool $deep = false,
        public bool $prepend = false,
        public ?string $matchOn = null,
    ) {}
}
