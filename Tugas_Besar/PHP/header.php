<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Website</title>
    <link rel="stylesheet" href="..//CSS/login.css">
    <link rel="stylesheet" href="..//CSS/index.css">
</head>

<body>
    <div class="navbar">
        <div class="logo">LOGO</div>
        <div class="nav-links">
            <a href="index.php" class="nav-link">Home</a>
            <?php
            if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
                <a href="manage_users.php" class="nav-link">Manage Users</a>
            <?php endif; ?>
            <a href="books.php" class="nav-link">Books</a>
            <a href="profile.php" class="nav-link">Profile</a>
            <a href="settings.php" class="nav-link">Settings</a>
            <a href="logout.php" class="nav-link">Log Out</a>
        </div>
    </div>
</body>

</html>