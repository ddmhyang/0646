<?php
require_once __DIR__ . '/includes/db.php';
header('Content-Type: application/json');

$content = trim($_POST['content'] ?? '');
if (!$content) { echo json_encode(['success' => false, 'message' => '내용을 입력해주세요.']); exit; }
if (mb_strlen($content) > 1000) { echo json_encode(['success' => false, 'message' => '1000자 이내로 작성해주세요.']); exit; }

$isAdmin = isset($_SESSION['admin']) ? 1 : 0;

$stmt = $mysqli->prepare("INSERT INTO home_guestbook (content, is_admin) VALUES (?, ?)");
$stmt->bind_param('si', $content, $isAdmin);
$stmt->execute();
$id = $mysqli->insert_id;
$created_at = date('Y.m.d');
echo json_encode([
    'success'    => true,
    'id'         => $id,
    'content'    => htmlspecialchars($content, ENT_QUOTES, 'UTF-8'),
    'created_at' => $created_at,
    'is_admin'   => $isAdmin,
]);
