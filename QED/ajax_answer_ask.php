<?php
require_once __DIR__ . '/includes/db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['admin'])) { echo json_encode(['success'=>false,'message'=>'권한 없음']); exit; }
$id = (int)($_POST['id'] ?? 0);
if (!$id) { echo json_encode(['success'=>false,'message'=>'잘못된 요청']); exit; }
$now  = date('Y-m-d H:i:s');
$stmt = $mysqli->prepare('UPDATE home_ask_board SET is_answered=1, answered_at=? WHERE id=?');
$stmt->bind_param('si', $now, $id);
$stmt->execute();
echo json_encode(['success'=>true]);
