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
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email']
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
            username,
            email,
            age,
            created_at,
            books_uploaded,
            about_me
            FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user']['id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $joined_date = date('d/m/Y', strtotime($user['created_at']));
        
    } catch(PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Books - Library Website</title>
    <link rel="stylesheet" href="..//CSS/settings.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="settings-container">
        <div class="sidebar">
            <div class="user-settings">
                <h3>USER SETTINGS</h3>
                <ul>
                    <li>My Account</li>
                    <li>Profiles</li>
                </ul>
            </div>
            
            <div class="Web-settings">
                <h3>WEB SETTINGS</h3>
                <ul>
                    <li>Theme</li>
                    <li>Language</li>
                </ul>
            </div>
        </div>
        
        <div class="main-content">
            <div class="user-info-section">
                <div class="user-profile">
                    <img src="" alt="User Avatar" class="avatar">
                    <div class="user-details">
                        <div class="username-tag"><?= htmlspecialchars($user['username']) ?></div>
                        <button><a href="edituser.php">Edit User Profile</a></button>
                    </div>
                </div>
                <div class="info-fields">
                    <div class="info-item">
                        <span>Username</span>
                        <span><?= htmlspecialchars($user['username']) ?></span> 
                    </div>
                     <div class="info-item">
                        <span>Email</span>
                        <span><?= htmlspecialchars($user['email']) ?></span>
                    </div>
                     <div class="info-item">
                        <span>Age</span>
                        <span><?php echo isset($_SESSION['age']) ? htmlspecialchars($_SESSION['age']) : 'You Have not set your age yet!.'; ?></span>
                    </div>
                </div>
                <hr>
                <div class="password-section">
                    <h3>Password</h3>
                    <button>Change Password</button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>