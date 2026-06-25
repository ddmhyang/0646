<?php
require_once __DIR__ . '/includes/db.php';
header('Content-Type: application/json');

$rows = $mysqli->query("SELECT id, content, created_at, is_admin FROM home_guestbook ORDER BY id DESC LIMIT 100")->fetch_all(MYSQLI_ASSOC);
echo json_encode(['success' => true, 'items' => $rows]);
