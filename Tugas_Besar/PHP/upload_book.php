<?php
require_once 'auth_check.php';
require_once 'config.php';

if (!isset($_SESSION['user'])) {
    header('Location: books.php');
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_book'])) {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $description = $_POST['description'];
    $created_at = date('Y-m-d H:i:s');

    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['cover_image']['tmp_name'];
        $fileName = uniqid() . '_' . basename($_FILES['cover_image']['name']);
        $targetDir = '../uploads/books/';
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($fileTmp, $targetFile)) {
            $stmt = $pdo->prepare("INSERT INTO books (title, author, description, cover_image, created_at) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $author, $description, $fileName, $created_at]);
            $message = "Buku berhasil diupload!";
        } else {
            $message = "Gagal upload cover buku.";
        }
    } else {
        $message = "Cover buku wajib diupload.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Buku</title>
    <link rel="stylesheet" href="..//CSS/books.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="books-content">
        <h2>Upload Buku Baru</h2>
        <?php if ($message): ?>
            <p><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <form action="upload_book.php" method="POST" enctype="multipart/form-data">
            <label>Judul Buku:</label>
            <input type="text" name="title" required>

            <label>Penulis:</label>
            <input type="text" name="author" required>

            <label>Deskripsi:</label>
            <textarea name="description" rows="3" required></textarea>

            <label>Cover Buku (jpg/png):</label>
            <input type="file" name="cover_image" accept="image/*" required>
            <button type="submit" name="upload_book">Upload</button>

        </form>
        <br>
        <a href="books.php">Kembali ke Daftar Buku</a>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>