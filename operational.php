
<?php require __DIR__.'/db.php';require __DIR__.'/auth.php';require_login();$a=$_GET['action']??($_POST['action']??'');
if($a==='add'&&$_SERVER['REQUEST_METHOD']==='POST'){ $amt=floatval($_POST['amount']??0);$note=trim($_POST['note']??''); if($amt>0) db()->prepare('INSERT INTO operational_withdrawals(amount,note,created_at) VALUES(?,?,?)')->execute([$amt,$note,date('Y-m-d H:i:s')]); header('Location:/dashboard.php?tab=operational'); exit;}
if($a==='delete'){ db()->prepare('DELETE FROM operational_withdrawals WHERE id=?')->execute([intval($_GET['id']??0)]); header('Location:/dashboard.php?tab=operational'); exit;}
if($a==='update'&&$_SERVER['REQUEST_METHOD']==='POST'){ $id=intval($_POST['id']);$amt=floatval($_POST['amount']??0);$note=trim($_POST['note']??''); if($amt>0) db()->prepare('UPDATE operational_withdrawals SET amount=?,note=? WHERE id=?')->execute([$amt,$note,$id]); header('Location:/dashboard.php?tab=operational'); exit;}
header('Location:/dashboard.php?tab=operational');
