<?php
if (isset($_POST['signup'])) {
    $pdo = new PDO('sqlite:' . dirname(__DIR__) . '/database.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $errors = [];

    if (empty($username) || empty($email) || empty($password)) {
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

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        $exists = $stmt->fetchColumn();

        if ($exists) {
            $errors[] = "Username or email already exists.";
        }
    }

    if (empty($errors)) {
        try {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $passwordHash]);
            header('Location: /LogIn-page.php');
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

            <?php
            if (!empty($errors)) {
                echo '<div class="user-message error">';
                foreach ($errors as $error) {
                    echo htmlspecialchars($error) . '<br>';
                }
                echo '</div>';
            }

            if (isset($successMessage)) {
                echo '<div class="user-message success">' . htmlspecialchars($successMessage) . '</div>';
            }
            ?>
            <button type="submit" name="signup">Sign Up</button>
        </form>

    </div>
    <p>Already have an account? <a href="/login-page.php">Log in</a>.</p>
    <p>If you want to continue without an account <a href="/index.php">Click here</a>.</p>
</body>

</html>