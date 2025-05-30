<?php
require_once 'auth_check.php';
require_once 'config.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Validate and sanitize input
$username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$age = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT);
$about_me = filter_input(INPUT_POST, 'about_me', FILTER_SANITIZE_STRING);

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Invalid email format";
    header('Location: edituser.php');
    exit;
}

// Validate age
if ($age !== false && $age < 0) {
    $_SESSION['error'] = "Age cannot be negative";
    header('Location: edituser.php');
    exit;
}

try {
    // Check if username is already taken by another user
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $stmt->execute([$username, $_SESSION['user']['id']]);
    if ($stmt->rowCount() > 0) {
        $_SESSION['error'] = "Username is already taken";
        header('Location: edituser.php');
        exit;
    }

    // Check if email is already taken by another user
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $_SESSION['user']['id']]);
    if ($stmt->rowCount() > 0) {
        $_SESSION['error'] = "Email is already taken";
        header('Location: edituser.php');
        exit;
    }

    $profile_image = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/profiles/';
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $file = $_FILES['profile_image'];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        if (in_array($mime, $allowedTypes) && $file['size'] <= $maxSize) {
            $fileName = uniqid('profile_', true) . '_' . preg_replace('/[^a-zA-Z0-9\.]/', '_', $file['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $profile_image = $fileName;
            } else {
                $_SESSION['error'] = "Failed to move uploaded file";
                header('Location: edituser.php');
                exit;
            }
        } else {
            $_SESSION['error'] = "Invalid file type or size (max 2MB)";
            header('Location: edituser.php');
            exit;
        }
    }
    
    if ($profile_image) {
        $stmt = $pdo->prepare("UPDATE users SET 
            username = ?,
            email = ?,
            age = ?,
            about_me = ?,
            profile_image = ?
            WHERE id = ?");
        
        $stmt->execute([
            $username,
            $email,
            $age,
            $about_me,
            $profile_image,
            $_SESSION['user']['id']
        ]);

        // Update session data with new profile image
        $_SESSION['user']['profile_image'] = $profile_image;
    } else {
        $stmt = $pdo->prepare("UPDATE users SET 
            username = ?,
            email = ?,
            age = ?,
            about_me = ?
            WHERE id = ?");
        
        $stmt->execute([
            $username,
            $email,
            $age,
            $about_me,
            $_SESSION['user']['id']
        ]);
    }

    // Update session data
    $_SESSION['user']['username'] = $username;
    $_SESSION['user']['email'] = $email;

    $_SESSION['success'] = "Profile updated successfully";
    header('Location: edituser.php');
    exit;

} catch(PDOException $e) {
    error_log("Update user error: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while updating your profile";
    header('Location: edituser.php');
    exit;
}
?> 