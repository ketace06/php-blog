<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    try {
        $pdo = new PDO('sqlite:' . dirname(__DIR__) . '/database.db');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        if (!$email) {
            $errorMessage = "Invalid email format.";
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($_POST['password'], $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];

                session_regenerate_id(true);
                header('Location: /index.php');
                exit;
            } elseif ($user) {
                $errorMessage = "Incorrect password. Please try again.";
            } else {
                $errorMessage = "No account exists with this email address. Please check the email entered or create a new account if you don't have one yet.";
            }
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        die("Sorry, a database error occurred. Please try again later.");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>

<body>
    <div class="form-container">
        <h1>Welcome back</h1>

        <?php if (!empty($errorMessage)) : ?>
            <p style="color:red;"><?= htmlspecialchars($errorMessage) ?></p>
        <?php endif; ?>

        <form action="/login-page.php" method="POST">
            <input type="email" name="email" placeholder="Email" autocomplete="email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Log In</button>
        </form>
    </div>
    <p>Don't have an account yet? <a href="/signup-page.php">Sign up</a>.</p>
</body>

</html>