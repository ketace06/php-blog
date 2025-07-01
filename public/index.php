<?php
include('includes/config.php');

if (!$isLoggedIn) {
    $_SESSION['flash_message'] = "<p class='message-user'>You don't have an account, so you can't use all the site's features. <br> Join our community by <a href='/signup-page.php'>creating an account</a>.</p>";
}

try {
    $stmt = $pdo->prepare("SELECT posts.*, users.username, categories.name AS category_name FROM posts 
                            JOIN users ON posts.user_id = users.id 
                            LEFT JOIN categories ON posts.category_id = categories.id
                            ORDER BY posts.created_at DESC");
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $categoriesStmt = $pdo->query("SELECT * FROM categories");
    $categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching posts: " . $e->getMessage());
}

?>

<?php include('includes/head.php'); ?>

<body>
    <?php include('includes/navbar.php'); ?>

    <main>
        <section>
            <?php if (!empty($categories)): ?>
                <div class="blog-title-container">
                    <h1>Categories</h1>
                </div>

                <?php foreach ($categories as $category): ?>
                    <?php
                    $stmtPosts = $pdo->prepare("SELECT * FROM posts
                        JOIN users ON posts.user_id = users.id 
                        WHERE category_id = ? 
                        ORDER BY created_at DESC LIMIT 3");
                    $stmtPosts->execute([$category['id']]);
                    $postsInCategory = $stmtPosts->fetchAll(PDO::FETCH_ASSOC);

                    if (empty($postsInCategory)) {
                        continue;
                    }
                    ?>

                    <div class="category-container">
                        <div class="cool-div">
                            <h2><?= htmlspecialchars($category['name']) ?></h2>
                            <a href="post-edition.php">View all ></a>
                        </div>
                        <div class="blog-posts-container">
                            <?php foreach ($postsInCategory as $post): ?>
                                <article class="blog-post">
                                    <a href="post-detail.php?id=<?= $post['id'] ?>">
                                        <img src="/uploads/<?= htmlspecialchars($post['img']) ?>" alt="<?= htmlspecialchars($post['title']) ?>">
                                        <h2><?= htmlspecialchars($post['title']) ?></h2>
                                        <p class="post-date"><?= date('F j, Y \a\t g:i A', strtotime($post['created_at'])) . ' · Posted by ' . htmlspecialchars($post['username']) ?></p>
                                    </a>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-categories-message">There are no categories available at the moment. Categories are managed by the site admins.</p>
            <?php endif; ?>
        </section>

        <section>
            <div class="blog-title-container">
                <h1>Daily Posts</h1>
            </div>
            <div class="blog-posts-container">
                <?php if (empty($posts)): ?>
                    <div class="no-post-div">
                        <p>There are no posts for today...</p>
                        <p>Enjoy!</p>
                    </div>
                <?php else: ?>
                    <?php
                    $count = 0;
                    foreach ($posts as $index => $post):
                        if ($count % 3 == 0 && $count > 0) {
                            echo '</div><div class="blog-posts-container">';
                        }
                    ?>
                        <article class="blog-post">
                            <?php if (!empty($post['category_name'])): ?>
                                <p class="post-category">From <?= htmlspecialchars($post['category_name']) ?> category</p>
                            <?php endif; ?>
                            <a href="post-detail.php?id=<?= $post['id'] ?>">
                                <img src="/uploads/<?= htmlspecialchars($post['img']) ?>" alt="<?= htmlspecialchars($post['title']) ?>">
                                <h2><?= htmlspecialchars($post['title']) ?></h2>
                                <p class="post-date"><?= date('F j, Y \a\t g:i A', strtotime($post['created_at'])) . ' · Posted by ' . htmlspecialchars($post['username']) ?></p>
                            </a>
                        </article>

                    <?php
                        $count++;
                    endforeach;
                    if ($count % 3 != 0) {
                        echo '</div>';
                    }
                    ?>
                <?php endif; ?>
            </div>
        </section>

        <footer>
            <div class="footer-container">
                <p>Made with <span style="color: #e25555;">&#10084;</span> by ketace06</p>
            </div>
        </footer>
    </main>
</body>

</html>