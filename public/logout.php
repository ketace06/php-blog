<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_logout'])) {
    session_unset();
    session_destroy();
    header('Location: /index.php');
    exit;
}
?>

<!DOCTYPE html>
<html>
<?php include('includes/head.php'); ?>

<body>
    <div class="logout"> 
        <?php include('includes/navbar.php'); ?>
        <h2>Are you sure you want to log out?</h2>
        <form method="POST" action="logout.php">
            <button type="submit" name="confirm_logout">Yes, Log Me Out</button>
        </form>
        <form method="GET" action="settings.php">
            <button type="submit">No, Take Me Back</button>
        </form>
</div>

</body>

</html>