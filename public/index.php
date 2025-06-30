<?php
include('includes/config.php');

if (!$isLoggedIn) {
    $_SESSION['flash_message'] = "<p class='message-user'>You don't have an account, so you can't use all the site's features. <br> Join our community by <a href='/signup-page.php'>creating an account</a>.</p>";
}

try {
    $stmt = $pdo->prepare("SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id ORDER BY posts.created_at DESC");
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $categoriesStmt = $pdo->query("SELECT * FROM categories");
    $categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetch: " . $e->getMessage());
}
?>

<?php include('includes/head.php'); ?>

<body>
    <?php include('includes/navbar.php'); ?>

    <main>
        <section>
            <div class="blog-title-container">
                <h1>Categories</h1>
            </div>

            <div class="blog-posts-container">
                <?php foreach ($categories as $category): ?>
                    <div class="category-container">
                        <h2><?= htmlspecialchars($category['name']) ?></h2>

                        <?php
                        $stmtPosts = $pdo->prepare("SELECT * FROM posts WHERE category_id = ? ORDER BY created_at DESC LIMIT 3");
                    $stmtPosts->execute([$category['id']]);
                    $postsInCategory = $stmtPosts->fetchAll(PDO::FETCH_ASSOC);
                    ?>

                        <div class="blog-post-category">
                            <?php if (!empty($postsInCategory)): ?>
                                <article class="blog-post-big-news">
                                    <a href="#">
                                        <img src="/public/uploads/<?= htmlspecialchars($postsInCategory[0]['img']) ?>" alt="<?= htmlspecialchars($postsInCategory[0]['title']) ?>">
                                        <h2><?= htmlspecialchars($postsInCategory[0]['title']) ?></h2>
                                        <p class="post-date"><?= htmlspecialchars($postsInCategory[0]['created_at']) ?></p>
                                    </a>
                                </article>
                            <?php endif; ?>

                            <div class="second-blog-posts-container">
                                <?php foreach (array_slice($postsInCategory, 1) as $post): ?>
                                    <article class="blog-post">
                                        <a href="#">
                                            <img src="/public/uploads/<?= htmlspecialchars($post['img']) ?>" alt="<?= htmlspecialchars($post['title']) ?>">
                                            <h2><?= htmlspecialchars($post['title']) ?></h2>
                                            <p class="post-date"><?= htmlspecialchars($post['created_at']) ?></p>
                                        </a>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section>
            <div class="blog-title-container">
                <h1>Daily posts</h1>
            </div>
            <?php
            $count = 0;
foreach ($posts as $index => $post):
    if ($count % 3 == 0) {
        echo '<div class="blog-posts-container">';
    }
    ?>
                <article class="blog-post">
                    <a href="post-detail.php?id=<?= $post['id'] ?>">
                        <img src="/uploads/<?= htmlspecialchars($post['img']) ?>" alt="<?= htmlspecialchars($post['title']) ?>">
                        <h2><?= htmlspecialchars($post['title']) ?></h2>
                        <p class="post-date"><?= date('F j, Y \a\t g:i A', strtotime($post['created_at'])) . ' · Posted by ' . htmlspecialchars($post['username']) ?></p>
                    </a>
                </article>
            <?php
        $count++;
    if ($count % 3 == 0 || $index == count($posts) - 1) {
        echo '</div>';
    }
endforeach;
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
