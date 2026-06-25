<?php
require_once __DIR__ . '/includes/db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['admin'])) {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}
if (empty($_FILES['image'])) {
    echo json_encode(['success' => false, 'message' => '파일이 없습니다.']);
    exit;
}
$file    = $_FILES['image'];
$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowed, true)) {
    echo json_encode(['success' => false, 'message' => '허용되지 않는 파일 형식입니다.']);
    exit;
}
$finfo     = finfo_open(FILEINFO_MIME_TYPE);
$real_mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);
$allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($real_mime, $allowed_mimes, true)) {
    echo json_encode(['success' => false, 'message' => '실제 파일 형식이 허용되지 않습니다.']);
    exit;
}
if ($file['size'] > 20 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => '파일 크기는 20MB 이하여야 합니다.']);
    exit;
}
$filename = uniqid('img_', true) . '.' . $ext;
$dest     = __DIR__ . '/uploads/' . $filename;
if (!move_uploaded_file($file['tmp_name'], $dest)) {
    echo json_encode(['success' => false, 'message' => '저장에 실패했습니다.']);
    exit;
}
echo json_encode(['success' => true, 'url' => '../uploads/' . $filename]);