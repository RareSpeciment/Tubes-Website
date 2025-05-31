<?php
require_once 'config.php';
session_start();

$book_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$book_id]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle comment submission
if (isset($_POST['submit_comment']) && !empty($_POST['comment']) && $book) {
    $comment = trim($_POST['comment']);
    $user_id = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;
    $username = isset($_SESSION['user']['username']) ? $_SESSION['user']['username'] : 'Guest';

    $stmt = $pdo->prepare("INSERT INTO comments (book_id, user_id, username, comment, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$book_id, $user_id, $username, $comment]);
    // Redirect to avoid resubmission
    header("Location: read.php?id=" . $book_id);
    exit;
}

// Fetch comments for this book
$stmt = $pdo->prepare("SELECT username, comment, created_at FROM comments WHERE book_id = ? ORDER BY created_at DESC");
$stmt->execute([$book_id]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung total like
$stmt = $pdo->prepare("SELECT COUNT(*) FROM book_likes WHERE book_id = ?");
$stmt->execute([$book_id]);
$total_likes = $stmt->fetchColumn();

// Cek apakah user sudah like
$user_liked = false;
if (isset($_SESSION['user']['id'])) {
    $stmt = $pdo->prepare("SELECT 1 FROM book_likes WHERE book_id = ? AND user_id = ?");
    $stmt->execute([$book_id, $_SESSION['user']['id']]);
    $user_liked = $stmt->fetchColumn() ? true : false;
}

// Handle like/unlike
if (isset($_POST['like_action']) && isset($_SESSION['user']['id'])) {
    if ($_POST['like_action'] === 'like' && !$user_liked) {
        $stmt = $pdo->prepare("INSERT INTO book_likes (book_id, user_id) VALUES (?, ?)");
        $stmt->execute([$book_id, $_SESSION['user']['id']]);
    } elseif ($_POST['like_action'] === 'unlike' && $user_liked) {
        $stmt = $pdo->prepare("DELETE FROM book_likes WHERE book_id = ? AND user_id = ?");
        $stmt->execute([$book_id, $_SESSION['user']['id']]);
    }
    header("Location: read.php?id=" . $book_id);
    exit;
}

// Refresh total like & status after action
$stmt = $pdo->prepare("SELECT COUNT(*) FROM book_likes WHERE book_id = ?");
$stmt->execute([$book_id]);
$total_likes = $stmt->fetchColumn();

$user_liked = false;
if (isset($_SESSION['user']['id'])) {
    $stmt = $pdo->prepare("SELECT 1 FROM book_likes WHERE book_id = ? AND user_id = ?");
    $stmt->execute([$book_id, $_SESSION['user']['id']]);
    $user_liked = $stmt->fetchColumn() ? true : false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Read Book</title>
    <link rel="stylesheet" href="../CSS/read.css">
</head>
<body>
    <?php include 'header.php'; ?>
        
    <div class="read-content">
        <a href="books.php" class="back-btn">&larr; Back to Books</a>
        <?php if ($book): ?>
            <h1 class="book-title"><?= htmlspecialchars($book['title']) ?></h1>
            <p><strong>Author:</strong> <?= htmlspecialchars($book['author']) ?></p>
            <hr>
            <?php if (!empty($book['txtfile'])): ?>
                <pre><?= htmlspecialchars($book['txtfile']) ?></pre>
            <?php else: ?>
                <p style="color:#888;">There is no stories.</p>
            <?php endif; ?>
            <hr>
            <!-- Like Section Start -->
            <div class="like-section" style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                <form method="post" style="display:inline;">
                    <?php if (isset($_SESSION['user']['id'])): ?>
                        <?php if ($user_liked): ?>
                            <button type="submit" name="like_action" value="unlike" class="like-btn liked">♥</button>
                        <?php else: ?>
                            <button type="submit" name="like_action" value="like" class="like-btn">♡</button>
                        <?php endif; ?>
                    <?php else: ?>
                        <button type="button" class="like-btn" title="Login to like" disabled>♡</button>
                    <?php endif; ?>
                </form>
                <span class="like-count"><?= $total_likes ?></span>
            </div>
            <!-- Like Section End -->
            <!-- Comment Section Start -->
            <div class="comment-section">
                <h3>Comments</h3>
                <form action="" method="post" class="comment-form">
                    <textarea name="comment" rows="3" placeholder="Write your comment..." required></textarea>
                    <br>
                    <button type="submit" name="submit_comment">Post Comment</button>
                </form>
                <div class="comments-list">
                    <?php if (count($comments) > 0): ?>
                        <?php foreach ($comments as $c): ?>
                            <div class="comment-item">
                                <strong><?= htmlspecialchars($c['username']) ?></strong>
                                <span style="color:#888;font-size:0.9em;">
                                    (<?= date('d/m/Y H:i', strtotime($c['created_at'])) ?>)
                                </span>
                                <div><?= nl2br(htmlspecialchars($c['comment'])) ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color:#888;">No comments yet.</p>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Comment Section End -->
        <?php else: ?>
            <p>Book not found.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
    include 'footer.php';
?>