<?php
require_once __DIR__ . '/includes/db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['admin'])) { echo json_encode(['success'=>false,'message'=>'권한 없음']); exit; }

$id            = (int)($_POST['id'] ?? 0);
$title         = trim($_POST['title'] ?? '');
$profile       = $_POST['profile'] ?? '';
$download_link = trim($_POST['download_link'] ?? '');
$thumbnail     = trim($_POST['thumbnail'] ?? '');
$audio_json    = $_POST['audio_json'] ?? '[]';

if ($title === '') { echo json_encode(['success'=>false,'message'=>'이름을 입력하세요.']); exit; }

$audios = json_decode($audio_json, true);
if (!is_array($audios)) $audios = [];

if ($id > 0) {
    $chk = $mysqli->prepare('SELECT thumbnail FROM `home_gallery_voicebank` WHERE id=? LIMIT 1');
    $chk->bind_param('i', $id);
    $chk->execute();
    $existing = $chk->get_result()->fetch_assoc();
    if ($thumbnail === '' && ($existing['thumbnail'] ?? null)) {
        $thumbnail = $existing['thumbnail'];
    }
    $stmt = $mysqli->prepare('UPDATE `home_gallery_voicebank` SET title=?,profile=?,download_link=?,thumbnail=?,updated_at=NOW() WHERE id=?');
    $stmt->bind_param('ssssi', $title, $profile, $download_link, $thumbnail, $id);
    $stmt->execute();
} else {
    $stmt = $mysqli->prepare('INSERT INTO `home_gallery_voicebank` (title,profile,download_link,thumbnail) VALUES (?,?,?,?)');
    $stmt->bind_param('ssss', $title, $profile, $download_link, $thumbnail);
    $stmt->execute();
    $id = $mysqli->insert_id;
}

$del = $mysqli->prepare('DELETE FROM `home_gallery_voicebank_audio` WHERE post_id=?');
$del->bind_param('i', $id);
$del->execute();

if (!empty($audios)) {
    $ins = $mysqli->prepare('INSERT INTO `home_gallery_voicebank_audio` (post_id, title, url, sort_order) VALUES (?,?,?,?)');
    foreach ($audios as $i => $a) {
        $at = trim($a['title'] ?? '');
        $au = trim($a['url'] ?? '');
        if ($au === '') continue;
        $ins->bind_param('issi', $id, $at, $au, $i);
        $ins->execute();
    }
}

echo json_encode(['success'=>true,'id'=>$id]);
