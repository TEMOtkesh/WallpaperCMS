<?php
session_start();

require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/classes/Wallpaper.php';

User::requireLogin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id < 1) {
    header('Location: dashboard.php?error=Invalid+wallpaper.');
    exit;
}

$wallpaperObj = new Wallpaper();
$wallpaper    = $wallpaperObj->getWallpaperById($id);

if (!$wallpaper) {
    header('Location: dashboard.php?error=Wallpaper+not+found.');
    exit;
}

// Only the uploader or an admin may delete
if ($_SESSION['role'] !== 'admin' && (int)$wallpaper['user_id'] !== (int)$_SESSION['user_id']) {
    header('Location: dashboard.php?error=Access+denied.');
    exit;
}

$wallpaperObj->deleteWallpaper($id);

header('Location: dashboard.php?success=Wallpaper+deleted+successfully.');
exit;
