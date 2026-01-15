<?php
/**
 * Migration: Create seller_payment_methods table
 */
require_once dirname(__DIR__) . '/app/config/database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Create table if not exists
    $sql = "
    CREATE TABLE IF NOT EXISTS seller_payment_methods (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        sellerId INT UNSIGNED NOT NULL,
        paymentType ENUM('qris', 'bank_transfer', 'ewallet', 'cash') NOT NULL,
        providerName VARCHAR(50) DEFAULT NULL,
        accountNumber VARCHAR(50) DEFAULT NULL,
        accountName VARCHAR(100) DEFAULT NULL,
        qrImage VARCHAR(255) DEFAULT NULL,
        isActive TINYINT(1) DEFAULT 1,
        createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        INDEX idx_payment_seller (sellerId),
        INDEX idx_payment_type (paymentType),
        CONSTRAINT fk_payment_seller
            FOREIGN KEY (sellerId) 
            REFERENCES seller_profiles (id) 
            ON DELETE CASCADE
            ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    $pdo->exec($sql);
    
    echo "SUCCESS: Table 'seller_payment_methods' created successfully!\n";
    exit(0);
    
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
