<?php
require_once __DIR__ . '/includes/db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['admin'])) { echo json_encode(['success'=>false,'message'=>'권한 없음']); exit; }
$cat_id  = (int)($_POST['category_id'] ?? 0);
$date    = preg_replace('/[^\d-]/', '', $_POST['date'] ?? '');
$content = trim($_POST['content'] ?? '');
$color   = trim($_POST['text_color'] ?? '#000000');
if (!$cat_id || !$date || !$content) { echo json_encode(['success'=>false,'message'=>'필드를 확인하세요.']); exit; }
$stmt = $mysqli->prepare('INSERT INTO home_todo_items (category_id,date,content,text_color) VALUES (?,?,?,?)');
$stmt->bind_param('isss', $cat_id, $date, $content, $color);
$stmt->execute();
echo json_encode(['success'=>true]);