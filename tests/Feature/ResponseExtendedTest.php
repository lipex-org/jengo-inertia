<?php

declare(strict_types=1);

namespace Tests\Feature;

use CodeIgniter\HTTP\Response as HTTPResponse;
use CodeIgniter\View\View;
use Jengo\Inertia\Inertia;
use Jengo\Inertia\Response;
use Jengo\Inertia\Testing\AssertableInertia;
use Tests\Support\FeatureRequestTestCase;

uses(FeatureRequestTestCase::class);

describe('Response Extended Tests', function () {
    it('supports response history encryption, clearHistory, and preserveFragment flags', function () {
        $routes = [['get', 'history-test', static function () {
            $res = new Response('HistoryPage', ['data' => 123], 'v1');
            $res->encryptHistory(true)
                ->clearHistory(true)
                ->preserveFragment(true)
                ->scrollProps(['users' => ['reset' => true]])
                ->withSharedKeys(['user', 'theme']);

            return $res->toResponse();
        }]];

        /** @var FeatureRequestTestCase $this */
        $result = $this->withRoutes($routes)->withHeaders(['X-Inertia' => 'true'])->get('/history-test');

        expect($result->response())->toBeInstanceOf(HTTPResponse::class);
        $page = json_decode($result->response()->getJSON(), true);

        expect($page['encryptHistory'])->toBeTrue();
        expect($page['clearHistory'])->toBeTrue();
        expect($page['preserveFragment'])->toBeTrue();
        expect($page['scrollProps'])->toEqual(['users' => ['reset' => true]]);
        expect($page['sharedProps'])->toEqual(['user', 'theme']);
    });

    it('supports __toString rendering on Response', function () {
        $config = config('Inertia') ?? new \Jengo\Inertia\Config\Inertia();
        $config->rootView = 'Tests\Feature\Views\app';
        \CodeIgniter\Config\Factories::injectMock('config', 'Inertia', $config);

        $res = new Response('StringPage', ['title' => 'Test'], '1.0');
        $stringOutput = (string) $res;

        expect($stringOutput)->toContain('data-page="app"');
        expect($stringOutput)->toContain('StringPage');
    });

    it('supports AssertableInertia array list scoping, partial matching, count and missing assertions', function () {
        helper('inertia');
        $config = config('Inertia') ?? new \Jengo\Inertia\Config\Inertia();
        $config->rootView = 'Tests\Feature\Views\app';
        \CodeIgniter\Config\Factories::injectMock('config', 'Inertia', $config);

        $routes = [['get', 'complex-page', static function () {
            return \inertia('Admin/Dashboard', [
                'users' => [
                    ['name' => 'Alice', 'role' => 'Admin'],
                    ['name' => 'Bob', 'role' => 'User'],
                ],
                'tags' => ['php', 'inertia', 'vue'],
                'settings' => ['theme' => 'dark'],
            ]);
        }]];

        /** @var FeatureRequestTestCase $this */
        $result = $this->withRoutes($routes)->get('/complex-page');

        $result->assertInertia(function (AssertableInertia $page) {
            $page->component('Dashboard', false) // partial match
                ->has('users', 2, function (AssertableInertia $userScope) {
                    $userScope->where('name', 'Alice')
                        ->where('role', 'Admin');
                })
                ->count('tags', 3)
                ->missing('deleted_at')
                ->has('settings.theme')
                ->etc();
        });
    });

    it('supports AssertableInertia reloadExcept follow-up request', function () {
        helper('inertia');
        $config = config('Inertia') ?? new \Jengo\Inertia\Config\Inertia();
        $config->rootView = 'Tests\Feature\Views\app';
        \CodeIgniter\Config\Factories::injectMock('config', 'Inertia', $config);

        $routes = [['get', 'reload-except-test', static function () {
            $request = service('request');
            $except = $request->header('X-Inertia-Partial-Except')
                ? explode(',', $request->header('X-Inertia-Partial-Except')->getValue())
                : [];

            $props = ['keep' => 'kept_data', 'drop' => 'dropped_data'];
            foreach ($except as $k) {
                unset($props[$k]);
            }

            return \inertia('Home', $props);
        }]];

        /** @var FeatureRequestTestCase $this */
        $result = $this->withRoutes($routes)->get('/reload-except-test');

        $result->assertInertia(function (AssertableInertia $page) {
            $page->component('Home')
                ->where('keep', 'kept_data')
                ->where('drop', 'dropped_data')
                ->reloadExcept('drop', function (AssertableInertia $reload) {
                    $reload->where('keep', 'kept_data')
                        ->missing('drop');
                });
        });
    });

    it('supports post, put, patch, delete HTTP methods and assertInertia without callback', function () {
        helper('inertia');
        $config = config('Inertia') ?? new \Jengo\Inertia\Config\Inertia();
        $config->rootView = 'Tests\Feature\Views\app';
        \CodeIgniter\Config\Factories::injectMock('config', 'Inertia', $config);

        $routes = [
            ['post', 'users', static function () {
                $name = service('request')->getPost('name') ?? 'Default';
                return \inertia('Users/Index', ['created' => $name]);
            }],
            ['put', 'users/(:num)', static function ($id) {
                return \inertia('Users/Index', ['updated' => (int) $id]);
            }],
            ['patch', 'users/(:num)', static function ($id) {
                return \inertia('Users/Index', ['patched' => (int) $id]);
            }],
            ['delete', 'users/(:num)', static function ($id) {
                return \inertia('Users/Index', ['deleted' => (int) $id]);
            }],
        ];

        /** @var FeatureRequestTestCase $this */
        $postRes = $this->withRoutes($routes)->post('/users', ['name' => 'Charlie']);
        $postRes->assertStatus(200);
        $this->assertInertia($postRes);
        $postRes->assertInertia(function (AssertableInertia $page) {
            $page->component('Users/Index')->where('created', 'Charlie');
        });

        $putRes = $this->withRoutes($routes)->put('/users/42', ['role' => 'admin']);
        $putRes->assertStatus(200);
        $this->assertInertia($putRes);
        $putRes->assertInertia(function (AssertableInertia $page) {
            $page->component('Users/Index')->where('updated', 42);
        });

        $patchRes = $this->withRoutes($routes)->patch('/users/42', ['status' => 'active']);
        $patchRes->assertStatus(200);
        $this->assertInertia($patchRes);
        $patchRes->assertInertia(function (AssertableInertia $page) {
            $page->component('Users/Index')->where('patched', 42);
        });

        $delRes = $this->withRoutes($routes)->delete('/users/42');
        $delRes->assertStatus(200);
        $this->assertInertia($delRes);
        $delRes->assertInertia(function (AssertableInertia $page) {
            $page->component('Users/Index')->where('deleted', 42);
        });
    });

    it('supports assertInertia on X-Inertia requests and raw response objects', function () {
        helper('inertia');
        $config = config('Inertia') ?? new \Jengo\Inertia\Config\Inertia();
        $config->rootView = 'Tests\Feature\Views\app';
        \CodeIgniter\Config\Factories::injectMock('config', 'Inertia', $config);

        $routes = [['get', 'api-page', static function () {
            return \inertia('Api/Page', ['status' => 'ok', 'nested' => ['item' => 'val']]);
        }]];

        /** @var FeatureRequestTestCase $this */
        $result = $this->withRoutes($routes)->withHeaders(['X-Inertia' => 'true'])->get('/api-page');
        $this->assertInertia($result);
        $this->assertInertia($result->response());

        // Test fluent assertInertia callback on X-Inertia response
        $result->assertInertia(function (AssertableInertia $page) {
            $page->component('Api/Page')
                ->where('status', 'ok')
                ->missing('nested.missing_prop')
                ->has('nested.item');
        });

        // Test __call delegation on TestResponse
        $result->assertStatus(200);
        $result->assertOK();
        expect($result->isOK())->toBeTrue();
        expect($result->response())->toBeInstanceOf(\CodeIgniter\HTTP\ResponseInterface::class);

        // Test inertiaProps on X-Inertia response
        $props = $result->inertiaProps();
        expect($props)->toHaveKey('status');
        expect($result->inertiaProps('status'))->toEqual('ok');
        expect($result->inertiaProps('nested.item'))->toEqual('val');
        expect($result->inertiaProps('nonexistent.nested'))->toBeNull();
    });

    it('supports loadDeferredProps with string group name and empty group fallback', function () {
        helper('inertia');
        $config = config('Inertia') ?? new \Jengo\Inertia\Config\Inertia();
        $config->rootView = 'Tests\Feature\Views\app';
        \CodeIgniter\Config\Factories::injectMock('config', 'Inertia', $config);

        $routes = [['get', 'deferred-group-test', static function () {
            $request = service('request');
            $partialOnly = $request->header('X-Inertia-Partial-Only')
                ? explode(',', $request->header('X-Inertia-Partial-Only')->getValue())
                : [];

            if (in_array('stats', $partialOnly, true)) {
                return \inertia('StatsPage', ['stats' => ['visits' => 100]]);
            }

            return \inertia('StatsPage', [
                'stats' => \Jengo\Inertia\Inertia::defer(fn() => ['visits' => 100], 'analytics'),
                'main' => 'data',
            ]);
        }]];

        /** @var FeatureRequestTestCase $this */
        $result = $this->withRoutes($routes)->get('/deferred-group-test?tab=overview');

        $result->assertInertia(function (AssertableInertia $page) {
            $page->component('StatsPage')
                ->where('main', 'data')
                ->loadDeferredProps('analytics', function (AssertableInertia $analytics) {
                    $analytics->where('stats.visits', 100);
                })
                ->loadDeferredProps('nonexistent_group', function (AssertableInertia $same) {
                    $same->where('main', 'data');
                });
        });
    });

    it('supports Response with individual key-value, component change, and X-Inertia __toString', function () {
        $res = new Response('InitialComp', [], '1.0');
        $res->with('single_key', 'single_val')
            ->withComponent('UpdatedComp')
            ->withVersion('3.0');

        // Mock incoming request with X-Inertia header
        $request = service('request');
        $request->setHeader('X-Inertia', 'true');

        $jsonOutput = (string) $res;
        $decoded = json_decode($jsonOutput, true);

        expect($decoded['component'])->toEqual('UpdatedComp');
        expect($decoded['props']['single_key'])->toEqual('single_val');
        expect($decoded['version'])->toEqual('3.0');
    });

    it('supports AssertableInertia missingFlash and hasFlash', function () {
        session()->setFlashdata('notification', 'Welcome back');

        $assertable = new AssertableInertia('Profile', ['name' => 'Bob'], '/profile');
        $assertable->missingFlash('error_notification')
            ->hasFlash('notification', 'Welcome back')
            ->etc();

        expect(true)->toBeTrue();
    });
});
