<?php
declare(strict_types=1);

$db = Database::getInstance();

// Filters
$search = input('cari');
$filter = input('filter') ?: 'semua';

// Build query
$where = ["sp.isVerified = 1", "u.status = 'active'"];
$params = [];

if ($search) {
    $where[] = "(sp.storeName LIKE ? OR sp.storeDescription LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$whereClause = implode(' AND ', $where);

$orderBy = match ($filter) {
    'rating' => 'sp.rating DESC',
    'produk' => 'sp.totalProducts DESC',
    'terlaris' => 'sp.totalSales DESC',
    default => 'sp.createdAt DESC'
};

// Get sellers
$sellers = $db->select("
    SELECT sp.*, u.phoneNumber, u.fullName as ownerName
    FROM seller_profiles sp
    JOIN users u ON sp.userId = u.id
    WHERE {$whereClause}
    ORDER BY {$orderBy}
    LIMIT 20
", $params);

$pageTitle = 'Daftar Penjual - WHFood';
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
    
    <header class="bg-white border-b border-gray-100 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="<?= url('/') ?>" class="flex items-center gap-2">
                    <div class="w-10 h-10 bg-primary-600 rounded-xl flex items-center justify-center">
                        <i data-lucide="utensils" class="w-5 h-5 text-white"></i>
                    </div>
                    <span class="font-bold text-gray-900">WHFood</span>
                </a>
                
                <form method="GET" class="flex-1 max-w-xl mx-8 hidden md:block">
                    <div class="relative">
                        <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"></i>
                        <input type="text" name="cari" value="<?= e($search) ?>"
                               placeholder="Cari nama toko..."
                               class="w-full pl-12 pr-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-100 outline-none">
                    </div>
                </form>
                
                <div class="flex items-center gap-4">
                    <a href="<?= url('produk') ?>" class="text-gray-600 hover:text-gray-900 font-medium hidden sm:block">Produk</a>
                    <?php if (isLoggedIn()): ?>
                        <?php if (isSeller()): ?>
                            <a href="<?= url('seller/dashboard') ?>" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg">Dashboard</a>
                        <?php else: ?>
                            <a href="<?= url('keluar') ?>" class="text-gray-600 hover:text-gray-900 font-medium">Keluar</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="<?= url('masuk') ?>" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg">Masuk</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
    
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Title -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Daftar Penjual</h1>
            <p class="text-gray-500"><?= count($sellers) ?> penjual terverifikasi di Way Huwi</p>
        </div>
        
        <!-- Filters -->
        <div class="flex flex-wrap items-center gap-3 mb-6">
            <a href="<?= url('penjual') ?>" 
               class="px-4 py-2 rounded-xl font-medium transition-all <?= $filter === 'semua' ? 'bg-primary-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' ?>">
                Semua
            </a>
            <a href="<?= url('penjual?filter=rating') ?>" 
               class="px-4 py-2 rounded-xl font-medium transition-all <?= $filter === 'rating' ? 'bg-primary-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' ?>">
                Rating Tertinggi
            </a>
            <a href="<?= url('penjual?filter=produk') ?>" 
               class="px-4 py-2 rounded-xl font-medium transition-all <?= $filter === 'produk' ? 'bg-primary-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' ?>">
                Terbanyak Produk
            </a>
            <a href="<?= url('penjual?filter=terlaris') ?>" 
               class="px-4 py-2 rounded-xl font-medium transition-all <?= $filter === 'terlaris' ? 'bg-primary-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' ?>">
                Terlaris
            </a>
        </div>
        
        <!-- Sellers Grid -->
        <?php if (empty($sellers)): ?>
            <div class="text-center py-16">
                <i data-lucide="store" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Penjual Tidak Ditemukan</h3>
                <p class="text-gray-500">Coba ubah kata kunci pencarian</p>
            </div>
        <?php else: ?>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($sellers as $seller): ?>
                    <a href="<?= url('penjual/' . $seller['storeSlug']) ?>" 
                       class="group bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all overflow-hidden">
                        <!-- Banner -->
                        <div class="h-32 bg-gradient-to-br from-primary-400 to-primary-600 relative">
                            <?php if ($seller['storeBanner']): ?>
                                <img src="<?= uploadUrl($seller['storeBanner']) ?>" alt="" class="w-full h-full object-cover">
                            <?php endif; ?>
                            
                            <!-- Status Badge -->
                            <div class="absolute top-3 right-3">
                                <?php if ($seller['isOpen']): ?>
                                    <span class="px-2 py-1 bg-green-500 text-white text-xs font-medium rounded-full">Buka</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 bg-gray-500 text-white text-xs font-medium rounded-full">Tutup</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="p-5 pt-0 -mt-10 relative">
                            <!-- Logo -->
                            <?php if ($seller['storeLogo']): ?>
                                <img src="<?= uploadUrl($seller['storeLogo']) ?>" alt="<?= e($seller['storeName']) ?>"
                                     class="w-20 h-20 rounded-xl object-cover border-4 border-white shadow-lg">
                            <?php else: ?>
                                <div class="w-20 h-20 rounded-xl bg-primary-100 border-4 border-white shadow-lg flex items-center justify-center">
                                    <span class="text-3xl font-bold text-primary-600"><?= mb_substr($seller['storeName'], 0, 1) ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mt-4">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-gray-900 text-lg group-hover:text-primary-600 transition-colors">
                                        <?= e($seller['storeName']) ?>
                                    </h3>
                                    <?php if ($seller['isVerified']): ?>
                                        <i data-lucide="badge-check" class="w-5 h-5 text-primary-600"></i>
                                    <?php endif; ?>
                                </div>
                                
                                <p class="text-gray-500 text-sm mt-1 line-clamp-2">
                                    <?= e($seller['storeDescription'] ?: $seller['address']) ?>
                                </p>
                                
                                <div class="flex items-center gap-4 mt-4 text-sm">
                                    <div class="flex items-center gap-1">
                                        <i data-lucide="star" class="w-4 h-4 text-accent-500 fill-accent-500"></i>
                                        <span class="font-semibold"><?= number_format((float)($seller['rating'] ?? 0), 1) ?></span>
                                    </div>
                                    <span class="text-gray-300">|</span>
                                    <span class="text-gray-500"><?= $seller['totalProducts'] ?> produk</span>
                                    <span class="text-gray-300">|</span>
                                    <span class="text-gray-500"><?= $seller['totalSales'] ?> penjualan</span>
                                </div>
                                
                                <div class="flex items-center gap-1 text-gray-400 text-sm mt-3">
                                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                                    <span><?= e($seller['village']) ?>, <?= e($seller['district']) ?></span>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <!-- CTA -->
        <div class="mt-16 bg-gradient-to-r from-primary-600 to-primary-700 rounded-2xl p-8 text-center text-white">
            <h2 class="text-2xl font-bold mb-3">Ingin Bergabung sebagai Penjual?</h2>
            <p class="text-primary-100 mb-6 max-w-xl mx-auto">
                Daftarkan toko Anda dan mulai jual makanan ke pelanggan di sekitar Way Huwi
            </p>
            <a href="<?= url('daftar-penjual') ?>" 
               class="inline-flex items-center gap-2 px-8 py-3 bg-white text-primary-600 font-bold rounded-xl hover:bg-primary-50 transition-colors">
                <i data-lucide="store" class="w-5 h-5"></i>
                Daftar Sekarang
            </a>
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-8 mt-16">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p class="text-gray-400">&copy; <?= date('Y') ?> WHFood - Way Huwi Food Marketplace</p>
        </div>
    </footer>
    
    <script>lucide.createIcons();</script>
</body>
</html>
