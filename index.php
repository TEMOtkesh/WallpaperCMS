<?php
// ============================================================
// index.php — Home Page
// Requirement: At least 5 pages (Home is page 1 of 5)
// Requirement: Hero section with gradient and animation
// Requirement: Featured wallpapers section
// Requirement: Color Picker / HEX / RGBA / HSL demonstration
// Requirement: Session Authentication (nav adapts to role)
// ============================================================

session_start();

require_once __DIR__ . '/classes/Wallpaper.php';

// Fetch the 6 most recent wallpapers for the Featured section
$wallpaperObj = new Wallpaper();
$featured     = array_slice($wallpaperObj->getAllWallpapers(), 0, 6);

$activePage = 'home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WallpaperCMS — Desktop Wallpaper Gallery</title>

    <!-- Requirement: External CSS file -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php require_once __DIR__ . '/includes/nav.php'; ?>

<!-- ============================================================
     HERO SECTION
     Requirement: CSS3 Gradient (linear-gradient) — see style.css .hero
     Requirement: CSS3 Animation (fadeIn) — see style.css .hero
     Requirement: CSS background property — see style.css .hero
     ============================================================ -->
<section class="hero">
    <span class="hero-badge">✦ Open-Source Gallery CMS</span>

    <h1>
        Beautiful Walls,<br>
        <span>Curated for You</span>
    </h1>

    <p>
        Discover and share stunning desktop wallpapers.
        Browse by category, upload your own, and build your perfect collection.
    </p>

    <div class="hero-actions">
        <a href="wallpapers.php" class="btn btn-primary">Browse Wallpapers</a>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="register.php" class="btn btn-outline">Join Free</a>
        <?php else: ?>
            <a href="upload.php" class="btn btn-outline">Upload Wallpaper</a>
        <?php endif; ?>
    </div>
</section>


<!-- ============================================================
     FEATURED WALLPAPERS
     Requirement: Wallpaper cards with box-shadow, transition, transform
     Requirement: Responsive Design — CSS Grid (cards-grid)
     Requirement: jQuery fadeIn() — triggered in script.js
     ============================================================ -->
<div class="section">
    <div class="section-header">
        <span class="section-label">Gallery</span>
        <h2>Featured Wallpapers</h2>
        <p>Hand-picked from our community of creators</p>
    </div>

    <?php if (empty($featured)): ?>
        <!-- Empty state when no wallpapers exist yet -->
        <div class="empty-state">
            <div class="icon">🖼</div>
            <h3>No wallpapers yet</h3>
            <p>Be the first to upload a stunning wallpaper.</p>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="upload.php" class="btn btn-primary">Upload Now</a>
            <?php else: ?>
                <a href="register.php" class="btn btn-primary">Get Started</a>
            <?php endif; ?>
        </div>
    <?php else: ?>

        <!-- Requirement: CSS Grid layout — see .cards-grid in style.css -->
        <div class="cards-grid">
            <?php foreach ($featured as $wp): ?>
                <!-- Requirement: wallpaper-card — box-shadow, transform, transition applied via CSS -->
                <!-- Requirement: jQuery fadeIn() — each .wallpaper-card fades in via script.js -->
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
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-3">
            <a href="wallpapers.php" class="btn btn-outline">View All Wallpapers →</a>
        </div>

    <?php endif; ?>
</div>


<!-- ============================================================
     CSS COLOR DEMONSTRATION SECTION
     Requirement: input type="color" (Color Picker)
     Requirement: HEX color example   — #6A11CB
     Requirement: RGBA color example  — rgba(106,17,203,1)
     Requirement: HSL color example   — hsl(271,85%,43%)
     ============================================================ -->
