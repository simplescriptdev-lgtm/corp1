<?php
require __DIR__.'/db.php';
require __DIR__.'/auth.php';
require_login();

$action = $_REQUEST['action'] ?? 'create';
$kind   = $_REQUEST['kind'] ?? 'rep'; // rep | don

$table = $kind === 'don' ? 'charity_don_withdrawals' : 'charity_rep_withdrawals';

if ($action === 'create') {
  $amount = (float)($_POST['amount'] ?? 0);
  $note   = trim($_POST['note'] ?? '');
  $stmt = db()->prepare("INSERT INTO $table (amount, note, created_at) VALUES (?, ?, datetime('now','localtime'))");
  $stmt->execute([$amount, $note]);
} elseif ($action === 'update') {
  $id     = (int)($_POST['id'] ?? 0);
  $amount = (float)($_POST['amount'] ?? 0);
  $note   = trim($_POST['note'] ?? '');
  $stmt = db()->prepare("UPDATE $table SET amount=?, note=? WHERE id=?");
  $stmt->execute([$amount, $note, $id]);
} elseif ($action === 'delete') {
  $id = (int)($_GET['id'] ?? 0);
  $stmt = db()->prepare("DELETE FROM $table WHERE id=?");
  $stmt->execute([$id]);
}

$redirect = '/dashboard.php?tab=charity&csub=' . ($kind === 'don' ? 'don' : 'rep');
header("Location: $redirect");
exit;
