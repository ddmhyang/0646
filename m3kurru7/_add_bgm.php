<?php
require_once __DIR__ . '/includes/db.php';

$youtubeId = 'DqjWFZN82_A';
$type  = 'youtube';
$title = '';
$res   = $mysqli->query('SELECT COALESCE(MAX(order_num), 0) + 1 AS n FROM home_bgm_playlist');
$next  = $res ? (int)$res->fetch_assoc()['n'] : 1;

$stmt = $mysqli->prepare('INSERT IGNORE INTO home_bgm_playlist (type, title, src, order_num) VALUES (?, ?, ?, ?)');
$stmt->bind_param('sssi', $type, $title, $youtubeId, $next);
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo "추가 완료: $youtubeId\n";
    } else {
        echo "이미 존재함: $youtubeId\n";
    }
} else {
    echo "오류: " . $stmt->error . "\n";
}
$stmt->close();
