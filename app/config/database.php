<?php

/**
 * ============================================================================
 * WHFood - Database Configuration (Singleton Pattern)
 * ============================================================================
 * 
 * File ini mengimplementasikan Singleton Pattern untuk koneksi database PDO.
 * Singleton memastikan hanya ada satu instance koneksi database yang aktif
 * sepanjang siklus aplikasi, menghemat resources dan menjaga konsistensi.
 * 
 * @package     WHFood
 * @subpackage  Config
 * @author      WHFood Development Team
 * @version     1.0.0
 * @since       2026-01-12
 */

declare(strict_types=1);

/**
 * Class Database
 * 
 * Singleton class untuk mengelola koneksi PDO ke MySQL database.
 * Menggunakan PDO prepared statements untuk keamanan dari SQL Injection.
 */
class Database
{
    /**
     * Instance tunggal dari class Database (Singleton)
     * 
     * @var Database|null
     */
    private static ?Database $instance = null;

    /**
     * PDO connection object
     * 
     * @var PDO|null
     */
    private ?PDO $connection = null;

    /**
     * Konfigurasi database
     * Sesuaikan dengan environment Anda
     */
    private const DB_HOST = 'localhost';
    private const DB_PORT = '3306';
    private const DB_NAME = 'whfood_db';
    private const DB_USER = 'root';
    private const DB_PASS = '';
    private const DB_CHARSET = 'utf8mb4';

    /**
     * PDO Options untuk keamanan dan performa
     */
    private const PDO_OPTIONS = [
        // Mode error: Throw exceptions untuk error handling yang lebih baik
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        
        // Gunakan prepared statements native untuk keamanan
        PDO::ATTR_EMULATE_PREPARES => false,
        
        // Fetch mode default: Associative array
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        
        // Persistent connection untuk performa
        PDO::ATTR_PERSISTENT => true,
        
        // Timeout koneksi (dalam detik)
        PDO::ATTR_TIMEOUT => 5,
    ];

    /**
     * Private constructor - mencegah instantiasi langsung
     * 
     * @throws PDOException Jika koneksi gagal
     */
    private function __construct()
    {
        $this->connect();
    }

    /**
     * Mencegah cloning instance (bagian dari Singleton pattern)
     */
    private function __clone(): void
    {
        // Tidak diizinkan
    }

    /**
     * Mencegah unserialization (bagian dari Singleton pattern)
     * 
     * @throws \Exception Selalu throw exception
     */
    public function __wakeup(): void
    {
        throw new \Exception("Cannot unserialize singleton");
    }

    /**
     * Mendapatkan instance tunggal dari Database
     * 
     * Metode ini adalah satu-satunya cara untuk mendapatkan instance Database.
     * Jika instance belum ada, akan dibuat baru. Jika sudah ada, 
     * mengembalikan instance yang sudah ada.
     * 
     * @return self Instance Database
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Membuat koneksi ke database
     * 
     * @throws PDOException Jika koneksi gagal
     */
    private function connect(): void
    {
        // Buat DSN (Data Source Name) untuk MySQL
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            self::DB_HOST,
            self::DB_PORT,
            self::DB_NAME,
            self::DB_CHARSET
        );

        try {
            $this->connection = new PDO($dsn, self::DB_USER, self::DB_PASS, self::PDO_OPTIONS);
            
            // Set timezone ke Asia/Jakarta (WIB)
            $this->connection->exec("SET time_zone = '+07:00'");
            
            // Set SQL mode untuk strict validation
            $this->connection->exec("SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");
            
        } catch (PDOException $e) {
            // Log error (dalam production, gunakan proper logging)
            error_log("Database Connection Error: " . $e->getMessage());
            
            // Throw exception dengan pesan yang aman (tanpa mengekspos credentials)
            throw new PDOException("Gagal terhubung ke database. Silakan coba beberapa saat lagi.");
        }
    }

    /**
     * Mendapatkan PDO connection object
     * 
     * @return PDO PDO connection instance
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }

    /**
     * Shortcut method untuk query SELECT
     * 
     * @param string $sql    SQL query dengan placeholders
     * @param array  $params Parameter untuk prepared statement
     * 
     * @return array Array hasil query
     */
    public function select(string $sql, array $params = []): array
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    /**
     * Shortcut method untuk query SELECT single row
     * 
     * @param string $sql    SQL query dengan placeholders
     * @param array  $params Parameter untuk prepared statement
     * 
     * @return array|false Single row atau false jika tidak ditemukan
     */
    public function selectOne(string $sql, array $params = []): array|false
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch();
    }

    /**
     * Shortcut method untuk query INSERT
     * 
     * @param string $table  Nama tabel
     * @param array  $data   Associative array [column => value]
     * 
     * @return int|false Last insert ID atau false jika gagal
     */
    public function insert(string $table, array $data): int|false
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        $stmt = $this->connection->prepare($sql);
        $success = $stmt->execute(array_values($data));
        
        return $success ? (int) $this->connection->lastInsertId() : false;
    }

    /**
     * Shortcut method untuk query UPDATE
     * 
     * @param string $table     Nama tabel
     * @param array  $data      Associative array [column => value]
     * @param string $where     WHERE clause (tanpa kata 'WHERE')
     * @param array  $whereParams Parameter untuk WHERE clause
     * 
     * @return int Jumlah row yang terpengaruh
     */
    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $setClause = implode(' = ?, ', array_keys($data)) . ' = ?';
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([...array_values($data), ...$whereParams]);
        
        return $stmt->rowCount();
    }

    /**
     * Shortcut method untuk query DELETE
     * 
     * @param string $table       Nama tabel
     * @param string $where       WHERE clause (tanpa kata 'WHERE')
     * @param array  $whereParams Parameter untuk WHERE clause
     * 
     * @return int Jumlah row yang terhapus
     */
    public function delete(string $table, string $where, array $whereParams = []): int
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($whereParams);
        
        return $stmt->rowCount();
    }

    /**
     * Memulai database transaction
     * 
     * @return bool True jika berhasil
     */
    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Commit transaction
     * 
     * @return bool True jika berhasil
     */
    public function commit(): bool
    {
        return $this->connection->commit();
    }

    /**
     * Rollback transaction
     * 
     * @return bool True jika berhasil
     */
    public function rollback(): bool
    {
        return $this->connection->rollBack();
    }
}

/**
 * Helper function untuk mendapatkan database connection dengan cepat
 * 
 * @return PDO PDO connection instance
 */
function db(): PDO
{
    return Database::getInstance()->getConnection();
}
