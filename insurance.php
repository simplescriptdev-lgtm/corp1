<?php
ini_set('display_errors',1); error_reporting(E_ALL);
require_once __DIR__.'/db.php';
$action = $_REQUEST['action'] ?? '';
if ($action==='create_source'){
  db()->prepare("INSERT INTO insurance_sources(name,amount,note,created_at) VALUES (?,?,?,datetime('now'))")
    ->execute([trim($_POST['name']), (float)$_POST['amount'], $_POST['note'] ?? null]);
  header("Location: dashboard.php?tab=insurance&sub=uah"); exit;
}
if ($action==='invest_create'){
  db()->prepare("INSERT INTO insurance_investments(source_id,amount,note,created_at) VALUES (?,?,?,datetime('now'))")
    ->execute([(int)$_POST['source_id'], (float)$_POST['amount'], $_POST['note'] ?? null]);
  header("Location: dashboard.php?tab=insurance&sub=uah"); exit;
}
if ($action==='invest_update'){
  db()->prepare("UPDATE insurance_investments SET source_id=?, amount=?, note=? WHERE id=?")
    ->execute([(int)$_POST['source_id'], (float)$_POST['amount'], $_POST['note'] ?? null, (int)$_POST['id']]);
  header("Location: dashboard.php?tab=insurance&sub=uah"); exit;
}
if ($action==='invest_delete'){
  db()->prepare("DELETE FROM insurance_investments WHERE id=?")->execute([(int)$_GET['id']]);
  header("Location: dashboard.php?tab=insurance&sub=uah"); exit;
}
header("Location: dashboard.php?tab=insurance"); exit;