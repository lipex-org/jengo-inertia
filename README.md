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
  <a href="https://lipex-org.github.io/jengophp.com/packages/inertia/"><strong>Official Documentation</strong></a> •
  <a href="https://github.com/lipex-org/jengo-inertia/blob/main/LICENSE"><strong>License</strong></a> •
  <a href="https://github.com/lipex-org/jengo-inertia/issues"><strong>Issues</strong></a> •
  <a href="https://packagist.org/packages/jengo/inertia"><strong>Packagist</strong></a>
</p>

---

`jengo/inertia` is a full-featured adapter implementing the **Inertia.js v3 server-side protocol** for **CodeIgniter 4**. Build modern single-page applications using React, Vue 3, or Svelte with familiar CodeIgniter controllers and routing.

> For comprehensive guides, complete API reference, and examples, visit the **[Official Jengo Inertia Documentation](https://lipex-org.github.io/jengophp.com/packages/inertia/)**.

---

## Protocol Support

`jengo/inertia` implements the complete **Inertia.js v3** protocol:

| Protocol Feature | Supported | Description |
| :--- | :---: | :--- |
| **Inertia Version** | **v3** | Full protocol implementation |
| **Initial Requests** | Yes | Renders root HTML template with script tag payload (`<script type="application/json" data-page="app">`) |
| **Inertia Visits** | Yes | JSON responses with `X-Inertia: true` header |
| **Asset Versioning** | Yes | `X-Inertia-Version` tracking with `409 Conflict` and `X-Inertia-Location` headers |
| **303 Redirects** | Yes | RFC 7231 `303 See Other` redirects on `PUT`, `PATCH`, and `DELETE` requests |
| **External Redirects** | Yes | `Inertia::location($url)` triggers full window location redirect |
| **Partial Reloads** | Yes | Selective prop evaluation via `only` and `except` |
| **Lazy Props** | Yes | Evaluated on-demand via `Inertia::lazy()` |
| **Deferred Props** | Yes | Non-blocking background props via `Inertia::defer()` |
| **Once Props** | Yes | Evaluated once and cached client-side via `Inertia::once()` |
| **Merge Props** | Yes | Pagination and scroll appending via `Inertia::merge()`, `prepend()`, and `deepMerge()` |
| **Always Props** | Yes | Evaluated on every request via `Inertia::always()` |
| **Instant Visits** | Yes | Shared keys passed automatically via `X-Inertia-Shared-Keys` |
| **History Control** | Yes | History encryption (`encryptHistory()`) and clearing (`clearHistory()`) |
| **Page Existence** | Yes | Prevents blank screens by validating component existence before render |
| **SSR Gateway** | Yes | Server-side rendering support with Node rendering service |
| **Testing Suite** | Yes | Fluent assertion helpers via `assertInertia` and `AssertableInertia` |

---

## Installation

Install via Composer:

```bash
composer require jengo/inertia
```

Scaffold Vite and Inertia configurations:

```bash
php spark jengo:install vite
php spark jengo:install inertia
```

---

## Root Template

The root template is the initial HTML layout loaded on the first page visit. By default, `jengo/inertia` uses `app/Views/app.php`.

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
    <?= vite_tags(); ?>
    <?= Inertia::init($page, true); /* SSR head tags */ ?>
</head>
<body>
    <?= Inertia::init($page); /* Inertia app container and JSON script payload */ ?>
</body>
</html>
```

To customize the root view name, configure `$rootView` in `app/Config/Inertia.php` or call `Inertia::setRootView('layout')`.

---

## Shared Data

Shared data is automatically passed to every Inertia response, making it available to all frontend components without passing it manually from each controller.

### Via Middleware (`HandleInertiaRequests`)

In `app/Filters/HandleInertiaRequests.php`, define shared props in `withShare()`:

```php
namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use Jengo\Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function withShare(RequestInterface $request): array
    {
        return array_merge(parent::withShare($request), [
            'auth' => [
                'user' => auth()->user() ? [
                    'id'    => auth()->user()->id,
                    'email' => auth()->user()->email,
                ] : null,
            ],
            'csrf_token' => csrf_token(),
            'csrf_hash'  => csrf_hash(),
        ]);
    }
}
```

### Via `Inertia::share()`

Share data dynamically anywhere in controllers or hooks:

```php
use Jengo\Inertia\Inertia;

Inertia::share('siteName', 'My Application');
Inertia::share(['locale' => 'en', 'theme' => 'dark']);
```

Validation errors (`errors`) and session flash data (`flash`) are automatically shared by default.

---

## Quick Example

```php
namespace App\Controllers;

use Jengo\Inertia\Inertia;

class DashboardController extends BaseController
{
    public function index()
    {
        return Inertia::render('Dashboard', [
            'totalUsers' => 150,
            'recentActivity' => Inertia::defer(fn () => model('ActivityModel')->recent()),
        ]);
    }
}
```

---

## Documentation

For complete guides, configuration options, and advanced features, visit the official documentation:

* [Basic Usage](https://lipex-org.github.io/jengophp.com/packages/inertia/basic-usage)
* [Inertia v3 Features (Deferred, Lazy, Once, Merge, Always Props)](https://lipex-org.github.io/jengophp.com/packages/inertia/v3-features)
* [Component Existence Checks](https://lipex-org.github.io/jengophp.com/packages/inertia/component-existence)
* [Exception Handling & Error Pages (404, 500, 403)](https://lipex-org.github.io/jengophp.com/packages/inertia/error-pages)
* [History & Navigation Control](https://lipex-org.github.io/jengophp.com/packages/inertia/history-control)
* [Testing with Pest & PHPUnit](https://lipex-org.github.io/jengophp.com/packages/inertia/testing)
* [Server-Side Rendering (SSR)](https://lipex-org.github.io/jengophp.com/packages/inertia/ssr)

---

## License & Maintainer

* **Maintainer**: Ian Ochieng ([ochiengian383@gmail.com](mailto:ochiengian383@gmail.com))
* **Organization**: [Lipex Org](https://github.com/lipex-org)
* **License**: Released under the [MIT License](https://github.com/lipex-org/jengo-inertia/blob/main/LICENSE).
