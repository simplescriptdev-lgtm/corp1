<?php
require_once __DIR__.'/db.php';
session_start();
if (isset($_SESSION['user_id'])) { header("Location: dashboard.php"); exit; }
$error = null;
if ($_SERVER['REQUEST_METHOD']==='POST'){
    $u = $_POST['username'] ?? '';
    $p = $_POST['password'] ?? '';
    $st = db()->prepare("SELECT * FROM users WHERE username=?");
    $st->execute([$u]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if ($row && password_verify($p, $row['password_hash'])){
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        header("Location: dashboard.php"); exit;
    } else {
        $error = "Невірний логін або пароль";
    }
}
?><!DOCTYPE html><html lang="uk"><head><meta charset="utf-8"><title>Вхід</title><link rel="stylesheet" href="styles.css"></head>
<body>
<div class="content" style="margin-left:0;display:flex;align-items:center;justify-content:center;height:100vh;background:#0f1e2d">
  <div class="card" style="min-width:320px;max-width:380px;width:100%">
    <h3 style="margin:0 0 10px">Вхід до CorpOS</h3>
    <?php if($error): ?><div class="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form class="vstack" method="post">
      <label>Логін<input class="input" name="username" placeholder="admin" required></label>
      <label>Пароль<input class="input" type="password" name="password" placeholder="123456" required></label>
      <button class="btn primary" type="submit">Увійти</button>
      <div class="hstack" style="justify-content:space-between;opacity:.7"><div>Користувач за замовчуванням: <b>admin / 123456</b></div></div>
    </form>
  </div>
</div></body></html>
