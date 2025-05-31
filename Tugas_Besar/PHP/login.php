<?php
session_start();
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier']);
    $password = trim($_POST['password']);

    if (empty($identifier) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$identifier, $identifier]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email']
                ];
                header('Location: index.php');
                exit;
            } else {
                $error = 'Invalid credentials';
            }
        } catch(PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
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
    <link rel="stylesheet" href="..//CSS/login.css">
</head>
<body>
    <div class="navbar">
        <div class="logo">LOGO</div>
        <div class="login-page">LOGIN PAGE</div>
    </div>
    <div class="container">
        <div class="left-section">
            <h1>The Role of Libraries in Society</h1>
            <p>
                Libraries are important centers of learning and knowledge offering access to books, information, and digital resources.<br><br>
                They have evolved into community hubs that support education, digital literacy, and cultural activities. In the digital era, libraries also provide access to e-books, online databases and internet services, helping people stay informed and connected.
            </p>
        </div>
        <div class="login-box">
            <form class="login-form" method="POST" action="login.php">
                <h2>Login</h2>
                <?php if ($error): ?>
                    <div class="error-message" style="color: red; margin-bottom: 15px;">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                <input type="text" name="identifier" placeholder="Email or Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
                <div class="register-link">
                    Dont have an Account?<br>
                    <a href="register.php">Register</a>
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