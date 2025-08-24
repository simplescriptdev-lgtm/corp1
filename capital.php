<?php
ini_set('display_errors',1); error_reporting(E_ALL);
require_once __DIR__.'/db.php';
$action = $_REQUEST['action'] ?? 'create';
if ($action==='create'){
  $amount = (float)($_POST['amount'] ?? 0);
  $source = $_POST['source'] ?? 'owner';
  $created = date('Y-m-d H:i:s');
  db()->prepare("INSERT INTO capital_inflows(amount,source,created_at) VALUES (?,?,?)")->execute([$amount,$source,$created]);
} elseif ($action==='delete'){
  $id = (int)($_GET['id'] ?? 0);
  db()->prepare("DELETE FROM capital_inflows WHERE id=?")->execute([$id]);
}
header("Location: dashboard.php?tab=capital"); exit;