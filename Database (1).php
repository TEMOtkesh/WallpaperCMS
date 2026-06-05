<?php
// ============================================================
// classes/Database.php
// Requirement: OOP Class
// Requirement: PDO Database Connection with Prepared Statements
// ============================================================

// Load credentials from config
require_once __DIR__ . '/../config/database.php';

// Requirement: OOP Class
class Database
{
    // Singleton instance — only one connection is ever created
    private static ?Database $instance = null;

    // The PDO connection object
    private PDO $pdo;

    // ------------------------------------------------------------
    // Private constructor — prevents direct instantiation.
    // Called only once via getInstance().
    // Requirement: PDO with error mode set to exceptions
    // ------------------------------------------------------------
    private function __construct()
    {
        $dsn = 'mysql:host=' . DB_HOST
             . ';dbname=' . DB_NAME
             . ';charset=' . DB_CHARSET;

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // throw exceptions on error
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // return rows as associative arrays
            PDO::ATTR_EMULATE_PREPARES   => false,                   // use real prepared statements
        ];

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Stop execution and show a safe error (never expose credentials)
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    // ------------------------------------------------------------
    // getInstance() — returns the single shared Database object.
    // Requirement: OOP Singleton pattern
    // ------------------------------------------------------------
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // ------------------------------------------------------------
    // getConnection() — returns the raw PDO object so other
    // classes can call prepare() / query() on it directly.
    // ------------------------------------------------------------
    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    // ------------------------------------------------------------
    // prepare() — convenience wrapper around PDO::prepare().
    // All queries go through here to ensure prepared statements.
    // Requirement: Prepared Statements
    // ------------------------------------------------------------
    public function prepare(string $sql): \PDOStatement
    {
        return $this->pdo->prepare($sql);
    }

    // ------------------------------------------------------------
    // lastInsertId() — returns the ID of the last inserted row.
    // ------------------------------------------------------------
    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }
}
