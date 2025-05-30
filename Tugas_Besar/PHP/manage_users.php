<?php
require_once 'auth_check.php';
require_once 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $uid = $_POST['user_id'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$uid]);
    header('Location: manage_users.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $uid = $_POST['user_id'];
    $newUsername = $_POST['username'];
    $newPass = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    if ($newPass !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        if ($newPass !== '') {
            $hash = password_hash($newPass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
            $stmt->execute([$newUsername, $hash, $uid]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
            $stmt->execute([$newUsername, $uid]);
        }
        header('Location: manage_users.php');
        exit;
    }
}

$stmt = $pdo->query("SELECT id, username, email FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Manage Users</title>
    <link rel="stylesheet" href="..//CSS/manage_users.css">
</head>

<body>
    <?php include 'header.php'; ?>
    <main class="mg-users-page">
        <div class="search-bar-container">
            <input type="text" placeholder="Search" id="userSearch" class="search-input">
            <button class="search-icon-btn" aria-label="Search">
                <span class="search-icon"></span>
            </button>
        </div>

        <div class="users-list">
            <?php foreach ($users as $user): ?>
                <div class="user-card">
                    <form method="POST" class="action-group">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        <button name="delete_user" type="submit" class="btn-link delete-link">Delete</button>
                        <button type="button" class="btn-link change-link"
                            data-id="<?= $user['id'] ?>"
                            data-username="<?= htmlspecialchars($user['username']) ?>">
                            Change
                        </button>
                    </form>
                    <div class="user-info">
                        <span>Username: <?= htmlspecialchars($user['username']) ?></span>
                        <span>Email: <?= htmlspecialchars($user['email']) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <form method="POST" class="modal-body">
                <h2>Edit User</h2>
                <input type="hidden" name="user_id" id="modalUserId">
                <label>Username</label>
                <input type="text" name="username" id="modalUsername" required>
                <label>Password (leave blank to keep)</label>
                <input type="password" name="password">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password">
                <button type="submit" name="update_user" class="blue-button">Save Changes</button>
            </form>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        const input = document.getElementById('userSearch');
        const cards = document.querySelectorAll('.user-card');
        input.addEventListener('input', () => {
            const term = input.value.trim().toLowerCase();
            cards.forEach(card => {
                const info = card.querySelector('.user-info').textContent.toLowerCase();
                card.style.display = info.includes(term) ? 'flex' : 'none';
            });
        });

        const modal = document.getElementById('editUserModal');
        const closeBtn = modal.querySelector('.close-button');
        const changeBtns = document.querySelectorAll('.change-link');
        const modalUserId = document.getElementById('modalUserId');
        const modalUsername = document.getElementById('modalUsername');

        changeBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                modalUserId.value = btn.dataset.id;
                modalUsername.value = btn.dataset.username;
                modal.style.display = 'block';
            });
        });

        closeBtn.addEventListener('click', () => modal.style.display = 'none');
        window.addEventListener('click', e => {
            if (e.target === modal) modal.style.display = 'none';
        });
    </script>
</body>

</html>