<section class="color-demo-section">
    <div class="color-demo-inner">
        <div class="section-header" style="text-align:left; margin-bottom:1.5rem;">
            <span class="section-label">CSS Demonstration</span>
            <h2>CSS Color Formats</h2>
            <p>Three ways to write the same color in CSS — HEX, RGBA, and HSL.</p>
        </div>

        <!-- Static swatches showing the three color format types -->
        <div class="color-swatches">

            <!-- Requirement: HEX color -->
            <div class="swatch swatch-hex">
                <span class="swatch-label">HEX</span>
                <!-- Requirement: CSS3 background property used on .swatch-hex in style.css -->
                <span class="swatch-value">#6A11CB</span>
                <small style="color:rgba(255,255,255,0.55); font-size:0.78rem;">
                    Hexadecimal — 6 digits, 3 channels
                </small>
            </div>

            <!-- Requirement: RGBA color -->
            <div class="swatch swatch-rgba">
                <span class="swatch-label">RGBA</span>
                <!-- Requirement: CSS3 background rgba() on .swatch-rgba in style.css -->
                <span class="swatch-value">rgba(106, 17, 203, 0.7)</span>
                <small style="color:rgba(255,255,255,0.55); font-size:0.78rem;">
                    Red, Green, Blue + Alpha transparency
                </small>
            </div>

            <!-- Requirement: HSL color -->
            <div class="swatch swatch-hsl">
                <span class="swatch-label">HSL</span>
                <!-- Requirement: CSS3 background hsl() on .swatch-hsl in style.css -->
                <span class="swatch-value">hsl(271, 85%, 43%)</span>
                <small style="color:rgba(255,255,255,0.55); font-size:0.78rem;">
                    Hue, Saturation, Lightness
                </small>
            </div>
        </div>

        <hr class="divider">

        <!-- Live color picker -->
        <!-- Requirement: input type="color" -->
        <p style="color:var(--clr-muted); margin-bottom:1rem; font-size:0.9rem;">
            Pick any color below — see all three formats update live via jQuery:
        </p>

        <div class="color-picker-row">
            <label for="theme-color-picker">Pick a Color:</label>

            <!-- Requirement: input type="color" (Color Picker) -->
            <input type="color" id="theme-color-picker" value="#6a11cb">

            <!-- Live preview box — background updated by jQuery -->
            <div id="live-preview" style="background:#6a11cb;"></div>

            <!-- Outputs — updated live by jQuery in script.js -->
            <div>
                <!-- Requirement: HEX output -->
                <div style="margin-bottom:0.4rem;">
                    <span style="font-size:0.75rem;color:var(--clr-muted);text-transform:uppercase;letter-spacing:0.1em;">HEX: </span>
                    <span id="hex-output">#6A11CB</span>
                </div>
                <!-- Requirement: RGBA output -->
                <div style="margin-bottom:0.4rem;">
                    <span style="font-size:0.75rem;color:var(--clr-muted);text-transform:uppercase;letter-spacing:0.1em;">RGBA: </span>
                    <span id="rgba-output">rgba(106, 17, 203, 1)</span>
                </div>
                <!-- Requirement: HSL output -->
                <div>
                    <span style="font-size:0.75rem;color:var(--clr-muted);text-transform:uppercase;letter-spacing:0.1em;">HSL: </span>
                    <span id="hsl-output">hsl(271, 85%, 43%)</span>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ============================================================
     CALL TO ACTION STRIP
     Requirement: Call-to-action button on home page
     ============================================================ -->
<section class="section" style="text-align:center; padding-top:4rem; padding-bottom:4rem;">
    <span class="section-label">Join the Community</span>
    <h2 style="font-family:var(--font-display); font-size:clamp(1.8rem,4vw,2.8rem); margin:0.5rem 0 1rem;">
        Ready to share your art?
    </h2>
    <p style="color:var(--clr-muted); max-width:480px; margin:0 auto 2rem;">
        Create a free account and start uploading your best desktop wallpapers today.
    </p>
    <?php if (!isset($_SESSION['user_id'])): ?>
        <a href="register.php" class="btn btn-primary" style="font-size:1rem; padding:0.9rem 2.5rem;">
            Get Started Free
        </a>
    <?php else: ?>
        <a href="upload.php" class="btn btn-primary" style="font-size:1rem; padding:0.9rem 2.5rem;">
            Upload a Wallpaper
        </a>
    <?php endif; ?>
</section>


<footer class="site-footer">
    <p>
        &copy; <?= date('Y') ?> WallpaperCMS &mdash;
        Built with PHP, MySQL, jQuery &mdash;
        <a href="about.php">About</a> &middot; <a href="contact.php">Contact</a>
    </p>
</footer>

<!-- Requirement: jQuery from CDN -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Requirement: External JS file -->
<script src="js/script.js"></script>
</body>
</html>
