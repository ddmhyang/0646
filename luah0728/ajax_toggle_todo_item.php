<?php
require_once __DIR__ . '/includes/db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['admin'])) { echo json_encode(['success'=>false,'message'=>'권한 없음']); exit; }
$id      = (int)($_POST['id']      ?? 0);
$checked = (int)($_POST['checked'] ?? 0);
$mysqli->query("UPDATE home_todo_items SET is_checked=$checked WHERE id=$id");
echo json_encode(['success'=>true]);