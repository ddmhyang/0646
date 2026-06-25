<?php
require_once __DIR__ . '/includes/db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['admin'])) { echo json_encode(['success'=>false,'message'=>'권한 없음']); exit; }
$valid_roads = ['M3', 'KURU'];
$road = trim($_POST['road'] ?? '');
if (!in_array($road, $valid_roads, true)) { echo json_encode(['success'=>false,'message'=>'잘못된 요청']); exit; }
$table   = "home_road_{$road}_posts";
$id      = (int)($_POST['id'] ?? 0);
$title   = trim($_POST['title']   ?? '');
$content = $_POST['content'] ?? '';
if (!$title) { echo json_encode(['success'=>false,'message'=>'제목을 입력하세요.']); exit; }
$thumb_exp = isset($_POST['thumbnail']) ? trim($_POST['thumbnail']) : null;
if ($thumb_exp === '') $thumb_exp = null;
if ($thumb_exp === null) {
    if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $_m)) $thumb_exp = $_m[1];
}
$is_secret = (int)($_POST['is_secret'] ?? 0) === 1 ? 1 : 0;
if ($id) {
    $stmt = $mysqli->prepare("UPDATE `$table` SET title=?,content=?,thumbnail=?,is_secret=?,updated_at=NOW() WHERE id=?");
    $stmt->bind_param('sssii', $title, $content, $thumb_exp, $is_secret, $id);
} else {
    $stmt = $mysqli->prepare("INSERT INTO `$table` (title,content,thumbnail,is_secret) VALUES (?,?,?,?)");
    $stmt->bind_param('sssi', $title, $content, $thumb_exp, $is_secret);
}
if (!$stmt->execute()) {
    echo json_encode(['success'=>false,'message'=>'DB 오류: '.$stmt->error]); exit;
}
$newId = $id ?: (int)$mysqli->insert_id;
echo json_encode(['success'=>true, 'id'=>$newId, 'road'=>$road, 'title'=>$title, 'content'=>$content, 'is_secret'=>$is_secret, 'is_edit'=>(bool)$id]);