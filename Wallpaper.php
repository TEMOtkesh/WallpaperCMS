<?php
// ============================================================
// classes/Wallpaper.php
// Requirement: OOP Class
// Requirement: CRUD Operations (Create, Read, Update, Delete)
// Requirement: File Upload (image_path stored in DB)
// Requirement: N:N Relationship (wallpapers <-> tags)
// Requirement: Prepared Statements
// ============================================================

require_once __DIR__ . '/../classes/Database.php';

// Requirement: OOP Class
class Wallpaper
{
    // Shared PDO connection
    private PDO $pdo;

    // Directory where uploaded images are saved (relative to project root)
    private string $uploadDir = 'uploads/';

    // Allowed MIME types for uploaded images
    private array $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    // Maximum file size: 10 MB
    private int $maxSize = 10485760;

    // ------------------------------------------------------------
    // Constructor
    // Requirement: OOP Constructor
    // ------------------------------------------------------------
    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    // ============================================================
    // createWallpaper()
    // Handles file upload and inserts a new wallpaper record.
    // Requirement: CRUD — Create
    // Requirement: File Upload
    // Requirement: N:N tags via wallpaper_tags
    // Requirement: Prepared Statements
    //
    // $data  = ['title', 'description', 'category_id', 'user_id']
    // $file  = $_FILES['image'] array
    // $tagIds = array of tag IDs (integers)
    // Returns: true on success, string error message on failure
    // ============================================================
    public function createWallpaper(array $data, array $file, array $tagIds = []): bool|string
    {
        // --- Validate & move uploaded file ---
        $uploadResult = $this->handleUpload($file);
        if (is_string($uploadResult)) {
            return $uploadResult; // return the error message
        }
        $imagePath = $uploadResult; // relative path e.g. uploads/abc123.jpg

        // --- Insert wallpaper row ---
        // Requirement: Prepared Statements
        $stmt = $this->pdo->prepare(
            'INSERT INTO wallpapers (title, description, image_path, user_id, category_id)
             VALUES (:title, :description, :image_path, :user_id, :category_id)'
        );
        $stmt->execute([
            ':title'       => htmlspecialchars(trim($data['title'])),
            ':description' => htmlspecialchars(trim($data['description'] ?? '')),
            ':image_path'  => $imagePath,
            ':user_id'     => (int) $data['user_id'],
            ':category_id' => (int) $data['category_id'],
        ]);

        $wallpaperId = (int) Database::getInstance()->lastInsertId();

        // --- Attach tags (N:N relationship) ---
        // Requirement: N:N Relationship
        if (!empty($tagIds)) {
            $this->syncTags($wallpaperId, $tagIds);
        }

        return true;
    }

