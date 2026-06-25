<?php
require_once __DIR__ . '/includes/db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['admin'])) { echo json_encode(['success'=>false,'message'=>'권한 없음']); exit; }
$id = (int)($_POST['id'] ?? 0);
$mysqli->query("DELETE FROM home_todo_items WHERE category_id=$id");
$mysqli->query("DELETE FROM home_todo_categories WHERE id=$id");
echo json_encode(['success'=>true]);