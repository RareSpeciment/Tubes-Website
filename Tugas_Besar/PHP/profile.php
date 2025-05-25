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
    <title>Profile - <?= htmlspecialchars($user['username']) ?></title>
    <link rel="stylesheet" href="../CSS/profile.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="profile-content">
        <div class="profile-container">
            <div class="profile-image"></div>
            <div class="profile-info">
                <div><span class="profile-label">Username</span> : 
                    <span class="profile-value"><?= htmlspecialchars($user['username']) ?></span>
                </div>
                <div><span class="profile-label">Email</span> : 
                    <span class="profile-value"><?= htmlspecialchars($user['email']) ?></span>
                </div>
                <div><span class="profile-label">Age</span> : 
                    <span class="profile-value"><?= htmlspecialchars($user['age']) ?></span>
                </div>
                <div><span class="profile-label">Joined Date</span> : 
                    <span class="profile-value"><?= $joined_date ?></span>
                </div>
                <div><span class="profile-label">Books Uploaded</span> : 
                    <span class="profile-value"><?= htmlspecialchars($user['books_uploaded']) ?></span>
                </div>
                <div class="profile-about-label">About Me :</div>
                <div class="profile-about">
                    <?= nl2br(htmlspecialchars($user['about_me'])) ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>