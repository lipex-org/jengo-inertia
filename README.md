<p align="center">
  <a href="https://lipex-org.github.io/jengophp.com/">
    <img src="https://raw.githubusercontent.com/lipex-org/jengophp.com/main/public/logo-full.png" width="220" alt="Jengo Logo">
  </a>
</p>

<h1 align="center">Jengo Inertia</h1>

<p align="center">
  <strong>The official CodeIgniter 4 adapter for Inertia.js v3, connecting CodeIgniter with React, Vue 3, and Svelte.</strong>
</p>

<p align="center">
  <a href="https://packagist.org/packages/jengo/inertia"><img src="https://img.shields.io/packagist/v/jengo/inertia.svg?style=flat-square" alt="Latest Version on Packagist"></a>
  <a href="https://github.com/lipex-org/jengo-inertia/actions"><img src="https://img.shields.io/github/actions/workflow/status/lipex-org/jengo-inertia/ci.yml?branch=main&style=flat-square" alt="Build Status"></a>
  <a href="https://packagist.org/packages/jengo/inertia"><img src="https://img.shields.io/packagist/dt/jengo/inertia.svg?style=flat-square" alt="Total Downloads"></a>
  <a href="https://github.com/lipex-org/jengo-inertia/blob/main/LICENSE"><img src="https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square" alt="License"></a>
</p>

<p align="center">
  <a href="https://lipex-org.github.io/jengophp.com/packages/inertia"><strong>Documentation</strong></a> •
  <a href="https://github.com/lipex-org/jengo-inertia/blob/main/LICENSE"><strong>License</strong></a> •
  <a href="https://github.com/lipex-org/jengo-inertia/issues"><strong>Issues</strong></a> •
  <a href="https://packagist.org/packages/jengo/inertia"><strong>Packagist</strong></a>
</p>

---

## Overview

`jengo/inertia` is a full-featured adapter implementing the **Inertia.js v3 protocol** for **CodeIgniter 4**. It allows building modern single-page applications (SPAs) with Vue 3, React, or Svelte without building a specialized client API or duplicating routing logic.

## Protocol Support & Compatibility

`jengo/inertia` implements the complete **Inertia.js v3** server-side protocol.

| Protocol Feature | Supported | Description |
| :--- | :---: | :--- |
| **Inertia Version** | **v3** | Full protocol implementation (compatible with Inertia v3 and v2 client adapters) |
| **Initial Page Load** | Yes | Renders root HTML view with script tag payload (`<script type="application/json" data-page="app">`) |
| **Inertia Responses** | Yes | Returns JSON with `X-Inertia: true` header on client visits |
| **Asset Versioning** | Yes | Generates `X-Inertia-Version`, responds with `409 Conflict` and `X-Inertia-Location` on stale assets |
| **303 Redirects** | Yes | Automatically transforms 302 redirects to RFC 7231 `303 See Other` on `PUT`, `PATCH`, and `DELETE` requests |
| **External Redirects** | Yes | `Inertia::location($url)` triggers full window location change |
| **Partial Reloads** | Yes | Supports selective prop evaluation via `only` and `except` (`X-Inertia-Partial-Component`, `X-Inertia-Partial-Data`) |
| **Lazy Props** | Yes | `Inertia::lazy()` callbacks are only evaluated when explicitly requested |
| **Deferred Props** | Yes | `Inertia::defer()` props load in the background without blocking initial render |
| **Once Props** | Yes | `Inertia::once()` evaluates once per session/visit and is skipped on subsequent visits (`X-Inertia-Except-Once-Props`) |
| **Merge Props** | Yes | `Inertia::merge()`, `Inertia::prepend()`, and `Inertia::deepMerge()` for append/prepend pagination |
| **Always Props** | Yes | `Inertia::always()` props are included even during partial reloads |
| **Instant Visits** | Yes | Automatic tracking of shared prop keys passed via `X-Inertia-Shared-Keys` |
| **History Encryption** | Yes | `encryptHistory()` / `clearHistory()` controls browser history state encryption (`X-Inertia-Encrypt-History`) |
| **Fragment Preservation** | Yes | `preserveFragment()` retains URL fragments across visits |
| **Scroll Props** | Yes | `scrollProps()` selectively resets or preserves scroll regions (`X-Inertia-Scroll-Props`) |
| **Page Existence Checks** | Yes | Server-side verification of component existence preventing frontend blank screens |
| **Server-Side Rendering** | Yes | High-performance SSR gateway integration for Node rendering services |
| **Testing Utilities** | Yes | Fluent assertion helpers via `assertInertia` and `AssertableInertia` |

---

## Installation

Install the package via Composer:

```bash
composer require jengo/inertia
```

Run the automated installers to scaffold Vite and Inertia configurations:

```bash
php spark jengo:install vite
php spark jengo:install inertia
```

