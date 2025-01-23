<?php
session_start();
require 'includes/db.php';

// Проверяем, передан ли ID кинотеатра в URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$cinemaId = (int) $_GET['id'];

// Получаем данные о кинотеатре из базы данных
$stmt = $pdo->prepare("SELECT * FROM cinemas WHERE id = :id");
$stmt->bindValue(':id', $cinemaId, PDO::PARAM_INT);
$stmt->execute();
$cinema = $stmt->fetch(PDO::FETCH_ASSOC);

// Если кинотеатр не найден, перенаправляем на главную страницу
if (!$cinema) {
    header('Location: index.php');
    exit;
}

// Парсим JSON из столбца photo
$photoData = json_decode($cinema['photo'], true);
$photoUrl = $photoData['url']; // URL фотографии
$photoTitle = $photoData['title']; // Название фотографии

// Парсим JSON из столбца phone_number
$phoneNumbers = json_decode($cinema['phone_number'], true);

// Функция для очистки текста от HTML-тегов
function cleanText($text)
{
    return strip_tags($text); // Удаляем все HTML-теги
}

// Функция для форматирования даты и времени
function formatDateTime($dateTime)
{
    $date = new DateTime($dateTime);
    return $date->format('d.m.Y H:i'); // Формат: день.месяц.год часы:минуты
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($cinema['name']) ?></title>
    <link rel="stylesheet" href="styles.css">
    <script src="js/stars_rev.js" defer></script>
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

    <div class="cinema-details">
        <!-- Кнопка возврата в каталог -->

        <h1><?= htmlspecialchars($cinema['name']) ?></h1>
        <!-- Увеличенное фото -->
        <img src="<?= $photoUrl ?>" alt="<?= $photoTitle ?>" class="cinema-photo-large">

        <!-- Секция "Информация" -->
        <section class="info-section">
            <h2>Информация</h2>
            <p><strong>Название:</strong> <?= htmlspecialchars($cinema['name']) ?></p>
            <p><strong>Местоположение:</strong> <?= htmlspecialchars($cinema['location']) ?></p>
            <p><strong>Адрес:</strong> <?= htmlspecialchars($cinema['street']) ?></p>
            <?php if (!empty($cinema['street_additional'])): ?>
                <p><strong>Дополнительные сведения к адресу:</strong> <?= htmlspecialchars($cinema['street_additional']) ?>
                </p>
            <?php endif; ?>
            <?php if (!empty($cinema['description'])): ?>
                <p><strong>Описание:</strong> <?= cleanText($cinema['description']) ?></p>
            <?php endif; ?>
        </section>

        <!-- Секции "Расписание" и "Контактные данные и сайт" на одном уровне -->
        <div class="row">
            <!-- Секция "Расписание" -->
            <section class="info-section">
                <h2>Расписание</h2>
                <?php
                $days = [
                    'Понедельник' => 'schedule_monday',
                    'Вторник' => 'schedule_tuesday',
                    'Среда' => 'schedule_wednesday',
                    'Четверг' => 'schedule_thursday',
                    'Пятница' => 'schedule_friday',
                    'Суббота' => 'schedule_saturday',
                    'Воскресенье' => 'schedule_sunday',
                ];

                foreach ($days as $dayName => $dayColumn) {
                    $schedule = json_decode($cinema[$dayColumn], true);
                    if ($schedule) {
                        echo "<p><strong>$dayName:</strong> с {$schedule['from']} до {$schedule['to']}</p>";
                    } else {
                        echo "<p><strong>$dayName:</strong> Нет данных</p>";
                    }
                }
                ?>
            </section>

            <!-- Секция "Контактные данные и сайт" -->
            <section class="info-section">
                <h2>Контактные данные и сайт</h2>
                <?php if (!empty($cinema['website'])): ?>
                    <p><strong>Сайт:</strong> <a href="<?= htmlspecialchars($cinema['website']) ?>"
                            target="_blank"><?= htmlspecialchars($cinema['website']) ?></a></p>
                <?php endif; ?>
                <?php if (!empty($cinema['email'])): ?>
                    <p><strong>Почта:</strong> <?= htmlspecialchars($cinema['email']) ?></p>
                <?php endif; ?>
                <?php if (!empty($phoneNumbers)): ?>
                    <p><strong>Телефон:</strong></p>
                    <ul>
                        <?php foreach ($phoneNumbers as $phone): ?>
                            <li><?= htmlspecialchars($phone['value']) ?> (<?= htmlspecialchars($phone['comment']) ?>)</li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>
        </div>

        <!-- Секция "Юридическая информация" -->
        <section class="info-section">
            <h2>Юридическая информация</h2>
            <p><strong>Организация:</strong> <?= $cinema['legal_entity'] ?></p>
            <p><strong>Адрес организации:</strong> <?= htmlspecialchars($cinema['le_address']) ?></p>
            <p><strong>ИНН:</strong> <?= htmlspecialchars($cinema['le_inn']) ?></p>
            <p><strong>Тип кинотеатра:</strong> <?= htmlspecialchars($cinema['affiliation']) ?></p>
            <p><strong>Номер ЕИПСК:</strong> <?= htmlspecialchars($cinema['ID_EIPSC']) ?></p>
        </section>

        <!-- Секция "Прочая информация" -->
        <section class="info-section">
            <h2>Прочая информация</h2>
            <p><strong>Дата добавления записи:</strong> <?= formatDateTime($cinema['record_date']) ?></p>
            <p><strong>Дата обновления записи:</strong> <?= formatDateTime($cinema['update_date']) ?></p>
        </section>

        <!-- Блок отзывов -->
        <section class="reviews-section">
            <h2>Отзывы</h2>

            <!-- Форма для добавления отзыва (доступна только авторизованным пользователям) -->
            <?php
            // Проверка, оставил ли текущий пользователь отзыв
            $userHasReviewed = false;
            if (isset($_SESSION['user_id'])) {
                $stmt = $pdo->prepare("SELECT cinema_id FROM reviews WHERE cinema_id = :cinema_id AND user_id = :user_id");
                $stmt->bindValue(':cinema_id', $cinemaId, PDO::PARAM_INT);
                $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
                $stmt->execute();
                $userHasReviewed = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            ?>

            <!-- Форма для добавления отзыва (доступна только авторизованным пользователям) -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if (!$userHasReviewed): ?>
                    <div class="add-review-form">
                        <h3>Оставить отзыв</h3>
                        <form id="review-form" method="POST" action="submit_review.php">
                            <input type="hidden" name="cinema_id" value="<?= $cinemaId ?>">
                            <div class="rating-criteria">
                                <label>Удобство расположения:</label>
                                <select name="location_score" required>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                </select>
                            </div>
                            <div class="rating-criteria">
                                <label>Качество сервиса:</label>
                                <select name="service_score" required>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                </select>
                            </div>
                            <div class="rating-criteria">
                                <label>Качество просмотра:</label>
                                <select name="viewing_score" required>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                </select>
                            </div>
                            <div class="rating-criteria">
                                <label>Качество зала (-ов):</label>
                                <select name="hall_score" required>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                </select>
                            </div>
                            <div class="rating-criteria">
                                <label>Вариативность фильмов:</label>
                                <select name="movie_variety_score" required>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                </select>
                            </div>
                            <div class="review-text">
                                <label for="review-text">Текст отзыва:</label>
                                <textarea id="review-text" name="review_text" rows="4" required></textarea>
                            </div>
                            <button type="submit" class="submit-button">Отправить отзыв</button>
                        </form>
                    </div>
                <?php else: ?>
                    <p>Вы уже оставили отзыв для этого кинотеатра.</p>
                <?php endif; ?>
            <?php else: ?>
                <p>Чтобы оставить отзыв, <a href="login.php">авторизуйтесь</a>.</p>
            <?php endif; ?>

            <?php
            // Получаем последние 10 отзывов для этого кинотеатра
            $reviews = []; // Инициализируем переменную как пустой массив
            try {
                $stmt = $pdo->prepare("SELECT r.*, u.name AS user_name FROM reviews r
                           JOIN users u ON r.user_id = u.id
                           WHERE r.cinema_id = :cinema_id
                           ORDER BY r.date DESC
                           LIMIT 10");
                $stmt->bindValue(':cinema_id', $cinemaId, PDO::PARAM_INT);
                $stmt->execute();
                $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC); // Получаем отзывы
            } catch (PDOException $e) {
                // Логируем ошибку, если запрос не удался
                error_log("Ошибка при получении отзывов: " . $e->getMessage());
                $reviews = []; // Оставляем переменную пустым массивом
            }
            ?>

            <!-- Список отзывов -->
            <div class="reviews-list">
                <h3>Последние отзывы</h3>
                <?php if (count($reviews) > 0): ?>
                    <?php foreach ($reviews as $review): ?>
                        <?php
                        $scores = explode(':', $review['score']);
                        // Проверяем, является ли отзыв оставленным текущим пользователем
                        $isCurrentUserReview = isset($_SESSION['user_id']) && $review['user_id'] == $_SESSION['user_id'];
                        ?>
                        <div class="review-item <?= $isCurrentUserReview ? 'user-review' : '' ?>">
                            <p><strong><?= htmlspecialchars($review['user_name']) ?></strong>
                                (<?= formatDateTime($review['date']) ?>)</p>
                            <p>Удобство расположения: <?= renderStars($scores[0]) ?></p>
                            <p>Качество сервиса: <?= renderStars($scores[1]) ?></p>
                            <p>Качество просмотра: <?= renderStars($scores[2]) ?></p>
                            <p>Качество зала (-ов): <?= renderStars($scores[3]) ?></p>
                            <p>Вариативность фильмов: <?= renderStars($scores[4]) ?></p>
                            <div class="review-text">
                                <p><?= nl2br(htmlspecialchars($review['text'])) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Пока нет отзывов. Будьте первым!</p>
                <?php endif; ?>
            </div>
            
            <?php
            // Функция для отображения звездочек
            function renderStars($score)
            {
                $stars = '';
                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= $score) {
                        $stars .= '&#9733;';
                    } else {
                        $stars .= '&#9734;';
                    }
                }
                return $stars;
            }
            ?>
        </section>
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