<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);
require_once __DIR__ . '/includes/db.php';
ob_clean();
header('Content-Type: application/json');
if (!isset($_SESSION['admin'])) {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']); exit;
}
if (empty($_FILES['image'])) {
    echo json_encode(['success' => false, 'message' => '파일이 없습니다.']); exit;
}
$file    = $_FILES['image'];
$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowed, true)) {
    echo json_encode(['success' => false, 'message' => '허용되지 않는 파일 형식입니다.']); exit;
}
if ($file['size'] > 20 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => '파일 크기는 20MB 이하여야 합니다.']); exit;
}
$filename = uniqid('img_', true) . '.' . $ext;
$dest     = __DIR__ . '/uploads/' . $filename;
if (!move_uploaded_file($file['tmp_name'], $dest)) {
    echo json_encode(['success' => false, 'message' => '저장에 실패했습니다.']); exit;
}
echo json_encode(['success' => true, 'url' => 'uploads/' . $filename]);