The installer publishes:
* `app/Views/app.php` (Root template)
* `app/Filters/HandleInertiaRequests.php` (Inertia middleware for shared data)
* `app/Config/Inertia.php` (Inertia configuration)
* `app/Config/Exceptions.php` (Inertia exception handler)
* Frontend scaffolding for React, Vue 3, or Svelte in `resources/js/inertia`
* `vite.config.ts` integration with CodeIgniter

---

## Root Template

When Inertia renders the initial page visit, it embeds the initial page JSON into your server-side HTML layout. By default, `jengo/inertia` looks for `app/Views/app.php`.

### Configuring the Root View

You can customize the root template path in `app/Config/Inertia.php`:

```php
namespace Config;

use Jengo\Inertia\Config\Inertia as BaseInertia;

class Inertia extends BaseInertia
{
    /**
     * The root template that's loaded on the first page visit.
     */
    public string $rootView = 'app';
}
```

Or dynamically inside a controller or filter:

```php
Inertia::setRootView('admin_layout');
```

### Template Directives

In your root template (`app/Views/app.php`), use `<?= Inertia::init($page) ?>` to render the Inertia root element and JSON script payload:

```php
<?php
use Jengo\Inertia\Inertia;
use function Jengo\Base\vite_tags;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jengo App</title>

    <!-- Vite Assets -->
    <?= vite_tags(); ?>

    <!-- Inertia SSR Head Tags -->
    <?= Inertia::init($page, true); ?>
</head>
<body>
    <!-- Inertia App Container & JSON Payload -->
    <?= Inertia::init($page); ?>
</body>
</html>
```

`Inertia::init($page)` automatically generates the Inertia v3 script tag and mount point:

```html
<script type="application/json" data-page="app">{"component":"Dashboard","props":{...},"url":"/dashboard","version":"..."}</script>
<div id="app"></div>
```

---

## Shared Data

Shared data is data that is automatically included with every Inertia response, making it accessible to all frontend components without manually passing it from every controller action.

### 1. Request Middleware (`HandleInertiaRequests`)

The recommended way to share data is via the `HandleInertiaRequests` filter (`app/Filters/HandleInertiaRequests.php`). It extends `Jengo\Inertia\Middleware` and overrides the `withShare()` method:

```php
namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use Jengo\Inertia\Middleware;
use function Jengo\Base\Support\arr;

class HandleInertiaRequests extends Middleware
{
    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function withShare(RequestInterface $request): array
    {
        return array_merge(parent::withShare($request), [
            'app_name' => config('App')->appName ?? 'Jengo',
            'auth' => [
                'user' => function_exists('auth') && auth()->user()
                    ? arr(auth()->user())->only(['id', 'username', 'email'])->asNullWhenEmpty()
                    : null,
            ],
            'csrf_token' => csrf_token(),
            'csrf_hash'  => csrf_hash(),
        ]);
    }
}
```

Enable the filter globally in `app/Config/Filters.php`:

```php
public array $aliases = [
    'inertia' => \App\Filters\HandleInertiaRequests::class,
];

public array $globals = [
    'before' => [
        'inertia',
    ],
    'after' => [
        'inertia',
    ],
];
```

### 2. Runtime Sharing (`Inertia::share`)

You can also share data imperatively at any point during request execution using the `Inertia::share()` API:

```php
use Jengo\Inertia\Inertia;

// Share single key
Inertia::share('siteName', 'My Application');

// Share multiple keys
Inertia::share([
    'locale' => session()->get('locale') ?? 'en',
    'theme'  => 'dark',
]);

// Read shared values
$siteName = Inertia::getShared('siteName');
$allShared = Inertia::getShared();
```

### 3. Automatic System Shared Props

`jengo/inertia` automatically shares the following system properties on every response:
* `errors`: Validation errors from CodeIgniter's validator or session flash data (formatted for error bags).
* `flash`: Session flash data (excluding internal CodeIgniter session keys and errors).

---

## Basic Usage

### Returning Responses

In your controllers, return responses using `Inertia::render()` or the `inertia()` helper function:

```php
namespace App\Controllers;

use Jengo\Inertia\Inertia;

class UserController extends BaseController
{
    public function index()
    {
        return Inertia::render('Users/Index', [
            'users' => model('UserModel')->paginate(15),
            'filters' => request()->getGet(),
        ]);
    }

    public function show(int $id)
    {
        // Using the global inertia() helper
        return inertia('Users/Show', [
            'user' => model('UserModel')->find($id),
        ]);
    }
}
```

---

## Inertia v3 Features

### Lazy Props

Lazy props are only evaluated when explicitly requested in partial reloads using `only`:

```php
return Inertia::render('Users/Index', [
    'users' => model('UserModel')->findAll(),
    'stats' => Inertia::lazy(fn () => model('StatsModel')->expensiveCalculation()),
]);
```

### Deferred Props

Deferred props load in the background after the initial page renders, preventing slow queries from delaying page load:

