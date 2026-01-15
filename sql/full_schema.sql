-- ============================================================================
-- WHFood - Full Database Schema (Consolidated)
-- ============================================================================
-- 
-- Gabungan dari semua skema: users, sellers, products, reviews, payment methods.
-- Ditambah kolom NIK pada tabel users yang sebelumnya terlewat.
-- 
-- @package     WHFood
-- @version     2.0.0 (Consolidated)
-- @since       2026-01-16
-- ============================================================================

-- Hapus database lama jika perlu reset total (hati-hati!)
-- DROP DATABASE IF EXISTS whfood_db;

CREATE DATABASE IF NOT EXISTS whfood_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE whfood_db;

-- ============================================================================
-- 1. USERS TABLE (Added NIK)
-- ============================================================================
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    
    -- Auth
    email VARCHAR(255) NOT NULL COMMENT 'Email unik untuk login',
    password VARCHAR(255) NOT NULL COMMENT 'Password hash (bcrypt)',
    
    -- Profile
    fullName VARCHAR(100) NOT NULL COMMENT 'Nama lengkap pengguna',
    nik CHAR(16) DEFAULT NULL COMMENT 'Nomor Induk Kependudukan (16 digit)',
    phoneNumber VARCHAR(20) DEFAULT NULL COMMENT 'Nomor telepon',
    profileImage VARCHAR(255) DEFAULT NULL,
    
    -- Role & Status
    role ENUM('buyer', 'seller', 'admin') NOT NULL DEFAULT 'buyer',
    status ENUM('active', 'inactive', 'suspended', 'pending') NOT NULL DEFAULT 'pending',
    
    -- Verification
    emailVerifiedAt TIMESTAMP NULL DEFAULT NULL,
    verificationToken VARCHAR(100) DEFAULT NULL,
    
    -- Reset Password
    resetToken VARCHAR(100) DEFAULT NULL,
    resetTokenExpiresAt TIMESTAMP NULL DEFAULT NULL,
    
    -- Timestamps
    lastLoginAt TIMESTAMP NULL DEFAULT NULL,
    createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id),
    UNIQUE KEY uk_users_email (email),
    UNIQUE KEY uk_users_nik (nik),
    INDEX idx_users_role (role),
    INDEX idx_users_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 2. SELLER PROFILES
