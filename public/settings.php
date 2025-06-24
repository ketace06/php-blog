<?php
include('includes/config.php');

$edit_mode = isset($_GET['edit']);

if (isset($_POST['update_account'])) {
    $user_id = $_SESSION['user_id'];
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $verifyPassword = $_POST['verify-password'];

    $errors = [];

    if (empty($username) || empty($email)) {
        $errors[] = "Username and email are required.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (!preg_match('/^[a-zA-Z0-9_ ]{3,20}$/', $username)) {
        $errors[] = "Username must be 3-20 characters, only letters, numbers, and underscores.";
    }

    if (!empty($password) && strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

    if ($password !== $verifyPassword) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$username, $user_id]);
        $usernameExists = $stmt->fetchColumn();

        if ($usernameExists) {
            $errors[] = "Username already exists.";
        }

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        $emailExists = $stmt->fetchColumn();

        if ($emailExists) {
            $errors[] = "Email already exists.";
        }
    }

    if (empty($errors)) {
        try {
            $query = "UPDATE users SET username = ?, email = ?";
            if (!empty($password)) {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $query .= ", password = ?";
            }
            $query .= " WHERE id = ?";
            $stmt = $pdo->prepare($query);
            if (!empty($password)) {
                $stmt->execute([$username, $email, $passwordHash, $user_id]);
            } else {
                $stmt->execute([$username, $email, $user_id]);
            }

            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;

            $_SESSION['flash_message'] = "Profile updated successfully.";
            header("Location: settings.php");
            exit();
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>

<?php include('includes/head.php'); ?>

<body class="settings-page">
    <?php include('includes/navbar.php'); ?>
    <div class="profile-settings-container">
        <aside>
            <h2>Settings</h2>
            <a href="">
                <h3>👤 Profile</h3>
            </a>
            <a href="">
                <h3>🔔 Notifications</h3>
            </a>
            <a href="logout.php">
                <h3>↩ Log out</h3>
            </a>
        </aside>
    </div>
    <div class="profile-settings-container">
        <?php if ($edit_mode): ?>
            <h1>Profile edition of <span class="username"><?= htmlspecialchars($_SESSION['username']); ?></span></h1>

            <form class="user-card-profile" action="settings.php" method="POST">
                <p><strong>Username:</strong> <input type="text" name="username" value="<?= htmlspecialchars($_SESSION['username']); ?>"></p>
                <p><strong>Email:</strong> <input type="email" name="email" value="<?= htmlspecialchars($_SESSION['email']); ?>"></p>
                <p><strong>Password:</strong> <input type="password" name="password" placeholder="Enter new password (leave blank to keep current)"></p>
                <p><strong>Verify password:</strong> <input type="password" name="verify-password" placeholder="Verify your new password"></p>
                <div class="actions-button-profile">
                    <a href="settings.php"><button type="button">Cancel</button></a>
                    <button type="submit" name="update_account">Update Profile</button>
                </div>
            </form>
        <?php else: ?>
            <h1>Profile of <span class="username"><?= htmlspecialchars($_SESSION['username']); ?></span></h1>

            <div class="user-card-profile">

                <p class="user-card-profile-p"><strong>Username:</strong> <?= htmlspecialchars($_SESSION['username']) ?></p>
                <p class="user-card-profile-p"><strong>Email:</strong> <?= htmlspecialchars($_SESSION['email']); ?></p>
                <p class="user-card-profile-p"><strong>Password:</strong>*********</p>
                <a href="settings.php?edit=profile_<?= htmlspecialchars($_SESSION['user_id']) ?>"><button>Edit</button></a>
                <?php if (!empty($errors)) {
                    echo '<div class="message-user-update">';
                    foreach ($errors as $error) {
                        echo htmlspecialchars($error) . '<br>';
                    }
                    echo '</div>';
                } ?>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>