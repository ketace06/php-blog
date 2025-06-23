<?php
include('include/config.php')

?>


<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>

<body class="settings-page">
    <?php include('includes/navbar.php'); ?>
    <div class="profile-settings-container">
        <aside>
            <h2>Settings</h2>
            <a href=""><h3>👤 Profile settings</h3></a>
            <a href=""><h3>🔔 Notifications</h3></a>
            <a href="logout.php"><h3>↩ Log out</h3></a>
        </aside>
    </div>
    <div class="profile-settings-container">
        <h1>Profile settings<span class="username"><?= htmlspecialchars($username); ?></span></h1>

        <form class="user-card-profile" action="settings.php" method="POST">
            <p><strong>Username:</strong> <input type="text" name="name" value="<?= htmlspecialchars($user_data['name']); ?>"></p>
            <p><strong>Email:</strong> <input type="email" name="email" value="<?= htmlspecialchars($user_data['email']); ?>"></p>
            <p><strong>Password:</strong> <input type="password" name="password" value=""><button type="button">Click to view the password</button></p>
            <p><strong>Verify password:</strong> <input type="password" name="password" value=""></p>
            <button type="submit">Update Profile</button>
        </form>
    </div>
</body>

</html>