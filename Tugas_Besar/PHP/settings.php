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
    } catch (PDOException $e) {
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
            about_me,
            profile_image
            FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user']['id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $joined_date = date('d/m/Y', strtotime($user['created_at']));
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Library Website</title>
    <link rel="stylesheet" href="../CSS/settings.css">
    <link rel="stylesheet" href="..//CSS/profilepreview.css">
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="settings-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="user-settings">
                <h3>USER SETTINGS</h3>
                <ul>
                    <li class="active">My Account</li>
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

        <!-- Main Content -->
        <div class="main-content">
            <div class="user-info-section">
                <div class="user-profile">
                    <div class="profile-image-container">
                        <label class="profile-image">
                            <?php if (!empty($user['profile_image'])): ?>
                                <img src="../uploads/profiles/<?= htmlspecialchars($user['profile_image']) ?>" alt="Profile" class="profile-img">
                            <?php endif; ?>
                        </label>
                    </div>
                    <button onclick="location.href='edituser.php'">Edit User Profile</button>
                </div>

                <div class="info-fields">
                    <div class="info-item">
                        <strong>Username:</strong>
                        <span><?= htmlspecialchars($user['username']) ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Email:</strong>
                        <span><?= htmlspecialchars($user['email']) ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Age:</strong>
                        <span><?= htmlspecialchars($user['age']) ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Joined:</strong>
                        <span><?= $joined_date ?></span>
                    </div>
                </div>

                <!-- Password Section -->
                <div class="password-section" style="margin-top: 25px;">
                    <h3>Password</h3>
                    <button onclick="location.href='changepass.php'">Change Password</button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>

</html>