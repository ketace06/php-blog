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

if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $stmt = $pdo->prepare("SELECT * FROM categories");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (isset($_GET['delete_category']) && is_numeric($_GET['delete_category'])) {
        $categoryIdToDelete = $_GET['delete_category'];
        try {
            $stmt = $pdo->prepare("SELECT * FROM posts WHERE category_id = ?");
            $stmt->execute([$categoryIdToDelete]);
            $postsInCategory = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($postsInCategory) > 0) {
                $_SESSION['flash_errors'] = "Cannot delete category, it has posts associated with it.";
            } else {
                $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
                $stmt->execute([$categoryIdToDelete]);
                $_SESSION['flash_message'] = "Category deleted successfully.";
            }
        } catch (PDOException $e) {
            $_SESSION['flash_errors'] = "Error deleting category: " . $e->getMessage();
        }
        header("Location: admin.php");
        exit();
    }

    if (isset($_POST['create_category'])) {
        $category_name = trim($_POST['category_name']);
        if (!empty($category_name)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
                $stmt->execute([$category_name]);
                $_SESSION['flash_message'] = "Category created successfully.";
                header("Location: admin.php");
                exit();
            } catch (PDOException $e) {
                $_SESSION['flash_errors'] = "Error creating category: " . $e->getMessage();
            }
        }
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'user' ORDER BY username ASC");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    http_response_code(403);
    exit();
}

$stmt = $pdo->prepare("SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id ORDER BY posts.created_at DESC");
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);


if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $postIdToDelete = $_GET['delete'];

    if ($_SESSION['role'] === 'admin') {
        try {
            $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
            $stmt->execute([$postIdToDelete]);
            $_SESSION['flash_message'] = "Post deleted successfully.";
            header("Location: admin.php");
            exit();
        } catch (PDOException $e) {
            $_SESSION['flash_errors'] = "Error deleting post: " . $e->getMessage();
            header("Location: admin.php");
            exit();
        }
    } else {
        http_response_code(403);
        exit();
    }
}
if (isset($_GET['delete_user']) && is_numeric($_GET['delete_user'])) {
    $userIdToDelete = $_GET['delete_user'];

    if ($_SESSION['role'] === 'admin') {
        try {
            if ($user) {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$userIdToDelete]);

                $_SESSION['flash_message'] = "User deleted successfully.";
            } else {
                $_SESSION['flash_errors'] = "User not found.";
            }
        } catch (PDOException $e) {
            $_SESSION['flash_errors'] = "Error deleting user: " . $e->getMessage();
        }
    } else {
        http_response_code(403);
        exit();
    }
    header("Location: admin.php");
    exit();
}

function renderPost($post)
{
    ?>
    <article class="blog-post">
        <a href="post-detail.php?id=<?= $post['id'] ?>">
            <img src="/uploads/<?= htmlspecialchars($post['img']) ?>" alt="Post Image">
            <h2><?= htmlspecialchars($post['title']) ?></h2>
            <p class="post-date"><?= date('F j, Y \a\t g:i A', strtotime($post['created_at'])) . ' · Posted by ' . htmlspecialchars($post['username']) . ' in ' . htmlspecialchars($post['category_name']) ?></p>
        </a>
        <div class="actions-button-edit">
            <?php if ($_SESSION['role'] === 'admin' || $_SESSION['user_id'] === $post['user_id']): ?>
                <a href="admin.php?delete=<?= $post['id'] ?>"><button type="button">Delete</button></a>

                <a href="post-edition.php?edit=<?= $post['id'] ?>"><button type="button">Edit</button></a>
                <a href="post-detail.php?id=<?= $post['id'] ?>"><button type="button">View</button></a>

            <?php endif; ?>
        </div>
    </article>
<?php
}
?>

<?php include('includes/head.php'); ?>

<body>
    <?php include('includes/navbar.php'); ?>
    <div class="recently-published-card">
        <main class="blog-description-page">

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

            <div class="category-management">
                <form method="POST" action="admin.php">
                    <h1>Add category</h1>
                    <input type="text" name="category_name" placeholder="Enter new category name" required>
                    <button type="submit" name="create_category">Create Category</button>
                </form>
                <ul>
                    <?php if (empty($categories)): ?>
                        <span>No categories found.</span>
                    <?php else: ?>
                        <?php foreach ($categories as $category): ?>
                            <li class="category-item-container">
                                <span><?= htmlspecialchars($category['name']) ?></span>
                                <a href="admin.php?delete_category=<?= $category['id'] ?>">Delete</a>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>

            <h1>Users management</h1>

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
                                        <a href="admin.php?delete_user=<?= htmlspecialchars($user['id']) ?>">Delete</a>
                                        <a href="settings.php?edit=1&profile=<?= $user['id'] ?>">Edit</a>
                                        <a href="settings.php?profile=<?= htmlspecialchars($user['id']) ?>">View</a>
                                    </div>
                                </details>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>


        </main>
    </div>
</body>

</html>