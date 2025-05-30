<?php
require_once 'auth_check.php';
require_once 'config.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user']['id']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || !password_verify($current, $row['password'])) {
        $message = 'Current password is incorrect.';
    } elseif (strlen($new) < 8) {
        $message = 'New password must be at least 8 characters.';
    } elseif ($new !== $confirm) {
        $message = 'New password and confirmation do not match.';
    } else {
        $new_hash = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($stmt->execute([$new_hash, $_SESSION['user']['id']])) {
            $message = 'Password changed successfully!';
        } else {
            $message = 'Failed to change password. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password</title>
    <link rel="stylesheet" href="../CSS/settings.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="change-password-container" style="max-width:400px;margin:40px auto;">
        <h2>Change Password</h2>
        <?php if ($message): ?>
            <div style="color:<?= strpos($message, 'success') !== false ? 'green' : 'red' ?>;margin-bottom:10px;">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        <form method="post">
            <label>Current Password</label><br>
            <input type="password" name="current_password" required><br><br>
            <label>New Password</label><br>
            <input type="password" name="new_password" required><br><br>
            <label>Confirm New Password</label><br>
            <input type="password" name="confirm_password" required><br><br>
            <button type="submit">Change Password</button>
        </form>
        <br>
        <a href="settings.php">Back to Settings</a>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>