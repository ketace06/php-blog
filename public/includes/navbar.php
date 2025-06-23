<?php
include('includes/config.php');
?>


<nav>
    <a href="/" class="site-title">The daily loot</a>
    <div class="nav-buttons">
        <a href="/profile.php">
            <img src="https://www.iconpacks.net/icons/2/free-user-icon-3296-thumb.png" class="user-pic" alt="User Profile">
            <?php if ($isLoggedIn) : ?>
                <p><?= htmlspecialchars($_SESSION['username']); ?></p>
            <?php else : ?>
                <p><a href="/login-page.php">Log in</a></p>
            <?php endif; ?>
        </a>
    </div>
</nav>