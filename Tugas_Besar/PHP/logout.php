<?php
session_start();
require_once 'config.php';

session_unset();
session_destroy();

if (isset($_COOKIE['remember_token'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE token = ?");
        $stmt->execute([$_COOKIE['remember_token']]);
    } catch(PDOException $e) {
        error_log("Logout error: " . $e->getMessage());
    }
    setcookie('remember_token', '', time() - 3600, '/');
}

setcookie('PHPSESSID', '', time() - 3600, '/');

header('Location: login.php');
exit;
?>