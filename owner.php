<?php
ini_set('display_errors',1); error_reporting(E_ALL);
require_once __DIR__.'/db.php';
$action = $_REQUEST['action'] ?? 'create';
if ($action==='create'){
  db()->prepare("INSERT INTO owner_withdrawals(amount,note,created_at) VALUES (?,?,datetime('now'))")
    ->execute([(float)$_POST['amount'], $_POST['note'] ?? null]);
} elseif ($action==='update'){
  db()->prepare("UPDATE owner_withdrawals SET amount=?, note=? WHERE id=?")
    ->execute([(float)$_POST['amount'], $_POST['note'] ?? null, (int)$_POST['id']]);
} elseif ($action==='delete'){
  db()->prepare("DELETE FROM owner_withdrawals WHERE id=?")->execute([(int)$_GET['id']]);
}
header("Location: dashboard.php?tab=owner"); exit;