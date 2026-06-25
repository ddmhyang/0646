<?php
require_once __DIR__ . '/includes/db.php';
header('Content-Type: application/json');
$name     = trim($_POST['name']     ?? '');
$email    = trim($_POST['email']    ?? '');
$question = trim($_POST['question'] ?? '');
if ($name === '' || $question === '') {
    echo json_encode(['success'=>false,'message'=>'이름과 내용을 입력해주세요.']);
    exit;
}
$stmt = $mysqli->prepare('INSERT INTO home_ask_board (name, email, question) VALUES (?, ?, ?)');
$stmt->bind_param('sss', $name, $email, $question);
$stmt->execute();
echo json_encode(['success'=>true]);
