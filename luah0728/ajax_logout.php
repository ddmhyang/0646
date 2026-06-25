<?php
require_once __DIR__ . '/includes/db.php';
$_SESSION = [];
session_destroy();
echo json_encode(['success' => true]);