<?php
session_start();

require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/classes/Wallpaper.php';

User::requireLogin();

$wallpaperObj = new Wallpaper();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id < 1) {
    header('Location: dashboard.php?error=Invalid+wallpaper.');
    exit;
}

$wallpaper = $wallpaperObj->getWallpaperById($id);

if (!$wallpaper) {
    header('Location: dashboard.php?error=Wallpaper+not+found.');
    exit;
}

// Only the uploader or an admin may edit
if ($_SESSION['role'] !== 'admin' && (int)$wallpaper['user_id'] !== (int)$_SESSION['user_id']) {
    header('Location: dashboard.php?error=Access+denied.');
    exit;
}

$categories     = $wallpaperObj->getAllCategories();
$tags           = $wallpaperObj->getAllTags();
$selectedTagIds = array_column($wallpaperObj->getTagsForWallpaper($id), 'id');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title      = trim($_POST['title']       ?? '');
    $desc       = trim($_POST['description'] ?? '');
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $tagIds     = array_map('intval', $_POST['tags'] ?? []);

    if (empty($title)) {
        $error = 'Please enter a title.';
    } elseif ($categoryId < 1) {
        $error = 'Please select a category.';
    } else {
        $data   = ['title' => $title, 'description' => $desc, 'category_id' => $categoryId];
        $file   = (!empty($_FILES['image']['name'])) ? $_FILES['image'] : [];
        $result = $wallpaperObj->updateWallpaper($id, $data, $file, $tagIds);

        if ($result === true) {
            header('Location: dashboard.php?success=Wallpaper+updated+successfully!');
            exit;
        } else {
            $error = $result;
        }
    }

    // Re-read for form repopulation
    $wallpaper      = $wallpaperObj->getWallpaperById($id);
    $selectedTagIds = $tagIds;
}

$activePage = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Wallpaper — WallpaperCMS</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php require_once __DIR__ . '/includes/nav.php'; ?>

<div class="page-header">
    <h1>Edit Wallpaper</h1>
    <p>Update the details for <strong><?= htmlspecialchars($wallpaper['title']) ?></strong></p>
</div>

<div class="section">
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="upload-card">
        <form action="edit_wallpaper.php?id=<?= $id ?>" method="POST" enctype="multipart/form-data" novalidate>

            <div class="form-group">
                <label for="title">Title *</label>
                <input type="text" id="title" name="title" required maxlength="200"
                       value="<?= htmlspecialchars($wallpaper['title']) ?>">
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"
                          maxlength="1000"><?= htmlspecialchars($wallpaper['description'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="category_id">Category *</label>
                <select id="category_id" name="category_id" required>
                    <option value="">— Select a category —</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"
                            <?= (int)$cat['id'] === (int)$wallpaper['category_id'] ? 'selected' : '' ?>>
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
                                <?= in_array((int)$tag['id'], array_map('intval', $selectedTagIds)) ? 'checked' : '' ?>>
                            <?= htmlspecialchars($tag['name']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="form-group">
                <label>Replace Image (optional)</label>
                <?php if (!empty($wallpaper['image_path']) && file_exists($wallpaper['image_path'])): ?>
                    <img src="<?= htmlspecialchars($wallpaper['image_path']) ?>"
                         alt="Current image"
                         style="max-height:180px; border-radius:var(--radius); margin-bottom:0.75rem;
                                border:1px solid var(--clr-border);">
                <?php endif; ?>
                <label class="file-drop-zone" for="image">
                    <span class="upload-icon">📤</span>
                    <span>Leave empty to keep the current image</span>
                    <span class="file-name-display" style="color:var(--clr-accent); margin-top:0.4rem;"></span>
                    <input type="file" id="image" name="image" accept="image/*" style="display:none;">
                </label>
            </div>

            <div style="display:flex; gap:1rem; margin-top:0.5rem;">
                <button type="submit" class="btn btn-primary">Save Changes</button>
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
