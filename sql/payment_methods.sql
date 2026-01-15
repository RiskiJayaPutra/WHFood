-- ============================================================================
-- WHFood - Seller Payment Methods
-- ============================================================================

USE whfood_db;

CREATE TABLE IF NOT EXISTS seller_payment_methods (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    sellerId INT UNSIGNED NOT NULL,
    
    -- Tipo pembayaran: qris, bank_transfer, ewallet, cash
    paymentType ENUM('qris', 'bank_transfer', 'ewallet', 'cash') NOT NULL,
    
    -- Provider: BCA, BRI, DANA, GoPay, etc (NULL untuk cash/qris generic)
    providerName VARCHAR(50) DEFAULT NULL,
    
    -- Detail Akun
    accountNumber VARCHAR(50) DEFAULT NULL,
    accountName VARCHAR(100) DEFAULT NULL,
    
    -- Gambar QRIS (Path)
    qrImage VARCHAR(255) DEFAULT NULL,
    
    -- Status
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
