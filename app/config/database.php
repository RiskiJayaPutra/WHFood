<?php



declare(strict_types=1);


class Database
{
    
    private static ?Database $instance = null;

    
    private ?PDO $connection = null;

    
    private const DB_HOST = 'localhost';
    private const DB_PORT = '3306';
    private const DB_NAME = 'whfood_db';
    private const DB_USER = 'root';
    private const DB_PASS = '';
    private const DB_CHARSET = 'utf8mb4';

    
    private const PDO_OPTIONS = [
        
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        
        
        PDO::ATTR_EMULATE_PREPARES => false,
        
        
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        
        
        PDO::ATTR_PERSISTENT => true,
        
        
        PDO::ATTR_TIMEOUT => 5,
    ];

    
    private function __construct()
    {
        $this->connect();
    }

    
    private function __clone(): void
    {
        
    }

    
    public function __wakeup(): void
    {
        throw new \Exception("Cannot unserialize singleton");
    }

    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    
    private function connect(): void
    {
        
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            self::DB_HOST,
            self::DB_PORT,
            self::DB_NAME,
            self::DB_CHARSET
        );

        try {
            $this->connection = new PDO($dsn, self::DB_USER, self::DB_PASS, self::PDO_OPTIONS);
            
            
            $this->connection->exec("SET time_zone = '+07:00'");
            
            
            $this->connection->exec("SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");
            
        } catch (PDOException $e) {
            
            error_log("Database Connection Error: " . $e->getMessage());
            
            
            throw new PDOException("Gagal terhubung ke database. Silakan coba beberapa saat lagi.");
        }
    }

    
    public function getConnection(): PDO
    {
        return $this->connection;
    }

    
    public function select(string $sql, array $params = []): array
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    
    public function selectOne(string $sql, array $params = []): array|false
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch();
    }

    
    public function insert(string $table, array $data): int|false
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        $stmt = $this->connection->prepare($sql);
        $success = $stmt->execute(array_values($data));
        
        return $success ? (int) $this->connection->lastInsertId() : false;
    }

    
    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $setClause = implode(' = ?, ', array_keys($data)) . ' = ?';
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([...array_values($data), ...$whereParams]);
        
        return $stmt->rowCount();
    }

    
    public function delete(string $table, string $where, array $whereParams = []): int
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($whereParams);
        
        return $stmt->rowCount();
    }

    
    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    
    public function commit(): bool
    {
        return $this->connection->commit();
    }

    
    public function rollback(): bool
    {
        return $this->connection->rollBack();
    }
}


function db(): PDO
{
    return Database::getInstance()->getConnection();
}
