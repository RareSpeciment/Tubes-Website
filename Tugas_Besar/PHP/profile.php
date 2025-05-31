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

// Fungsi hapus buku (hanya buku milik user sendiri)
if (isset($_GET['delete_book'])) {
    $book_id = intval($_GET['delete_book']);
    // Pastikan buku milik user yang sedang login
    $stmt = $pdo->prepare("DELETE FROM books WHERE id = ? AND uploaded_by = ?");
    $stmt->execute([$book_id, $_SESSION['user']['id']]);
    header("Location: profile.php");
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT 
        username,
        email,
        age,
        created_at,
        about_me,
        profile_image
        FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user']['id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $joined_date = date('d/m/Y', strtotime($user['created_at']));

    // Ambil buku yang diupload user ini
    $stmtBooks = $pdo->prepare("SELECT id, title, author, cover_image, created_at FROM books WHERE uploaded_by = ? ORDER BY created_at DESC");
    $stmtBooks->execute([$_SESSION['user']['id']]);
    $uploaded_books = $stmtBooks->fetchAll(PDO::FETCH_ASSOC);

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
    <link rel="stylesheet" href="../CSS/profilepreview.css">
    <link rel="stylesheet" href="../CSS/books.css">
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="profile-content">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <div class="profile-container">
            <div class="profile-image-container">
                <label class="profile-image">
                    <?php if (!empty($user['profile_image'])): ?>
                        <img src="../uploads/profiles/<?= htmlspecialchars($user['profile_image']) ?>" alt="Profile" class="profile-img">
                    <?php endif; ?>
                </label>
            </div>

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
                    <span class="profile-value"><?= count($uploaded_books) ?></span>
                </div>
                <div class="profile-about-label">About Me :</div>
                <div class="profile-about">
                    <?= nl2br(htmlspecialchars($user['about_me'])) ?>
                </div>
            </div>
        </div>

        <br>
        <label>Books Uploaded By You</label>
        <hr>
        <div class="uploaded-books">
            <?php if (count($uploaded_books) > 0): ?>
                <div class="books-row">
                    <?php foreach ($uploaded_books as $book): ?>
                        <div class="book-card">
                            <div class="book-image">
                                <?php if ($book['cover_image']): ?>
                                    <img src="../uploads/books/<?= htmlspecialchars($book['cover_image']) ?>" alt="Book Cover">
                                <?php endif; ?>
                            </div>
                            <div class="book-title"><?= htmlspecialchars($book['title']) ?></div>
                            <div class="book-author">
                                <?= htmlspecialchars($book['author']) ?><br>
                                <span class="book-date"><?= date('d/m/Y', strtotime($book['created_at'])) ?></span>
                            </div>
                            <div class="admin-actions">
                                <a href="profile.php?delete_book=<?= $book['id'] ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this book?');">Delete</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="text-align:center;">You haven't uploaded any books yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        document.getElementById('profileUpload').addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
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