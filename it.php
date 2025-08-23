
<?php
require __DIR__.'/db.php'; require __DIR__.'/auth.php'; require_login();
$action=$_POST['action']??($_GET['action']??'');
if($action==='create'){
  $amt=floatval($_POST['amount']??0); $note=trim($_POST['note']??''); $dt=date('Y-m-d H:i:s');
  if($amt>0){ db()->prepare('INSERT INTO it_withdrawals(amount,note,created_at) VALUES(?,?,?)')->execute([$amt,$note,$dt]); }
  header('Location: /dashboard.php?tab=it'); exit;
}
if($action==='update'){
  $id=intval($_POST['id']??0); $amt=floatval($_POST['amount']??0); $note=trim($_POST['note']??'');
  db()->prepare('UPDATE it_withdrawals SET amount=?, note=? WHERE id=?')->execute([$amt,$note,$id]);
  header('Location: /dashboard.php?tab=it'); exit;
}
if($action==='delete'){
  $id=intval($_GET['id']??0);
  db()->prepare('DELETE FROM it_withdrawals WHERE id=?')->execute([$id]);
  header('Location: /dashboard.php?tab=it'); exit;
}
