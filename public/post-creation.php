<?php
include('includes/config.php');

$title = trim($_POST['title']);
$content = trim($_POST['content']);
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

    if (!isset($_FILES['img']) || $_FILES['img']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "A cover image is required.";
    } else {
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

            if (empty($errors) && !move_uploaded_file($imgTmpName, $uploadPath)) {
                $errors[] = "Failed to upload the image.";
            }
        }
    }

    if (empty($errors)) {

        try {
            $stmt = $pdo->prepare("INSERT INTO posts (title, img, content, user_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$title, $img, $content, $user_id]);

            $_SESSION['flash_message'] = "Your blog has been successfully created.";
            header('Location: /index.php');
            exit();
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    } else {
        $_SESSION['flash_errors'] = implode('<br>', $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>

<body>
    <?php include('includes/navbar.php'); ?>

    <main class="post-creation-page">
        <div>
            <h1>Blog creation</h1>
            <form class="form-container-creation" action="post-creation.php" method="POST" enctype="multipart/form-data">
                <div>
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" required value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '' ?>">
                </div>

                <div>
                    <label for="img">Cover Image</label>
                    <input type="file" id="img" name="img" accept="image/jpeg, image/png" required size="4000000">
                </div>

                <div>
                    <label for="content">Content</label>
                    <textarea id="content" name="content" class="content" required><?= isset($_POST['content']) ? htmlspecialchars($_POST['content']) : '' ?></textarea>
                </div>

                <div>
                    <button type="submit" name="post-blog">Create Post</button>
                </div>
            </form>
        </div>
    </main>
</body>

</html>