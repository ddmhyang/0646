<?php
require_once __DIR__ . '/includes/db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['admin'])) { echo json_encode(['success'=>false]); exit; }
$id    = (int)($_POST['id'] ?? 0);
$color = preg_replace('/[^#a-fA-F0-9]/', '', $_POST['color'] ?? '');
if (!$id || strlen($color) < 4) { echo json_encode(['success'=>false]); exit; }
$stmt = $mysqli->prepare('UPDATE home_todo_categories SET color=? WHERE id=?');
$stmt->bind_param('si', $color, $id);
$stmt->execute();
echo json_encode(['success'=>true]);