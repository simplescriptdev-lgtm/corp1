
<?php
require __DIR__.'/db.php'; require __DIR__.'/auth.php'; require_login();

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

if($action==='add' && $_SERVER['REQUEST_METHOD']==='POST'){
  $amount = floatval($_POST['amount'] ?? 0);
  $note = trim($_POST['note'] ?? '');
  if($amount<=0){ header('Location: /dashboard.php?tab=owner&err=amount'); exit; }
  $date = date('Y-m-d H:i:s');
  db()->prepare('INSERT INTO owner_withdrawals (amount,note,created_at) VALUES (?,?,?)')->execute([$amount,$note,$date]);
  header('Location: /dashboard.php?tab=owner&ok=added'); exit;
}
if($action==='delete' && isset($_GET['id'])){
  $id=intval($_GET['id']); db()->prepare('DELETE FROM owner_withdrawals WHERE id=?')->execute([$id]);
  header('Location: /dashboard.php?tab=owner&ok=deleted'); exit;
}
if($action==='update' && $_SERVER['REQUEST_METHOD']==='POST'){
  $id=intval($_POST['id']??0); $amount=floatval($_POST['amount']??0); $note=trim($_POST['note']??'');
  if($id>0 && $amount>0){
    db()->prepare('UPDATE owner_withdrawals SET amount=?, note=? WHERE id=?')->execute([$amount,$note,$id]);
    header('Location: /dashboard.php?tab=owner&ok=updated'); exit;
  } else {
    header('Location: /dashboard.php?tab=owner&err=update'); exit;
  }
}
header('Location: /dashboard.php?tab=owner');
