<?php
require_once __DIR__ . '/includes/db.php';
header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');
if ($q === '') { echo json_encode(['results' => []]); exit; }

$isGuest     = !isset($_SESSION['admin']) && !isset($_SESSION['user']);
$secretWhere = $isGuest ? ' AND is_secret = 0' : '';
$results     = [];

function snippet($html) {
    return mb_substr(trim(strip_tags($html)), 0, 80);
}

/* 날짜 패턴 감지: YYYYMMDD, YYYY-MM-DD, YYYY/MM/DD, YY.MM.DD, YY-MM-DD, YY/MM/DD */
$dateStr = null;
if (preg_match('/^(\d{4})[.\-\/](\d{2})[.\-\/](\d{2})$/', $q, $m)) {
    $dateStr = sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]);
} elseif (preg_match('/^(\d{4})(\d{2})(\d{2})$/', $q, $m)) {
    $dateStr = sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]);
} elseif (preg_match('/^(\d{2})[.\-\/](\d{2})[.\-\/](\d{2})$/', $q, $m)) {
    $dateStr = sprintf('20%02d-%02d-%02d', $m[1], $m[2], $m[3]);
} elseif (preg_match('/^(\d{2})(\d{2})(\d{2})$/', $q, $m)) {
    $dateStr = sprintf('20%02d-%02d-%02d', $m[1], $m[2], $m[3]);
}

if ($dateStr !== null) {
    foreach (['home_road_M3_posts' => 'M3', 'home_road_KURU_posts' => 'KURU'] as $table => $road) {
        $stmt = $mysqli->prepare("SELECT id, title, content FROM $table WHERE DATE(created_at) = ?$secretWhere ORDER BY id DESC LIMIT 20");
        $stmt->bind_param('s', $dateStr);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $results[] = ['road' => $road, 'id' => (int)$row['id'], 'title' => $row['title'], 'snippet' => snippet($row['content'])];
        }
        $stmt->close();
    }
} else {
    $like = '%' . $q . '%';
    foreach (['home_road_M3_posts' => 'M3', 'home_road_KURU_posts' => 'KURU'] as $table => $road) {
        $stmt = $mysqli->prepare("SELECT id, title, content FROM $table WHERE (title LIKE ? OR content LIKE ?)$secretWhere ORDER BY id DESC LIMIT 20");
        $stmt->bind_param('ss', $like, $like);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $results[] = ['road' => $road, 'id' => (int)$row['id'], 'title' => $row['title'], 'snippet' => snippet($row['content'])];
        }
        $stmt->close();
    }
}

echo json_encode(['results' => $results]);
