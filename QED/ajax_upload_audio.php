<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);
require_once __DIR__ . '/includes/db.php';
ob_clean();
header('Content-Type: application/json');
if (!isset($_SESSION['admin'])) { echo json_encode(['success'=>false,'message'=>'권한이 없습니다.']); exit; }
if (empty($_FILES['audio'])) { echo json_encode(['success'=>false,'message'=>'파일이 없습니다.']); exit; }

$file    = $_FILES['audio'];
$allowed = ['mp3', 'wav', 'ogg', 'm4a', 'flac', 'aac', 'opus', 'wma'];
$ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowed, true)) { echo json_encode(['success'=>false,'message'=>'허용되지 않는 확장자입니다: .' . $ext]); exit; }
if ($file['size'] > 50 * 1024 * 1024) { echo json_encode(['success'=>false,'message'=>'파일 크기는 50MB 이하여야 합니다.']); exit; }
if ($file['error'] !== UPLOAD_ERR_OK) { echo json_encode(['success'=>false,'message'=>'업로드 오류 코드: ' . $file['error']]); exit; }

$filename = uniqid('audio_', true) . '.' . $ext;
$dest     = __DIR__ . '/uploads/' . $filename;
if (!move_uploaded_file($file['tmp_name'], $dest)) { echo json_encode(['success'=>false,'message'=>'저장에 실패했습니다.']); exit; }

echo json_encode(['success'=>true,'url'=>'uploads/' . $filename]);
