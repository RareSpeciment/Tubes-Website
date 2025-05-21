<?php
session_start();
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } elseif (strlen($username) < 4) {
        $error = 'Username must be at least 4 characters';
    } elseif (empty($username) || empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->rowCount() > 0) {
                $error = 'Username or email already exists';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$username, $email, $hashed_password]);
                
                $_SESSION['success'] = 'Registration successful! Please login';
                header('Location: login.php');
                exit;
            }
        } catch(PDOException $e) {
            $error = 'Registration failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="..//CSS/register.css">
</head>
<body>
    <div class="navbar">
        <div class="logo">LOGO</div>
        <div class="login-page">LOGIN PAGE</div>
    </div>
    <div class="container">
        <div class="register-box">
            <form class="register-form" method="POST" action="register.php">
                <h2>Register</h2>
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="success-message">
                        <?= htmlspecialchars($_SESSION['success']) ?>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="error-message">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                <input type="text" name="username" placeholder="Username" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <button type="submit">Register</button>
                <div class="login-link"> 
                    Already have an Account?<br>
                    <a href="login.php">Login</a>
                </div>
            </form>
        </div>
    </div>
    <footer>
        <table class="footer-table">
            <tr>
                <td class="footer-left">&copy; 2025 Library Website</td>
                <td class="footer-right">Designed</td>
            </tr>
        </table>
    </footer>
</body>
</html>