<?php
require_once __DIR__ . '/includes/db.php';
header('Content-Type: application/json');
$valid_roads = ['M3', 'KURU'];
$road = trim($_POST['road'] ?? '');
if (!in_array($road, $valid_roads, true)) { echo json_encode(['success'=>false,'message'=>'잘못된 요청']); exit; }
$id = (int)($_POST['id'] ?? 0);
$content = trim($_POST['content'] ?? '');
if ($content === '') { echo json_encode(['success'=>false,'message'=>'내용을 입력하세요.']); exit; }
$user_id = $_SESSION['user']['id'] ?? (!empty($_SESSION['admin_user_id']) ? (int)$_SESSION['admin_user_id'] : null);
if (!$user_id) { echo json_encode(['success'=>false,'message'=>'권한 없음']); exit; }
$stmt = $mysqli->prepare("UPDATE `home_road_{$road}_comments` SET content=? WHERE id=? AND user_id=?");
$stmt->bind_param('sii', $content, $id, $user_id);
$stmt->execute();
if ($stmt->affected_rows > 0) { echo json_encode(['success'=>true]); exit; }
echo json_encode(['success'=>false,'message'=>'권한 없음']);