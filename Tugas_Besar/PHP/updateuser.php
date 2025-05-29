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

    // Update user information
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