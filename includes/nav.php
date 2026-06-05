<?php
// ============================================================
// includes/nav.php
// Requirement: Navigation Menu — reusable across all pages
// Requirement: Session Authentication — shows different links
//              depending on whether the user is logged in.
// Requirement: User Roles — admin sees Admin Panel link.
// ============================================================
// Usage: require_once __DIR__ . '/../includes/nav.php';
//        Set $activePage = 'home' | 'wallpapers' | 'categories'
//        | 'about' | 'contact' | 'dashboard' before including.
?>
<nav class="navbar">
    <a href="<?= $rootPath ?? '' ?>index.php" class="nav-brand">🖼 WallpaperCMS</a>

    <!-- Hamburger — visible only on mobile (jQuery toggles .open) -->
    <button class="nav-toggle" id="nav-toggle" aria-label="Toggle navigation">
        <span></span><span></span><span></span>
    </button>

    <!-- Requirement: Navigation Menu -->
    <ul class="nav-links">
        <li>
            <a href="<?= $rootPath ?? '' ?>index.php"
               class="<?= ($activePage ?? '') === 'home'        ? 'active' : '' ?>">
                Home
            </a>
        </li>
        <li>
            <a href="<?= $rootPath ?? '' ?>wallpapers.php"
               class="<?= ($activePage ?? '') === 'wallpapers'  ? 'active' : '' ?>">
                Wallpapers
            </a>
        </li>
        <li>
            <a href="<?= $rootPath ?? '' ?>categories.php"
               class="<?= ($activePage ?? '') === 'categories'  ? 'active' : '' ?>">
                Categories
            </a>
        </li>
        <li>
            <a href="<?= $rootPath ?? '' ?>about.php"
               class="<?= ($activePage ?? '') === 'about'       ? 'active' : '' ?>">
                About
            </a>
        </li>
        <li>
            <a href="<?= $rootPath ?? '' ?>contact.php"
               class="<?= ($activePage ?? '') === 'contact'     ? 'active' : '' ?>">
                Contact
            </a>
        </li>

        <?php if (isset($_SESSION['user_id'])): ?>
            <!-- Requirement: Session Authentication — logged-in links -->
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <!-- Requirement: User Roles — admin-only link -->
                <li>
                    <a href="<?= $rootPath ?? '' ?>admin/users.php"
                       class="<?= ($activePage ?? '') === 'admin' ? 'active' : '' ?>">
                        Admin Panel
                    </a>
                </li>
            <?php endif; ?>
            <li>
                <a href="<?= $rootPath ?? '' ?>dashboard.php"
                   class="<?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>">
                    Dashboard
                </a>
            </li>
            <li>
                <a href="<?= $rootPath ?? '' ?>logout.php" class="btn-nav">
                    Logout
                </a>
            </li>
        <?php else: ?>
            <!-- Guest links -->
            <li><a href="<?= $rootPath ?? '' ?>login.php">Login</a></li>
            <li><a href="<?= $rootPath ?? '' ?>register.php" class="btn-nav">Register</a></li>
        <?php endif; ?>
    </ul>
</nav>
