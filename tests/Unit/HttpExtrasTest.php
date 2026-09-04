<?php

declare(strict_types=1);

namespace Tests\Unit;

use ArrayObject;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use Config\App;
use Config\Services;
use Jengo\Inertia\Extras\Arr;
use Jengo\Inertia\Extras\Http;
use Tests\TestCase;

class HttpExtrasTest extends TestCase
{
    private function createRequest(array $headers = []): IncomingRequest
    {
        $config = new App();
        $uri = new URI('http://example.com/test');
        $userAgent = new UserAgent();
        $request = new IncomingRequest($config, $uri, 'php://input', $userAgent);

        foreach ($headers as $key => $val) {
            $request->setHeader($key, $val);
        }

        Services::injectMock('request', $request);

        return $request;
    }

    public function testHttpIsInertiaRequest(): void
    {
        $req1 = $this->createRequest(['X-Inertia' => 'true']);
        $this->assertTrue(Http::isInertiaRequest($req1));

        $req2 = $this->createRequest([]);
        $this->assertFalse(Http::isInertiaRequest($req2));
    }

    public function testHttpIsPartialReload(): void
    {
        $req1 = $this->createRequest([
            'X-Inertia' => 'true',
            'X-Inertia-Partial-Component' => 'Users/Index',
        ]);
        $this->assertTrue(Http::isPartialReload($req1));

        $req2 = $this->createRequest(['X-Inertia' => 'true']);
        $this->assertFalse(Http::isPartialReload($req2));
    }

    public function testHttpGetPartialHeaders(): void
    {
        $req = $this->createRequest([
            'X-Inertia' => 'true',
            'X-Inertia-Partial-Data' => 'users,stats',
            'X-Inertia-Partial-Except' => 'meta,sidebar',
            'X-Inertia-Reset' => 'comments,feed',
            'X-Inertia-Except-Once-Props' => 'auth,theme',
        ]);

        $this->assertSame(['users', 'stats'], array_values(Http::getPartialData($req)));
        $this->assertSame(['meta', 'sidebar'], array_values(Http::getPartialExcept($req)));
        $this->assertSame(['comments', 'feed'], array_values(Http::getResetProps($req)));
        $this->assertSame(['auth', 'theme'], array_values(Http::getExceptOnceProps($req)));
    }

    public function testHttpGetHeaderValue(): void
    {
        $req = $this->createRequest(['Custom-Header' => 'CustomValue']);

        $this->assertSame('CustomValue', Http::getHeaderValue('Custom-Header', 'default', $req));
        $this->assertSame('fallback', Http::getHeaderValue('Non-Existent', 'fallback', $req));
    }

    public function testArrAccessibleAndExists(): void
    {
        $this->assertTrue(Arr::accessible(['a' => 1]));
        $this->assertTrue(Arr::accessible(new ArrayObject(['a' => 1])));
        $this->assertFalse(Arr::accessible('string'));
        $this->assertFalse(Arr::accessible(123));

        $this->assertTrue(Arr::exists(['key' => 'val'], 'key'));
        $this->assertFalse(Arr::exists(['key' => 'val'], 'missing'));

        $ao = new ArrayObject(['k' => 'v']);
        $this->assertTrue(Arr::exists($ao, 'k'));
        $this->assertFalse(Arr::exists($ao, 'm'));
        $this->assertFalse(Arr::exists('string_target', 'k'));
        $this->assertFalse(Arr::exists(123, 'k'));
    }

    public function testArrGet(): void
    {
        $data = [
            'user' => [
                'name' => 'Alice',
                'profile' => [
                    'role' => 'admin',
                ],
            ],
            'theme' => 'dark',
        ];

        $this->assertSame($data, Arr::get($data, null));
        $this->assertSame('dark', Arr::get($data, 'theme'));
        $this->assertSame('Alice', Arr::get($data, 'user.name'));
        $this->assertSame('admin', Arr::get($data, 'user.profile.role'));
        $this->assertSame('default', Arr::get($data, 'user.missing.role', 'default'));
        $this->assertSame('computed', Arr::get($data, 'user.nonexistent', fn() => 'computed'));
        $this->assertSame('not_accessible', Arr::get('scalar', 'key', 'not_accessible'));
    }

    public function testArrSet(): void
    {
        $data = [];
        Arr::set($data, 'user.name', 'Bob');
        Arr::set($data, 'user.profile.age', 30);
        Arr::set($data, 'active', true);

        $this->assertSame('Bob', $data['user']['name']);
        $this->assertSame(30, $data['user']['profile']['age']);
        $this->assertTrue($data['active']);

        $replaced = 'initial';
        Arr::set($replaced, null, 'all_new');
        $this->assertSame('all_new', $replaced);
    }

    public function testArrOnlyAndValue(): void
    {
        $data = ['a' => 1, 'b' => 2, 'c' => 3];
        $filtered = Arr::only($data, ['a', 'c']);
        $this->assertSame(['a' => 1, 'c' => 3], $filtered);

        $this->assertSame('hello', Arr::value('hello'));
        $this->assertSame(42, Arr::value(fn() => 42));
        $this->assertSame('arg_test', Arr::value(fn($x) => "arg_{$x}", 'test'));
    }
}
