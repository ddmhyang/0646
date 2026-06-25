<?php
require_once __DIR__ . '/includes/db.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
if ($username === '' || $password === '') { echo json_encode(['success'=>false,'message'=>'아이디와 비밀번호를 입력하세요.']); exit; }
$stmt = $mysqli->prepare('SELECT a.password, u.id AS uid, u.nickname FROM home_admins a LEFT JOIN home_users u ON a.user_id = u.id WHERE a.username = ? LIMIT 1');
$stmt->bind_param('s', $username);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
if ($row && password_verify($password, $row['password'])) {
    session_regenerate_id(true);
    $_SESSION['admin'] = $username;
    if ($row['uid'])      $_SESSION['admin_user_id']  = (int)$row['uid'];
    if ($row['nickname']) $_SESSION['admin_nickname'] = $row['nickname'];
    echo json_encode(['success'=>true]); exit;
}
echo json_encode(['success'=>false,'message'=>'아이디 또는 비밀번호가 올바르지 않습니다.']);
