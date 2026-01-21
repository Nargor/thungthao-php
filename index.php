<?php
namespace App;
use App\includes\Router;
session_start();

require_once __DIR__ . '/includes/Router.php';
$config = getConfig();
define('URL', $config['web']['url']);
$request = $_SERVER['REQUEST_URI'];
if (strpos($request, '/js/') === 0 || strpos($request, '/uploads/') === 0) {
    $file = __DIR__ . '/public' . $request;
    if (file_exists($file)) {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        switch ($ext) {
            case 'js':
                header('Content-Type: application/javascript');
                break;
            case 'css':
                header('Content-Type: text/css');
                break;
            case 'jpg':
            case 'jpeg':
                header('Content-Type: image/jpeg');
                break;
            case 'png':
                header('Content-Type: image/png');
                break;
            case 'gif':
                header('Content-Type: image/gif');
                break;
            case 'mp4':
                header('Content-Type: video/mp4');
                break;
        }
        readfile($file);
        exit;
    }
}




require __DIR__ . '/global.php';

$router = new Router();

$router->dispatch();