    // ============================================================
    // getAllWallpapers()
    // Returns all wallpapers with uploader name and category name.
    // Requirement: CRUD — Read
    // Requirement: JOIN across 1:N relationships
    // ============================================================
    public function getAllWallpapers(): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT w.id, w.title, w.description, w.image_path, w.created_at,
                    u.name  AS uploader,
                    c.name  AS category
             FROM   wallpapers w
             JOIN   users      u ON u.id = w.user_id
             JOIN   categories c ON c.id = w.category_id
             ORDER  BY w.created_at DESC'
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ============================================================
    // getWallpapersByUser()
    // Returns wallpapers uploaded by a specific user (dashboard).
    // Requirement: CRUD — Read (filtered)
    // Requirement: Prepared Statements
    // ============================================================
    public function getWallpapersByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT w.id, w.title, w.description, w.image_path, w.created_at,
                    c.name AS category
             FROM   wallpapers w
             JOIN   categories c ON c.id = w.category_id
             WHERE  w.user_id = :user_id
             ORDER  BY w.created_at DESC'
        );
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    // ============================================================
    // getWallpaperById()
    // Returns a single wallpaper record with related data.
    // Requirement: CRUD — Read (single)
    // Requirement: Prepared Statements
    // ============================================================
    public function getWallpaperById(int $id): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT w.id, w.title, w.description, w.image_path,
                    w.user_id, w.category_id, w.created_at,
                    u.name  AS uploader,
                    c.name  AS category
             FROM   wallpapers w
             JOIN   users      u ON u.id = w.user_id
             JOIN   categories c ON c.id = w.category_id
             WHERE  w.id = :id
             LIMIT  1'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // ============================================================
    // getWallpapersByCategory()
    // Returns wallpapers filtered by category ID.
    // Requirement: CRUD — Read (filtered)
    // Requirement: 1:N Category -> Wallpapers
    // ============================================================
    public function getWallpapersByCategory(int $categoryId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT w.id, w.title, w.description, w.image_path, w.created_at,
                    u.name  AS uploader,
                    c.name  AS category
             FROM   wallpapers w
             JOIN   users      u ON u.id = w.user_id
             JOIN   categories c ON c.id = w.category_id
             WHERE  w.category_id = :cat_id
             ORDER  BY w.created_at DESC'
        );
        $stmt->execute([':cat_id' => $categoryId]);
        return $stmt->fetchAll();
    }

    // ============================================================
    // updateWallpaper()
    // Updates title, description, and category of a wallpaper.
    // Optionally replaces the image if a new file is uploaded.
    // Requirement: CRUD — Update
    // Requirement: Prepared Statements
    // ============================================================
    public function updateWallpaper(int $id, array $data, array $file = [], array $tagIds = []): bool|string
    {
        // Fetch existing record to get current image path
        $existing = $this->getWallpaperById($id);
        if (!$existing) {
            return 'Wallpaper not found.';
        }

        $imagePath = $existing['image_path']; // keep old image by default

        // Replace image only if a new file was actually uploaded
        if (!empty($file['name'])) {
            $uploadResult = $this->handleUpload($file);
            if (is_string($uploadResult)) {
                return $uploadResult;
            }
            // Delete the old image file from disk
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
            $imagePath = $uploadResult;
        }

        // Requirement: Prepared Statements
        $stmt = $this->pdo->prepare(
            'UPDATE wallpapers
             SET title = :title,
                 description = :description,
                 image_path = :image_path,
                 category_id = :category_id
             WHERE id = :id'
        );
        $stmt->execute([
            ':title'       => htmlspecialchars(trim($data['title'])),
            ':description' => htmlspecialchars(trim($data['description'] ?? '')),
            ':image_path'  => $imagePath,
            ':category_id' => (int) $data['category_id'],
            ':id'          => $id,
        ]);

        // Re-sync tags
        // Requirement: N:N Relationship
        $this->syncTags($id, $tagIds);

        return true;
    }

    // ============================================================
    // deleteWallpaper()
    // Deletes a wallpaper record and its image file from disk.
    // Requirement: CRUD — Delete
    // Requirement: Prepared Statements
    // ============================================================
    public function deleteWallpaper(int $id): bool
    {
        // Get image path before deleting the row
        $wallpaper = $this->getWallpaperById($id);
        if (!$wallpaper) {
            return false;
        }

        // Remove the image file from the uploads folder
        if (!empty($wallpaper['image_path']) && file_exists($wallpaper['image_path'])) {
            unlink($wallpaper['image_path']);
        }

        // Delete DB row — wallpaper_tags cascade automatically
        // Requirement: Prepared Statements
        $stmt = $this->pdo->prepare(
            'DELETE FROM wallpapers WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    // ============================================================
    // getTagsForWallpaper()
    // Fetches all tags attached to a given wallpaper.
    // Requirement: N:N Relationship read
    // ============================================================
    public function getTagsForWallpaper(int $wallpaperId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT t.id, t.name
             FROM   tags t
             JOIN   wallpaper_tags wt ON wt.tag_id = t.id
             WHERE  wt.wallpaper_id = :wid'
        );
        $stmt->execute([':wid' => $wallpaperId]);
        return $stmt->fetchAll();
    }

    // ============================================================
    // getAllCategories()
    // Returns all categories for use in dropdowns.
    // Requirement: 1:N Category -> Wallpapers
    // ============================================================
    public function getAllCategories(): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name FROM categories ORDER BY name ASC'
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ============================================================
    // getCategoriesWithCount()
    // Returns each category with how many wallpapers it contains.
    // Used on the Categories page.
    // Requirement: 1:N Relationship read with aggregation
    // ============================================================
    public function getCategoriesWithCount(): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT c.id, c.name, COUNT(w.id) AS wallpaper_count
             FROM   categories c
             LEFT   JOIN wallpapers w ON w.category_id = c.id
             GROUP  BY c.id, c.name
             ORDER  BY c.name ASC'
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ============================================================
    // getAllTags()
    // Returns all tags for use in checkboxes on the upload form.
    // Requirement: N:N Relationship
    // ============================================================
    public function getAllTags(): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name FROM tags ORDER BY name ASC'
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ============================================================
    // getCategoryById()
    // Fetches a single category row.
    // ============================================================
    public function getCategoryById(int $id): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name FROM categories WHERE id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // ============================================================
    // handleUpload()  — PRIVATE helper
    // Validates and moves an uploaded file to the uploads/ folder.
    // Requirement: File Upload
    // Returns: relative file path on success, error string on failure
    // ============================================================
    private function handleUpload(array $file): string
    {
        // Check for PHP upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return 'File upload failed (PHP error code ' . $file['error'] . ').';
        }

        // Validate MIME type using finfo (not just extension)
        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        if (!in_array($mimeType, $this->allowedTypes, true)) {
            return 'Invalid file type. Please upload a JPEG, PNG, GIF, or WebP image.';
        }

        // Validate file size
        if ($file['size'] > $this->maxSize) {
            return 'Image is too large. Maximum allowed size is 10 MB.';
        }

        // Generate a unique filename to prevent overwrites
        $extension = match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
        };
        $filename  = uniqid('wp_', true) . '.' . $extension;
        $destPath  = $this->uploadDir . $filename;

        // Move from PHP temp dir to our uploads folder
        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return 'Could not save the uploaded file. Check folder permissions.';
        }

        return $destPath; // e.g. "uploads/wp_abc123.jpg"
    }

    // ============================================================
    // syncTags()  — PRIVATE helper
    // Deletes all existing tag links for a wallpaper and re-inserts
    // the supplied list. Keeps tag management simple and clean.
    // Requirement: N:N Relationship (wallpaper_tags table)
    // Requirement: Prepared Statements
    // ============================================================
    private function syncTags(int $wallpaperId, array $tagIds): void
    {
        // Remove all current tag associations for this wallpaper
        $del = $this->pdo->prepare(
            'DELETE FROM wallpaper_tags WHERE wallpaper_id = :wid'
        );
        $del->execute([':wid' => $wallpaperId]);

        // Insert fresh associations
        if (!empty($tagIds)) {
            $ins = $this->pdo->prepare(
                'INSERT IGNORE INTO wallpaper_tags (wallpaper_id, tag_id)
                 VALUES (:wid, :tid)'
            );
            foreach ($tagIds as $tagId) {
                $ins->execute([
                    ':wid' => $wallpaperId,
                    ':tid' => (int) $tagId,
                ]);
            }
        }
    }
}
