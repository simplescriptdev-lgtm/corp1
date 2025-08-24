<?php
require __DIR__.'/db.php';
require __DIR__.'/auth.php';
require_login();

function redirect_sf($sub='uah'){ header('Location: /dashboard.php?tab=insurance&sub='.$sub); exit; }

$action = $_REQUEST['action'] ?? '';
$sub    = $_REQUEST['sub']    ?? 'uah'; // 'uah'|'fx'|'metal' (сьогодні використовуємо лише 'uah')

if ($action === 'create_source') {
    $name = trim($_POST['name'] ?? '');
    $amount = (float)($_POST['amount'] ?? 0);
    $note = trim($_POST['note'] ?? '');
    $dt = date('Y-m-d H:i:s');
    $stmt = db()->prepare("INSERT INTO insurance_sources (name, amount, note, created_at) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $amount, $note, $dt]);
    redirect_sf('uah');
}

if ($action === 'update_source') {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $amount = (float)($_POST['amount'] ?? 0);
    $note = trim($_POST['note'] ?? '');
    if ($id>0) {
        $stmt = db()->prepare("UPDATE insurance_sources SET name=?, amount=?, note=? WHERE id=?");
        $stmt->execute([$name, $amount, $note, $id]);
    }
    redirect_sf('uah');
}

if ($action === 'delete_source') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id>0) {
        // також видалимо пов'язані інвестиції
        $stmt = db()->prepare("DELETE FROM insurance_investments WHERE source_id=?");
        $stmt->execute([$id]);
        $stmt = db()->prepare("DELETE FROM insurance_sources WHERE id=?");
        $stmt->execute([$id]);
    }
    redirect_sf('uah');
}

if ($action === 'invest_create') {
    $source_id = (int)($_POST['source_id'] ?? 0);
    $amount = (float)($_POST['amount'] ?? 0);
    $note = trim($_POST['note'] ?? '');
    $dt = date('Y-m-d H:i:s');
    $stmt = db()->prepare("INSERT INTO insurance_investments (source_id, amount, note, created_at) VALUES (?, ?, ?, ?)");
    $stmt->execute([$source_id, $amount, $note, $dt]);
    redirect_sf('uah');
}

if ($action === 'invest_update') {
    $id = (int)($_POST['id'] ?? 0);
    $source_id = (int)($_POST['source_id'] ?? 0);
    $amount = (float)($_POST['amount'] ?? 0);
    $note = trim($_POST['note'] ?? '');
    if ($id>0) {
        $stmt = db()->prepare("UPDATE insurance_investments SET source_id=?, amount=?, note=? WHERE id=?");
        $stmt->execute([$source_id, $amount, $note, $id]);
    }
    redirect_sf('uah');
}

if ($action === 'invest_delete') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id>0) {
        $stmt = db()->prepare("DELETE FROM insurance_investments WHERE id=?");
        $stmt->execute([$id]);
    }
    redirect_sf('uah');
}

// fallback
redirect_sf('uah');
