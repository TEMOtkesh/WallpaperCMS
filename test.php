<?php
// Diagnostic page - delete this file after fixing
$uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;

echo "<h2>Path Check</h2>";
echo "<b>Project root (__DIR__):</b> " . __DIR__ . "<br>";
echo "<b>Upload folder path:</b> " . $uploadDir . "<br>";
echo "<b>Upload folder exists:</b> " . (is_dir($uploadDir) ? 'YES' : 'NO') . "<br>";
echo "<b>Upload folder writable:</b> " . (is_writable($uploadDir) ? 'YES' : 'NO') . "<br>";

echo "<h2>Files in uploads/</h2>";
$files = glob($uploadDir . '*');
if (empty($files)) {
    echo "No files found in uploads folder.<br>";
} else {
    foreach ($files as $f) {
        echo $f . "<br>";
    }
}

echo "<h2>Database Image Paths</h2>";
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/Database.php';
$pdo = Database::getInstance()->getConnection();
$rows = $pdo->query('SELECT id, title, image_path FROM wallpapers LIMIT 10')->fetchAll();
if (empty($rows)) {
    echo "No wallpapers in database.<br>";
} else {
    foreach ($rows as $r) {
        $abs = __DIR__ . '/' . $r['image_path'];
        echo "<b>ID {$r['id']}:</b> {$r['title']}<br>";
        echo "&nbsp;&nbsp;DB path: {$r['image_path']}<br>";
        echo "&nbsp;&nbsp;Absolute path: {$abs}<br>";
        echo "&nbsp;&nbsp;file_exists: " . (file_exists($abs) ? '<span style=color:green>YES</span>' : '<span style=color:red>NO</span>') . "<br>";
        echo "&nbsp;&nbsp;Image URL: <a href='{$r['image_path']}'>{$r['image_path']}</a><br><br>";
    }
}
?>
