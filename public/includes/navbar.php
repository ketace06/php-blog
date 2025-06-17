<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']) && isset($_SESSION['username']);
?>

<nav>
    <a href="/" class="site-title">The daily loot</a>
    <div class="nav-buttons">
        <?php if ($isLoggedIn) : ?>
            <div class="welcome-message">
                <a href="post-creation.php">Start writing</a>
            </div>
            <a href="logout.php">Log out</a>
        <?php else: ?>
            <?php
            $currentPage = basename($_SERVER['PHP_SELF']);
            if (
                $currentPage === 'post-creation.php' ||
                $currentPage === 'post-edition.php' ||
                $currentPage === 'logout.php'
            ) {
                header('Location: login-page.php');
                exit();
            }
            ?>
            <a href="login-page.php">Log in</a>
            <a href="signup-page.php">Sign up</a>
        <?php endif; ?>
    </div>
</nav>