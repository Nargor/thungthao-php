<?php
header('Content-Type: text/css');

// กรองชื่อไฟล์ ไม่ให้เป็น path traversal
$file = isset($_GET['file']) ? basename($_GET['file']) : '';
$directory = __DIR__ . '/../../protect/css/';

// ถ้า user ส่งมาเป็นชื่อไฟล์ที่มี .css อยู่แล้ว ให้ตัดออกก่อน
$file = preg_replace('/\.css$/i', '', $file);

// ต่อชื่อไฟล์เข้าไป
$file_path = $directory . $file . '.css';

// เช็กว่าเป็นไฟล์ .css จริงๆ
if (is_file($file_path) && strtolower(pathinfo($file_path, PATHINFO_EXTENSION)) === 'css') {
    readfile($file_path);
} else {
    echo 'Error: File not found or invalid file type.';
}
?>
