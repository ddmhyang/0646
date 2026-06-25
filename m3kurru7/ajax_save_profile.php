<?php
require_once __DIR__ . '/includes/db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['admin'])) { echo json_encode(['success'=>false,'message'=>'권한 없음']); exit; }

$content       = $_POST['content'] ?? '';
$profile_image = isset($_POST['profile_image']) ? trim($_POST['profile_image']) : null;

$slug = 'main_content'; $title = 'main_content';
$stmt = $mysqli->prepare('INSERT INTO home_pages (slug,title,content) VALUES (?,?,?) ON DUPLICATE KEY UPDATE content=VALUES(content),updated_at=NOW()');
$stmt->bind_param('sss', $slug, $title, $content);
if (!$stmt->execute()) { echo json_encode(['success'=>false,'message'=>'내용 저장 실패']); exit; }
$stmt->close();

if ($profile_image !== null) {
    $key = 'profile_image';
    $stmt = $mysqli->prepare('INSERT INTO home_settings (`key`,value) VALUES (?,?) ON DUPLICATE KEY UPDATE value=VALUES(value)');
    $stmt->bind_param('ss', $key, $profile_image);
    if (!$stmt->execute()) { echo json_encode(['success'=>false,'message'=>'이미지 저장 실패']); exit; }
    $stmt->close();
}

echo json_encode(['success'=>true]);
