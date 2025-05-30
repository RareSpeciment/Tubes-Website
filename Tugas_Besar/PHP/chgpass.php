<?php
require_once 'auth_check.php';
require_once 'config.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
    $_SESSION['error'] = "All fields are required";
    header('Location: settings.php');
    exit;
}

if ($new_password !== $confirm_password) {
    $_SESSION['error'] = "New passwords do not match";
    header('Location: settings.php');
    exit;
}

if (strlen($new_password) < 8) {
    $_SESSION['error'] = "Password must be at least 8 characters long";
    header('Location: settings.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user']['id']]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($current_password, $user['password'])) {
        $_SESSION['error'] = "Current password is incorrect";
        header('Location: settings.php');
        exit;
    }

    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashed_password, $_SESSION['user']['id']]);

    $_SESSION['success'] = "Password updated successfully";
    header('Location: settings.php');
    exit;

} catch(PDOException $e) {
    error_log("Password update error: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while updating your password";
    header('Location: settings.php');
    exit;
}
?> 