<?php
include('includes/config.php');

$currentUserId = $_SESSION['user_id'] ?? null;
$isAuthor = $currentUserId && $post['user_id'] === $currentUserId;

try {
} catch (PDOException $e) {
    die("Error fetch : " . $e->getMessage());
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;


$stmt = $pdo->prepare('SELECT * FROM posts WHERE id = :id');
$stmt->execute(['id' => $id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    http_response_code(404);
    die();
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
                <p class="post-date"><?= date('F j, Y \a\t g:i A', strtotime($post['created_at'])) ?></p>
                <img src="/uploads/<?= htmlspecialchars($post['img']) ?>">
                <p><?= htmlspecialchars($post['content']) ?></p>
            </div>
        </article>
    </main>
</body>

</html>