<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    die('Ошибка: Пользователь не авторизован.');
}

if (!isset($_POST['cinema_id'], $_POST['location_score'], $_POST['service_score'], $_POST['viewing_score'], $_POST['hall_score'], $_POST['movie_variety_score'], $_POST['review_text'])) {
    die('Ошибка: Не все данные переданы.');
}

$cinemaId = (int)$_POST['cinema_id'];
$userId = (int)$_SESSION['user_id'];
$locationScore = (int)$_POST['location_score'];
$serviceScore = (int)$_POST['service_score'];
$viewingScore = (int)$_POST['viewing_score'];
$hallScore = (int)$_POST['hall_score'];
$movieVarietyScore = (int)$_POST['movie_variety_score'];
$reviewText = trim($_POST['review_text']);

$score = "$locationScore:$serviceScore:$viewingScore:$hallScore:$movieVarietyScore";

$stmt = $pdo->prepare("INSERT INTO reviews (cinema_id, user_id, date, text, score) VALUES (:cinema_id, :user_id, NOW(), :text, :score)");
$stmt->bindValue(':cinema_id', $cinemaId, PDO::PARAM_INT);
$stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
$stmt->bindValue(':text', $reviewText, PDO::PARAM_STR);
$stmt->bindValue(':score', $score, PDO::PARAM_STR);
$stmt->execute();

header("Location: cinema.php?id=$cinemaId");
exit;