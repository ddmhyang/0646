<?php
require_once __DIR__ . '/includes/db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['admin'])) { echo json_encode(['success'=>false,'message'=>'권한 없음']); exit; }
$valid_roads = ['M3', 'KURU'];
$road = trim($_POST['road'] ?? '');
if (!in_array($road, $valid_roads, true)) { echo json_encode(['success'=>false,'message'=>'잘못된 요청']); exit; }
$id = (int)($_POST['id'] ?? 0);
$mysqli->query("DELETE FROM `home_road_{$road}_comments` WHERE post_id=$id");
$mysqli->query("DELETE FROM `home_road_{$road}_posts` WHERE id=$id");
echo json_encode(['success'=>true]);