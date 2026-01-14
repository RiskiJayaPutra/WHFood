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
    
    echo "<h1>Installing Reviews Table (SQLite)</h1>";
    
    // 1. Create Table
    $sql = "
    CREATE TABLE IF NOT EXISTS reviews (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        productId INTEGER NOT NULL,
        userId INTEGER NOT NULL,
        rating INTEGER NOT NULL CHECK (rating BETWEEN 1 AND 5),
        comment TEXT DEFAULT NULL,
        image TEXT DEFAULT NULL,
        isVisible INTEGER DEFAULT 1,
        isVerified INTEGER DEFAULT 0,
        createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
        updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (productId) REFERENCES products(id) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (userId) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
    );
    ";
    
    $pdo->exec($sql);
    echo "Table 'reviews' created.<br>";
    
    // 2. Create Triggers
    // Trigger Insert
    $pdo->exec("
    CREATE TRIGGER IF NOT EXISTS trg_reviews_after_insert
    AFTER INSERT ON reviews
    BEGIN
        UPDATE products 
        SET 
            rating = (SELECT AVG(rating) FROM reviews WHERE productId = NEW.productId AND isVisible = 1),
            totalReviews = (SELECT COUNT(*) FROM reviews WHERE productId = NEW.productId AND isVisible = 1)
        WHERE id = NEW.productId;
    END;
    ");
    echo "Trigger 'trg_reviews_after_insert' created.<br>";
    
    // Trigger Update
    $pdo->exec("
    CREATE TRIGGER IF NOT EXISTS trg_reviews_after_update
    AFTER UPDATE ON reviews
    BEGIN
        UPDATE products 
        SET 
            rating = (SELECT AVG(rating) FROM reviews WHERE productId = NEW.productId AND isVisible = 1),
            totalReviews = (SELECT COUNT(*) FROM reviews WHERE productId = NEW.productId AND isVisible = 1)
        WHERE id = NEW.productId;
    END;
    ");
    echo "Trigger 'trg_reviews_after_update' created.<br>";
    
    // Trigger Delete
    $pdo->exec("
    CREATE TRIGGER IF NOT EXISTS trg_reviews_after_delete
    AFTER DELETE ON reviews
    BEGIN
        UPDATE products 
        SET 
            rating = COALESCE((SELECT AVG(rating) FROM reviews WHERE productId = OLD.productId AND isVisible = 1), 0),
            totalReviews = (SELECT COUNT(*) FROM reviews WHERE productId = OLD.productId AND isVisible = 1)
        WHERE id = OLD.productId;
    END;
    ");
    echo "Trigger 'trg_reviews_after_delete' created.<br>";
    
    echo "<h2>Done!</h2>";
    
} catch (Exception $e) {
    echo "<h2>Error:</h2>" . $e->getMessage();
}
