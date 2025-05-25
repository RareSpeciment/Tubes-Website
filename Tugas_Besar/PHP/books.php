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
    <link rel="stylesheet" href="..//CSS/books.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="books-content">
        <div class="search-bar-container">
            <input type="text" class="search-input" placeholder="Search">
            <button class="search-icon-btn" aria-label="Menu">
                <span class="search-icon"></span>
            </button>
        </div>

        <!-- for now since this row was suppose to be generated after the book was published -->
        <div class="books-row">
            <div class="book-card">
                <div class="book-image"></div>
                <div class="book-title">Title</div>
                <div class="book-author">Author<br><span class="book-date">xx/xx/xxxx</span></div>
            </div>
            <div class="book-card">
                <div class="book-image"></div>
                <div class="book-title">Title</div>
                <div class="book-author">Author<br><span class="book-date">xx/xx/xxxx</span></div>
            </div>
            <div class="book-card">
                <div class="book-image"></div>
                <div class="book-title">Title</div>
                <div class="book-author">Author<br><span class="book-date">xx/xx/xxxx</span></div>
            </div>
            <div class="book-card">
                <div class="book-image"></div>
                <div class="book-title">Title</div>
                <div class="book-author">Author<br><span class="book-date">xx/xx/xxxx</span></div>
            </div>
            <div class="book-card">
                <div class="book-image"></div>
                <div class="book-title">Title</div>
                <div class="book-author">Author<br><span class="book-date">xx/xx/xxxx</span></div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>