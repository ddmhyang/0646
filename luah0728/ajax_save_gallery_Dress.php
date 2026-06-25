<?php
require_once __DIR__ . '/includes/db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['admin'])) { echo json_encode(['success'=>false,'message'=>'권한 없음']); exit; }
$id         = (int)($_POST['id'] ?? 0);
$title      = trim($_POST['title'] ?? '');
$content    = $_POST['content'] ?? '';
$is_private = (int)($_POST['is_private'] ?? 0);
$password   = $_POST['password'] ?? '';
if ($title === '') { echo json_encode(['success'=>false,'message'=>'제목을 입력하세요.']); exit; }
$pw_hash = $password !== '' ? password_hash($password, PASSWORD_BCRYPT) : null;
$thumb_exp = trim($_POST['thumbnail'] ?? '');
$thumb_auto = null;
if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $_m)) {
    $thumb_auto = $_m[1];
}
if ($id > 0) {
    $chk = $mysqli->prepare('SELECT thumbnail, password_hash FROM `home_gallery_Dress` WHERE id=? LIMIT 1');
    $chk->bind_param('i', $id);
    $chk->execute();
    $existing = $chk->get_result()->fetch_assoc();
    if ($thumb_exp !== '') {
        $thumbnail = $thumb_exp;
    } elseif ($existing['thumbnail'] ?? null) {
        $thumbnail = $existing['thumbnail'];
    } else {
        $thumbnail = $thumb_auto;
    }
    if ($pw_hash === null && $is_private && !($existing['password_hash'] ?? null)) {
        $pw_hash = password_hash('1234', PASSWORD_BCRYPT);
    }
    if ($pw_hash !== null) {
        $stmt = $mysqli->prepare('UPDATE `home_gallery_Dress` SET title=?,content=?,thumbnail=?,is_private=?,password_hash=?,updated_at=NOW() WHERE id=?');
        $stmt->bind_param('sssisi', $title, $content, $thumbnail, $is_private, $pw_hash, $id);
    } else {
        $stmt = $mysqli->prepare('UPDATE `home_gallery_Dress` SET title=?,content=?,thumbnail=?,is_private=?,updated_at=NOW() WHERE id=?');
        $stmt->bind_param('sssii', $title, $content, $thumbnail, $is_private, $id);
    }
    $stmt->execute();
    echo json_encode(['success'=>true,'id'=>$id]);
} else {
    $thumbnail = $thumb_exp !== '' ? $thumb_exp : $thumb_auto;
    $stmt = $mysqli->prepare('INSERT INTO `home_gallery_Dress` (title,content,thumbnail,is_private,password_hash) VALUES (?,?,?,?,?)');
    $stmt->bind_param('sssis', $title, $content, $thumbnail, $is_private, $pw_hash);
    $stmt->execute();
    echo json_encode(['success'=>true,'id'=>$mysqli->insert_id]);
}