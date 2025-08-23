
<?php
// auth.php — session guard
session_start();
function require_login() {
    if (!isset($_SESSION['uid'])) {
        header('Location: /index.php'); exit;
    }
}
