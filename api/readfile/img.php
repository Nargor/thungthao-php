<?php
$config = require(__DIR__ . "/../../config.php");
$allowed_extensions = array_keys($config['web']['upload_extensions']);
$file = isset($_GET['file']) ? $_GET['file'] : '';
$directory = __DIR__ . '/../../protect/img/';

// Get file extension from the provided file name
$file_extension = pathinfo($file, PATHINFO_EXTENSION);

// Build the full file path
$file_path = $directory . $file;

// Check if the extension is allowed and the file exists
if (in_array(strtolower($file_extension), $allowed_extensions) && file_exists($file_path)) {
    // Set appropriate Content-Type header based on file extension
    $mime_types = $config['web']['upload_extensions'];
    header('Content-Type: ' . $mime_types[$file_extension]);
    readfile($file_path);
} else {
    header('Content-Type: text/plain');
    echo 'Error: File not found or invalid file type.';
}
?>