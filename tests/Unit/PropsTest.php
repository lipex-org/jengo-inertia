<?php

declare(strict_types=1);

namespace Tests\Unit;

use Jengo\Inertia\Props\Always;
use Jengo\Inertia\Props\Defer;
use Jengo\Inertia\Props\Lazy;
use Jengo\Inertia\Props\Mergeable;
use Jengo\Inertia\Props\Once;
use Tests\TestCase;

class PropsTest extends TestCase
{
    public function testAlwaysProp(): void
    {
        $prop = new Always('constant_value');
        $this->assertSame('constant_value', $prop->value);
        $this->assertSame('constant_value', $prop->resolve());

        $propCallable = new Always(fn() => 'computed_always');
        $this->assertSame('computed_always', $propCallable->resolve());
    }

    public function testLazyProp(): void
    {
        $evaluated = false;
        $prop = new Lazy(function () use (&$evaluated) {
            $evaluated = true;
            return 'lazy_result';
        });

        $this->assertFalse($evaluated);
        $this->assertSame('lazy_result', $prop->resolve());
        $this->assertTrue($evaluated);
    }

    public function testDeferProp(): void
    {
        $prop = new Defer('analytics', fn() => ['views' => 100], true);
        $this->assertSame('analytics', $prop->group);
        $this->assertTrue($prop->rescue);
        $this->assertSame(['views' => 100], $prop->resolve());
    }

    public function testMergeableProp(): void
    {
        $prop = new Mergeable(['item1'], deep: true, prepend: true, matchOn: 'id');
        $this->assertSame(['item1'], $prop->value);
        $this->assertTrue($prop->deep);
        $this->assertTrue($prop->prepend);
        $this->assertSame('id', $prop->matchOn);
        $this->assertSame(['item1'], $prop->resolve());
    }

    public function testOnceProp(): void
    {
        $expires = time() + 3600;
        $prop = new Once(fn() => 'cached_once', $expires);
        $this->assertSame($expires, $prop->expiresAt);
        $this->assertSame('cached_once', $prop->resolve());
    }
}
