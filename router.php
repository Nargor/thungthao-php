<?php
$uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
if (!isset($blockedDirs)) {
    $myconfig = require(__DIR__ . '/config.php');
    $blockedDirs = $myconfig['web']['protect_folder'];


    foreach ($blockedDirs as $dir) {
        // ป้องกันเข้าทุก path ที่เริ่มด้วยโฟลเดอร์ที่กำหนด
        if (preg_match("#^/{$dir}(/|$)#", $uri)) {
            http_response_code(404);
            return false;
           
        }
    }
}
$file = __DIR__ . $uri;

if (is_file($file)) {
    return false;
}
include __DIR__ . "/index.php";
