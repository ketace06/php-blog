<?php
session_start();
try {
    $pdo = new PDO('sqlite:' . dirname(__DIR__) . '/database.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT * FROM posts WHERE user_id = :user_id ORDER BY created_at DESC");
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetch: " . $e->getMessage());
}

$isEdit = isset($_GET['edit']) && is_numeric($_GET['edit']);
$postId = $isEdit ? (int) $_GET['edit'] : null;

$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$user_id = $_SESSION['user_id'];
$img = "";
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($title) || empty($content)) {
        $errors[] = "Title and Content are required.";
    }

    if (strlen($title) < 5) {
        $errors[] = "Title must be at least 5 characters.";
    }

    if (strlen($content) < 10) {
        $errors[] = "Content must be at least 10 characters.";
    }

    if (isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK) {
        $imgTmpName = $_FILES['img']['tmp_name'];
        $imgOriginalName = $_FILES['img']['name'];
        $imgSize = $_FILES['img']['size'];
        $imgType = mime_content_type($imgTmpName);

        $allowedTypes = ['image/jpeg', 'image/png'];
        if (!in_array($imgType, $allowedTypes)) {
            $errors[] = "The file must be an image (JPEG or PNG).";
        }
        if ($imgSize > 4 * 1024 * 1024) {
            $errors[] = "The image must be smaller than 4MB.";
        }

        if (empty($errors)) {
            $img = uniqid('post_', true) . '.' . pathinfo($imgOriginalName, PATHINFO_EXTENSION);
            $uploadDir = dirname(__DIR__) . '/public/uploads/';
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    $errors[] = "Failed to create the uploads directory.";
                }
            }
            $uploadPath = $uploadDir . $img;
            if (!move_uploaded_file($imgTmpName, $uploadPath)) {
                $errors[] = "Failed to upload the image.";
            }
        }
    }

    if (empty($errors)) {
        try {
            if ($isEdit) {
                $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
                $stmt->execute([$postId, $user_id]);
                $existingPost = $stmt->fetch(PDO::FETCH_ASSOC);
                if (empty($img)) {
                    $img = $existingPost['img'];
                }

                $stmt = $pdo->prepare("UPDATE posts SET title = ?, img = ?, content = ? WHERE id = ? AND user_id = ?");
                $stmt->execute([$title, $img, $content, $postId, $user_id]);
            } else {
                if (empty($img)) {
                    $errors[] = "A cover image is required.";
                } else {
                    $stmt = $pdo->prepare("INSERT INTO posts (title, img, content, user_id) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$title, $img, $content, $user_id]);
                }
            }
            header('Location: /post-edition.php');
            exit();
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

function renderPost($post)
{
    ?>
    <article class="blog-post">
        <a href="post-detail.php?id=<?= $post['id'] ?>">
            <img src="/uploads/<?= htmlspecialchars($post['img']) ?>">
            <h2><?= htmlspecialchars($post['title']) ?></h2>
            <p class="post-date">
                <p class="post-date"><?= date('F j, Y \a\t g:i A', strtotime($post['created_at'])) ?></p>
            </p>
        </a>
        <a href="post-detail.php?id=<?= $post['id'] ?>"><button type="button">View</button></a>

        <?php if (isset($_GET['edit']) && $_GET['edit'] == $post['id']): ?>

            <a href="post-edition.php"><button type="button">Cancel changes</button></a>

            <form method="POST" enctype="multipart/form-data" action="post-edition.php?edit=<?= $post['id'] ?>">
                <div>
                    <label for="title<?= $post['id'] ?>">Title:</label>
                    <input type="text" id="title<?= $post['id'] ?>" name="title" value="<?= htmlspecialchars($post['title']) ?>">
                </div>
                <div>
                    <label for="img<?= $post['id'] ?>">Image:</label>
                    <input type="file" id="img<?= $post['id'] ?>" name="img">
                    <small>Current image: <?= htmlspecialchars($post['img']) ?></small>
                </div>
                <div>
                    <label for="content<?= $post['id'] ?>">Content:</label>
                    <textarea id="content<?= $post['id'] ?>" name="content"><?= htmlspecialchars($post['content']) ?></textarea>
                </div>
                <button type="submit">Save Changes</button>
            </form>
        <?php else: ?>
            <a href="post-edition.php?edit=<?= $post['id'] ?>"><button type="button">Edit</button></a>
        <?php endif; ?>
    </article>
<?php
}

if ($isEdit) {
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([$postId]);
    $editPost = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$editPost) {
        http_response_code(404);
        die();
    }

    if (!isset($_SESSION['user_id']) || $editPost['user_id'] != $_SESSION['user_id']) {
        http_response_code(401);
        die();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>

<body>
    <?php include('includes/navbar.php'); ?>
    <h1>Recently Published Blogs</h1>
    <div class="recently-published-card">
        <main class="blog-description-page">
            <?php
            if (!empty($errors)) {
                echo '<ul class="error-messages">';
                foreach ($errors as $error) {
                    echo '<li>' . htmlspecialchars($error) . '</li>';
                }
                echo '</ul>';
            }

$count = 0;
foreach ($posts as $index => $post) {
    if ($count % 3 == 0) {
        echo '<div class="blog-posts-container">';
    }

    renderPost($post);

    $count++;
    if ($count % 3 == 0 || $index == count($posts) - 1) {
        echo '</div>';
    }
}
if (count($posts) === 0) {
    echo '<p>You have not published any posts</p>';
}
?>
        </main>
    </div>
</body>

</html>