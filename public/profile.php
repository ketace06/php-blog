<?php
include('includes/config.php');
?>

<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>

<body>
    <?php include('includes/navbar.php'); ?>

    <div class="profile-settings-container">
        <ul>
            <?php if (isset($_SESSION['user_id'])) : ?>
                <li><a href="post-creation.php">Post creation</a></li>
                <li><a href="post-edition.php">Post edition</a></li>
                <li><a href="logout.php">Log out</a></li>
            <?php else: ?>
                <li><a href="login-page.php">Log in</a></li>
                <li><a href="signup-page.php">Sign up</a></li>
            <?php endif; ?>
        </ul>
    </div>
</body>

</html>