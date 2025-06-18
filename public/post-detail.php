<?php
try {
    $pdo = new PDO('sqlite:' . dirname(__DIR__) . '/database.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error fetch : " . $e->getMessage());
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare('SELECT * FROM posts WHERE id = :id');
$stmt->execute(['id' => $id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    die('Post not found.');
}
?>

<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>

<body>
    <?php include('includes/navbar.php'); ?>

    <main class="blog-description-page">
        <article>
            <div class="description-blog">
                <h1><?= htmlspecialchars($post['title']) ?></h1>
                <p class="post-date"><?= htmlspecialchars($post['created_at']) ?></p>
                <img src="/uploads/<?= htmlspecialchars($post['img']) ?>">
                <p><?= htmlspecialchars($post['content']) ?></p>
            </div>
        </article>
    </main>
</body>

</html>