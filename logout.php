<?php
// ============================================================
// logout.php
// Requirement: Session Authentication — destroy session on logout
// Requirement: Registration and Login System (logout)
// ============================================================

// Requirement: Session Authentication — start session so we can destroy it
session_start();

// Remove all session variables
$_SESSION = [];

// Destroy the session cookie in the browser
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,           // set expiry in the past to delete it
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Destroy the session on the server
session_destroy();

// Redirect to login page with a confirmation message
header('Location: login.php?error=You+have+been+logged+out.');
exit;
