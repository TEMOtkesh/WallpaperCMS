<?php
// ============================================================
// register.php
// Requirement: Registration Form
// Requirement: Registration and Login System
// Requirement: Session Authentication (starts session)
// Requirement: HTML Validation Attributes (required, minlength, email)
// ============================================================

// Requirement: Session Authentication — start session before any output
session_start();

require_once __DIR__ . '/classes/User.php';

// If already logged in, redirect to dashboard
if (User::isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error   = '';
$success = '';

// ============================================================
// Handle POST — process the registration form submission
// Requirement: Registration System
// Requirement: password_hash() used inside User::register()
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm']  ?? '');

    // Server-side validation (mirrors HTML attributes)
    if (empty($name) || empty($email) || empty($password) || empty($confirm)) {
        $error = 'All fields are required.';
    } elseif (strlen($name) < 2) {
        $error = 'Name must be at least 2 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Requirement: OOP — delegate to User class
        $user   = new User();
        $result = $user->register($name, $email, $password);

        if ($result === true) {
            $success = 'Account created! You can now <a href="login.php">log in</a>.';
        } else {
            $error = $result; // error message returned by register()
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — WallpaperCMS</title>

    <!-- Requirement: External CSS file -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-page">

<!-- ============================================================
     Requirement: Navigation Menu
     ============================================================ -->
<nav class="navbar">
    <a href="index.php" class="nav-brand">🖼 WallpaperCMS</a>
    <ul class="nav-links">
        <li><a href="index.php">Home</a></li>
        <li><a href="wallpapers.php">Wallpapers</a></li>
        <li><a href="categories.php">Categories</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="contact.php">Contact</a></li>
        <li><a href="login.php">Login</a></li>
    </ul>
</nav>

<!-- ============================================================
     Requirement: Registration Form
     Requirement: HTML Validation Attributes
     ============================================================ -->
<main class="auth-container">
    <div class="auth-card">
        <h1>Create Account</h1>
        <p class="auth-subtitle">Join the gallery community</p>

        <!-- Success / Error messages -->
        <!-- Requirement: User-Friendly Interface — feedback messages -->
        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <?php if (!$success): // Hide form after successful registration ?>
        <form action="register.php" method="POST" novalidate>

            <!-- Name field -->
            <!-- Requirement: HTML Validation — required, minlength -->
            <div class="form-group">
                <label for="name">Full Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    placeholder="Your name"
                    required
                    minlength="2"
                    maxlength="100"
                    value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                >
            </div>

            <!-- Email field -->
            <!-- Requirement: HTML Validation — email, required -->
            <div class="form-group">
                <label for="email">Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="you@example.com"
                    required
                    maxlength="150"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                >
            </div>

            <!-- Password field -->
            <!-- Requirement: HTML Validation — required, minlength -->
            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Minimum 6 characters"
                    required
                    minlength="6"
                >
            </div>

            <!-- Confirm password -->
            <!-- Requirement: HTML Validation — required -->
            <div class="form-group">
                <label for="confirm">Confirm Password</label>
                <input
                    type="password"
                    id="confirm"
                    name="confirm"
                    placeholder="Repeat your password"
                    required
                    minlength="6"
                >
            </div>

            <button type="submit" class="btn btn-primary btn-full">
                Create Account
            </button>
        </form>
        <?php endif; ?>

        <p class="auth-switch">
            Already have an account? <a href="login.php">Log in</a>
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
