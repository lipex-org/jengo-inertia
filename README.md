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

## Documentation

For full guides on Inertia v3 protocol features (lazy, deferred, and partial props), history encryption, and Vite configuration, visit https://lipex-org.github.io/jengophp.com/packages/inertia.

## License

Released under the MIT License.
