-- ============================================================================
-- WHFood - Database Schema
-- ============================================================================
-- 
-- Skema database lengkap untuk WHFood (Way Huwi Food Marketplace).
-- Menggunakan MySQL 8.0 dengan InnoDB engine dan foreign key constraints.
-- 
-- @package     WHFood
-- @version     1.0.0
-- @since       2026-01-12
-- ============================================================================

-- Hapus database jika ada (gunakan dengan hati-hati!)
-- DROP DATABASE IF EXISTS whfood_db;

-- Buat database
CREATE DATABASE IF NOT EXISTS whfood_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

-- Gunakan database
USE whfood_db;

-- ============================================================================
-- TABEL: users
-- ============================================================================
-- Tabel utama untuk menyimpan data pengguna (buyer & seller).
-- Menggunakan ENUM untuk role dan status agar data konsisten.
-- ============================================================================

CREATE TABLE IF NOT EXISTS users (
    -- Primary Key
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    
    -- Informasi Autentikasi
    email VARCHAR(255) NOT NULL COMMENT 'Email unik untuk login',
    password VARCHAR(255) NOT NULL COMMENT 'Password hash (bcrypt)',
    
    -- Informasi Profil Dasar
    fullName VARCHAR(100) NOT NULL COMMENT 'Nama lengkap pengguna',
    phoneNumber VARCHAR(20) DEFAULT NULL COMMENT 'Nomor telepon (format: 08xxxxxxxxxx)',
    profileImage VARCHAR(255) DEFAULT NULL COMMENT 'Path ke foto profil',
    
    -- Role & Status
    role ENUM('buyer', 'seller', 'admin') NOT NULL DEFAULT 'buyer' COMMENT 'Peran pengguna',
    status ENUM('active', 'inactive', 'suspended', 'pending') NOT NULL DEFAULT 'pending' COMMENT 'Status akun',
    
    -- Email Verification
    emailVerifiedAt TIMESTAMP NULL DEFAULT NULL COMMENT 'Waktu verifikasi email',
    verificationToken VARCHAR(100) DEFAULT NULL COMMENT 'Token untuk verifikasi email',
    
    -- Password Reset
    resetToken VARCHAR(100) DEFAULT NULL COMMENT 'Token untuk reset password',
    resetTokenExpiresAt TIMESTAMP NULL DEFAULT NULL COMMENT 'Waktu kadaluarsa token reset',
    
    -- Tracking
    lastLoginAt TIMESTAMP NULL DEFAULT NULL COMMENT 'Waktu login terakhir',
    createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu pembuatan akun',
    updatedAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Waktu update terakhir',
    
    -- Constraints
    PRIMARY KEY (id),
    UNIQUE KEY uk_users_email (email),
    INDEX idx_users_role (role),
    INDEX idx_users_status (status),
    INDEX idx_users_created_at (createdAt)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Tabel utama pengguna WHFood';

-- ============================================================================
-- TABEL: seller_profiles
-- ============================================================================
-- Profil tambahan khusus untuk seller/penjual.
-- Memiliki relasi one-to-one dengan tabel users.
-- ============================================================================

CREATE TABLE IF NOT EXISTS seller_profiles (
    -- Primary Key
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    
    -- Foreign Key ke users
    userId INT UNSIGNED NOT NULL COMMENT 'Referensi ke tabel users',
    
    -- Informasi Toko
    storeName VARCHAR(100) NOT NULL COMMENT 'Nama toko/warung',
    storeSlug VARCHAR(120) NOT NULL COMMENT 'URL-friendly nama toko',
    storeDescription TEXT DEFAULT NULL COMMENT 'Deskripsi toko',
    storeLogo VARCHAR(255) DEFAULT NULL COMMENT 'Path ke logo toko',
    storeBanner VARCHAR(255) DEFAULT NULL COMMENT 'Path ke banner toko',
    
    -- Lokasi
    address TEXT NOT NULL COMMENT 'Alamat lengkap toko',
    village VARCHAR(100) NOT NULL DEFAULT 'Way Huwi' COMMENT 'Desa/Kelurahan',
    district VARCHAR(100) NOT NULL DEFAULT 'Jati Agung' COMMENT 'Kecamatan',
    regency VARCHAR(100) NOT NULL DEFAULT 'Lampung Selatan' COMMENT 'Kabupaten/Kota',
    province VARCHAR(100) NOT NULL DEFAULT 'Lampung' COMMENT 'Provinsi',
    postalCode VARCHAR(10) DEFAULT NULL COMMENT 'Kode pos',
    latitude DECIMAL(10, 8) DEFAULT NULL COMMENT 'Koordinat latitude',
    longitude DECIMAL(11, 8) DEFAULT NULL COMMENT 'Koordinat longitude',
    
    -- Operasional
    operationalHours JSON DEFAULT NULL COMMENT 'Jam operasional dalam format JSON',
    isOpen TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Status buka/tutup toko',
    
    -- Verifikasi & Rating
    isVerified TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Status verifikasi seller',
    verifiedAt TIMESTAMP NULL DEFAULT NULL COMMENT 'Waktu verifikasi',
    rating DECIMAL(3, 2) DEFAULT 0.00 COMMENT 'Rating rata-rata (0.00 - 5.00)',
    totalReviews INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Jumlah total review',
    totalProducts INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Jumlah produk aktif',
    totalSales INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Total penjualan',
    
    -- Pembayaran
    bankName VARCHAR(50) DEFAULT NULL COMMENT 'Nama bank untuk pembayaran',
    bankAccountNumber VARCHAR(30) DEFAULT NULL COMMENT 'Nomor rekening',
    bankAccountName VARCHAR(100) DEFAULT NULL COMMENT 'Nama pemilik rekening',
    
    -- Tracking
    createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu pembuatan profil',
    updatedAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Waktu update terakhir',
    
    -- Constraints
    PRIMARY KEY (id),
    UNIQUE KEY uk_seller_profiles_user_id (userId),
    UNIQUE KEY uk_seller_profiles_store_slug (storeSlug),
    INDEX idx_seller_profiles_store_name (storeName),
    INDEX idx_seller_profiles_is_verified (isVerified),
    INDEX idx_seller_profiles_rating (rating),
    INDEX idx_seller_profiles_is_open (isOpen),
    
    -- Foreign Key Constraint
    CONSTRAINT fk_seller_profiles_user_id
        FOREIGN KEY (userId) 
        REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
        
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Profil seller/penjual WHFood';

-- ============================================================================
-- TABEL: products
-- ============================================================================
-- Tabel untuk menyimpan produk/menu makanan dari seller.
-- Memiliki relasi many-to-one dengan seller_profiles.
-- ============================================================================

CREATE TABLE IF NOT EXISTS products (
    -- Primary Key
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    
    -- Foreign Key ke seller_profiles
    sellerId INT UNSIGNED NOT NULL COMMENT 'Referensi ke tabel seller_profiles',
    
    -- Informasi Produk Dasar
    name VARCHAR(150) NOT NULL COMMENT 'Nama produk/menu',
    slug VARCHAR(170) NOT NULL COMMENT 'URL-friendly nama produk',
    description TEXT DEFAULT NULL COMMENT 'Deskripsi produk',
    
    -- Kategori
    category ENUM(
        'makanan_berat',
        'makanan_ringan', 
        'minuman',
        'dessert',
        'frozen_food',
        'sambal',
        'lainnya'
    ) NOT NULL DEFAULT 'lainnya' COMMENT 'Kategori produk',
    
    -- Harga
    price DECIMAL(12, 2) NOT NULL COMMENT 'Harga jual normal',
    discountPrice DECIMAL(12, 2) DEFAULT NULL COMMENT 'Harga setelah diskon (nullable)',
    discountPercentage TINYINT UNSIGNED DEFAULT NULL COMMENT 'Persentase diskon (0-100)',
    
    -- Gambar Produk
    primaryImage VARCHAR(255) DEFAULT NULL COMMENT 'Path gambar utama produk',
    images JSON DEFAULT NULL COMMENT 'Array path gambar tambahan dalam JSON',
    
    -- Stok & Ketersediaan
    stock INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Jumlah stok tersedia',
    minOrder TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Minimal pemesanan',
    maxOrder INT UNSIGNED DEFAULT NULL COMMENT 'Maksimal pemesanan per transaksi',
    unit VARCHAR(30) NOT NULL DEFAULT 'porsi' COMMENT 'Satuan produk (porsi, pcs, kg, dll)',
    
    -- Status
    status ENUM('active', 'inactive', 'soldout', 'deleted') NOT NULL DEFAULT 'active' COMMENT 'Status produk',
    isAvailable TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Ketersediaan produk',
    isFeatured TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Produk unggulan',
    
    -- Info Tambahan
    weight INT UNSIGNED DEFAULT NULL COMMENT 'Berat dalam gram (untuk pengiriman)',
    preparationTime INT UNSIGNED DEFAULT NULL COMMENT 'Waktu persiapan dalam menit',
    
    -- Nutrisi & Label (Opsional)
    nutritionInfo JSON DEFAULT NULL COMMENT 'Informasi nutrisi dalam JSON',
    tags JSON DEFAULT NULL COMMENT 'Tags produk dalam JSON (halal, vegetarian, dll)',
    
    -- Rating & Statistik
    rating DECIMAL(3, 2) DEFAULT 0.00 COMMENT 'Rating rata-rata (0.00 - 5.00)',
    totalReviews INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Jumlah review',
    totalSold INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Total terjual',
    viewCount INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Jumlah dilihat',
    
    -- Tracking
    createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu pembuatan produk',
    updatedAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Waktu update terakhir',
    
    -- Constraints
    PRIMARY KEY (id),
    UNIQUE KEY uk_products_seller_slug (sellerId, slug),
    INDEX idx_products_seller_id (sellerId),
    INDEX idx_products_category (category),
    INDEX idx_products_status (status),
    INDEX idx_products_price (price),
    INDEX idx_products_rating (rating),
    INDEX idx_products_is_available (isAvailable),
    INDEX idx_products_is_featured (isFeatured),
    INDEX idx_products_created_at (createdAt),
    
    -- Full-text search untuk pencarian produk
    FULLTEXT INDEX ft_products_search (name, description),
    
    -- Foreign Key Constraint
    CONSTRAINT fk_products_seller_id
        FOREIGN KEY (sellerId) 
        REFERENCES seller_profiles (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
        
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Produk/menu makanan WHFood';

-- ============================================================================
-- TRIGGER: Auto-update seller totalProducts
-- ============================================================================
-- Trigger untuk otomatis update jumlah produk di seller_profiles
-- ============================================================================

DELIMITER //

-- Trigger setelah INSERT produk
CREATE TRIGGER trg_products_after_insert
AFTER INSERT ON products
FOR EACH ROW
BEGIN
    IF NEW.status = 'active' THEN
        UPDATE seller_profiles 
        SET totalProducts = totalProducts + 1 
        WHERE id = NEW.sellerId;
    END IF;
END//

-- Trigger setelah UPDATE produk (status berubah)
CREATE TRIGGER trg_products_after_update
AFTER UPDATE ON products
FOR EACH ROW
BEGIN
    -- Jika dari non-active ke active
    IF OLD.status != 'active' AND NEW.status = 'active' THEN
        UPDATE seller_profiles 
        SET totalProducts = totalProducts + 1 
        WHERE id = NEW.sellerId;
    -- Jika dari active ke non-active
    ELSEIF OLD.status = 'active' AND NEW.status != 'active' THEN
        UPDATE seller_profiles 
        SET totalProducts = GREATEST(0, totalProducts - 1) 
        WHERE id = NEW.sellerId;
    END IF;
END//

-- Trigger setelah DELETE produk
CREATE TRIGGER trg_products_after_delete
AFTER DELETE ON products
FOR EACH ROW
BEGIN
    IF OLD.status = 'active' THEN
        UPDATE seller_profiles 
        SET totalProducts = GREATEST(0, totalProducts - 1) 
        WHERE id = OLD.sellerId;
    END IF;
END//

DELIMITER ;

-- ============================================================================
-- SAMPLE DATA (Opsional - untuk testing)
-- ============================================================================
-- Uncomment bagian ini jika ingin memasukkan data contoh
-- ============================================================================

/*
-- Insert sample user (password: 'password123')
INSERT INTO users (email, password, fullName, phoneNumber, role, status, emailVerifiedAt) VALUES
('seller@whfood.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Toko Makan Way Huwi', '081234567890', 'seller', 'active', NOW()),
('buyer@whfood.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Pembeli Demo', '081234567891', 'buyer', 'active', NOW());

-- Insert sample seller profile
INSERT INTO seller_profiles (userId, storeName, storeSlug, storeDescription, address, isVerified, isOpen) VALUES
(1, 'Warung Makan Bu Dewi', 'warung-makan-bu-dewi', 'Warung makan tradisional khas Lampung dengan cita rasa autentik', 'Jl. Way Huwi No. 123, Desa Way Huwi', 1, 1);

-- Insert sample products
INSERT INTO products (sellerId, name, slug, description, category, price, stock, unit, status, isAvailable) VALUES
(1, 'Nasi Uduk Komplit', 'nasi-uduk-komplit', 'Nasi uduk dengan ayam goreng, tempe orek, dan sambal', 'makanan_berat', 15000.00, 50, 'porsi', 'active', 1),
(1, 'Es Teh Manis', 'es-teh-manis', 'Es teh manis segar dengan gula aren', 'minuman', 5000.00, 100, 'gelas', 'active', 1),
(1, 'Pisang Goreng', 'pisang-goreng', 'Pisang goreng crispy dengan topping keju/coklat', 'makanan_ringan', 10000.00, 30, 'porsi', 'active', 1);
*/

-- ============================================================================
-- END OF SCHEMA
-- ============================================================================