-- ============================================================================
CREATE TABLE IF NOT EXISTS seller_profiles (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    userId INT UNSIGNED NOT NULL,
    
    storeName VARCHAR(100) NOT NULL,
    storeSlug VARCHAR(120) NOT NULL,
    storeDescription TEXT DEFAULT NULL,
    storeLogo VARCHAR(255) DEFAULT NULL,
    storeBanner VARCHAR(255) DEFAULT NULL,
    
    address TEXT NOT NULL,
    village VARCHAR(100) NOT NULL DEFAULT 'Way Huwi',
    district VARCHAR(100) NOT NULL DEFAULT 'Jati Agung',
    regency VARCHAR(100) NOT NULL DEFAULT 'Lampung Selatan',
    province VARCHAR(100) NOT NULL DEFAULT 'Lampung',
    postalCode VARCHAR(10) DEFAULT NULL,
    latitude DECIMAL(10, 8) DEFAULT NULL,
    longitude DECIMAL(11, 8) DEFAULT NULL,
    
    operationalHours JSON DEFAULT NULL,
    isOpen TINYINT(1) NOT NULL DEFAULT 1,
    
    isVerified TINYINT(1) NOT NULL DEFAULT 0,
    verifiedAt TIMESTAMP NULL DEFAULT NULL,
    rating DECIMAL(3, 2) DEFAULT 0.00,
    totalReviews INT UNSIGNED NOT NULL DEFAULT 0,
    totalProducts INT UNSIGNED NOT NULL DEFAULT 0,
    totalSales INT UNSIGNED NOT NULL DEFAULT 0,
    
    -- Legacy fields (untuk kompatibilitas, tapi disarankan pakai payment_methods table)
    bankName VARCHAR(50) DEFAULT NULL,
    bankAccountNumber VARCHAR(30) DEFAULT NULL,
    bankAccountName VARCHAR(100) DEFAULT NULL,
    
    createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id),
    UNIQUE KEY uk_seller_profiles_user_id (userId),
    UNIQUE KEY uk_seller_profiles_store_slug (storeSlug),
    
    CONSTRAINT fk_seller_profiles_user_id
        FOREIGN KEY (userId) REFERENCES users (id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 3. PRODUCTS
-- ============================================================================
CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    sellerId INT UNSIGNED NOT NULL,
    
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(170) NOT NULL,
    description TEXT DEFAULT NULL,
    
    category ENUM('makanan_berat','makanan_ringan','minuman','dessert','frozen_food','sambal','lainnya') NOT NULL DEFAULT 'lainnya',
    
    price DECIMAL(12, 2) NOT NULL,
    discountPrice DECIMAL(12, 2) DEFAULT NULL,
    discountPercentage TINYINT UNSIGNED DEFAULT NULL,
    
    primaryImage VARCHAR(255) DEFAULT NULL,
    images JSON DEFAULT NULL,
    
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    minOrder TINYINT UNSIGNED NOT NULL DEFAULT 1,
    maxOrder INT UNSIGNED DEFAULT NULL,
    unit VARCHAR(30) NOT NULL DEFAULT 'porsi',
    
    status ENUM('active', 'inactive', 'soldout', 'deleted') NOT NULL DEFAULT 'active',
    isAvailable TINYINT(1) NOT NULL DEFAULT 1,
    isFeatured TINYINT(1) NOT NULL DEFAULT 0,
    
    weight INT UNSIGNED DEFAULT NULL,
    preparationTime INT UNSIGNED DEFAULT NULL,
    nutritionInfo JSON DEFAULT NULL,
    tags JSON DEFAULT NULL,
    
    rating DECIMAL(3, 2) DEFAULT 0.00,
    totalReviews INT UNSIGNED NOT NULL DEFAULT 0,
    totalSold INT UNSIGNED NOT NULL DEFAULT 0,
    viewCount INT UNSIGNED NOT NULL DEFAULT 0,
    
    createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id),
    UNIQUE KEY uk_products_seller_slug (sellerId, slug),
    FULLTEXT INDEX ft_products_search (name, description),
    
    CONSTRAINT fk_products_seller_id
        FOREIGN KEY (sellerId) REFERENCES seller_profiles (id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 4. REVIEWS
-- ============================================================================
CREATE TABLE IF NOT EXISTS reviews (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    
    productId INT UNSIGNED NOT NULL,
    userId INT UNSIGNED NOT NULL,
    
    rating TINYINT UNSIGNED NOT NULL,
    comment TEXT DEFAULT NULL,
    
    isVerified TINYINT(1) NOT NULL DEFAULT 0,
    isVisible TINYINT(1) NOT NULL DEFAULT 1,
    
    createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id),
    UNIQUE KEY uk_reviews_product_user (productId, userId),
    CONSTRAINT chk_reviews_rating CHECK (rating BETWEEN 1 AND 5),
    
    CONSTRAINT fk_reviews_product_id
        FOREIGN KEY (productId) REFERENCES products (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    
    CONSTRAINT fk_reviews_user_id
        FOREIGN KEY (userId) REFERENCES users (id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 5. PAYMENT METHODS
-- ============================================================================
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
    CONSTRAINT fk_payment_seller_method
        FOREIGN KEY (sellerId) REFERENCES seller_profiles (id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TRIGGERS
-- ============================================================================

DELIMITER //

-- Product Stats Triggers
CREATE TRIGGER trg_products_after_insert AFTER INSERT ON products
FOR EACH ROW
BEGIN
    IF NEW.status = 'active' THEN
        UPDATE seller_profiles SET totalProducts = totalProducts + 1 WHERE id = NEW.sellerId;
    END IF;
END//

CREATE TRIGGER trg_products_after_update AFTER UPDATE ON products
FOR EACH ROW
BEGIN
    IF OLD.status != 'active' AND NEW.status = 'active' THEN
        UPDATE seller_profiles SET totalProducts = totalProducts + 1 WHERE id = NEW.sellerId;
    ELSEIF OLD.status = 'active' AND NEW.status != 'active' THEN
        UPDATE seller_profiles SET totalProducts = GREATEST(0, totalProducts - 1) WHERE id = NEW.sellerId;
    END IF;
END//

CREATE TRIGGER trg_products_after_delete AFTER DELETE ON products
FOR EACH ROW
BEGIN
    IF OLD.status = 'active' THEN
        UPDATE seller_profiles SET totalProducts = GREATEST(0, totalProducts - 1) WHERE id = OLD.sellerId;
    END IF;
END//

-- Review Stats Triggers
CREATE TRIGGER trg_reviews_after_insert AFTER INSERT ON reviews
FOR EACH ROW
BEGIN
    UPDATE products 
    SET rating = (SELECT AVG(rating) FROM reviews WHERE productId = NEW.productId AND isVisible = 1),
        totalReviews = (SELECT COUNT(*) FROM reviews WHERE productId = NEW.productId AND isVisible = 1)
    WHERE id = NEW.productId;

    UPDATE seller_profiles sp
    SET rating = (SELECT AVG(p.rating) FROM products p WHERE p.sellerId = sp.id AND p.status = 'active'),
        totalReviews = (SELECT SUM(p.totalReviews) FROM products p WHERE p.sellerId = sp.id)
    WHERE sp.id = (SELECT sellerId FROM products WHERE id = NEW.productId);
END//

CREATE TRIGGER trg_reviews_after_update AFTER UPDATE ON reviews
FOR EACH ROW
BEGIN
    UPDATE products 
    SET rating = (SELECT AVG(rating) FROM reviews WHERE productId = NEW.productId AND isVisible = 1),
        totalReviews = (SELECT COUNT(*) FROM reviews WHERE productId = NEW.productId AND isVisible = 1)
    WHERE id = NEW.productId;
END//

CREATE TRIGGER trg_reviews_after_delete AFTER DELETE ON reviews
FOR EACH ROW
BEGIN
    UPDATE products 
    SET rating = COALESCE((SELECT AVG(rating) FROM reviews WHERE productId = OLD.productId AND isVisible = 1), 0),
        totalReviews = (SELECT COUNT(*) FROM reviews WHERE productId = OLD.productId AND isVisible = 1)
    WHERE id = OLD.productId;
END//

DELIMITER ;

-- ============================================================================
-- DEFAULT DATA
-- ============================================================================
INSERT INTO users (email, password, fullName, phoneNumber, role, status, emailVerifiedAt) VALUES
('admin@whfood.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin WHFood', '081234567890', 'admin', 'active', NOW())
ON DUPLICATE KEY UPDATE email = email;