```php
return Inertia::render('Dashboard', [
    'totalUsers' => 120,
    'chartData'  => Inertia::defer(fn () => model('MetricsModel')->getChartData(), 'metrics'),
]);
```

### Once Props

Once props are evaluated on the initial visit and cached client-side; they are excluded from partial reloads via `X-Inertia-Except-Once-Props`:

```php
return Inertia::render('Profile', [
    'permissions' => Inertia::once(fn () => $user->getAllPermissions()),
]);
```

### Merge & Prepend Props

Mergeable props are useful for infinite scroll pagination:

```php
// Append new results
return Inertia::render('Feed', [
    'posts' => Inertia::merge($posts),
]);

// Prepend new results
return Inertia::render('Notifications', [
    'items' => Inertia::prepend($newItems),
]);

// Deep recursive merge
return Inertia::render('Tree', [
    'categories' => Inertia::deepMerge($categories),
]);
```

### Always Props

Always props are guaranteed to be returned on every visit, even during partial reloads:

```php
return Inertia::render('Dashboard', [
    'timestamp' => Inertia::always(time()),
]);
```

### External Redirects

To redirect to an external URL or non-Inertia route:

```php
return Inertia::location('https://stripe.com/checkout');
```

### History Encryption & Clearing

Protect sensitive information in browser history:

```php
// Encrypt current page state in browser history
return Inertia::render('Billing/Invoices', $data)->encryptHistory();

// Clear previous history state (e.g. after logout)
return Inertia::render('Auth/Login')->clearHistory();
```

---

## Page Component Existence Checks

To prevent blank screens when a frontend component is missing or mistyped, `jengo/inertia` provides server-side component verification.

Configure the `$pages` array in `app/Config/Inertia.php`:

```php
public array $pages = [
    'ensure_page_exists' => true,
    'pages' => [
        resource_path('js/inertia/pages'),
        resource_path('js/Pages'),
    ],
    'extensions' => ['vue', 'jsx', 'tsx', 'svelte', 'js', 'ts'],
    'error' => 'error',
];
```

When `ensure_page_exists` is `true` and a component cannot be found on disk, `Inertia::render()` throws `CodeIgniter\Exceptions\PageNotFoundException`, which `InertiaExceptionHandler` intercepts and renders as a 404 Inertia error component (`error` or `errors/404`).

### Component Existence in Tests

You can also enforce page component verification during test assertions via `$testing`:

```php
public array $testing = [
    'ensure_page_exists' => true,
    'page_paths' => [
        resource_path('js/Pages'),
    ],
];
```

In feature tests, `$page->component('Dashboard')` verifies that the component file exists on disk, failing the test if the file is absent.

---

## Exception Handling & Error Pages

To return Inertia error components on HTTP errors (e.g. 404, 500, 403) instead of raw CodeIgniter HTML views, `jengo/inertia` provides `InertiaExceptionHandler`.

In your `app/Config/Exceptions.php`:

```php
use CodeIgniter\Debug\ExceptionHandlerInterface;
use Jengo\Inertia\Debug\InertiaExceptionHandler;
use Throwable;

public function handler(int $statusCode, Throwable $exception): ExceptionHandlerInterface
{
    return new InertiaExceptionHandler($this);
}
```

Error pages are mapped per status code in `app/Config/Inertia.php`:

```php
public array $errorPages = [
    401 => 'errors/401',
    403 => 'errors/403',
    404 => 'errors/404',
    419 => 'errors/419',
    500 => 'errors/500',
    503 => 'errors/503',
    'error' => 'error',
];
```

---

## Testing

`jengo/inertia` provides fluent testing assertions via `assertInertia`:

```php
use Tests\Support\FeatureRequestTestCase;

class DashboardTest extends FeatureRequestTestCase
{
    public function testDashboardRendersCorrectly()
    {
        $result = $this->get('/dashboard');

        $result->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('totalUsers')
            ->where('totalUsers', 150)
            ->has('recentActivity', 5)
        );
    }
}
```

---

## Server-Side Rendering (SSR)

To enable Server-Side Rendering (SSR), set `$isSsrEnabled = true` in `app/Config/Inertia.php`:

```php
public bool $isSsrEnabled = true;
public string $ssrUrl = 'http://127.0.0.1:13714';
```

When enabled, `Inertia::init($page)` dispatches the page payload to the Node SSR service gateway and renders the server-rendered HTML and head elements.

---

## Maintainer & Community

* **Maintainer**: Ian Ochieng ([@ochiengian383](mailto:ochiengian383@gmail.com))
* **Organization**: [Lipex Org](https://github.com/lipex-org)
* **Website**: [https://lipex-org.github.io/jengophp.com/](https://lipex-org.github.io/jengophp.com/)

## License

Released under the [MIT License](https://github.com/lipex-org/jengo-inertia/blob/main/LICENSE).
