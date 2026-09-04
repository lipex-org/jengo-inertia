<?php

namespace Jengo\Inertia\Props;

use Inertia\Protocol\Props\Mergeable as ProtocolMergeable;

class Mergeable extends ProtocolMergeable
{
    public function __construct(
        mixed $value,
        bool $deep = false,
        bool $prepend = false,
        ?string $matchOn = null,
    ) {
        parent::__construct(
            value: $value,
            prepend: $prepend,
            deep: $deep,
            matchOn: $matchOn
        );
    }
}
