<?php
require_once __DIR__ . '/includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin'])) { echo json_encode(['success'=>false,'message'=>'권한 없음']); exit; }
$user_id = null;
$author  = null;
if (isset($_SESSION['admin'])) {
    $user_id = !empty($_SESSION['admin_user_id']) ? (int)$_SESSION['admin_user_id'] : null;
    if (!$user_id) $author = $_SESSION['admin'];
} elseif (isset($_SESSION['user'])) {
    $user_id = $_SESSION['user']['id'];
} else {
    $author = trim($_POST['author'] ?? '') ?: null;
}
$valid_roads = ['M3', 'KURU'];
$road = trim($_POST['road'] ?? '');
if (!in_array($road, $valid_roads, true)) { echo json_encode(['success'=>false,'message'=>'잘못된 요청']); exit; }
$post_id = (int)($_POST['post_id'] ?? 0);
$content = trim($_POST['content'] ?? '');
if (!$content) { echo json_encode(['success'=>false,'message'=>'내용을 입력하세요.']); exit; }
$stmt = $mysqli->prepare("INSERT INTO `home_road_{$road}_comments` (post_id, author, user_id, content) VALUES (?, ?, ?, ?)");
$stmt->bind_param('isis', $post_id, $author, $user_id, $content);
$stmt->execute();
echo json_encode(['success'=>true]);