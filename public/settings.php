<?php
include('includes/config.php');

$edit_mode = isset($_GET['edit']);
$profile_id = isset($_GET['profile']) && is_numeric($_GET['profile']) ? (int)$_GET['profile'] : null;

if (!$profile_id) {
    echo "No profile ID provided.";
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$profile_id]);
$profile_user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$profile_user && $is_admin) {
    http_response_code(404);
    echo "User not found.";
    exit;
}

$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$is_owner = isset($_SESSION['user_id']) && $_SESSION['user_id'] === $profile_id;

if (!$is_owner && !$is_admin) {
    header("Location: /");
    http_response_code(403);
    exit;
}
if (isset($_POST['update_account']) && ($is_owner || $is_admin)) {
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
        $errors[] = "Username must be 3-20 characters.";
    }

    if (!empty($password) && strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

    if ($password !== $verifyPassword) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$username, $profile_id]);
        if ($stmt->fetchColumn()) {
            $errors[] = "Username already exists.";
        }

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $profile_id]);
        if ($stmt->fetchColumn()) {
            $errors[] = "Email already exists.";
        }
    }

    if (empty($errors)) {
        $query = "UPDATE users SET username = ?, email = ?";
        $params = [$username, $email];
        if (!empty($password)) {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $query .= ", password = ?";
            $params[] = $passwordHash;
        }
        $query .= " WHERE id = ?";
        $params[] = $profile_id;

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        if ($is_owner) {
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
        }

        $_SESSION['flash_message'] = "Profile updated successfully.";
        header("Location: settings.php?profile=$profile_id");
        exit;
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
            <a href="/logout.php">
                <h3>↩ Log out</h3>
            </a>
        </aside>
    </div>
    <div class="profile-settings-container">
        <?php if ($edit_mode): ?>
            <h1>Profile edition of <span class="username"><?= htmlspecialchars($profile_user['username']); ?></span></h1>

            <form class="user-card-profile" action="settings.php?profile=<?= $profile_id ?>&edit=1" method="POST">
                <p><strong>Username:</strong> <input type="text" name="username" value="<?= htmlspecialchars($profile_user['username']); ?>"></p>
                <p><strong>Email:</strong> <input type="email" name="email" value="<?= htmlspecialchars($profile_user['email']); ?>"></p>
                <p><strong>Password:</strong> <input type="password" name="password" placeholder="Enter new password (leave blank to keep current)"></p>
                <p><strong>Verify password:</strong> <input type="password" name="verify-password" placeholder="Verify your new password"></p>
                <div class="actions-button-profile">
                    <a href="settings.php?profile=<?= $profile_id ?>"><button type="button">Cancel</button></a>
                    <button type="submit" name="update_account">Update Profile</button>
                </div>
            </form>
        <?php else: ?>
            <h1>Profile of <span class="username"><?= htmlspecialchars($profile_user['username']); ?></span></h1>

            <div class="user-card-profile">
                <p class="user-card-profile-p"><strong>Username:</strong> <?= htmlspecialchars($profile_user['username']) ?></p>
                <p class="user-card-profile-p"><strong>Email:</strong> <?= htmlspecialchars($profile_user['email']); ?></p>
                <p class="user-card-profile-p"><strong>Password:</strong>*********</p>
                <?php if ($is_owner || $is_admin): ?>
                    <a href="settings.php?edit=1&profile=<?= $profile_user['id'] ?>"><button>Edit</button></a>
                <?php endif; ?>
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