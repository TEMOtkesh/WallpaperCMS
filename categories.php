<?php
// ============================================================
// categories.php — Categories Page
// Requirement: At least 5 pages (Categories is page 3 of 5)
// Requirement: 1:N Category -> Wallpapers — displayed with counts
// Requirement: Responsive Design — CSS Grid (category-grid)
// Requirement: OOP — getCategoriesWithCount() method used
// ============================================================

session_start();

require_once __DIR__ . '/classes/Wallpaper.php';

// Requirement: OOP — use Wallpaper class method
$wallpaperObj = new Wallpaper();

// Requirement: 1:N relationship — each category shows wallpaper count
// Requirement: OOP Method — getCategoriesWithCount()
$categories = $wallpaperObj->getCategoriesWithCount();

// Map each category name to a display emoji icon
$icons = [
    'Nature'       => '🌿',
    'Space'        => '🌌',
    'Architecture' => '🏛',
    'Abstract'     => '🎨',
    'Minimalist'   => '◻',
];

$activePage = 'categories';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories — WallpaperCMS</title>

    <!-- Requirement: External CSS file -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php require_once __DIR__ . '/includes/nav.php'; ?>


<!-- PAGE HEADER -->
<div class="page-header">
    <h1>Browse by Category</h1>
    <p>Find wallpapers that match your style.</p>
</div>


<div class="section">

    <!-- ============================================================
         CATEGORY CARDS GRID
         Requirement: Responsive Design — CSS Grid (category-grid)
         Requirement: CSS3 Box Shadow — .category-card in style.css
         Requirement: CSS3 Transition — .category-card in style.css
         Requirement: CSS3 Transform (translateY) — .category-card:hover
         Requirement: jQuery fadeIn() — triggered in script.js
         Requirement: 1:N Category -> Wallpapers (count shown)
         ============================================================ -->
    <?php if (empty($categories)): ?>
        <div class="empty-state">
            <div class="icon">📂</div>
            <h3>No categories found</h3>
            <p>Categories will appear here once the database is seeded.</p>
        </div>

    <?php else: ?>

        <!-- Requirement: CSS Grid — see .category-grid in style.css -->
        <div class="category-grid">
            <?php foreach ($categories as $cat): ?>
                <!-- Requirement: jQuery fadeIn() — each .category-card in script.js -->
                <!-- Clicking a category takes user to filtered wallpapers list -->
                <a
                    href="wallpapers.php?category=<?= $cat['id'] ?>"
                    class="category-card"
                >
                    <div class="category-icon">
                        <?= $icons[$cat['name']] ?? '📁' ?>
                    </div>

                    <div class="category-name">
                        <?= htmlspecialchars($cat['name']) ?>
                    </div>

                    <!-- Requirement: 1:N relationship count -->
                    <span class="category-count">
                        <?= (int) $cat['wallpaper_count'] ?>
                        wallpaper<?= (int)$cat['wallpaper_count'] !== 1 ? 's' : '' ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>


    <!-- ============================================================
         SUMMARY TABLE — all categories at a glance
         Requirement: 1:N relationship — data clearly visible
         ============================================================ -->
    <?php if (!empty($categories)): ?>
        <h3 style="font-family:var(--font-display); margin: 3rem 0 1rem; font-size:1.4rem;">
            Category Overview
        </h3>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Wallpapers</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td>
                                <?= $icons[$cat['name']] ?? '📁' ?>
                                <?= htmlspecialchars($cat['name']) ?>
                            </td>
                            <td><?= (int) $cat['wallpaper_count'] ?></td>
                            <td>
                                <a
                                    href="wallpapers.php?category=<?= $cat['id'] ?>"
                                    class="btn btn-outline btn-sm"
                                >
                                    Browse →
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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
