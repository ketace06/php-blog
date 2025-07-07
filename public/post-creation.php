<?php
include('includes/config.php');

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

$title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$category_name = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
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
        $errors[] = "A cover image is required. 4 MB maximum upload.";
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

    $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
    $stmt->execute([$category_name]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);

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

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO posts (title, img, content, user_id, category_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $img, $content, $user_id, $category_id]);

            $_SESSION['flash_message'] = "Your blog has been successfully created.";
            header('Location: /');
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

    <main>
        <form class="form-container-creation" action="post-creation.php" method="POST" enctype="multipart/form-data">
            <h1>Post creation</h1>
            <div>
                <label for="title">Title</label>
                <input type="text" id="title" name="title" required value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '' ?>">
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



                <div>
                    <label for="img">Cover Image</label>
                    <input type="file" id="img" name="img" accept="image/jpeg, image/png" required size="4000000">
                </div>

                <div>
                    <label for="content">Content</label>
                    <textarea id="content" name="content" class="content" required><?= isset($_POST['content']) ? htmlspecialchars($_POST['content']) : '' ?></textarea>
                </div>

                <div>
                    <button type="submit" name="post-blog">Send the post</button>
                </div>
        </form>
    </main>
</body>

</html>