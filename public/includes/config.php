<?php

session_start();

$pdo = new PDO('sqlite:' . dirname(__DIR__, 2) . '/database.db');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = :id");
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        session_unset();
        session_destroy();
        header("Location: index.php");
        exit;
    }
}

$isLoggedIn = isset($_SESSION['user_id']);

if (isset($_SESSION['flash_message'])) {
    echo "<div class='flash-message'>" . $_SESSION['flash_message'] . "</div>";
    unset($_SESSION['flash_message']);
}

if (isset($_SESSION['flash_errors'])) {
    echo "<div class='flash_errors'>" . $_SESSION['flash_errors'] . "</div>";
    unset($_SESSION['flash_errors']);
}



$protectedPages = ['post-creation.php', 'post-edition.php', 'logout.php', 'settings.php'];
$currentPage = basename($_SERVER['PHP_SELF']);

if (!$isLoggedIn && in_array($currentPage, $protectedPages)) {
    $_SESSION['flash_errors'] = "Please log in to access this page.";
    header('Location: login-page.php');
    exit();
}
