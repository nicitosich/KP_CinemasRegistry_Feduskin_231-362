<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT hashed_password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if ($user && password_verify($old_password, $user['hashed_password'])) {
        $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("UPDATE users SET hashed_password = ? WHERE id = ?");
        $stmt->execute([$new_hashed_password, $user_id]);

        header('Location: index.php');
        exit;
    } else {
        $error = "Неверный старый пароль";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Изменить пароль</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="auth-container">
        <form method="POST" action="update_password.php" class="auth-form">
            <h2>Изменить пароль</h2>
            <?php if (isset($error)): ?>
                <p class="error"><?= $error ?></p>
            <?php endif; ?>
            <input type="password" name="old_password" placeholder="Старый пароль" required>
            <input type="password" name="new_password" placeholder="Новый пароль" required>
            <button type="submit">Сохранить</button>
            <a href="index.php" class="back-button">Вернуться назад</a>
        </form>
    </div>
</body>
</html>