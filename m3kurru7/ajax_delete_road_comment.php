<?php
require_once __DIR__ . '/includes/db.php';
header('Content-Type: application/json');
$valid_roads = ['M3', 'KURU'];
$road = trim($_POST['road'] ?? '');
if (!in_array($road, $valid_roads, true)) { echo json_encode(['success'=>false,'message'=>'잘못된 요청']); exit; }
$id = (int)($_POST['id'] ?? 0);
$isAdmin = isset($_SESSION['admin']);
$userId  = $_SESSION['user']['id'] ?? null;
if (!$isAdmin && $userId === null) { echo json_encode(['success'=>false,'message'=>'권한 없음']); exit; }
if (!$isAdmin) {
    $chk = $mysqli->query("SELECT user_id FROM `home_road_{$road}_comments` WHERE id=$id LIMIT 1")->fetch_assoc();
    if (!$chk || (int)$chk['user_id'] !== (int)$userId) { echo json_encode(['success'=>false,'message'=>'권한 없음']); exit; }
}
$mysqli->query("DELETE FROM `home_road_{$road}_comments` WHERE id=$id");
echo json_encode(['success'=>true]);