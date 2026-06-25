<?php
require_once __DIR__ . '/includes/db.php';
header('Content-Type: application/json');
$action = $_GET['action'] ?? 'day';

if ($action === 'month') {
    $ym = preg_replace('/[^\d-]/', '', $_GET['ym'] ?? date('Y-m'));
    $from = $ym . '-01';
    $to   = date('Y-m-t', strtotime($from));
    $rows = $mysqli->query(
        "SELECT date, COUNT(*) as total, SUM(is_checked) as checked
         FROM home_todo_items WHERE date BETWEEN '$from' AND '$to' GROUP BY date"
    );
    $summary = [];
    while ($r = $rows->fetch_assoc())
        $summary[$r['date']] = ['total'=>(int)$r['total'], 'checked'=>(int)$r['checked']];
    echo json_encode(['summary' => $summary]);
    exit;
}

// action=day
$date = preg_replace('/[^\d-]/', '', $_GET['date'] ?? date('Y-m-d'));
$cats = [];
$res = $mysqli->query('SELECT * FROM home_todo_categories ORDER BY sort_order ASC, id ASC');
while ($r = $res->fetch_assoc()) $cats[] = $r;

$items = [];
$res2 = $mysqli->query("SELECT * FROM home_todo_items WHERE date='$date' ORDER BY sort_order ASC, id ASC");
while ($r = $res2->fetch_assoc()) {
    $cid = $r['category_id'];
    if (!isset($items[$cid])) $items[$cid] = [];
    $items[$cid][] = $r;
}
echo json_encode(['categories' => $cats, 'items' => $items]);