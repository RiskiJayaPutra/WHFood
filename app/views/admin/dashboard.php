<?php
declare(strict_types=1);

requireAdmin();

$currentPage = 'dashboard';
$db = Database::getInstance();

$stats = [
    'totalUsers' => $db->selectOne("SELECT COUNT(*) as count FROM users")['count'] ?? 0,
    'totalSellers' => $db->selectOne("SELECT COUNT(*) as count FROM users WHERE role = 'seller'")['count'] ?? 0,
    'pendingSellers' => $db->selectOne("SELECT COUNT(*) as count FROM users WHERE role = 'seller' AND status = 'pending'")['count'] ?? 0,
    'verifiedSellers' => $db->selectOne("SELECT COUNT(*) as count FROM seller_profiles WHERE isVerified = 1")['count'] ?? 0,
    'totalProducts' => $db->selectOne("SELECT COUNT(*) as count FROM products WHERE status = 'active'")['count'] ?? 0,
    'totalReviews' => 0,
];

// Try to get reviews count (table may not exist yet)
try {
    $stats['totalReviews'] = $db->selectOne("SELECT COUNT(*) as count FROM reviews")['count'] ?? 0;
} catch (Exception $e) {
    $stats['totalReviews'] = 0;
}

$recentSellers = $db->select("
    SELECT u.id, u.fullName, u.email, u.status, u.createdAt, sp.storeName, sp.isVerified
    FROM users u
    LEFT JOIN seller_profiles sp ON u.id = sp.userId
    WHERE u.role = 'seller'
    ORDER BY u.createdAt DESC
    LIMIT 5
");

$pageTitle = 'Admin Dashboard - WHFood';
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
                        }
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
    
    <?php require VIEWS_PATH . '/components/admin-sidebar.php'; ?>
    
    <main class="transition-all duration-300 md:ml-64 p-4 md:p-8">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Dashboard Admin</h1>
            <p class="text-gray-500">Selamat datang di panel administrasi WHFood</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                        <i data-lucide="users" class="w-6 h-6 text-blue-600"></i>
                    </div>
                </div>
                <h3 class="text-3xl font-bold text-gray-900"><?= $stats['totalUsers'] ?></h3>
                <p class="text-gray-500 text-sm">Total Pengguna</p>
            </div>
            
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                        <i data-lucide="store" class="w-6 h-6 text-green-600"></i>
                    </div>
                    <?php if ($stats['pendingSellers'] > 0): ?>
                        <span class="text-xs font-medium text-orange-600 bg-orange-50 px-2 py-1 rounded-full">
                            <?= $stats['pendingSellers'] ?> pending
                        </span>
                    <?php endif; ?>
                </div>
                <h3 class="text-3xl font-bold text-gray-900"><?= $stats['totalSellers'] ?></h3>
                <p class="text-gray-500 text-sm">Total Penjual</p>
            </div>
            
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                        <i data-lucide="package" class="w-6 h-6 text-purple-600"></i>
                    </div>
                </div>
                <h3 class="text-3xl font-bold text-gray-900"><?= $stats['totalProducts'] ?></h3>
                <p class="text-gray-500 text-sm">Total Produk Aktif</p>
            </div>
            
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                        <i data-lucide="star" class="w-6 h-6 text-yellow-600"></i>
                    </div>
                </div>
                <h3 class="text-3xl font-bold text-gray-900"><?= $stats['totalReviews'] ?></h3>
                <p class="text-gray-500 text-sm">Total Ulasan</p>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-900">Penjual Terbaru</h2>
                    <a href="<?= url('admin/sellers') ?>" class="text-sm text-primary-600 hover:underline">Lihat Semua</a>
                </div>
                
                <?php if (empty($recentSellers)): ?>
                    <div class="text-center py-8">
                        <i data-lucide="store" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
                        <p class="text-gray-500">Belum ada penjual terdaftar</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($recentSellers as $seller): ?>
                            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                                <div class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center">
                                    <span class="text-gray-600 font-semibold"><?= mb_substr($seller['storeName'] ?? $seller['fullName'], 0, 1) ?></span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-medium text-gray-900 truncate"><?= e($seller['storeName'] ?? $seller['fullName']) ?></h4>
                                    <p class="text-xs text-gray-500"><?= e($seller['email']) ?></p>
                                </div>
                                <div>
                                    <?php if ($seller['status'] === 'pending'): ?>
                                        <span class="px-2 py-1 bg-orange-100 text-orange-700 text-xs font-medium rounded-full">Pending</span>
                                    <?php elseif ($seller['isVerified']): ?>
                                        <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full">Verified</span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs font-medium rounded-full">Active</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Aksi Cepat</h2>
                <div class="grid grid-cols-2 gap-4">
                    <a href="<?= url('admin/sellers?filter=pending') ?>" 
                       class="flex flex-col items-center justify-center p-4 bg-orange-50 hover:bg-orange-100 rounded-xl transition-colors">
                        <i data-lucide="user-check" class="w-8 h-8 text-orange-600 mb-2"></i>
                        <span class="text-sm font-medium text-orange-700">Verifikasi Penjual</span>
                        <?php if ($stats['pendingSellers'] > 0): ?>
                            <span class="text-xs text-orange-500 mt-1"><?= $stats['pendingSellers'] ?> menunggu</span>
                        <?php endif; ?>
                    </a>
                    <a href="<?= url('admin/users') ?>" 
                       class="flex flex-col items-center justify-center p-4 bg-blue-50 hover:bg-blue-100 rounded-xl transition-colors">
                        <i data-lucide="users" class="w-8 h-8 text-blue-600 mb-2"></i>
                        <span class="text-sm font-medium text-blue-700">Kelola Pengguna</span>
                    </a>
                    <a href="<?= url('admin/products') ?>" 
                       class="flex flex-col items-center justify-center p-4 bg-purple-50 hover:bg-purple-100 rounded-xl transition-colors">
                        <i data-lucide="package" class="w-8 h-8 text-purple-600 mb-2"></i>
                        <span class="text-sm font-medium text-purple-700">Lihat Produk</span>
                    </a>
                    <a href="<?= url('/') ?>" target="_blank"
                       class="flex flex-col items-center justify-center p-4 bg-gray-50 hover:bg-gray-100 rounded-xl transition-colors">
                        <i data-lucide="external-link" class="w-8 h-8 text-gray-600 mb-2"></i>
                        <span class="text-sm font-medium text-gray-700">Lihat Website</span>
                    </a>
                </div>
            </div>
        </div>
    </main>
    
    <script>lucide.createIcons();</script>
</body>
</html>
