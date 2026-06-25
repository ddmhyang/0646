<?php
require_once __DIR__ . '/includes/db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['admin'])) { echo json_encode(['success'=>false]); exit; }
$valid_roads = ['M3', 'KURU'];
$road = trim($_POST['road'] ?? '');
if (!in_array($road, $valid_roads, true)) { echo json_encode(['success'=>false]); exit; }
$id        = (int)($_POST['id']        ?? 0);
$collapsed = (int)($_POST['collapsed'] ?? 0);
$mysqli->query("UPDATE `home_road_{$road}_posts` SET collapsed=$collapsed WHERE id=$id");
echo json_encode(['success'=>true]);