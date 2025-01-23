<?php
session_start();
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'];
    $password = $_POST['password'];
    $name = $_POST['name'];

    // Проверяем, существует ли пользователь с таким логином
    $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ?");
    $stmt->execute([$login]);
    $user = $stmt->fetch();

    if ($user) {
        $error = "Данный логин уже занят.";
    } else {
        // Хэш пароль
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (login, hashed_password, name) VALUES (?, ?, ?)");
        $stmt->execute([$login, $hashed_password, $name]);

        header('Location: login.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Регистрация</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <!-- Шапка -->
    <header>
        <nav>
            <div class="logo">Кинотеатры</div>
            <ul>
                <li><a href="index.php">Главная</a></li>
                <li><a href="search.php">Поиск</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="#" id="user-menu"><?= $_SESSION['user_name'] ?></a></li>
                    <li><a href="logout.php">Выйти</a></li>
                <?php else: ?>
                    <li><a href="login.php">Авторизация</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <!-- Основной контент -->
    <div class="auth-container">
        <form method="POST" action="register.php" class="auth-form">
            <h2>Регистрация</h2>
            <?php if (isset($error)): ?>
                <p class="error"><?= $error ?></p>
            <?php endif; ?>
            <input type="text" name="login" placeholder="Логин" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <input type="text" name="name" placeholder="Имя" required>
            <button type="submit">Зарегистрироваться</button>
            <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
        </form>
    </div>

    <!-- Футер -->
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>О нас</h3>
                <p>Данный сервис помогает людям находить лучшие кинотеатры.</p>
            </div>
            <div class="footer-section">
                <h3>Контакты</h3>
                <p>Email: info@cinema.com</p>
                <p>Телефон: +7 (123) 456-78-90</p>
            </div>
            <div class="footer-section">
                <h3>Открытые данные</h3>
                <ul>
                    Сайт использует открытые данные из источника: https://opendata.mkrf.ru/
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 Кинотеатры. Все права защищены.</p>
        </div>
    </footer>
</body>

</html>