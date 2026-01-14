-- ============================================================================
-- WHFood - Reviews Table
-- ============================================================================

USE whfood_db;

CREATE TABLE IF NOT EXISTS reviews (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    
    productId INT UNSIGNED NOT NULL COMMENT 'Referensi ke tabel products',
    userId INT UNSIGNED NOT NULL COMMENT 'Referensi ke user yang memberi review',
    
    rating TINYINT UNSIGNED NOT NULL COMMENT 'Rating 1-5',
    comment TEXT DEFAULT NULL COMMENT 'Komentar review',
    
    isVerified TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Review terverifikasi',
    isVisible TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Tampilkan review',
    
    createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id),
    UNIQUE KEY uk_reviews_product_user (productId, userId),
    INDEX idx_reviews_product_id (productId),
    INDEX idx_reviews_user_id (userId),
    INDEX idx_reviews_rating (rating),
    INDEX idx_reviews_created_at (createdAt),
    
    CONSTRAINT fk_reviews_product_id
        FOREIGN KEY (productId) 
        REFERENCES products (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
        
    CONSTRAINT fk_reviews_user_id
        FOREIGN KEY (userId) 
        REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
        
    CONSTRAINT chk_reviews_rating
        CHECK (rating BETWEEN 1 AND 5)
        
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Review produk dari buyer';

-- Trigger untuk update rating produk setelah review
DELIMITER //

CREATE TRIGGER trg_reviews_after_insert
AFTER INSERT ON reviews
FOR EACH ROW
BEGIN
    UPDATE products 
    SET 
        rating = (SELECT AVG(rating) FROM reviews WHERE productId = NEW.productId AND isVisible = 1),
        totalReviews = (SELECT COUNT(*) FROM reviews WHERE productId = NEW.productId AND isVisible = 1)
    WHERE id = NEW.productId;
    
    -- Update juga rating seller
    UPDATE seller_profiles sp
    SET 
        rating = (SELECT AVG(p.rating) FROM products p WHERE p.sellerId = sp.id AND p.status = 'active'),
        totalReviews = (SELECT SUM(p.totalReviews) FROM products p WHERE p.sellerId = sp.id)
    WHERE sp.id = (SELECT sellerId FROM products WHERE id = NEW.productId);
END//

CREATE TRIGGER trg_reviews_after_update
AFTER UPDATE ON reviews
FOR EACH ROW
BEGIN
    UPDATE products 
    SET 
        rating = (SELECT AVG(rating) FROM reviews WHERE productId = NEW.productId AND isVisible = 1),
        totalReviews = (SELECT COUNT(*) FROM reviews WHERE productId = NEW.productId AND isVisible = 1)
    WHERE id = NEW.productId;
END//

CREATE TRIGGER trg_reviews_after_delete
AFTER DELETE ON reviews
FOR EACH ROW
BEGIN
    UPDATE products 
    SET 
        rating = COALESCE((SELECT AVG(rating) FROM reviews WHERE productId = OLD.productId AND isVisible = 1), 0),
        totalReviews = (SELECT COUNT(*) FROM reviews WHERE productId = OLD.productId AND isVisible = 1)
    WHERE id = OLD.productId;
END//

DELIMITER ;

-- ============================================================================
-- Insert Admin User (password: admin123)
-- ============================================================================
INSERT INTO users (email, password, fullName, phoneNumber, role, status, emailVerifiedAt) VALUES
('admin@whfood.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin WHFood', '081234567890', 'admin', 'active', NOW())
ON DUPLICATE KEY UPDATE email = email;
