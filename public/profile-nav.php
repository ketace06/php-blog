<?php
include('includes/config.php');
?>

<?php include('includes/head.php'); ?>

<body>
    <?php include('includes/navbar.php'); ?>

    <div class="profile-settings-container">
        <ul>
            <?php if (isset($_SESSION['user_id'])) : ?>
                <li><a href="post-creation.php">Post creation</a></li>
                <li><a href="post-edition.php">Post edition</a></li>
                <?php if ($_SESSION['role'] === 'admin') : ?>
                    <li><a href="admin.php">Admin Dashboard</a></li>
                <?php endif; ?>
                <li><a href="settings.php?profile=<?= $_SESSION['user_id'] ?>">Settings</a></li>

            <?php else: ?>
                <li><a href="login-page.php">Log in</a></li>
                <li><a href="signup-page.php">Sign up</a></li>
            <?php endif; ?>
        </ul>
    </div>
</body>

</html>