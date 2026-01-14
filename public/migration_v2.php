<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', APP_PATH . '/config');

require_once CONFIG_PATH . '/database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "<h1>Running Migration V2</h1>";
    
    // 1. Add columns to seller_profiles
    echo "<h2>Updating seller_profiles...</h2>";
    try {
        $pdo->exec("ALTER TABLE seller_profiles ADD COLUMN nik VARCHAR(20) NULL AFTER userId");
        echo "Added 'nik' column.<br>";
    } catch (PDOException $e) {
        echo "Column 'nik' might already exist: " . $e->getMessage() . "<br>";
    }
    
    try {
        $pdo->exec("ALTER TABLE seller_profiles ADD COLUMN ownerName VARCHAR(100) NULL AFTER nik");
        echo "Added 'ownerName' column.<br>";
    } catch (PDOException $e) {
        echo "Column 'ownerName' might already exist: " . $e->getMessage() . "<br>";
    }
    
    // 2. Create product_images table
    echo "<h2>Creating product_images table...</h2>";
    $sql = "
    CREATE TABLE IF NOT EXISTS product_images (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        productId INT UNSIGNED NOT NULL,
        imagePath VARCHAR(255) NOT NULL,
        createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_product (productId),
        FOREIGN KEY (productId) REFERENCES products(id) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    $pdo->exec($sql);
    echo "Table 'product_images' created or already exists.<br>";
    
    echo "<h2>Migration V2 Complete!</h2>";
    
} catch (Exception $e) {
    echo "<h2>Fatal Error:</h2>" . $e->getMessage();
}
