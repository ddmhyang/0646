<?php
require_once __DIR__ . '/includes/db.php';
header('Content-Type: application/json');
$valid_roads = ['M3', 'KURU'];
$road = trim($_GET['road'] ?? '');
if (!in_array($road, $valid_roads, true)) { echo json_encode(['items'=>[],'is_admin'=>false,'is_user'=>false]); exit; }
$post_id = (int)($_GET['id'] ?? 0);
$session_user_id = $_SESSION['user']['id'] ?? null;
$items = [];
$result = $mysqli->query("
    SELECT c.id, COALESCE(u.nickname, c.author) AS author, c.content, c.created_at, c.user_id
    FROM `home_road_{$road}_comments` c
    LEFT JOIN home_users u ON c.user_id = u.id
    WHERE c.post_id = $post_id ORDER BY c.id ASC
");
while ($row = $result->fetch_assoc()) {
    $row['is_mine'] = ($session_user_id !== null && (int)$row['user_id'] === (int)$session_user_id);
    $items[] = $row;
}
echo json_encode(['items'=>$items,'is_admin'=>isset($_SESSION['admin']),'is_user'=>isset($_SESSION['user'])]);