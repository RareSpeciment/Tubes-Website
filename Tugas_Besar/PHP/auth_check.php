<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

if (!isset($_SESSION['user'])) {
    if (isset($_COOKIE['remember_token'])) {
        try {
            $stmt = $pdo->prepare("SELECT u.* FROM users u 
                JOIN remember_tokens rt ON u.id = rt.user_id 
                WHERE rt.token = ? AND rt.expires_at > NOW()");
            $stmt->execute([$_COOKIE['remember_token']]);
            $user = $stmt->fetch();
            
            if ($user) {
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email']
                ];
            } else {
                header('Location: login.php');
                exit;
            }
        } catch(PDOException $e) {
            header('Location: login.php');
            exit;
        }
    } else {
        header('Location: login.php');
        exit;
    }
}

if (isset($_SESSION['user']) && !isset($_COOKIE['PHPSESSID'])) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}
?>