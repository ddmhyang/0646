<?php
require_once __DIR__ . '/includes/db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['admin'])) { echo json_encode(['success'=>false,'message'=>'권한 없음']); exit; }
$name  = trim($_POST['name']  ?? '');
$color = trim($_POST['color'] ?? '#ff9a9e');
if (!$name) { echo json_encode(['success'=>false,'message'=>'이름을 입력하세요.']); exit; }
$stmt = $mysqli->prepare('INSERT INTO home_todo_categories (name,color) VALUES (?,?)');
$stmt->bind_param('ss', $name, $color);
$stmt->execute();
echo json_encode(['success'=>true]);