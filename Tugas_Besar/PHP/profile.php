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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
    $uploadDir = '../uploads/profiles/';
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxSize = 2 * 1024 * 1024;

    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (!is_writable($uploadDir)) {
        $_SESSION['error'] = "Upload directory is not writable.";
    } else {
        $file = $_FILES['profile_image'];

        if ($file['error'] === UPLOAD_ERR_OK) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);

            if (in_array($mime, $allowedTypes) && $file['size'] <= $maxSize) {
                $fileName = uniqid('profile_', true) . '_' . preg_replace('/[^a-zA-Z0-9\.]/', '_', $file['name']);
                $targetPath = $uploadDir . $fileName;

                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    try {
                        $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                        $stmt->execute([$fileName, $_SESSION['user']['id']]);

                        $_SESSION['user']['profile_image'] = $fileName;

                        header("Location: profile.php");
                        exit;
                    } catch (PDOException $e) {
                        error_log("Database update error: " . $e->getMessage());
                        $_SESSION['error'] = "Failed to update profile image.";
                    }
                } else {
                    $_SESSION['error'] = "Failed to move uploaded file.";
                }
            } else {
                $_SESSION['error'] = "Invalid file type or size (max 2MB).";
            }
        } else {
            $_SESSION['error'] = "Error uploading file.";
        }
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
    <title>Profile - <?= htmlspecialchars($user['username']) ?></title>
    <link rel="stylesheet" href="../CSS/profile.css">
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="profile-content">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <div class="profile-container">
            <form method="POST" enctype="multipart/form-data" class="image-upload-form">
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
                <button type="submit" class="hidden-submit" style="display:none;">Submit</button>
            </form>
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

    <script>
        document.getElementById('profileUpload').addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    let img = document.querySelector('.profile-img');
                    if (img) {
                        img.src = e.target.result;
                    } else {
                        const placeholder = document.querySelector('.upload-indicator');
                        img = document.createElement('img');
                        img.className = 'profile-img';
                        img.alt = 'Profile';
                        img.src = e.target.result;
                        placeholder.parentNode.replaceChild(img, placeholder);
                    }
                };
                reader.readAsDataURL(file);
                this.form.submit();
            }
        });
    </script>
</body>

</html>