<?php
// SQLite connection + schema bootstrap
if (!function_exists('db')) {
function db() {
    static $pdo = null;
    if ($pdo) return $pdo;
    $dbPath = __DIR__ . '/data.sqlite';
    $isNew = !file_exists($dbPath);
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if ($isNew) {
        $pdo->exec("PRAGMA journal_mode=WAL;");
    }
    // Users
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password_hash TEXT NOT NULL
    );");
    // Capital inflows
    $pdo->exec("CREATE TABLE IF NOT EXISTS capital_inflows (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        amount REAL NOT NULL,
        source TEXT NOT NULL,
        created_at TEXT NOT NULL
    );");
    // Withdrawals tables
    foreach (['owner_withdrawals','operational_withdrawals','it_withdrawals','charity_rep_withdrawals','charity_don_withdrawals'] as $t) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS $t (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            amount REAL NOT NULL,
            note TEXT,
            created_at TEXT NOT NULL
        );");
    }
    // Insurance
    $pdo->exec("CREATE TABLE IF NOT EXISTS insurance_sources (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        amount REAL NOT NULL DEFAULT 0,
        note TEXT,
        created_at TEXT NOT NULL
    );");
    $pdo->exec("CREATE TABLE IF NOT EXISTS insurance_investments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        source_id INTEGER NOT NULL,
        amount REAL NOT NULL,
        note TEXT,
        created_at TEXT NOT NULL,
        FOREIGN KEY(source_id) REFERENCES insurance_sources(id) ON DELETE CASCADE
    );");
    // default admin user
    $row = $pdo->query("SELECT COUNT(*) AS c FROM users")->fetch(PDO::FETCH_ASSOC);
    if (!$row || (int)$row['c']===0) {
        $hash = password_hash('123456', PASSWORD_DEFAULT);
        $st = $pdo->prepare("INSERT INTO users (username,password_hash) VALUES (?,?)");
        $st->execute(['admin',$hash]);
    }
    return $pdo;
}
}
?>
