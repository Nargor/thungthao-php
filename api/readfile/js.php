<?php
header('Content-Type: application/javascript');
$file = isset($_GET['file']) ? $_GET['file'] : '';
$directory = __DIR__ . '/../../protect/js/';
$file_path = $directory . $file . '.js';
$allowed_extensions = ['js'];
$file_extension = pathinfo($file_path, PATHINFO_EXTENSION);
if (in_array($file_extension, $allowed_extensions) && file_exists($file_path)) {
    readfile($file_path);
} else {
    echo 'Error: File not found or invalid file type.';
}
?>