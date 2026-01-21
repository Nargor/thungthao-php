<?php
require_once __DIR__ . '/includes/CoreFunction.php';
loadEnv(__DIR__ . '/.env');
$config = require_once __DIR__ . '/config.php';

$parsedUrl = parse_url($config['web']['url']);
$host = $parsedUrl['host'];
$port = $parsedUrl['port'] != null && !empty($parsedUrl['port']) ? $parsedUrl['port'] : '5000';

echo "Starting PHP Development Server...\n";
echo "Server will be available at http://{$host}:{$port}\n";
echo "Press Ctrl+C to stop the server\n";

$routerFile = __DIR__ . '/router.php';
if (!file_exists($routerFile)) {
file_put_contents($routerFile, <<<'PHP'
<?php
 $uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$file = __DIR__ . $uri;

// ✅ ถ้าไฟล์มีอยู่จริง ให้ PHP server เสิร์ฟเอง
if (is_file($file)) {
    return false;
}

// 🛠️ ไม่ใช่ไฟล์ → ส่งให้ index.php จัดการ
include __DIR__ . '/index.php';
PHP);
}

shell_exec(sprintf(
    'php -S %s:%d -t %s %s',
    $host,
    $port,
    escapeshellarg(__DIR__),
    escapeshellarg($routerFile)
));