<?php
session_start();

require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Wallpaper.php';

User::requireAdmin();

$userObj      = new User();
$wallpaperObj = new Wallpaper();

$error   = '';
$success = '';

// Path to the admin user log file (Requirement: write/view user info in a file)
$logFile = __DIR__ . '/../logs/users_export.txt';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'change_role') {
        $targetId = (int) ($_POST['user_id'] ?? 0);
        $newRole  = $_POST['role'] ?? '';

        if ($targetId === (int) $_SESSION['user_id']) {
            $error = 'You cannot change your own role.';
        } else {
            $userObj->updateRole($targetId, $newRole)
                ? $success = 'Role updated successfully.'
                : $error   = 'Failed to update role.';
        }

    } elseif ($_POST['action'] === 'delete_user') {
        $targetId = (int) ($_POST['user_id'] ?? 0);

        if ($targetId === (int) $_SESSION['user_id']) {
            $error = 'You cannot delete your own account.';
        } else {
            $userObj->deleteUser($targetId)
                ? $success = 'User deleted.'
                : $error   = 'Failed to delete user.';
        }

    // ============================================================
    // Requirement: Admin writes user information to a file
    // Uses PHP file I/O: file_put_contents() to write, file_get_contents() to read
    // ============================================================
    } elseif ($_POST['action'] === 'export_users') {
        $allUsers = $userObj->getAllUsers();

        $lines  = "WallpaperCMS — User Export\n";
        $lines .= "Generated: " . date('Y-m-d H:i:s') . " by " . $_SESSION['user_name'] . "\n";
        $lines .= str_repeat('=', 60) . "\n\n";

        foreach ($allUsers as $u) {
            $lines .= "ID      : " . $u['id']         . "\n";
            $lines .= "Name    : " . $u['name']        . "\n";
            $lines .= "Email   : " . $u['email']       . "\n";
            $lines .= "Role    : " . $u['role']        . "\n";
            $lines .= "Joined  : " . $u['created_at']  . "\n";
            $lines .= str_repeat('-', 40) . "\n";
        }

        // Requirement: write to file using file_put_contents()
        if (file_put_contents($logFile, $lines) !== false) {
            $success = 'User list exported to logs/users_export.txt successfully.';
        } else {
            $error = 'Could not write to file. Check that the logs/ folder is writable.';
        }
    }
}

$users      = $userObj->getAllUsers();
$wallpapers = $wallpaperObj->getAllWallpapers();

// Requirement: read user info FROM file using file_get_contents()
$logContent = file_exists($logFile) ? file_get_contents($logFile) : null;

$rootPath   = '../';
$activePage = 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel — WallpaperCMS</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php require_once __DIR__ . '/../includes/nav.php'; ?>

<div class="page-header">
    <h1>Admin Panel</h1>
    <p>Manage users, wallpapers, and export reports.</p>
</div>

<div class="section">

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>


    <!-- ====== USERS TABLE ====== -->
    <div class="flex-between" style="margin-bottom:1.25rem; flex-wrap:wrap; gap:1rem;">
        <h2 style="font-family:var(--font-display); font-size:1.5rem;">
            Users (<?= count($users) ?>)
        </h2>
        <!-- Requirement: write user info to file -->
        <form method="POST" style="display:inline;">
            <input type="hidden" name="action" value="export_users">
            <button type="submit" class="btn btn-outline btn-sm">
                📄 Export Users to File
            </button>
        </form>
    </div>

    <?php if (empty($users)): ?>
        <p style="color:var(--clr-muted);">No users found.</p>
    <?php else: ?>
        <div class="admin-table-wrap" style="margin-bottom:3rem;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= (int) $u['id'] ?></td>
                            <td><?= htmlspecialchars($u['name']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <span class="role-badge role-<?= $u['role'] ?>">
                                    <?= htmlspecialchars($u['role']) ?>
                                </span>
                            </td>
                            <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                            <td>
                                <?php if ((int)$u['id'] !== (int)$_SESSION['user_id']): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action"  value="change_role">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <input type="hidden" name="role"
                                               value="<?= $u['role'] === 'admin' ? 'user' : 'admin' ?>">
                                        <button type="submit" class="btn btn-outline btn-sm">
                                            Make <?= $u['role'] === 'admin' ? 'User' : 'Admin' ?>
                                        </button>
                                    </form>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action"  value="delete_user">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <button type="submit"
                                                class="btn btn-danger btn-sm confirm-delete">Delete</button>
                                    </form>
                                <?php else: ?>
                                    <span style="font-size:0.8rem;color:var(--clr-muted);">You</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>


    <!-- ====================================================
         Requirement: VIEW user information FROM a file
         file_get_contents() reads the exported log file
         ==================================================== -->
    <?php if ($logContent !== null): ?>
        <h2 style="font-family:var(--font-display); font-size:1.5rem; margin-bottom:1rem;">
            📄 User Export Log
        </h2>
        <div style="background:var(--clr-surface); border:1px solid var(--clr-border);
                    border-radius:var(--radius-lg); padding:1.5rem;
                    font-family:'Courier New',monospace; font-size:0.85rem;
                    color:var(--clr-muted); white-space:pre-wrap;
                    max-height:400px; overflow-y:auto; margin-bottom:3rem;">
            <?= htmlspecialchars($logContent) ?>
        </div>
    <?php endif; ?>


    <!-- ====== WALLPAPERS TABLE ====== -->
    <h2 style="font-family:var(--font-display); font-size:1.5rem; margin-bottom:1.25rem;">
        All Wallpapers (<?= count($wallpapers) ?>)
    </h2>

    <?php if (empty($wallpapers)): ?>
        <p style="color:var(--clr-muted);">No wallpapers uploaded yet.</p>
    <?php else: ?>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Uploader</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($wallpapers as $wp): ?>
                        <tr>
                            <td><?= (int) $wp['id'] ?></td>
                            <td><?= htmlspecialchars($wp['title']) ?></td>
                            <td><?= htmlspecialchars($wp['category']) ?></td>
                            <td><?= htmlspecialchars($wp['uploader']) ?></td>
                            <td><?= date('M j, Y', strtotime($wp['created_at'])) ?></td>
                            <td>
                                <a href="../edit_wallpaper.php?id=<?= $wp['id'] ?>"
                                   class="btn btn-outline btn-sm">Edit</a>
                                <a href="../delete_wallpaper.php?id=<?= $wp['id'] ?>"
                                   class="btn btn-danger btn-sm confirm-delete">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

</div>

<footer class="site-footer">
    <p>&copy; <?= date('Y') ?> WallpaperCMS &mdash; <a href="../index.php">Home</a></p>
</footer>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="../js/script.js"></script>
</body>
</html>
