<?php
session_start();
include('includes/config.php');

$title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$user_id = $_SESSION['user_id'];
$img = "";
$errors = [];

$isEdit = isset($_GET['edit']) && is_numeric($_GET['edit']);
$postId = $isEdit ? (int)$_GET['edit'] : null;

$stmt = $pdo->prepare("SELECT id, username, role FROM users WHERE role != 'admin'");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $stmt = $pdo->prepare("SELECT posts.*, users.id as user_id, users.username FROM posts JOIN users ON posts.user_id = users.id ORDER BY posts.created_at DESC");
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    http_response_code(403);
    exit;
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $userIdToDelete = $_GET['delete'];

    if ($_SESSION['role'] === 'admin') {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userIdToDelete]);

            $_SESSION['flash_message'] = "User has been deleted successfully.";
            header("Location: admin.php");
            exit();
        } catch (PDOException $e) {
            $_SESSION['flash_errors'] = "Error deleting user: " . $e->getMessage();
            header("Location: admin.php");
            exit();
        }
    } else {
        http_response_code(403);
        exit();
    }
}

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
                mkdir($uploadDir, 0755, true);
            }
            $uploadPath = $uploadDir . $img;
            move_uploaded_file($imgTmpName, $uploadPath);
        }
    }

    if (empty($errors)) {
        try {
            if ($isEdit) {
                $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
                $stmt->execute([$postId]);
                $existingPost = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$existingPost) {
                    $errors[] = "Post not found.";
                } elseif ($_SESSION['role'] === 'admin' || $_SESSION['user_id'] === $existingPost['user_id']) {
                    if (empty($img)) {
                        $img = $existingPost['img'];
                    }

                    $stmt = $pdo->prepare("UPDATE posts SET title = ?, img = ?, content = ? WHERE id = ?");
                    $stmt->execute([$title, $img, $content, $postId]);
                } else {
                    $errors[] = "You don't have permission to edit this post.";
                }
            } else {
                if (empty($img)) {
                    $errors[] = "A cover image is required.";
                } else {
                    $stmt = $pdo->prepare("INSERT INTO posts (title, img, content, user_id) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$title, $img, $content, $user_id]);
                }
            }

            if (empty($errors)) {
                $_SESSION['flash_message'] = "Your blog has been successfully updated.";
                header('Location: /post-edition.php');
                exit();
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    } else {
        $_SESSION['flash_errors'] = implode('<br>', $errors);
    }
}

function renderPost($post)
{
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
                <a href="post-detail.php?id=<?= $post['id'] ?>"><button type="button">View</button></a>
                <?php if ($_SESSION['role'] === 'admin' || $_SESSION['user_id'] === $post['user_id']): ?>
                    <a href="post-edition.php?edit=<?= $post['id'] ?>"><button type="button">Edit</button></a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </article>
<?php
}
?>

<?php include('includes/head.php'); ?>

<body>
    <?php include('includes/navbar.php'); ?>
    <div class="recently-published-card">
        <main class="blog-description-page">
            <h1>Admin's dashboard</h1>
            <div class="user-container">
                <ul class="user-list">
                    <?php if (empty($users)): ?>
                        <span>No users found.</span>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <li class="user-item-container">
                                <details>
                                    <summary class="user-item"><?= htmlspecialchars($user['username']) ?></summary>
                                    <div class="user-management">
                                        <a href="admin.php?delete=<?= htmlspecialchars($user['id']) ?>">Delete</a>
                                        <a href="settings.php?edit=1&profile=<?= $user['id'] ?>">Edit</a>
                                        <a href="settings.php?profile=<?= htmlspecialchars($user['id']) ?>">View</a>
                                    </div>
                                </details>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>

                </ul>
            </div>
            <div class="cool-div">
                <h1>Recent Posts Management</h1>
                <a href="post-edition.php">View all ></a>
            </div>
            <?php
            $count = 0;
            $limit = 3;
            foreach ($posts as $index => $post) {
                if ($count >= $limit) {
                    break;
                }

                if ($count % 3 == 0) {
                    echo '<div class="blog-posts-container">';
                }

                renderPost($post);
                $count++;

                if ($count % 3 == 0 || $index == count($posts) - 1) {
                    echo '</div>';
                }
            }

            if ($count === 0) {
                echo '<p>There are no posts</p>';
            }
            ?>
        </main>
    </div>
</body>

</html>