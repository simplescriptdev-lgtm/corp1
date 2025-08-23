
<?php
require __DIR__.'/db.php';
session_start();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = trim($_POST['password'] ?? '');
    $stmt = db()->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->execute([$user]);
    $row = $stmt->fetch();
    if ($row && password_verify($pass, $row['password_hash'])) {
        $_SESSION['uid'] = $row['id'];
        $_SESSION['username'] = $user;
        header('Location: /dashboard.php');
        exit;
    } else {
        $error = 'Невірний логін або пароль';
    }
}
?>
<!doctype html>
<html lang="uk">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CorpOS — Вхід</title>
  <link rel="stylesheet" href="/styles.css">
</head>
<body>
  <div class="login card">
    <div class="header">Вхід до CorpOS</div>
    <?php if ($error): ?><div class="alert"><?=htmlspecialchars($error)?></div><?php endif; ?>
    <form method="post" class="vstack" style="display:flex; flex-direction:column; gap:10px;">
      <label>Логін
        <input class="input" type="text" name="username" placeholder="admin" required>
      </label>
      <label>Пароль
        <input class="input" type="password" name="password" placeholder="••••••" required>
      </label>
      <button class="btn primary" type="submit">Увійти</button>
    </form>
    <div class="footer small">Дефолтний користувач: <b>admin</b> / <b>123456</b></div>
  </div>
</body>
</html>
