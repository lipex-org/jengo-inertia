<?php

namespace Jengo\Inertia\Props;

use Inertia\Protocol\Props\Mergeable as ProtocolMergeable;

class Mergeable extends ProtocolMergeable
{
    public function __construct(
        public mixed $value,
        public bool $deep = false,
        public bool $prepend = false,
        public ?string $matchOn = null,
    ) {
        parent::__construct($value, $matchOn, $deep, $prepend);
    }
}
