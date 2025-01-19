<?php
session_start();
require 'includes/db.php';

// Получаем уникальные местоположения и сортируем их от А до Я
$locations = $pdo->query("SELECT DISTINCT location FROM cinemas ORDER BY location ASC")->fetchAll(PDO::FETCH_COLUMN);
// Получаем типы кинотеатров для фильтров
$affiliations = $pdo->query("SELECT DISTINCT affiliation FROM cinemas")->fetchAll(PDO::FETCH_COLUMN);

// Обработка AJAX-запроса для живого поиска и подгрузки
if (isset($_GET['search']) || isset($_GET['ajax'])) {
    $searchTerm = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';
    $locationFilter = isset($_GET['location']) && $_GET['location'] !== '' ? $_GET['location'] : null;
    $affiliationFilter = isset($_GET['affiliation']) && $_GET['affiliation'] !== '' ? $_GET['affiliation'] : null;
    $minReviews = isset($_GET['min_reviews']) ? (int) $_GET['min_reviews'] : 0;
    $sortOrder = isset($_GET['sort']) ? $_GET['sort'] : '';
    $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;
    $limit = 10;

    // Основной запрос для поиска кинотеатров
    $query = "SELECT c.id, c.name, c.location, c.photo, c.affiliation, COUNT(r.cinema_id) AS review_count
              FROM cinemas c
              LEFT JOIN reviews r ON c.id = r.cinema_id
              WHERE c.name LIKE :search";

    // Фильтры
    if ($locationFilter) {
        $query .= " AND c.location = :location";
    }
    if ($affiliationFilter) {
        $query .= " AND c.affiliation = :affiliation";
    }

    // Группировка по кинотеатрам и фильтруем по количеству отзывов
    $query .= " GROUP BY c.id HAVING COUNT(r.cinema_id) >= :min_reviews";

    // Добавление сортировки, если она выбрана
    if ($sortOrder) {
        switch ($sortOrder) {
            case 'name_asc':
                $query .= " ORDER BY c.name ASC";
                break;
            case 'name_desc':
                $query .= " ORDER BY c.name DESC";
                break;
            case 'reviews_asc':
                $query .= " ORDER BY review_count ASC";
                break;
            case 'reviews_desc':
                $query .= " ORDER BY review_count DESC";
                break;
        }
    }

    $query .= " LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':search', $searchTerm, PDO::PARAM_STR);
    $stmt->bindValue(':min_reviews', $minReviews, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    if ($locationFilter) {
        $stmt->bindValue(':location', $locationFilter, PDO::PARAM_STR);
    }
    if ($affiliationFilter) {
        $stmt->bindValue(':affiliation', $affiliationFilter, PDO::PARAM_STR);
    }

    $stmt->execute();
    $cinemas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($cinemas);
    exit;
}

// Изначальные данные
$offset = 0;
$limit = 10;

// Первые 10 кинотеатров из таблицы cinemas 
$stmt = $pdo->prepare("SELECT c.id, c.name, c.location, c.photo, c.affiliation, COUNT(r.cinema_id) AS review_count
                       FROM cinemas c
                       LEFT JOIN reviews r ON c.id = r.cinema_id
                       GROUP BY c.id
                       LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$cinemas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Поиск кинотеатров</title>
    <link rel="stylesheet" href="styles.css">
    <script src="search-script.js" defer></script>
</head>

<body>
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

    <!-- Меню пользователя -->
    <div id="user-dropdown" class="dropdown-menu">
        <p>Логин: <?= $_SESSION['user_login'] ?></p>
        <a href="update_name.php">Изменить имя</a>
        <a href="update_password.php">Изменить пароль</a>
    </div>

    <!-- Область для поиска и фильтрации -->
    <div class="search-container">
        <div class="search-bar">
            <input type="text" id="search-input" placeholder="Поиск кинотеатров...">
            <button id="filter-button">Фильтр</button>
            <div class="sort-container">
                <select id="sort-select">
                    <option value="">По умолчанию</option> <!-- Сортировка по умолчанию -->
                    <option value="name_asc">Название (А-Я)</option>
                    <option value="name_desc">Название (Я-А)</option>
                    <option value="reviews_asc">Отзывы (с меньшего)</option>
                    <option value="reviews_desc">Отзывы (с большего)</option>
                </select>
            </div>
        </div>

        <!-- Фильтры (скрыты по умолчанию) -->
        <div id="filters" style="display: none;">
            <div class="filter-group">
                <label for="location-filter">Местоположение:</label>
                <select id="location-filter">
                    <option value="">Все</option>
                    <?php foreach ($locations as $location): ?>
                        <option value="<?= $location ?>"><?= $location ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="affiliation-filter">Тип кинотеатра:</label>
                <select id="affiliation-filter">
                    <option value="">Все</option>
                    <option value="commercial">Коммерческий</option>
                    <option value="noncommercial">Некоммерческий</option>
                    <option value="minculi">Министерство культуры</option>
                    <option value="othergov">Другие государственные</option>
                    <option value="control">Контролируемые</option>
                    <option value="mineduc">Министерство образования</option>
                    <option value="mindef">Министерство обороны</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="min-reviews">Минимальное количество отзывов:</label>
                <input type="number" id="min-reviews" min="0" value="0">
            </div>

            <button onclick="applyFilters()">Применить фильтры</button>
        </div>

        <!-- Каталог кинотеатров -->
        <div class="catalog" id="catalog">
            <?php foreach ($cinemas as $cinema): ?>
                <?php
                // Парсинг JSON из столбца photo
                $photoData = json_decode($cinema['photo'], true);
                $photoUrl = $photoData['url']; // URL фотографии
                $photoTitle = $photoData['title']; // Название фотографии
                ?>
                <div class="cinema-card">
                    <img src="<?= $photoUrl ?>" alt="<?= $photoTitle ?>" class="cinema-photo">
                    <div class="cinema-info">
                        <h3><?= $cinema['name'] ?></h3>
                        <p><?= $cinema['location'] ?></p>
                        <p>Отзывы: <?= $cinema['review_count'] ?></p>
                    </div>
                    <button class="details-button">Подробнее</button> 
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Кнопка "Показать ещё" -->
        <?php if (count($cinemas) === $limit): ?>
            <div class="load-more">
                <button id="load-more-button">Показать ещё</button>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>