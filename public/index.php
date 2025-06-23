<?php
include('includes/config.php');

$isLoggedIn = isset($_SESSION['user_id']) && isset($_SESSION['username']);

if (!$isLoggedIn) {
    $_SESSION['flash_message'] = "<p class='message-user'>You don't have an account, so you can't use all the site's features. <br> Join our community by <a href='/signup-page.php'>creating an account</a>.</p>";
}

if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = :id");
        $stmt->execute(['id' => $_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            session_unset();
            session_destroy();
            header('Location: /login-page.php');
            exit;
        }
    } catch (PDOException $e) {
        session_unset();
        session_destroy();
        header('Location: /login-page.php');
        exit;
    }
}

try {
    $stmt = $pdo->prepare("SELECT * FROM posts ORDER BY created_at DESC");
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetch: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>

<body>
    <?php include('includes/navbar.php'); ?>
    <main>
        <section>
            <div class="blog-title-container">
                <h1>In the spotlight</h1>
            </div>
            <div class="blog-post-spotlight">
                <article class="blog-post-big-news">
                    <a href="#">
                        <img src="https://jvmag.ch/wp-content/uploads/2025/06/playstation-6-1024x576.jpg" alt="PlayStation 6">
                        <h2>PlayStation 6 is officially a very big priority at Sony</h2>
                        <p class="post-date">2025-06-10</p>
                    </a>
                </article>
                <div class="second-blog-spotlight-container">
                    <article class="blog-post">
                        <a href="#">
                            <img src="https://cdn.cloudflare.steamstatic.com/steam/apps/730/header.jpg?t=1683566799" alt="Counter-Strike 2">
                            <h2>Counter-Strike 2: What's changed and what to expect</h2>
                            <p class="post-date">2025-06-08</p>
                        </a>
                    </article>
                    <article class="blog-post">
                        <a href="#">
                            <img src="https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/1716740/capsule_616x353.jpg?t=1749757928" alt="Starfield Update">
                            <h2>Starfield receives huge update: performance & mod support improved</h2>
                            <p class="post-date">2025-06-05</p>
                        </a>
                    </article>
                </div>
            </div>
        </section>

        <section>
            <div class="blog-title-container">
                <h1>Latest gaming blogs</h1>
            </div>
            <?php
            $count = 0;
foreach ($posts as $index => $post) {
    if ($count % 3 == 0) {
        echo '<div class="blog-posts-container">';
    }
    ?>
                <article class="blog-post">
                    <a href="post-detail.php?id=<?= $post['id'] ?>">
                        <img src="/uploads/<?= htmlspecialchars($post['img']) ?>">
                        <h2><?= htmlspecialchars($post['title']) ?></h2>
                        <p class="post-date"><?= date('F j, Y \a\t g:i A', strtotime($post['created_at'])) ?></p>
                    </a>
                </article>
            <?php
        $count++;
    if ($count % 3 == 0 || $index == count($posts) - 1) {
        echo '</div>';
    }
}
?>
        </section>
    </main>
    <footer>
        <div class="footer-container">
            <p>Made with <span style="color: #e25555;">&#10084;</span> by ketace06</p>
        </div>
    </footer>
</body>

</html>