<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>

<body>
    <?php include('includes/navbar.php'); ?>

    <main class="post-creation-page">
        <div>
            <h1>Create Your Blog Post!</h1>
            <form class="form-container-creation">
                <div>
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" required>
                </div>
                <div>
                    <label for="author">Author</label>
                    <input type="text" id="author" name="author" required>
                </div>
                <div>
                    <label for="cover">Cover Image</label>
                    <input type="file" id="cover" name="cover" accept="image/*" required>
                </div>
                <div>
                    <label for="content">Content</label>
                    <textarea id="content" name="content" class="content" required></textarea>
                </div>
                <div>
                    <button type="submit">Create Post</button>
                </div>
            </form>
        </div>
        <h1 class="h1mt">Recently published blogs</h1>
        <div class="recently-published-card">
            <div class="recent-blog">
                <p>Everything you need to know about the Nintendo Switch 2</p>
                <button type="button">Edit</button>
                <input type="text" value="Everything you need to know about the Nintendo Switch 2">
                <button type="button">Delete</button>
            </div>
            <div class="recent-blog">
                <p>Everything you need to know about ...</p>
                <button type="button">Edit</button>
                <input type="text" value="Everything you need to know about ...">
                <button type="button">Delete</button>
            </div>
            <div class="recent-blog">
                <p>Everything you need to know about ...</p>
                <button type="button">Edit</button>
                <input type="text" value="Everything you need to know about ...">
                <button type="button">Delete</button>
            </div>
        </div>
    </main>
</body>

</html>