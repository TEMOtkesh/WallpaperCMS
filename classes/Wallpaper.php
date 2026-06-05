<?php
require_once __DIR__ . '/Database.php';

class Wallpaper
{
    private PDO $pdo;
    private string $uploadDir = 'uploads/';
    private array $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private int $maxSize = 10485760;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function createWallpaper(array $data, array $file, array $tagIds = []): bool|string
    {
        $uploadResult = $this->handleUpload($file);
        if (is_string($uploadResult)) {
            return $uploadResult;
        }
        $imagePath = $uploadResult;

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

        if (!empty($tagIds)) {
            $this->syncTags($wallpaperId, $tagIds);
        }

        return true;
    }

    public function getAllWallpapers(): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT w.id, w.title, w.description, w.image_path, w.created_at,
                    w.user_id,
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

    public function getWallpapersByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT w.id, w.title, w.description, w.image_path, w.created_at,
                    w.user_id,
                    c.name AS category
             FROM   wallpapers w
             JOIN   categories c ON c.id = w.category_id
             WHERE  w.user_id = :user_id
             ORDER  BY w.created_at DESC'
        );
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

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

    public function getWallpapersByCategory(int $categoryId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT w.id, w.title, w.description, w.image_path, w.created_at,
                    w.user_id,
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

    public function updateWallpaper(int $id, array $data, array $file = [], array $tagIds = []): bool|string
    {
        $existing = $this->getWallpaperById($id);
        if (!$existing) {
            return 'Wallpaper not found.';
        }

        $imagePath = $existing['image_path'];

        if (!empty($file['name'])) {
            $uploadResult = $this->handleUpload($file);
            if (is_string($uploadResult)) {
                return $uploadResult;
            }
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
            $imagePath = $uploadResult;
        }

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

        $this->syncTags($id, $tagIds);

        return true;
    }

    public function deleteWallpaper(int $id): bool
    {
        $wallpaper = $this->getWallpaperById($id);
        if (!$wallpaper) {
            return false;
        }

        if (!empty($wallpaper['image_path']) && file_exists($wallpaper['image_path'])) {
            unlink($wallpaper['image_path']);
        }

        $stmt = $this->pdo->prepare(
            'DELETE FROM wallpapers WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

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

    public function getAllCategories(): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name FROM categories ORDER BY name ASC'
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

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

    public function getAllTags(): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name FROM tags ORDER BY name ASC'
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getCategoryById(int $id): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name FROM categories WHERE id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    private function handleUpload(array $file): string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return 'File upload failed (PHP error code ' . $file['error'] . ').';
        }

        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        if (!in_array($mimeType, $this->allowedTypes, true)) {
            return 'Invalid file type. Please upload a JPEG, PNG, GIF, or WebP image.';
        }

        if ($file['size'] > $this->maxSize) {
            return 'Image is too large. Maximum allowed size is 10 MB.';
        }

        $extension = match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
        };
        $filename = uniqid('wp_', true) . '.' . $extension;
        $destPath = $this->uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return 'Could not save the uploaded file. Check folder permissions.';
        }

        return $destPath;
    }

    private function syncTags(int $wallpaperId, array $tagIds): void
    {
        $del = $this->pdo->prepare(
            'DELETE FROM wallpaper_tags WHERE wallpaper_id = :wid'
        );
        $del->execute([':wid' => $wallpaperId]);

        if (!empty($tagIds)) {
            $ins = $this->pdo->prepare(
                'INSERT IGNORE INTO wallpaper_tags (wallpaper_id, tag_id)
                 VALUES (:wid, :tid)'
            );
            foreach ($tagIds as $tagId) {
                $ins->execute([':wid' => $wallpaperId, ':tid' => (int) $tagId]);
            }
        }
    }
}
