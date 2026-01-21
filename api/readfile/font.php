<?php
$file = isset($_GET['file']) ? basename($_GET['file']) : '';
$directory = __DIR__ . '/../../protect/font/';
$file_path = realpath($directory . $file);

$allowed_extensions = ['woff2', 'woff', 'otf'];
$file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

// เช็คว่า path อยู่ใน directory ที่กำหนด และไฟล์มีอยู่จริง
if (
    $file_path &&
    strpos($file_path, realpath($directory)) === 0 &&
    in_array($file_extension, $allowed_extensions) &&
    file_exists($file_path)
) {
    // Set Content-Type ที่ถูกต้อง
    switch ($file_extension) {
        case 'woff2':
            header('Content-Type: font/woff2');
            break;
        case 'woff':
            header('Content-Type: font/woff');
            break;
        case 'otf':
            header('Content-Type: font/otf');
            break;
        case 'ttf':
            header('Content-Type: font/ttf');
            break;
    }
    header('Content-Length: ' . filesize($file_path));
    readfile($file_path);
    exit;
} else {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Error: File not found or invalid file type.';
    exit;
}
?>