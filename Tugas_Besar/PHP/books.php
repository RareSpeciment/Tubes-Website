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
                'email' => $user['email'],
                'role' => $user['role']
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_book'])) {
    if ($_SESSION['user']['role'] === 'admin') {
        $bookId = $_POST['book_id'];
        $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
        $stmt->execute([$bookId]);
        header("Location: books.php");
        exit;
    }
}

try {
    $stmt = $pdo->prepare("SELECT 
            username,
            email,
            age,
            created_at,
            books_uploaded,
            about_me,
            role
            FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user']['id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $_SESSION['user']['role'] = $user['role'];

    $joined_date = date('d/m/Y', strtotime($user['created_at']));
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

try {
    $stmt = $pdo->prepare("SELECT * FROM books");
    $stmt->execute();
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Books - Library Website</title>
    <link rel="stylesheet" href="../CSS/books.css">
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="books-content">
        <div class="books-content">
            <div class="search-bar-container">
                <input type="text" class="search-input" placeholder="Search">
                <button class="search-icon-btn" aria-label="Menu">
                    <span class="search-icon"></span>
                </button>
                <?php if (isset($_SESSION['user'])): ?>
                    <button><a href="upload_book.php">Upload Buku Baru</a></button>
                <?php endif; ?>
            </div>
        <div class="books-container" id="booksContainer">
            <div class="books-row" id="booksRow">
                <?php foreach ($books as $index => $book): ?>
                    <div class="book-card<?= $index >= 8 ? ' extra-card' : '' ?>">

                        <div class="book-image">
                            <?php if ($book['cover_image']): ?>
                                <img src="../uploads/books/<?= htmlspecialchars($book['cover_image']) ?>" alt="Book Cover">
                            <?php endif; ?>
                        </div>
                        <div class="book-title"><?= htmlspecialchars($book['title']) ?></div>
                        <div class="book-author">
                            <?= htmlspecialchars($book['author']) ?><br>
                            <span class="book-date">
                                <?= date('d/m/Y', strtotime($book['created_at'])) ?>
                            </span>
                        </div>

                        <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                            <form method="POST" class="delete-form">
                                <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                                <button type="submit" name="delete_book" class="delete-btn">
                                    <span class="delete-text">Delete</span>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        const container = document.getElementById('booksContainer');
        const row = document.getElementById('booksRow');
        let isDragging = false;
        let startX;
        let scrollLeft;
        let currentTranslate = 0;
        let maxScroll = 0;

        function calculateMaxScroll() {
            const containerWidth = container.offsetWidth;
            const rowWidth = row.scrollWidth;
            maxScroll = rowWidth - containerWidth;

        }

        window.addEventListener('resize', calculateMaxScroll);
        calculateMaxScroll();

        container.addEventListener('mousedown', (e) => {
            isDragging = true;
            startX = e.pageX - container.offsetLeft;
            scrollLeft = currentTranslate;
            container.style.cursor = 'grabbing';
        });

        container.addEventListener('mousemove', (e) => {
            if (!isDragging) return;
            e.preventDefault();

            const x = e.pageX - container.offsetLeft;
            const walk = (x - startX) * 1.5;
            let newTranslate = scrollLeft - walk;

            newTranslate = Math.max(-maxScroll, Math.min(newTranslate, 0));
            currentTranslate = newTranslate;
            row.style.transform = `translateX(${newTranslate}px)`;
        });

        container.addEventListener('mouseup', () => {
            isDragging = false;
            container.style.cursor = 'grab';
        });

        container.addEventListener('touchstart', (e) => {
            isDragging = true;
            startX = e.touches[0].pageX - container.offsetLeft;
            scrollLeft = currentTranslate;
        });

        container.addEventListener('touchmove', (e) => {
            if (!isDragging) return;
            e.preventDefault();

            const x = e.touches[0].pageX - container.offsetLeft;
            const walk = (x - startX) * 1.5;
            let newTranslate = scrollLeft - walk;

            newTranslate = Math.max(-maxScroll, Math.min(newTranslate, 0));
            currentTranslate = newTranslate;
            row.style.transform = `translateX(${newTranslate}px)`;
        });

        container.addEventListener('touchend', () => {
            isDragging = false;
        });

        document.body.style.overflowX = 'hidden';
    </script>
    <?php include 'footer.php'; ?>
</body>
</html>
