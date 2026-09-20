# Jengo Inertia

The official CodeIgniter 4 adapter for Inertia.js, bringing modern single-page app frontend workflows (React, Vue, Svelte) to classic server-side routing and controllers.

Documentation: https://lipex-org.github.io/jengophp.com/packages/inertia

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

## Exception Handling & Error Pages

To return matching Inertia components on errors (e.g. 404, 500, 403) instead of raw CodeIgniter HTML views, `jengo/inertia` provides `InertiaExceptionHandler`.

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
