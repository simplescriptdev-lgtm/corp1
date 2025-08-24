<?php
ini_set('display_errors',1); error_reporting(E_ALL);
require_once __DIR__.'/db.php';
$action = $_REQUEST['action'] ?? 'create';
$type = $_REQUEST['type'] ?? 'rep';
$table = ($type==='don') ? 'charity_don_withdrawals' : 'charity_rep_withdrawals';
$sub = ($type==='don') ? 'don' : 'rep';
if ($action==='create'){
  db()->prepare("INSERT INTO $table(amount,note,created_at) VALUES (?,?,datetime('now'))")
    ->execute([(float)$_POST['amount'], $_POST['note'] ?? null]);
} elseif ($action==='update'){
  db()->prepare("UPDATE $table SET amount=?, note=? WHERE id=?")
    ->execute([(float)$_POST['amount'], $_POST['note'] ?? null, (int)$_POST['id']]);
} elseif ($action==='delete'){
  db()->prepare("DELETE FROM $table WHERE id=?")->execute([(int)$_GET['id']]);
}
header("Location: dashboard.php?tab=charity&sub=$sub"); exit;