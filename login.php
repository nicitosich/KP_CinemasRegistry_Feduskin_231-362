<?php
session_start();
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'];
    $password = $_POST['password'];

    // Ищем пользователя в БД
    $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ?");
    $stmt->execute([$login]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['hashed_password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_login'] = $user['login'];
        $_SESSION['user_name'] = $user['name'];

        header('Location: index.php');
        exit;
    } else {
        $error = "Неверный логин или пароль";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Авторизация</title>
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
        <form method="POST" action="login.php" class="auth-form">
            <h2>Авторизация</h2>
            <?php if (isset($error)): ?>
                <p class="error"><?= $error ?></p>
            <?php endif; ?>
            <input type="text" name="login" placeholder="Логин" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <button type="submit">Войти</button>
            <p>Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
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