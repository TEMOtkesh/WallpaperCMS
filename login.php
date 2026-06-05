<?php
// ============================================================
// login.php
// Requirement: Login Form
// Requirement: Registration and Login System
// Requirement: Session Authentication
// Requirement: password_verify() used inside User::login()
// ============================================================

// Requirement: Session Authentication — must start before any output
session_start();

require_once __DIR__ . '/classes/User.php';

// If already logged in, go straight to dashboard
if (User::isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

// Carry forward any message from a redirect (e.g. from requireLogin())
if (!empty($_GET['error'])) {
    $error = htmlspecialchars($_GET['error']);
}

// ============================================================
// Handle POST — process the login form submission
// Requirement: Login System
// Requirement: password_verify() used inside User::login()
// Requirement: Session Authentication — $_SESSION set in User::login()
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    // Basic server-side validation
    if (empty($email) || empty($password)) {
        $error = 'Please enter your email and password.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Requirement: OOP — delegate to User class
        $user   = new User();
        $result = $user->login($email, $password);

        if ($result === true) {
            // Requirement: Session Authentication
            // $_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['role']
            // are set inside User::login() — redirect based on role

            if ($_SESSION['role'] === 'admin') {
                header('Location: admin/users.php');
            } else {
                header('Location: dashboard.php');
            }
            exit;
        } else {
            $error = $result; // error message from User::login()
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — WallpaperCMS</title>

    <!-- Requirement: External CSS file -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-page">

<!-- Requirement: Navigation Menu -->
<nav class="navbar">
    <a href="index.php" class="nav-brand">🖼 WallpaperCMS</a>
    <ul class="nav-links">
        <li><a href="index.php">Home</a></li>
        <li><a href="wallpapers.php">Wallpapers</a></li>
        <li><a href="categories.php">Categories</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="contact.php">Contact</a></li>
        <li><a href="register.php">Register</a></li>
    </ul>
</nav>

<!-- ============================================================
     Requirement: Login Form
     Requirement: HTML Validation Attributes (required, email)
     ============================================================ -->
<main class="auth-container">
    <div class="auth-card">
        <h1>Welcome Back</h1>
        <p class="auth-subtitle">Sign in to your account</p>

        <!-- Requirement: User-Friendly Interface — error feedback -->
        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST" novalidate>

            <!-- Email field -->
            <!-- Requirement: HTML Validation — type="email", required -->
            <div class="form-group">
                <label for="email">Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="you@example.com"
                    required
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                >
            </div>

            <!-- Password field -->
            <!-- Requirement: HTML Validation — required -->
            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Your password"
                    required
                >
            </div>

            <button type="submit" class="btn btn-primary btn-full">
                Log In
            </button>
        </form>

        <!-- Demo credentials hint — helpful during a project defense -->
        <div class="demo-hint">
            <strong>Demo admin:</strong> admin@wallpaper.cms / admin123
        </div>

        <p class="auth-switch">
            No account yet? <a href="register.php">Register here</a>
        </p>
    </div>
</main>

<footer class="site-footer">
    <p>&copy; <?= date('Y') ?> WallpaperCMS</p>
</footer>

<!-- Requirement: jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="js/script.js"></script>
</body>
</html>
