<?php
// ============================================================
// wallpapers.php — Wallpapers Gallery Page
// Requirement: At least 5 pages (Wallpapers is page 2 of 5)
// Requirement: Display wallpapers from database using Wallpaper class
// Requirement: Search panel controlled by jQuery slideToggle()
// Requirement: Category filter
// Requirement: Responsive Design — CSS Grid
// Requirement: jQuery fadeIn() on cards
// ============================================================

session_start();

require_once __DIR__ . '/classes/Wallpaper.php';

$wallpaperObj = new Wallpaper();
$categories   = $wallpaperObj->getAllCategories();

// Category filter — read from URL param
$filterCatId = isset($_GET['category']) ? (int) $_GET['category'] : 0;

// Fetch the right set of wallpapers
// Requirement: CRUD Read — getAllWallpapers / getWallpapersByCategory
if ($filterCatId > 0) {
    $wallpapers = $wallpaperObj->getWallpapersByCategory($filterCatId);
} else {
    $wallpapers = $wallpaperObj->getAllWallpapers();
}

$activePage = 'wallpapers';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wallpapers — WallpaperCMS</title>

    <!-- Requirement: External CSS file -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php require_once __DIR__ . '/includes/nav.php'; ?>


<!-- ============================================================
     PAGE HEADER
     ============================================================ -->
<div class="page-header">
    <h1>Wallpaper Gallery</h1>
    <p>Browse <?= count($wallpapers) ?> wallpaper<?= count($wallpapers) !== 1 ? 's' : '' ?> from our community.</p>

    <div class="page-actions">
        <!-- Requirement: jQuery slideToggle() — this button triggers it -->
        <button class="search-toggle-btn" id="search-toggle">
            🔍 <span class="toggle-label">Search & Filter</span>
        </button>

        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="upload.php" class="btn btn-primary btn-sm">+ Upload Wallpaper</a>
        <?php endif; ?>
    </div>
</div>


<div class="section">

    <!-- ============================================================
         SEARCH PANEL
         Requirement: jQuery slideToggle() — #search-panel is hidden by
         default (display:none in CSS) and revealed by jQuery in script.js
         ============================================================ -->
    <div id="search-panel">
        <div class="search-row">
            <!-- Live text search — jQuery filters cards client-side -->
            <!-- Requirement: jQuery live search -->
            <div class="form-group">
                <label for="search-input">Search by Title</label>
                <input
                    type="text"
                    id="search-input"
                    placeholder="e.g. Mountains, Galaxy..."
                >
            </div>

            <!-- Category filter — server-side (page reload) -->
            <div class="form-group">
                <label for="cat-filter">Filter by Category</label>
                <select id="cat-filter"
                    onchange="window.location='wallpapers.php?category='+this.value">
                    <option value="0" <?= $filterCatId === 0 ? 'selected' : '' ?>>All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option
                            value="<?= $cat['id'] ?>"
                            <?= $filterCatId === (int)$cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Reset button -->
            <div class="form-group" style="justify-content:flex-end;">
                <a href="wallpapers.php" class="btn btn-outline btn-sm">Reset</a>
            </div>
        </div>
    </div>


    <!-- Category filter pills (quick navigation) -->
    <div class="filter-bar">
        <a href="wallpapers.php"
           class="filter-pill <?= $filterCatId === 0 ? 'active' : '' ?>">
            All
        </a>
        <?php foreach ($categories as $cat): ?>
            <a href="wallpapers.php?category=<?= $cat['id'] ?>"
               class="filter-pill <?= $filterCatId === (int)$cat['id'] ? 'active' : '' ?>">
                <?= htmlspecialchars($cat['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>


    <!-- ============================================================
         WALLPAPER CARDS GRID
         Requirement: Responsive Design — CSS Grid (cards-grid)
         Requirement: CSS3 Box Shadow — .wallpaper-card in style.css
         Requirement: CSS3 Transition — .wallpaper-card in style.css
         Requirement: CSS3 Transform (scale) — .wallpaper-card:hover
         Requirement: jQuery fadeIn() — triggered per-card in script.js
         ============================================================ -->
    <?php if (empty($wallpapers)): ?>
        <div class="empty-state">
            <div class="icon">🔍</div>
            <h3>No wallpapers found</h3>
            <p>
                <?php if ($filterCatId > 0): ?>
                    No wallpapers in this category yet.
                <?php else: ?>
                    No wallpapers have been uploaded yet.
                <?php endif; ?>
            </p>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="upload.php" class="btn btn-primary">Upload the first one</a>
            <?php endif; ?>
        </div>

    <?php else: ?>

        <!-- CSS Grid — see .cards-grid in style.css -->
        <div class="cards-grid">
            <?php foreach ($wallpapers as $wp): ?>
                <!-- Requirement: jQuery fadeIn() applied to each .wallpaper-card in script.js -->
                <div class="wallpaper-card">
                    <div class="card-image-wrap">
                        <?php if (!empty($wp['image_path']) && file_exists(__DIR__ . '/' . $wp['image_path'])): ?>
                            <img
                                src="<?= htmlspecialchars($wp['image_path']) ?>"
                                alt="<?= htmlspecialchars($wp['title']) ?>"
                                loading="lazy"
                            >
                        <?php else: ?>
                            <div class="card-placeholder">🖼</div>
                        <?php endif; ?>

                        <span class="card-category-badge">
                            <?= htmlspecialchars($wp['category']) ?>
                        </span>
                    </div>

                    <div class="card-body">
                        <div class="card-title"><?= htmlspecialchars($wp['title']) ?></div>
                        <div class="card-meta">
                            By <?= htmlspecialchars($wp['uploader']) ?>
                            &middot;
                            <?= date('M j, Y', strtotime($wp['created_at'])) ?>
                        </div>

                        <?php if (!empty($wp['description'])): ?>
                            <p style="font-size:0.83rem;color:var(--clr-muted);
                                      overflow:hidden;display:-webkit-box;
                                      -webkit-line-clamp:2;-webkit-box-orient:vertical;">
                                <?= htmlspecialchars($wp['description']) ?>
                            </p>
                        <?php endif; ?>

                        <!-- Edit/Delete only visible to uploader or admin -->
                        <?php if (
                            isset($_SESSION['user_id']) &&
                            (
                                $_SESSION['role'] === 'admin' ||
                                $_SESSION['user_id'] == $wp['user_id'] ?? false
                            )
                        ): ?>
                            <div class="card-actions">
                                <a href="edit_wallpaper.php?id=<?= $wp['id'] ?>"
                                   class="btn btn-outline btn-sm">Edit</a>
                                <a href="delete_wallpaper.php?id=<?= $wp['id'] ?>"
                                   class="btn btn-danger btn-sm confirm-delete">Delete</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- "No results" notice — shown by jQuery live search -->
        <div id="no-results" class="empty-state" style="display:none;">
            <div class="icon">😕</div>
            <h3>No match found</h3>
            <p>Try a different search term.</p>
        </div>

    <?php endif; ?>
</div>


<footer class="site-footer">
    <p>&copy; <?= date('Y') ?> WallpaperCMS &mdash; <a href="index.php">Home</a></p>
</footer>

<!-- Requirement: jQuery from CDN -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Requirement: External JS file -->
<script src="js/script.js"></script>
</body>
</html>
