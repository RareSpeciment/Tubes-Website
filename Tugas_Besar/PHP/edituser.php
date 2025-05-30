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
            about_me,    
            profile_image
            FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user']['id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
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
    <title>Edit User</title>
    <link rel="stylesheet" href="..//CSS/edituser.css">
    <link rel="stylesheet" href="..//CSS/profile.css">
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

    <form action="updateuser.php" method="post" enctype="multipart/form-data">
        <h2>Edit User</h2>
        
        <div class="profile-image-container">
            <label class="profile-image">
                <input type="file" name="profile_image" accept="image/*" class="hidden-input" id="profileUpload">
                <?php if (!empty($user['profile_image'])): ?>
                    <img src="../uploads/profiles/<?= htmlspecialchars($user['profile_image']) ?>"
                        alt="Profile"
                        class="profile-img">
                <?php else: ?>
                    <div class="upload-indicator">
                        <i class="fas fa-camera"></i>
                    </div>
                <?php endif; ?>
            </label>
        </div>

        <label for="username">Username:</label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

        <label for="age">Age:</label>
        <input type="number" id="age" name="age" value="<?php echo htmlspecialchars($user['age']); ?>" min="0">

        <label for="about_me">About Me:</label>
        <textarea id="about_me" name="about_me"><?php echo htmlspecialchars($user['about_me']); ?></textarea>

        <div class="button-group">
            <button type="submit">Update Profile</button>
            <button type="button" onclick="window.location.href='settings.php'">Cancel</button>
        </div>
    </form>
</body>

</html>

<?php
include "footer.php";
?>