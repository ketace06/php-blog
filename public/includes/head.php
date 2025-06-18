<?php
session_start();

$isLoggedIn = isset($_SESSION['user_id']) && isset($_SESSION['username']);

$protectedPages = ['post-creation.php', 'post-edition.php', 'logout.php'];
$currentPage = basename($_SERVER['PHP_SELF']);

if (!$isLoggedIn && in_array($currentPage, $protectedPages)) {
    header('Location: login-page.php');
    exit();
}
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The daily loot</title>
    <link rel="stylesheet" href="assets/app.css">
</head>