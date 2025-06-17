<?php
if (isset($_POST['register'])) {
    $pdo = new PDO('sqlite:' . dirname(__DIR__) . '/database.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    $exists = $stmt->fetchColumn();

    if ($exists) {
        $errorMessage = "Error: Username or email already exists.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $password]);
        } catch (PDOException $e) {
            $errorMessage = "Error: " . $e->getMessage();
        }
        header('Location: /login-page.php');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>

<body>
    <div class="form-container">
        <h1>Sign up</h1>
        <form action="register-page.php" method="POST">
            <input type="text" name="username" placeholder="Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>

            <?php
            if (isset($errorMessage)) {
                echo '<div class="user-message">' . htmlspecialchars($errorMessage) . '</div>';
            }
            ?>

            <button type="submit" name="register">Sign up</button>
        </form>
    </div>
    <p>Already have an account? <a href="/login-page.php">Log in here</a>.</p>
</body>

</html>