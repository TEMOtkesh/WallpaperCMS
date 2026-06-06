<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/Database.php';

echo "<h2>1. Path Check</h2>";
echo "Project root: <b>" . __DIR__ . "</b><br>";
$uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
echo "Upload dir: <b>$uploadDir</b><br>";
echo "Upload dir exists: <b>" . (is_dir($uploadDir) ? 'YES' : 'NO') . "</b><br>";
echo "Upload dir writable: <b>" . (is_writable($uploadDir) ? 'YES' : 'NO') . "</b><br>";

echo "<h2>2. Database Connection</h2>";
try {
    $pdo = Database::getInstance()->getConnection();
    echo "Connected: <b style='color:green'>YES</b><br>";
} catch (Exception $e) {
    echo "Connected: <b style='color:red'>NO — " . $e->getMessage() . "</b><br>";
    exit;
}

echo "<h2>3. Table Row Counts</h2>";
$tables = ['users','categories','wallpapers','tags','wallpaper_tags','contacts'];
foreach ($tables as $t) {
    try {
        $count = $pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn();
        echo "$t: <b>$count rows</b><br>";
    } catch (Exception $e) {
        echo "$t: <b style='color:red'>ERROR — " . $e->getMessage() . "</b><br>";
    }
}

echo "<h2>4. Categories (needed for upload)</h2>";
$cats = $pdo->query("SELECT id, name FROM categories")->fetchAll();
if (empty($cats)) {
    echo "<b style='color:red'>NO CATEGORIES FOUND — this will break uploads!</b><br>";
} else {
    foreach ($cats as $c) echo "ID {$c['id']}: {$c['name']}<br>";
}

echo "<h2>5. Users</h2>";
$users = $pdo->query("SELECT id, name, email, role FROM users")->fetchAll();
if (empty($users)) {
    echo "<b style='color:red'>NO USERS FOUND — admin account missing!</b><br>";
} else {
    foreach ($users as $u) echo "ID {$u['id']}: {$u['name']} ({$u['email']}) — {$u['role']}<br>";
}

echo "<h2>6. Wallpapers in DB</h2>";
$wps = $pdo->query("SELECT id, title, image_path FROM wallpapers LIMIT 10")->fetchAll();
if (empty($wps)) {
    echo "<b style='color:red'>No wallpapers in database.</b><br>";
} else {
    foreach ($wps as $w) {
        $abs = __DIR__ . '/' . $w['image_path'];
        echo "ID {$w['id']}: {$w['title']}<br>";
        echo "&nbsp;&nbsp;Path: {$w['image_path']}<br>";
        echo "&nbsp;&nbsp;File exists: " . (file_exists($abs) ? '<b style=color:green>YES</b>' : '<b style=color:red>NO</b>') . "<br>";
    }
}

echo "<h2>7. Files in uploads/</h2>";
$files = glob($uploadDir . '*');
if (empty($files)) {
    echo "No files found.<br>";
} else {
    foreach ($files as $f) echo basename($f) . "<br>";
}

echo "<h2>8. Test DB Insert</h2>";
try {
    $stmt = $pdo->prepare("INSERT INTO wallpapers (title, description, image_path, user_id, category_id) VALUES ('TEST','TEST','uploads/test.jpg',1,1)");
    $stmt->execute();
    $id = $pdo->lastInsertId();
    echo "<b style='color:green'>Test insert SUCCEEDED — ID $id</b><br>";
    $pdo->exec("DELETE FROM wallpapers WHERE id = $id");
    echo "Test row cleaned up.<br>";
} catch (Exception $e) {
    echo "<b style='color:red'>Test insert FAILED: " . $e->getMessage() . "</b><br>";
}
?>
