<?php
require_once __DIR__ . '/includes/db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['admin'])) { echo json_encode(['success'=>false,'message'=>'권한 없음']); exit; }
$content = $_POST['content'] ?? '';
$slug    = 'GUIDELINE';
$title   = 'GUIDELINE';
$stmt = $mysqli->prepare('INSERT INTO home_pages (slug,title,content) VALUES (?,?,?) ON DUPLICATE KEY UPDATE content=VALUES(content),updated_at=NOW()');
$stmt->bind_param('sss', $slug, $title, $content);
$stmt->execute();
echo json_encode(['success'=>true]);