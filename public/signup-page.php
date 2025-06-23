<?php
include('includes/config.php');
if (isset($_POST['signup'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $verifyPassword = $_POST['verify-password'];

    $errors = [];

    if (empty($username) || empty($email) || empty($password) || empty($verifyPassword)) {
        $errors[] = "All fields are required.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (!preg_match('/^[a-zA-Z0-9_ ]{3,20}$/', $username)) {
        $errors[] = "Username must be 3-20 characters, only letters, numbers and underscores.";
    }

    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

    if ($password !== $verifyPassword) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $usernameExists = $stmt->fetchColumn();

        if ($usernameExists) {
            $errors[] = "Username already exists.";
        }

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $emailExists = $stmt->fetchColumn();

        if ($emailExists) {
            $errors[] = "Email already exists.";
        }
    }

    if (empty($errors)) {
        try {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $passwordHash]);

            $_SESSION['flash_message'] = "The account has been successfully created.";
            header('Location: /login-page.php');
            exit();
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>

<body>
    <div class="form-container">
        <h1>Create an account</h1>
        <form action="signup-page.php" method="POST">
            <input type="text" name="username" placeholder="Name" required value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">
            <input type="email" name="email" placeholder="Email" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="verify-password" placeholder="Verify password" required>
            <?php
            if (!empty($errors)) {
                echo '<div class="message-user-creation">';
                foreach ($errors as $error) {
                    echo htmlspecialchars($error) . '<br>';
                }
                echo '</div>';
            }
?>
            <button type="submit" name="signup">Sign Up</button>
        </form>

    </div>
    <p>Already have an account? <a href="/login-page.php">Log in</a>.</p>
    <p>If you want to continue without an account <a href="/index.php">Click here</a>.</p>
</body>

</html>