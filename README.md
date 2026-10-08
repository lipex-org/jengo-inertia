<p align="center">
  <a href="https://lipex-org.github.io/jengophp.com/">
    <img src="https://raw.githubusercontent.com/lipex-org/docs/main/public/logo-full.png" width="220" alt="Jengo Logo">
  </a>
</p>

<h1 align="center">Jengo Inertia</h1>

<p align="center">
  <strong>Modern Single Page Application (SPA) adapter connecting CodeIgniter 4 with React, Vue 3, and Svelte via Inertia.js v3.</strong>
</p>

<p align="center">
  <a href="https://lipex-org.github.io/jengophp.com/packages/inertia"><strong>Documentation</strong></a> •
  <a href="https://github.com/lipex-org/inertia/blob/main/LICENSE"><strong>License</strong></a> •
  <a href="https://github.com/lipex-org/inertia/issues"><strong>Issues</strong></a>
</p>

---

## Installation

```bash
composer require jengo/inertia
php spark jengo:install vite
php spark jengo:install inertia
```

## Quick Start

```php
namespace App\Controllers;

use Jengo\Inertia\Inertia;

class DashboardController extends BaseController
{
    public function index()
    {
        return Inertia::render('Dashboard', [
            'totalUsers' => 150,
            'recentActivity' => [...],
        ]);
    }
}
```

## Exception Handling & Error Pages *(New in v1.1.23)*

To return matching Inertia components on errors (e.g. 404, 500, 403) instead of raw CodeIgniter HTML views, `jengo/inertia` provides `InertiaExceptionHandler` (introduced in **v1.1.23**).

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

Running `php spark jengo:install inertia` automatically publishes this configuration. Error pages can be customized per status code in `app/Config/Inertia.php` via `$errorPages`.

## Documentation

For full guides on Inertia v3 protocol features (lazy, deferred, and partial props), history encryption, exception handling, and Vite configuration, visit https://lipex-org.github.io/jengophp.com/packages/inertia.

## License

Released under the MIT License.
