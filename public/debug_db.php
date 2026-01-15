<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', APP_PATH . '/config');

require_once CONFIG_PATH . '/database.php';

echo "<h1>Debug DB</h1>";

try {
    $dbInstance = Database::getInstance();
    $pdo = $dbInstance->getConnection();
    
    echo "Class: " . get_class($dbInstance) . "<br>";
    echo "Driver: " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . "<br>";
    
    // List tables (different for mysql/sqlite)
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    echo "<h3>Tables:</h3>";
    if ($driver === 'sqlite') {
        $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        print_r($tables);
    } else {
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        print_r($tables);
    }

    echo "<h3>Query Test:</h3>";
    $sql = "SELECT * FROM reviews LIMIT 1";
    $res = $pdo->query($sql);
    echo "Query OK. Rows: " . count($res->fetchAll());
    
} catch (Exception $e) {
    echo "<h2>Error:</h2>";
    echo $e->getMessage();
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
