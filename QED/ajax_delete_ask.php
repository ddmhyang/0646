<?php
require_once __DIR__ . '/includes/db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['admin'])) { echo json_encode(['success'=>false,'message'=>'권한 없음']); exit; }
$id = (int)($_POST['id'] ?? 0);
if (!$id) { echo json_encode(['success'=>false,'message'=>'잘못된 요청']); exit; }
$stmt = $mysqli->prepare('DELETE FROM home_ask_board WHERE id=?');
$stmt->bind_param('i', $id);
$stmt->execute();
echo json_encode(['success'=>true]);