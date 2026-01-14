<?php
declare(strict_types=1);

requireSeller();

$currentPage = 'dashboard';
$seller = sellerProfile();
$currentUser = user();

// Get statistics
$db = Database::getInstance();

// Total produk aktif
$productStats = $db->selectOne(
    "SELECT COUNT(*) as total, SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active FROM products WHERE sellerId = ?",
    [$seller['id'] ?? 0]
);

// Produk terlaris (simulasi - nanti bisa dari order table)
$topProducts = $db->select(
    "SELECT name, totalSold, price, primaryImage FROM products WHERE sellerId = ? ORDER BY totalSold DESC LIMIT 5",
    [$seller['id'] ?? 0]
);

$pageTitle = 'Dashboard Penjual - WHFood';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#ecfdf5', 100: '#d1fae5', 200: '#a7f3d0',
                            300: '#6ee7b7', 400: '#34d399', 500: '#10b981',
                            600: '#059669', 700: '#047857', 800: '#065f46', 900: '#064e3b',
                        },
                        accent: { 500: '#f59e0b', 600: '#d97706', 700: '#b45309' }
                    },
                    fontFamily: { sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'] },
                }
            }
        }
    </script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen">
    
    <!-- Sidebar -->
    <?php require VIEWS_PATH . '/components/seller-sidebar.php'; ?>
    
    <!-- Main Content -->
    <main class="ml-64 p-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
            <p class="text-gray-500">Selamat datang kembali, <?= e($currentUser['fullName'] ?? 'Penjual') ?>!</p>
        </div>
        
        <!-- Flash Messages -->
        <?php if ($success = flash('success')): ?>
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl flex items-center gap-3">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
                <span><?= e($success) ?></span>
            </div>
        <?php endif; ?>
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Produk -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center">
                        <i data-lucide="package" class="w-6 h-6 text-primary-600"></i>
                    </div>
                    <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded-full">
                        <?= $productStats['active'] ?? 0 ?> aktif
                    </span>
                </div>
                <h3 class="text-3xl font-bold text-gray-900"><?= $productStats['total'] ?? 0 ?></h3>
                <p class="text-gray-500 text-sm">Total Produk</p>
            </div>
            
            <!-- Total Penjualan -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-accent-100 rounded-xl flex items-center justify-center">
                        <i data-lucide="shopping-cart" class="w-6 h-6 text-accent-600"></i>
                    </div>
                </div>
                <h3 class="text-3xl font-bold text-gray-900"><?= $seller['totalSales'] ?? 0 ?></h3>
                <p class="text-gray-500 text-sm">Total Penjualan</p>
            </div>
            
            <!-- Rating -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                        <i data-lucide="star" class="w-6 h-6 text-yellow-600"></i>
                    </div>
                </div>
                <h3 class="text-3xl font-bold text-gray-900"><?= number_format($seller['rating'] ?? 0, 1) ?></h3>
                <p class="text-gray-500 text-sm">Rating (<?= $seller['totalReviews'] ?? 0 ?> ulasan)</p>
            </div>
            
            <!-- Status Toko -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 <?= ($seller['isOpen'] ?? false) ? 'bg-green-100' : 'bg-red-100' ?> rounded-xl flex items-center justify-center">
                        <i data-lucide="<?= ($seller['isOpen'] ?? false) ? 'door-open' : 'door-closed' ?>" 
                           class="w-6 h-6 <?= ($seller['isOpen'] ?? false) ? 'text-green-600' : 'text-red-600' ?>"></i>
                    </div>
                </div>
                <h3 class="text-xl font-bold <?= ($seller['isOpen'] ?? false) ? 'text-green-600' : 'text-red-600' ?>">
                    <?= ($seller['isOpen'] ?? false) ? 'Buka' : 'Tutup' ?>
                </h3>
                <p class="text-gray-500 text-sm">Status Toko</p>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Quick Actions -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Aksi Cepat</h2>
                <div class="grid grid-cols-2 gap-4">
                    <a href="<?= url('seller/tambah-produk') ?>" 
                       class="flex flex-col items-center justify-center p-4 bg-primary-50 hover:bg-primary-100 rounded-xl transition-colors">
                        <i data-lucide="plus-circle" class="w-8 h-8 text-primary-600 mb-2"></i>
                        <span class="text-sm font-medium text-primary-700">Tambah Produk</span>
                    </a>
                    <a href="<?= url('seller/produk') ?>" 
                       class="flex flex-col items-center justify-center p-4 bg-gray-50 hover:bg-gray-100 rounded-xl transition-colors">
                        <i data-lucide="edit" class="w-8 h-8 text-gray-600 mb-2"></i>
                        <span class="text-sm font-medium text-gray-700">Kelola Produk</span>
                    </a>
                    <a href="<?= url('seller/profil') ?>" 
                       class="flex flex-col items-center justify-center p-4 bg-gray-50 hover:bg-gray-100 rounded-xl transition-colors">
                        <i data-lucide="settings" class="w-8 h-8 text-gray-600 mb-2"></i>
                        <span class="text-sm font-medium text-gray-700">Pengaturan Toko</span>
                    </a>
                    <a href="<?= url('penjual/' . ($seller['storeSlug'] ?? '')) ?>" target="_blank"
                       class="flex flex-col items-center justify-center p-4 bg-gray-50 hover:bg-gray-100 rounded-xl transition-colors">
                        <i data-lucide="external-link" class="w-8 h-8 text-gray-600 mb-2"></i>
                        <span class="text-sm font-medium text-gray-700">Lihat Toko</span>
                    </a>
                </div>
            </div>
            
            <!-- Top Products -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Produk Terlaris</h2>
                <?php if (empty($topProducts)): ?>
                    <div class="text-center py-8">
                        <i data-lucide="package-x" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
                        <p class="text-gray-500">Belum ada produk</p>
                        <a href="<?= url('seller/tambah-produk') ?>" class="text-primary-600 hover:underline text-sm">
                            Tambah produk pertama Anda
                        </a>
                    </div>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($topProducts as $product): ?>
                            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                                <img src="<?= uploadUrl($product['primaryImage']) ?>" 
                                     alt="<?= e($product['name']) ?>"
                                     class="w-12 h-12 rounded-lg object-cover">
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-medium text-gray-900 truncate"><?= e($product['name']) ?></h4>
                                    <p class="text-sm text-gray-500"><?= rupiah($product['price']) ?></p>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-medium text-gray-900"><?= $product['totalSold'] ?></span>
                                    <p class="text-xs text-gray-500">Terjual</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Store Verification Notice -->
        <?php if ($seller && !$seller['isVerified']): ?>
            <div class="mt-6 p-4 bg-amber-50 border border-amber-200 rounded-xl flex items-start gap-3">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5"></i>
                <div>
                    <h4 class="font-medium text-amber-800">Toko Belum Terverifikasi</h4>
                    <p class="text-sm text-amber-700 mt-1">
                        Toko Anda sedang dalam proses verifikasi oleh admin. 
                        Produk Anda akan muncul di halaman utama setelah toko terverifikasi.
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </main>
    
    <script>lucide.createIcons();</script>
</body>
</html>
