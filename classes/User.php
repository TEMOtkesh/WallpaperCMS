<?php
require_once __DIR__ . '/Database.php';

class User
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function register(string $name, string $email, string $password): bool|string
    {
        $stmt = $this->pdo->prepare(
            'SELECT id FROM users WHERE email = :email LIMIT 1'
        );
        $stmt->execute([':email' => $email]);

        if ($stmt->fetch()) {
            return 'An account with that email already exists.';
        }

        $hashed = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $this->pdo->prepare(
            'INSERT INTO users (name, email, password, role)
             VALUES (:name, :email, :password, :role)'
        );

        $stmt->execute([
            ':name'     => htmlspecialchars(trim($name)),
            ':email'    => strtolower(trim($email)),
            ':password' => $hashed,
            ':role'     => 'user',
        ]);

        return true;
    }

    public function login(string $email, string $password): bool|string
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, email, password, role
             FROM users
             WHERE email = :email
             LIMIT 1'
        );
        $stmt->execute([':email' => strtolower(trim($email))]);
        $user = $stmt->fetch();

        if (!$user) {
            return 'No account found with that email address.';
        }

        if (!password_verify($password, $user['password'])) {
            return 'Incorrect password. Please try again.';
        }

        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['role']      = $user['role'];

        return true;
    }

    public function getUserById(int $id): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, email, role, created_at
             FROM users
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function getAllUsers(): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, email, role, created_at
             FROM users
             ORDER BY created_at DESC'
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateRole(int $userId, string $role): bool
    {
        if (!in_array($role, ['admin', 'user'], true)) {
            return false;
        }

        $stmt = $this->pdo->prepare(
            'UPDATE users SET role = :role WHERE id = :id'
        );
        $stmt->execute([':role' => $role, ':id' => $userId]);
        return $stmt->rowCount() > 0;
    }

    public function deleteUser(int $userId): bool
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM users WHERE id = :id'
        );
        $stmt->execute([':id' => $userId]);
        return $stmt->rowCount() > 0;
    }

    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    public static function isAdmin(): bool
    {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    // Use relative redirects so the app works in any subdirectory
    public static function requireLogin(): void
    {
        if (!self::isLoggedIn()) {
            header('Location: login.php?error=Please+log+in+to+continue.');
            exit;
        }
    }

    public static function requireAdmin(): void
    {
        self::requireLogin();
        if (!self::isAdmin()) {
            header('Location: index.php?error=Access+denied.');
            exit;
        }
    }
}
