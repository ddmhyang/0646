<?php
require_once __DIR__ . '/includes/db.php';
header('Content-Type: application/json');
$result = $mysqli->query('SELECT id, title, type, src, order_num FROM home_bgm_playlist ORDER BY order_num ASC, id ASC');
$items = [];
if ($result) { while ($row = $result->fetch_assoc()) $items[] = $row; }
echo json_encode(['items' => $items]);