<?php
require_once __DIR__ . '/includes/db.php';
header('Content-Type: application/json');
$id = (int)($_POST['id'] ?? 0);
$pw = $_POST['password'] ?? '';
$stmt = $mysqli->prepare('SELECT password_hash FROM `home_gallery_OneLD` WHERE id=? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
if (!$row) { echo json_encode(['success'=>false,'message'=>'게시물을 찾을 수 없습니다.']); exit; }
if ($row['password_hash'] && password_verify($pw, $row['password_hash'])) {
    $_SESSION['post_access_OneLD_' . $id] = time() + 1800;
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false,'message'=>'비밀번호가 틀렸습니다.']);
}