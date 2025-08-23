
<?php
// db.php — SQLite initialization and helpers
$dbFile = __DIR__ . '/corpos.db';
$initNeeded = !file_exists($dbFile);
$pdo = new PDO('sqlite:' . $dbFile, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
if ($initNeeded) {
    $pdo->exec('PRAGMA journal_mode=WAL;');
    $pdo->exec('CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password_hash TEXT NOT NULL
    );');
    $pdo->exec('CREATE TABLE capital_inflows (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        amount REAL NOT NULL,
        created_at TEXT NOT NULL
    );');
    // default user: admin / 123456
    $hash = password_hash("123456", PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
    $stmt->execute(["admin", $hash]);
}
function db() { global $pdo; return $pdo; }
