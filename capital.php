
<?php
require __DIR__.'/db.php';
require __DIR__.'/auth.php';
require_login();

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = floatval($_POST['amount'] ?? 0);
    $date = trim($_POST['date'] ?? '');
    if ($amount <= 0) {
        header('Location: /dashboard.php?tab=capital&err=amount'); exit;
    }
    if ($date === '') { $date = date('Y-m-d H:i:s'); }
    else {
        // normalize date
        $ts = strtotime($date);
        if ($ts === false) { $date = date('Y-m-d H:i:s'); }
        else { $date = date('Y-m-d H:i:s', $ts); }
    }
    $stmt = db()->prepare('INSERT INTO capital_inflows (amount, created_at) VALUES (?, ?)');
    $stmt->execute([$amount, $date]);
    header('Location: /dashboard.php?tab=capital&ok=added'); exit;
}

if ($action === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = db()->prepare('DELETE FROM capital_inflows WHERE id = ?');
    $stmt->execute([$id]);
    header('Location: /dashboard.php?tab=capital&ok=deleted'); exit;
}

header('Location: /dashboard.php?tab=capital');
