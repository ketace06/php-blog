<?php
include('includes/config.php');

$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

if ($category_id === 0) {
    die('Invalid category.');
}

try {
    $catStmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
    $catStmt->execute([$category_id]);
    $category = $catStmt->fetch(PDO::FETCH_ASSOC);

    if (!$category) {
        die("Category not found.");
    }

    $stmt = $pdo->prepare("
        SELECT posts.*, users.username 
        FROM posts 
        JOIN users ON posts.user_id = users.id 
        WHERE posts.category_id = ?
        ORDER BY posts.created_at DESC
    ");
    $stmt->execute([$category_id]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<?php include('includes/head.php'); ?>

<body>
    <?php include('includes/navbar.php'); ?>

    <main>
        <section>
            <div class="blog-title-container">
                <h1>Posts in <?= htmlspecialchars($category['name']) ?> category</h1>
            </div>
            <div class="blog-posts-container">
                <?php if (empty($posts)): ?>
                    <p>No posts in this category yet.</p>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <article class="blog-post">
                            <a href="post-detail.php?id=<?= $post['id'] ?>">
                                <img src="/uploads/<?= htmlspecialchars($post['img']) ?>" alt="<?= htmlspecialchars($post['title']) ?>">
                                <h2><?= htmlspecialchars($post['title']) ?></h2>
                                <p class="post-date"><?= date('F j, Y \a\t g:i A', strtotime($post['created_at'])) ?> · Posted by <?= htmlspecialchars($post['username']) ?></p>
                            </a>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

</body>

</html>