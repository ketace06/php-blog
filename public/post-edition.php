<?php
include('includes/config.php');

$category_name = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $postId = (int)$_GET['delete'];

    $stmt = $pdo->prepare("SELECT id, user_id, img FROM posts WHERE id = ?");
    $stmt->execute([$postId]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($post) {
        if ($_SESSION['role'] === 'admin' || $_SESSION['user_id'] === $post['user_id']) {
            try {
                if ($post['img']) {
                    $imagePath = dirname(__DIR__) . '/public/uploads/' . $post['img'];
                    if (file_exists($imagePath) && !unlink($imagePath)) {
                        $_SESSION['flash_errors'] = "Error deleting image.";
                        header('Location: post-edition.php');
                        exit();
                    }
                }

                $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
                $stmt->execute([$postId]);

                $_SESSION['flash_message'] = "Post successfully deleted.";
                header('Location: post-edition.php');
                exit();
            } catch (PDOException $e) {
                $_SESSION['flash_errors'] = "Error deleting post: " . $e->getMessage();
                header('Location: post-edition.php');
                exit();
            }
        } else {
            http_response_code(403);
            exit();
        }
    } else {
        $_SESSION['flash_errors'] = "Post not found.";
        header('Location: post-edition.php');
        exit();
    }
}

$title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$user_id = $_SESSION['user_id'];
$img = "";
$errors = [];

if (!empty($category_name)) {
    $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
    $stmt->execute([$category_name]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($category) {
        $category_id = $category['id'];
    } else {
        $errors[] = "The selected category does not exist.";
    }
} else {
    $category_id = null;
}

$isEdit = isset($_GET['edit']) && is_numeric($_GET['edit']);
$postId = $isEdit ? (int)$_GET['edit'] : null;

$query = ($_SESSION['role'] === 'admin')
    ? "SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id ORDER BY posts.created_at DESC"
    : "SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id WHERE posts.user_id = :user_id ORDER BY posts.created_at DESC";

$stmt = $pdo->prepare($query);
if ($_SESSION['role'] !== 'admin') {
    $stmt->execute(['user_id' => $user_id]);
} else {
    $stmt->execute();
}
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    }

    if (empty($errors)) {
        if (!empty($_FILES['img']['tmp_name'])) {
            $img = uniqid('post_', true) . '.' . pathinfo($imgOriginalName, PATHINFO_EXTENSION);
            $uploadDir = dirname(__DIR__) . '/public/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $uploadPath = $uploadDir . $img;
            if (!move_uploaded_file($imgTmpName, $uploadPath)) {
                $errors[] = "Failed to upload the image.";
            }
        }

        try {
            if ($isEdit) {
                $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
                $stmt->execute([$postId]);
                $existingPost = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($_SESSION['role'] === 'admin' || $_SESSION['user_id'] === $existingPost['user_id']) {
                    $img = empty($img) ? $existingPost['img'] : $img;
                    $stmt = $pdo->prepare("UPDATE posts SET title = ?, img = ?, content = ?, user_id = ?, category_id = ? WHERE id = ?");
                    $stmt->execute([$title, $img, $content, $user_id, $category_id, $postId]);
                } else {
                    http_response_code(403);
                    exit();
                }
            } else {
                if (empty($img)) {
                    $errors[] = "A cover image is required.";
                } else {
                    $stmt = $pdo->prepare("INSERT INTO posts (title, img, content, user_id, category_id) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$title, $img, $content, $user_id, $category_id]);
                }
            }

            $_SESSION['flash_message'] = "Your blog has been successfully updated.";
            header('Location: post-edition.php');
            exit();
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    } else {
        $_SESSION['flash_errors'] = implode('<br>', $errors);
    }
}
?>

<?php include('includes/head.php'); ?>

<body>
    <?php include('includes/navbar.php'); ?>
    <div class="recently-published-card">
        <main class="blog-description-page">
            <h1>Recent posts</h1>
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
                        <p class="post-date"><?= date('F j, Y \a\t g:i A', strtotime($post['created_at'])) . ' · Posted by ' . htmlspecialchars($post['username']) ?></p>
                    </a>
                    <?php if (isset($_GET['edit']) && $_GET['edit'] == $post['id']): ?>
                        <a href="post-edition.php"><button type="button">Cancel changes</button></a>
                        <form method="POST" enctype="multipart/form-data" action="post-edition.php?edit=<?= $post['id'] ?>">
                            <div>
                                <label for="title<?= $post['id'] ?>">Title:</label>
                                <input type="text" id="title<?= $post['id'] ?>" name="title" value="<?= htmlspecialchars($post['title']) ?>">
                            </div>
                            <div>
                                <label for="category">Category</label>
                                <input list="categories" id="category" name="category" placeholder="Choose a category" value="<?= isset($_POST['category']) ? htmlspecialchars($_POST['category']) : '' ?>">
                                <datalist id="categories">
                                    <?php
                            $stmt = $pdo->query("SELECT name FROM categories");
                        while ($category = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo '<option value="' . htmlspecialchars($category['name']) . '">';
                        }
                        ?>
                                </datalist>
                            </div>
                            <div>
                                <label for="img<?= $post['id'] ?>">Cover image:</label>
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
                        <div class="actions-button-edit">
                            <a href="post-edition.php?delete=<?= $post['id'] ?>"><button type="button">Delete</button></a>
                            <?php if ($_SESSION['role'] === 'admin' || $_SESSION['user_id'] === $post['user_id']): ?>
                                <a href="post-edition.php?edit=<?= $post['id'] ?>"><button type="button">Edit</button></a>
                                <a href="post-detail.php?id=<?= $post['id'] ?>"><button type="button">View</button></a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </article>
            <?php
                $count++;
    if ($count % 3 == 0 || $index == count($posts) - 1) {
        echo '</div>';
    }
}
if (count($posts) === 0) {
    echo '<p>There are no posts</p>';
}
?>
        </main>
    </div>
</body>

</html>