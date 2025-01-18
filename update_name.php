<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_name = $_POST['new_name'];
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
    $stmt->execute([$new_name, $user_id]);

    $_SESSION['user_name'] = $new_name;
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Изменить имя</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="auth-container">
        <form method="POST" action="update_name.php" class="auth-form">
            <h2>Изменить имя</h2>
            <input type="text" name="new_name" placeholder="Новое имя" required>
            <button type="submit">Сохранить</button>
            <a href="index.php" class="back-button">Вернуться назад</a>
        </form>
    </div>
</body>
</html>