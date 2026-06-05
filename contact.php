<?php
session_start();

require_once __DIR__ . '/classes/Database.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name']    ?? '');
    $email   = trim($_POST['email']   ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($message) < 10) {
        $error = 'Message must be at least 10 characters.';
    } else {
        $pdo  = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare(
            'INSERT INTO contacts (name, email, message)
             VALUES (:name, :email, :message)'
        );
        $stmt->execute([
            ':name'    => htmlspecialchars($name),
            ':email'   => strtolower($email),
            ':message' => htmlspecialchars($message),
        ]);
        $success = 'Your message has been sent. Thank you!';
    }
}

$activePage = 'contact';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact — WallpaperCMS</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php require_once __DIR__ . '/includes/nav.php'; ?>

<div class="page-header">
    <h1>Contact Us</h1>
    <p>Have a question or suggestion? We'd love to hear from you.</p>
</div>

<div class="section">

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="contact-layout">

        <!-- Info panel -->
        <div class="contact-info">
            <h2>Get in Touch</h2>
            <p>
                WallpaperCMS is an open-source gallery project. Use this form to send
                feedback, report bugs, or just say hello.
            </p>
            <div class="contact-detail">
                <span>📧</span>
                <span>admin@wallpaper.cms</span>
            </div>
            <div class="contact-detail">
                <span>🌐</span>
                <span>localhost/WallpaperCMS</span>
            </div>
        </div>

        <!-- Form -->
        <div class="contact-form-card">
            <?php if (!$success): ?>
            <form action="contact.php" method="POST" novalidate>

                <div class="form-group">
                    <label for="name">Your Name *</label>
                    <input type="text" id="name" name="name" required maxlength="100"
                           placeholder="Full name"
                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" required maxlength="150"
                           placeholder="you@example.com"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="message">Message *</label>
                    <textarea id="message" name="message" required minlength="10"
                              placeholder="Write your message here..."><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Send Message</button>

            </form>
            <?php else: ?>
                <p style="color:var(--clr-muted); text-align:center; padding:2rem 0;">
                    Message sent! <a href="contact.php">Send another</a>
                </p>
            <?php endif; ?>
        </div>

    </div>
</div>

<footer class="site-footer">
    <p>&copy; <?= date('Y') ?> WallpaperCMS &mdash; <a href="index.php">Home</a></p>
</footer>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="js/script.js"></script>
</body>
</html>
