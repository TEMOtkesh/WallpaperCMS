<?php
// ============================================================
// about.php — About Page
// Requirement: At least 5 pages (About is page 4 of 5)
// ============================================================

session_start();

$activePage = 'about';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About — WallpaperCMS</title>

    <!-- Requirement: External CSS file -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php require_once __DIR__ . '/includes/nav.php'; ?>


<!-- ============================================================
     HERO — smaller version for inner pages
     Requirement: CSS3 Gradient on hero background
     Requirement: CSS3 Animation fadeIn
     ============================================================ -->
<section class="hero" style="min-height:38vh; padding:3rem 2rem;">
    <span class="hero-badge">✦ The Project</span>
    <h1>About <span>WallpaperCMS</span></h1>
    <p>An open-source desktop wallpaper gallery built for a university web programming course.</p>
</section>


<div class="section">

    <!-- ============================================================
         FEATURE GRID
         Requirement: Responsive Design — CSS Grid (about-grid)
         Requirement: CSS3 Box Shadow — .about-feature in style.css
         Requirement: CSS3 Transition / Transform — .about-feature:hover
         ============================================================ -->
    <div class="about-grid">

        <!-- Feature 1: What is it? -->
        <div class="about-feature">
            <div class="icon">🖼</div>
            <h3>What is WallpaperCMS?</h3>
            <p>
                WallpaperCMS is a full-stack content management system that lets users
                browse, upload, edit, and delete desktop wallpapers. It is built using
                plain PHP 8, MySQL, HTML5, CSS3, and jQuery — no frameworks, no magic.
            </p>
            <p style="margin-top:0.75rem;">
                Wallpapers are organized by categories and tagged with keywords,
                making it easy to find exactly what you are looking for.
            </p>
        </div>

        <!-- Feature 2: User roles -->
        <!-- Requirement: User Roles explanation -->
        <div class="about-feature">
            <div class="icon">👥</div>
            <h3>Admin &amp; User Roles</h3>
            <p>
                WallpaperCMS has two roles:
            </p>
            <ul style="margin:0.75rem 0 0 1.25rem; color:var(--clr-muted); line-height:2;">
                <li>
                    <strong style="color:var(--clr-accent);">Admin</strong> —
                    Can manage all wallpapers and users, change roles, and delete any content.
                </li>
                <li>
                    <strong style="color:var(--clr-text);">User</strong> —
                    Can upload wallpapers, and edit or delete their own uploads only.
                </li>
            </ul>
            <p style="margin-top:0.75rem;">
                Role-based access is enforced on every protected page using PHP sessions.
            </p>
        </div>

        <!-- Feature 3: Upload functionality -->
        <!-- Requirement: File Upload explanation -->
        <div class="about-feature">
            <div class="icon">📤</div>
            <h3>Upload Functionality</h3>
            <p>
                Registered users can upload wallpaper images (JPEG, PNG, GIF, WebP)
                up to 10 MB. Each upload requires a title and category, and supports
                optional tags for better discoverability.
            </p>
            <p style="margin-top:0.75rem;">
                Images are stored in the <code style="color:var(--clr-accent);">uploads/</code>
                folder and their paths are saved in the database.
                Users can edit or delete their own uploads at any time.
            </p>
        </div>

        <!-- Feature 4: Tech stack -->
        <div class="about-feature">
            <div class="icon">⚙️</div>
            <h3>Technology Stack</h3>
            <ul style="margin:0.75rem 0 0 1.25rem; color:var(--clr-muted); line-height:2.1;">
                <li>PHP 8+ — OOP with PDO &amp; prepared statements</li>
                <li>MySQL — 6 tables, 1:N &amp; N:N relationships</li>
                <li>HTML5 — semantic markup &amp; form validation</li>
                <li>CSS3 — Flexbox, Grid, animations, transitions</li>
                <li>jQuery — slideToggle, fadeIn, live color picker</li>
                <li>Sessions &amp; Cookies — for authentication</li>
            </ul>
        </div>

        <!-- Feature 5: Security -->
        <div class="about-feature">
            <div class="icon">🔒</div>
            <h3>Security</h3>
            <p>
                Passwords are stored using <code style="color:var(--clr-accent);">password_hash()</code>
                with the BCRYPT algorithm and verified with
                <code style="color:var(--clr-accent);">password_verify()</code>.
            </p>
            <p style="margin-top:0.75rem;">
                All database queries use PDO prepared statements to prevent SQL injection.
                User input is sanitized with <code style="color:var(--clr-accent);">htmlspecialchars()</code>
                before display.
            </p>
        </div>

        <!-- Feature 6: Database design -->
        <!-- Requirement: Database / Relationships explanation -->
        <div class="about-feature">
            <div class="icon">🗄</div>
            <h3>Database Design</h3>
            <p>The database contains 6 tables:</p>
            <ul style="margin:0.75rem 0 0 1.25rem; color:var(--clr-muted); line-height:2.1;">
                <li><strong style="color:var(--clr-text);">users</strong> — accounts &amp; roles</li>
                <li><strong style="color:var(--clr-text);">categories</strong> — wallpaper groups</li>
                <li><strong style="color:var(--clr-text);">wallpapers</strong> — core content</li>
                <li><strong style="color:var(--clr-text);">tags</strong> — keyword labels</li>
                <li><strong style="color:var(--clr-text);">wallpaper_tags</strong> — N:N junction</li>
                <li><strong style="color:var(--clr-text);">contacts</strong> — form submissions</li>
            </ul>
        </div>

    </div>

    <!-- CTA -->
    <div class="text-center mt-3" style="margin-top:3rem;">
        <a href="wallpapers.php" class="btn btn-primary">Browse the Gallery</a>
        &nbsp;
        <a href="contact.php" class="btn btn-outline">Get in Touch</a>
    </div>
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
