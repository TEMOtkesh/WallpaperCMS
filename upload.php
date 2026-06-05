<?php
session_start();

require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/classes/Wallpaper.php';

User::requireLogin();

$wallpaperObj = new Wallpaper();
$categories   = $wallpaperObj->getAllCategories();
$tags         = $wallpaperObj->getAllTags();

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title      = trim($_POST['title']       ?? '');
    $desc       = trim($_POST['description'] ?? '');
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $tagIds     = array_map('intval', $_POST['tags'] ?? []);

    if (empty($title)) {
        $error = 'Please enter a title.';
    } elseif ($categoryId < 1) {
        $error = 'Please select a category.';
    } elseif (empty($_FILES['image']['name'])) {
        $error = 'Please choose an image to upload.';
    } else {
        $data = [
            'title'       => $title,
            'description' => $desc,
            'category_id' => $categoryId,
            'user_id'     => $_SESSION['user_id'],
        ];

        $result = $wallpaperObj->createWallpaper($data, $_FILES['image'], $tagIds);

        if ($result === true) {
            header('Location: dashboard.php?success=Wallpaper+uploaded+successfully!');
            exit;
        } else {
            $error = $result;
        }
    }
}

$activePage = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Wallpaper — WallpaperCMS</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php require_once __DIR__ . '/includes/nav.php'; ?>

<div class="page-header">
    <h1>Upload Wallpaper</h1>
    <p>Share a stunning wallpaper with the community.</p>
</div>

<div class="section">
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="upload-card">
        <form action="upload.php" method="POST" enctype="multipart/form-data" novalidate>

            <div class="form-group">
                <label for="title">Title *</label>
                <input type="text" id="title" name="title" required maxlength="200"
                       placeholder="e.g. Misty Mountain Sunrise"
                       value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" placeholder="Optional description..."
                          maxlength="1000"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="category_id">Category *</label>
                <select id="category_id" name="category_id" required>
                    <option value="">— Select a category —</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"
                            <?= (int)($_POST['category_id'] ?? 0) === (int)$cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if (!empty($tags)): ?>
            <div class="form-group">
                <label>Tags</label>
                <div class="tags-checkboxes">
                    <?php foreach ($tags as $tag): ?>
                        <label class="tag-check">
                            <input type="checkbox" name="tags[]" value="<?= $tag['id'] ?>"
                                <?= in_array((int)$tag['id'], array_map('intval', $_POST['tags'] ?? [])) ? 'checked' : '' ?>>
                            <?= htmlspecialchars($tag['name']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="form-group">
                <label>Image * (JPEG, PNG, GIF, WebP — max 10 MB)</label>
                <label class="file-drop-zone" for="image">
                    <span class="upload-icon">📤</span>
                    <span>Drag & drop or click to choose a file</span>
                    <span class="file-name-display" style="color:var(--clr-accent); margin-top:0.4rem;"></span>
                    <input type="file" id="image" name="image" accept="image/*"
                           style="display:none;" required>
                </label>
            </div>

            <div style="display:flex; gap:1rem; margin-top:0.5rem;">
                <button type="submit" class="btn btn-primary">Upload Wallpaper</button>
                <a href="dashboard.php" class="btn btn-outline">Cancel</a>
            </div>

        </form>
    </div>
</div>

<footer class="site-footer">
    <p>&copy; <?= date('Y') ?> WallpaperCMS &mdash; <a href="index.php">Home</a></p>
</footer>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="js/script.js"></script>
</body>
</html>
