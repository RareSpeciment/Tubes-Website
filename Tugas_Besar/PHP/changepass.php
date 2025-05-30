<?php
    require_once 'auth_check.php';
    require_once 'config.php';

    if (!isset($_SESSION['user']) && isset($_COOKIE['remember_token'])) {
        try {
            $stmt = $pdo->prepare("SELECT u.* FROM users u 
                JOIN remember_tokens rt ON u.id = rt.user_id 
                WHERE rt.token = ? AND rt.expires_at > NOW()");
            $stmt->execute([$_COOKIE['remember_token']]);
            $user = $stmt->fetch();
            
            if ($user) {
                $_SESSION['user'] = [
                    'password' => $user['password']
                ];
            }
        } catch(PDOException $e) {
            error_log("Cookie login error: " . $e->getMessage());
        }
    }

    if (!isset($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT 
            password
            FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user']['id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch(PDOException $e) {
        die("Database error: " . $e->getMessage());
    }

    if (!$user) {
        die("User not found.");
    }
?>

<?php
    include "header.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=s, initial-scale=1.0">
    <title>Change Pass User</title>
    <link rel="stylesheet" href="..//CSS/edituser.css">
</head>
<body>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="message error">
            <?php 
                echo htmlspecialchars($_SESSION['error']);
                unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="message success">
            <?php 
                echo htmlspecialchars($_SESSION['success']);
                unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>
    
    <form action="chgpass.php" method="post">
        <h2>Change Password</h2>
        <label for="Password">Current Password:</label>
        <input type="text" id="current_password" name="current_password" required>

        <label for="Password">New Password:</label>
        <input type="text" id="new_password" name="new_password" required>

        <label for="Password">Confirm Password:</label>
        <input type="text" id="confirm_password" name="confirm_password" required>
        
        <button type="submit">Change Password</button>
        <button type="button" onclick="window.location.href='settings.php'">Cancel</button>
    </form>
</body>
</html>

<?php
    include "footer.php";
?>
