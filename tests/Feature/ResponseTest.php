<?php

declare(strict_types=1);

/**
 * This file is part of Inertia.js Codeigniter 4.
 *
 * (c) 2023 Fab IT Hub <hello@fabithub.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests\Feature;

use CodeIgniter\HTTP\Response as HTTPResponse;
use CodeIgniter\View\View;
use Jengo\Inertia\Directive;
use Jengo\Inertia\Response;
use Tests\Support\FeatureRequestTestCase;

uses(FeatureRequestTestCase::class);

describe('Inertia Response Tests', function () {
    it(
        'is a valid inertia response from a server request',
        function () {
            $routes = [['get', 'user/(:num)', '\Jengo\Inertia\Controllers\TestController::index']];

            /** @var FeatureRequestTestCase $this */
            $result = $this->withRoutes($routes)->withBodyFormat('json')->get('/user/123');

            $user = ['name' => 'Jonathon'];
            $response = new Response('User/Edit', ['user' => $user], '123');
            $view = $response->toResponse($result->request());
            $page = $view->getData()['page'];

            expect($result->response())->toBeInstanceOf(HTTPResponse::class);
            expect($view)->toBeInstanceOf(View::class);
            expect($page)->toHaveKeys(['component', 'props.user.name', 'url', 'version']);

            expect($page['version'])->toEqual('123');
            expect($page['component'])->toEqual('User/Edit');
            expect($page['props']['user']['name'])->toEqual('Jonathon');
            expect(str_replace('index.php/', '', $page['url']))->toEqual('/user/123');

            $expectedHtml = '<script type="application/json" data-page="app">' . json_encode($page, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES) . '</script><div id="app"></div>';
            expect($view->renderString(Directive::compile($page)))->toEqual($expectedHtml);
        }
    );

    it(
        'is a valid inertia response from a xhr request',
        function () {
            $routes = [['get', 'user/(:num)', '\Jengo\Inertia\Controllers\TestController::index']];
            $headers = ['X-Inertia' => true];

            /** @var FeatureRequestTestCase $this */
            $result = $this->withRoutes($routes)->withHeaders($headers)->get('/user/123');

            $user = ['name' => 'Jonathon'];
            $response = new Response('User/Edit', ['user' => $user], '123');
            $view = $response->toResponse($result->request());
            $page = json_decode($view->getJSON());

            expect($view)->toBeInstanceOf(HTTPResponse::class);

            expect($page->version)->toEqual('123');
            expect($page->component)->toEqual('User/Edit');
            expect($page->props->user->name)->toEqual('Jonathon');
            expect(str_replace('index.php/', '', $page->url))->toEqual('/user/123');
        }
    );

    it(
        'can assert inertia response payloads fluently',
        function () {
            helper('inertia');

            $config = config('Inertia') ?? new \Jengo\Inertia\Config\Inertia();
            $config->rootView = 'Tests\Feature\Views\app';
            \CodeIgniter\Config\Factories::injectMock('config', 'Inertia', $config);

            $routes = [
                [
                    'get',
                    'user-profile',
                    static function () {
                        return \inertia('User/Edit', [
                            'user' => ['name' => 'Jonathon', 'email' => 'jon@example.com'],
                            'roles' => ['admin', 'editor']
                        ]);
                    }
                ]
            ];

            /** @var FeatureRequestTestCase $this */
            $result = $this->withRoutes($routes)->get('/user-profile');

            $this->assertInertia($result, function (\Jengo\Inertia\Testing\AssertableInertia $page) {
                $page->component('User/Edit')
                    ->has('user')
                    ->where('user.name', 'Jonathon')
                    ->where('user.email', 'jon@example.com')
                    ->count('roles', 2);
            });
        }
    );

    it('can retrieve inertia props using dot notation', function () {
        helper('inertia');
        $config = config('Inertia') ?? new \Jengo\Inertia\Config\Inertia();
        $config->rootView = 'Tests\Feature\Views\app';
        \CodeIgniter\Config\Factories::injectMock('config', 'Inertia', $config);

        $routes = [
            ['get', 'props-test', static function () {
                return \inertia('Home', ['user' => ['name' => 'John', 'age' => 30]]);
            }]
        ];

        /** @var FeatureRequestTestCase $this */
        $result = $this->withRoutes($routes)->get('/props-test');

        expect($result->inertiaProps())->toBeArray();
        expect($result->inertiaProps('user.name'))->toEqual('John');
        expect($result->inertiaProps('user.age'))->toEqual(30);
    });

    it('validates interactions and supports etc()', function () {
        helper('inertia');
        $config = config('Inertia') ?? new \Jengo\Inertia\Config\Inertia();
        $config->rootView = 'Tests\Feature\Views\app';
        \CodeIgniter\Config\Factories::injectMock('config', 'Inertia', $config);

        $routes = [
            ['get', 'interaction-test', static function () {
                return \inertia('Home', ['user' => 'John', 'ignored' => 'value']);
            }]
        ];

        /** @var FeatureRequestTestCase $this */
        $result = $this->withRoutes($routes)->get('/interaction-test');

        // 1. Fails if we don't interact with all keys
        $failed = false;
        try {
            $result->assertInertia(fn ($page) => $page->component('Home')->has('user'));
        } catch (\PHPUnit\Framework\AssertionFailedError $e) {
            $failed = true;
            expect($e->getMessage())->toContain('unexpected properties: [ignored]');
        }
        expect($failed)->toBeTrue();

        // 2. Passes if we call etc()
        $result->assertInertia(fn ($page) => $page->component('Home')->has('user')->etc());
    });

    it('supports reloadOnly and reloadExcept', function () {
        helper('inertia');
        $config = config('Inertia') ?? new \Jengo\Inertia\Config\Inertia();
        $config->rootView = 'Tests\Feature\Views\app';
        \CodeIgniter\Config\Factories::injectMock('config', 'Inertia', $config);

        $routes = [
            ['get', 'reload-test', static function () {
                $request = service('request');
                $isPartial = $request->header('X-Inertia-Partial-Only') ? true : false;
                $props = ['a' => 'initial_a', 'b' => 'initial_b'];
                if ($isPartial) {
                    $only = explode(',', $request->header('X-Inertia-Partial-Only')->getValue());
                    $filtered = [];
                    foreach ($only as $k) {
                        $filtered[$k] = 'loaded_' . $k;
                    }
                    return \inertia('Home', $filtered);
                }
                return \inertia('Home', $props);
            }]
        ];

        /** @var FeatureRequestTestCase $this */
        $result = $this->withRoutes($routes)->get('/reload-test');

        $result->assertInertia(fn ($page) => $page
            ->component('Home')
            ->where('a', 'initial_a')
            ->where('b', 'initial_b')
            ->reloadOnly('a', fn ($reload) => $reload
                ->where('a', 'loaded_a')
                ->missing('b')
            )
        );
    });

    it('supports loadDeferredProps', function () {
        helper('inertia');
        $config = config('Inertia') ?? new \Jengo\Inertia\Config\Inertia();
        $config->rootView = 'Tests\Feature\Views\app';
        \CodeIgniter\Config\Factories::injectMock('config', 'Inertia', $config);

        $routes = [
            ['get', 'deferred-test', static function () {
                $request = service('request');
                $isPartial = $request->header('X-Inertia-Partial-Only') ? true : false;

                if ($isPartial) {
                    return \inertia('Home', ['deferred_prop' => 'resolved_value']);
                }

                return \inertia('Home', [
                    'normal_prop' => 'normal',
                    'deferred_prop' => \Jengo\Inertia\Inertia::defer(fn() => 'resolved_value', 'default')
                ]);
            }]
        ];

        /** @var FeatureRequestTestCase $this */
        $result = $this->withRoutes($routes)->get('/deferred-test');

        $result->assertInertia(fn ($page) => $page
            ->component('Home')
            ->where('normal_prop', 'normal')
            ->missing('deferred_prop')
            ->loadDeferredProps(fn ($reload) => $reload
                ->where('deferred_prop', 'resolved_value')
                ->etc()
            )
        );
    });

    it('supports flash data assertions', function () {
        helper('inertia');
        $config = config('Inertia') ?? new \Jengo\Inertia\Config\Inertia();
        $config->rootView = 'Tests\Feature\Views\app';
        \CodeIgniter\Config\Factories::injectMock('config', 'Inertia', $config);

        $routes = [
            ['get', 'flash-page', static function () {
                session()->setFlashdata('message', 'Item saved!');
                session()->setFlashdata('nested', ['type' => 'success']);
                return \inertia('Home', []);
            }],
            ['get', 'redirect-trigger', static function () {
                session()->setFlashdata('message', 'Redirect flash!');
                return redirect()->to('/flash-page');
            }]
        ];

        /** @var FeatureRequestTestCase $this */
        $result = $this->withRoutes($routes)->get('/flash-page');

        $result->assertInertia(fn ($page) => $page
            ->component('Home')
            ->hasFlash('message', 'Item saved!')
            ->hasFlash('nested.type', 'success')
            ->missingFlash('error')
        );

        $redirectResult = $this->withRoutes($routes)->get('/redirect-trigger');
        $redirectResult->assertInertiaFlash('message', 'Redirect flash!')
            ->assertInertiaFlashMissing('error');
    });
});