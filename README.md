# Thungthao
 
 Pages layouts components system by PHP starter code.
 
starter with layouts, pages (file-based routing), components, controllers, composables, API, and global hooks.

## Features
- **Layouts**: Define page shells in `layouts/` and switch with `$layout->setLayout('default')`.
- **File-based Routing**: Files in `pages/` map to URLs. Dynamic params via `[param]`, folder `index.php` supported.
- **Components**: Reusable UI from `components/` included in layouts/pages.
- **Controllers**: PSR-4 autoloaded under `App\\Controllers\\`.
- **Composables**: Utilities in `composables/` that return callable arrays.
- **API**: JSON endpoints in `api/` (directly accessible as `/api/*.php`).
- **Global**: `global.php` runs on every request.
- **Styling**: TailwindCSS + jQuery via CDN in layout.
- **Docs**: Built-in documentation at `/docs` (navbar, sidebar, search).

## Documentation
- Online: https://thungthao.online

## Requirements
- PHP >= 7.0
- Composer
- Web server (Apache/Nginx) or PHP built-in server
- For Apache, enable rewrite and use the provided `.htaccess`

## Quick Start
```bash
# create a new project
composer create-project webdernargor/thungthao

# copy environment template
cp example.env .env   # Windows PowerShell: Copy-Item example.env .env

# start the dev server (uses serve.php)
composer web
```
Open the URL configured in `.env` (`WEB_URL`) or as printed by the dev server.

## Configuration (.env)
Key variables consumed by `config.php`:
- `WEB_MODE` (e.g. `development`)
- `WEB_URL` (e.g. `http://127.0.0.1:5000`)
- `WEB_NAME`
- `JWT_SECRET`
- `DB_DRIVER` (`mysql` | `sqlite` | `none`)
- MySQL: `MYSQL_HOST`, `MYSQL_DATABASE`, `MYSQL_USER`, `MYSQL_PASSWORD`, `MYSQL_PORT`, `MYSQL_CHARSET`
- SQLite: `SQLITE_PATH`

## Directory Structure
```
.
├─ api/                # JSON endpoints
├─ components/         # UI components
├─ composables/        # Reusable utilities (return arrays of functions)
├─ controllers/        # App\\Controllers classes
├─ includes/           # Core (internal) — avoid editing
├─ layouts/            # Page shells
├─ pages/              # File-based routes
├─ public/             # Public assets (e.g., js/docs-search.js)
├─ config.php          # Loads .env and builds config array
├─ global.php          # Runs on every request
├─ index.php           # Entry point
└─ serve.php           # Dev server helper
```

## Usage Examples

### Set head in a page
```php
<?php
$config = getConfig();
$setHead(<<<HTML
<title> Home - {$config['web']['name']}</title>
HTML);
?>
```

### Switch layout for a page
```php
<?php $layout->setLayout('default'); ?>
```

### Include a component
```php
<?php include __DIR__ . "/../components/navbar.php"; ?>
```

### Use a composable
```php
<?php
$dbUtil = composables('useDB');
$pdo = $dbUtil['connect']();
?>
```

### Use a controller
```php
<?php
use App\\Controllers\\Mycontroller;
$ctl = new Mycontroller();
?>
```

### Create an API endpoint
```php
<?php
// api/hello.php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/CoreFunction.php';
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['status' => 'ok']);
```

### Dynamic params in routes
```php
<?php
// pages/blog/[id].php
$params = useParams();
echo $params['id'] ?? '';
?>
```

### Global hooks
```php
<?php
// global.php
// Place helpers or middleware-like checks here
```

## Development Tips
- Set `WEB_MODE=development` to enable helpful logs via `show_error_log()`.
- Avoid modifying `includes/` unless you intend to change the framework internals.

## License (Owner‑Managed)
- The project owner [@WEBDERNargor](https://github.com/WEBDERNargor) manages the license terms.
- Current status: **Free to use** (personal or commercial) and modify within your projects. Attribution is appreciated.
- Future changes: License terms may change at your discretion. New releases may adopt new terms; previously released versions remain governed by the terms under which they were published.
- Provided “as is”, without warranty of any kind. Use at your own risk.

If you formalize terms later, add a dedicated `LICENSE` file and update this section.



## Authors
- [@WEBDERNargor](https://github.com/WEBDERNargor)
