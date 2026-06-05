<?php
// ============================================================
// classes/User.php
// Requirement: OOP Class
// Requirement: Registration and Login System
// Requirement: Password Hashing (password_hash / password_verify)
// Requirement: User Roles (admin / user)
// Requirement: Prepared Statements
// ============================================================

require_once __DIR__ . '/../classes/Database.php';

// Requirement: OOP Class
class User
{
    // Shared PDO connection — injected via Database singleton
    private PDO $pdo;

    // ------------------------------------------------------------
    // Constructor — grabs the PDO connection from Database class
    // Requirement: OOP Constructor / Dependency via Singleton
    // ------------------------------------------------------------
    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    // ============================================================
    // register()
    // Creates a new user account.
    // Requirement: Registration System
    // Requirement: password_hash() for secure password storage
    // Requirement: Prepared Statements (no SQL injection possible)
    // Returns: true on success, string error message on failure
    // ============================================================
    public function register(string $name, string $email, string $password): bool|string
    {
        // Check if the email is already taken
        $stmt = $this->pdo->prepare(
            'SELECT id FROM users WHERE email = :email LIMIT 1'
        );
        $stmt->execute([':email' => $email]);

        if ($stmt->fetch()) {
            return 'An account with that email already exists.';
        }

        // Requirement: password_hash() — never store plain-text passwords
        $hashed = password_hash($password, PASSWORD_BCRYPT);

        // Insert the new user (role defaults to 'user' per DB schema)
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

    // ============================================================
    // login()
    // Verifies credentials and populates $_SESSION on success.
    // Requirement: Login System
    // Requirement: password_verify() for checking hashed password
    // Requirement: Session Authentication ($_SESSION variables)
    // Returns: true on success, string error message on failure
    // ============================================================
    public function login(string $email, string $password): bool|string
    {
        // Fetch the user row by email
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

        // Requirement: password_verify() — compare plain input to stored hash
        if (!password_verify($password, $user['password'])) {
            return 'Incorrect password. Please try again.';
        }

        // Requirement: Session Authentication
        // Store essential user data in session for use across all pages
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['role']      = $user['role'];   // 'admin' or 'user'

        return true;
    }

    // ============================================================
    // getUserById()
    // Fetches a single user record by primary key.
    // Requirement: OOP Method / Prepared Statements
    // Returns: user array or false if not found
    // ============================================================
    public function getUserById(int $id): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, email, role, created_at
             FROM users
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(); // returns array or false
    }

    // ============================================================
    // getAllUsers()
    // Returns all users — used by the admin panel.
    // Requirement: Admin Features (view all users)
    // Requirement: User Roles visible in code
    // ============================================================
    public function getAllUsers(): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, email, role, created_at
             FROM users
             ORDER BY created_at DESC'
        );
        $stmt->execute();
        return $stmt->fetchAll(); // always returns array (empty if none)
    }

    // ============================================================
    // updateRole()
    // Allows an admin to change any user's role.
    // Requirement: Admin Features (change user roles)
    // Requirement: User Roles
    // Requirement: Prepared Statements
    // ============================================================
    public function updateRole(int $userId, string $role): bool
    {
        // Whitelist — only allow valid role values
        if (!in_array($role, ['admin', 'user'], true)) {
            return false;
        }

        $stmt = $this->pdo->prepare(
            'UPDATE users SET role = :role WHERE id = :id'
        );
        $stmt->execute([
            ':role' => $role,
            ':id'   => $userId,
        ]);

        return $stmt->rowCount() > 0;
    }

    // ============================================================
    // deleteUser()
    // Allows an admin to delete a user (cascades to wallpapers).
    // Requirement: Admin Features
    // Requirement: Prepared Statements
    // ============================================================
    public function deleteUser(int $userId): bool
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM users WHERE id = :id'
        );
        $stmt->execute([':id' => $userId]);
        return $stmt->rowCount() > 0;
    }

    // ============================================================
    // isLoggedIn()
    // Helper — returns true if a valid session exists.
    // Requirement: Session Authentication
    // ============================================================
    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    // ============================================================
    // isAdmin()
    // Helper — returns true if the logged-in user is an admin.
    // Requirement: User Roles / Role-based Permissions
    // ============================================================
    public static function isAdmin(): bool
    {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    // ============================================================
    // requireLogin()
    // Guards pages that need authentication — redirects if not logged in.
    // Requirement: Session Authentication
    // ============================================================
    public static function requireLogin(): void
    {
        if (!self::isLoggedIn()) {
            header('Location: /login.php?error=Please+log+in+to+continue.');
            exit;
        }
    }

    // ============================================================
    // requireAdmin()
    // Guards admin-only pages — redirects if not an admin.
    // Requirement: User Roles / Role-based Permissions
    // ============================================================
    public static function requireAdmin(): void
    {
        self::requireLogin();
        if (!self::isAdmin()) {
            header('Location: /index.php?error=Access+denied.');
            exit;
        }
    }
}
