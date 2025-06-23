<?php

session_start();

if (isset($_SESSION['flash_message'])) {
    echo "<div class='flash-message'>" . $_SESSION['flash_message'] . "</div>";
    unset($_SESSION['flash_message']);
}

if (isset($_SESSION['flash_errors'])) {
    echo "<div class='flash_errors'>" . $_SESSION['flash_errors'] . "</div>";
    unset($_SESSION['flash_errors']);
}

$pdo = new PDO('sqlite:' . dirname(__DIR__, 2) . '/database.db');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$isLoggedIn = isset($_SESSION['user_id']) && isset($_SESSION['username']);

$protectedPages = ['post-creation.php', 'post-edition.php', 'logout.php'];
$currentPage = basename($_SERVER['PHP_SELF']);

if (!$isLoggedIn && in_array($currentPage, $protectedPages)) {
    $_SESSION['flash_errors'] = "Please log in to access this page.";
    header('Location: login-page.php');
    exit();
}
