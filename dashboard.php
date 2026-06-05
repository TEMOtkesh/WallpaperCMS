<?php
session_start();

require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/classes/Wallpaper.php';

User::requireLogin();

$wallpaperObj = new Wallpaper();
$myWallpapers = $wallpaperObj->getWallpapersByUser((int) $_SESSION['user_id']);
$totalCount   = count($myWallpapers);

$activePage = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — WallpaperCMS</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php require_once __DIR__ . '/includes/nav.php'; ?>

<div class="page-header">
    <h1>My Dashboard</h1>
    <p>Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?>!</p>
    <div class="page-actions">
        <a href="upload.php" class="btn btn-primary">+ Upload Wallpaper</a>
    </div>
</div>

<div class="section">

    <!-- Stats -->
    <div class="dashboard-stats">
        <div class="stat-card">
            <div class="stat-number"><?= $totalCount ?></div>
            <div class="stat-label">Wallpapers Uploaded</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= ucfirst(htmlspecialchars($_SESSION['role'])) ?></div>
            <div class="stat-label">Your Role</div>
        </div>
    </div>

    <!-- Success / Error messages -->
    <?php if (!empty($_GET['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>
    <?php if (!empty($_GET['error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <h2 style="font-family:var(--font-display); font-size:1.5rem; margin-bottom:1.5rem;">
        My Uploads
    </h2>

    <?php if (empty($myWallpapers)): ?>
        <div class="empty-state">
            <div class="icon">📤</div>
            <h3>No uploads yet</h3>
            <p>Share your first wallpaper with the community.</p>
            <a href="upload.php" class="btn btn-primary">Upload Now</a>
        </div>
    <?php else: ?>
        <div class="cards-grid">
            <?php foreach ($myWallpapers as $wp): ?>
                <div class="wallpaper-card">
                    <div class="card-image-wrap">
                        <?php if (!empty($wp['image_path']) && file_exists($wp['image_path'])): ?>
                            <img src="<?= htmlspecialchars($wp['image_path']) ?>"
                                 alt="<?= htmlspecialchars($wp['title']) ?>" loading="lazy">
                        <?php else: ?>
                            <div class="card-placeholder">🖼</div>
                        <?php endif; ?>
                        <span class="card-category-badge"><?= htmlspecialchars($wp['category']) ?></span>
                    </div>
                    <div class="card-body">
                        <div class="card-title"><?= htmlspecialchars($wp['title']) ?></div>
                        <div class="card-meta"><?= date('M j, Y', strtotime($wp['created_at'])) ?></div>
                        <div class="card-actions">
                            <a href="edit_wallpaper.php?id=<?= $wp['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                            <a href="delete_wallpaper.php?id=<?= $wp['id'] ?>"
                               class="btn btn-danger btn-sm confirm-delete">Delete</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<footer class="site-footer">
    <p>&copy; <?= date('Y') ?> WallpaperCMS &mdash; <a href="index.php">Home</a></p>
</footer>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="js/script.js"></script>
</body>
</html>
