<?php
session_start();
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Главная страница</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <header>
        <nav>
            <div class="logo">Кинотеатры</div>
            <ul>
                <li><a href="#">Главная</a></li>
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

    <!-- Меню пользователя -->
    <div id="user-dropdown" class="dropdown-menu">
        <p>Ваш логин: <?= $_SESSION['user_login'] ?></p>
        <a href="update_name.php">Изменить имя</a>
        <a href="update_password.php">Изменить пароль</a>
    </div>

    <script>
        document.getElementById('user-menu').addEventListener('click', function (event) {
            event.preventDefault();
            const dropdown = document.getElementById('user-dropdown');
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        });


        document.addEventListener('click', function (event) {
            const dropdown = document.getElementById('user-dropdown');
            const userMenu = document.getElementById('user-menu');
            if (dropdown.style.display === 'block' && event.target !== userMenu && !dropdown.contains(event.target)) {
                dropdown.style.display = 'none';
            }
        });
    </script>

    <!-- Слайд-шоу -->
    <section class="slideshow">
        <div class="slides">
            <img src="images/slide1.jpg" alt="Слайд 1">
            <img src="images/slide2.jpg" alt="Слайд 2">
            <img src="images/slide3.jpg" alt="Слайд 3">
        </div>
        <div class="overlay"></div>
        <a href="search.php" class="search-button">Перейти к поиску</a>
    </section>

    <!-- Описание сайта -->
    <section class="description">
        <div class="container">
            <h1>Добро пожаловать на наш сайт!</h1>
            <p>
                Мы рады приветствовать вас на платформе, посвященной миру кино и кинотеатров.
                Здесь вы найдете самую актуальную информацию о лучших кинотеатрах вашего города и не только.
                Наш сайт создан для того, чтобы помочь вам сделать правильный выбор и насладиться просмотром фильмов в
                комфортной атмосфере.
            </p>
            <p>
                На страницах нашего сайта вы сможете:
            </p>
            <ul>
                <li>Узнать подробности о кинотеатрах: их местоположение, расписание , контактные данные и другую информацию.</li>
                <li>Ознакомиться с отзывами реальных посетителей, чтобы понять, какой кинотеатр подходит именно вам.
                </li>
            </ul>
            <p>
                Мы стремимся сделать ваш опыт максимально удобным и приятным. Наш сайт — это не просто каталог
                кинотеатров, это ваш проводник в мир качественного кинопоказа.
                Погрузитесь в атмосферу кино вместе с нами, и пусть каждый поход в кинотеатр станет незабываемым
                событием!
            </p>
        </div>
    </section>

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

    <!-- Скрипт для слайд-шоу -->
    <script src="script.js"></script>
</body>

</html>