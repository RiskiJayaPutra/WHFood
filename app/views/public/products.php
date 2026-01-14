<?php
declare(strict_types=1);

$db = Database::getInstance();

// Filters
$category = input('kategori');
$search = input('cari');
$sort = input('urut') ?: 'terbaru';

// Build query
$where = ["p.status = 'active'", "p.isAvailable = 1", "sp.isVerified = 1"];
$params = [];

if ($category) {
    $where[] = "p.category = ?";
    $params[] = $category;
}

if ($search) {
    $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$whereClause = implode(' AND ', $where);

$orderBy = match ($sort) {
    'populer' => 'p.totalSold DESC',
    'rating' => 'p.rating DESC',
    'harga-rendah' => 'COALESCE(p.discountPrice, p.price) ASC',
    'harga-tinggi' => 'COALESCE(p.discountPrice, p.price) DESC',
    default => 'p.createdAt DESC'
};

// Get products
$products = $db->select("
    SELECT p.*, sp.storeName, sp.storeSlug, sp.isVerified as sellerVerified, u.phoneNumber as sellerPhone
    FROM products p
    JOIN seller_profiles sp ON p.sellerId = sp.id
    JOIN users u ON sp.userId = u.id
    WHERE {$whereClause}
    ORDER BY {$orderBy}
    LIMIT 24
", $params);

// Categories
$categories = [
    'makanan_berat' => 'Makanan Berat',
    'makanan_ringan' => 'Makanan Ringan',
    'minuman' => 'Minuman',
    'dessert' => 'Dessert',
    'frozen_food' => 'Frozen Food',
    'sambal' => 'Sambal',
    'lainnya' => 'Lainnya'
];

$pageTitle = 'Semua Produk - WHFood';
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
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .whatsapp-btn { background: linear-gradient(135deg, #25D366 0%, #128C7E 100%); }
    </style>
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
                
                <!-- Search -->
                <form method="GET" class="flex-1 max-w-xl mx-8 hidden md:block">
                    <div class="relative">
                        <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"></i>
                        <input type="text" name="cari" value="<?= e($search) ?>"
                               placeholder="Cari makanan atau minuman..."
                               class="w-full pl-12 pr-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-100 outline-none">
                    </div>
                </form>
                
                <!-- Nav -->
                <div class="flex items-center gap-4">
                    <a href="<?= url('penjual') ?>" class="text-gray-600 hover:text-gray-900 font-medium hidden sm:block">Penjual</a>
                    <?php if (isLoggedIn()): ?>
                        <?php if (isSeller()): ?>
                            <a href="<?= url('seller/dashboard') ?>" class="text-gray-600 hover:text-gray-900 font-medium">Dashboard</a>
                        <?php endif; ?>
                        <a href="<?= url('keluar') ?>" class="text-gray-600 hover:text-gray-900 font-medium">Keluar</a>
                    <?php else: ?>
                        <a href="<?= url('masuk') ?>" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">Masuk</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
    
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Title -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">
                <?= $search ? 'Hasil Pencarian: "' . e($search) . '"' : 'Semua Produk' ?>
            </h1>
            <p class="text-gray-500"><?= count($products) ?> produk ditemukan</p>
        </div>
        
        <!-- Filters -->
        <div class="flex flex-wrap items-center gap-3 mb-6">
            <!-- Categories -->
            <a href="<?= url('produk') ?>" 
               class="px-4 py-2 rounded-xl font-medium transition-all <?= !$category ? 'bg-primary-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' ?>">
                Semua
            </a>
            <?php foreach ($categories as $key => $label): ?>
                <a href="<?= url('produk?kategori=' . $key) ?>" 
                   class="px-4 py-2 rounded-xl font-medium transition-all <?= $category === $key ? 'bg-primary-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' ?>">
                    <?= $label ?>
                </a>
            <?php endforeach; ?>
            
            <!-- Sort -->
            <div class="ml-auto">
                <select onchange="location.href=this.value" class="px-4 py-2 rounded-xl border border-gray-200 bg-white text-gray-700 focus:border-primary-500 outline-none">
                    <option value="<?= url('produk?' . http_build_query(array_merge($_GET, ['urut' => 'terbaru']))) ?>" <?= $sort === 'terbaru' ? 'selected' : '' ?>>Terbaru</option>
                    <option value="<?= url('produk?' . http_build_query(array_merge($_GET, ['urut' => 'populer']))) ?>" <?= $sort === 'populer' ? 'selected' : '' ?>>Terpopuler</option>
                    <option value="<?= url('produk?' . http_build_query(array_merge($_GET, ['urut' => 'rating']))) ?>" <?= $sort === 'rating' ? 'selected' : '' ?>>Rating Tertinggi</option>
                    <option value="<?= url('produk?' . http_build_query(array_merge($_GET, ['urut' => 'harga-rendah']))) ?>" <?= $sort === 'harga-rendah' ? 'selected' : '' ?>>Harga Terendah</option>
                    <option value="<?= url('produk?' . http_build_query(array_merge($_GET, ['urut' => 'harga-tinggi']))) ?>" <?= $sort === 'harga-tinggi' ? 'selected' : '' ?>>Harga Tertinggi</option>
                </select>
            </div>
        </div>
        
        <!-- Products Grid -->
        <?php if (empty($products)): ?>
            <div class="text-center py-16">
                <i data-lucide="search-x" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Produk Tidak Ditemukan</h3>
                <p class="text-gray-500">Coba ubah filter atau kata kunci pencarian</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
                <?php foreach ($products as $product): ?>
                    <article class="group bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden">
                        <!-- Image -->
                        <a href="<?= url('produk/' . $product['slug']) ?>" class="block relative aspect-square overflow-hidden">
                            <img src="<?= uploadUrl($product['primaryImage']) ?>" 
                                 alt="<?= e($product['name']) ?>"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            
                            <?php if ($product['sellerVerified']): ?>
                                <div class="absolute top-2 left-2 flex items-center gap-1 px-2 py-1 bg-primary-600 text-white text-xs font-medium rounded-full">
                                    <i data-lucide="badge-check" class="w-3 h-3"></i>
                                    Terverifikasi
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($product['discountPrice']): ?>
                                <div class="absolute top-2 right-2 px-2 py-1 bg-accent-500 text-white text-xs font-bold rounded-lg">
                                    -<?= $product['discountPercentage'] ?>%
                                </div>
                            <?php endif; ?>
                        </a>
                        
                        <!-- Content -->
                        <div class="p-4">
                            <a href="<?= url('penjual/' . $product['storeSlug']) ?>" class="text-xs text-gray-500 hover:text-primary-600 flex items-center gap-1 mb-1">
                                <i data-lucide="store" class="w-3 h-3"></i>
                                <?= e($product['storeName']) ?>
                            </a>
                            
                            <a href="<?= url('produk/' . $product['slug']) ?>">
                                <h3 class="font-semibold text-gray-900 line-clamp-2 group-hover:text-primary-600 transition-colors">
                                    <?= e($product['name']) ?>
                                </h3>
                            </a>
                            
                            <div class="flex items-center gap-1 mt-2">
                                <i data-lucide="star" class="w-4 h-4 text-accent-500 fill-accent-500"></i>
                                <span class="text-sm font-medium"><?= number_format($product['rating'], 1) ?></span>
                                <span class="text-xs text-gray-400">(<?= $product['totalSold'] ?> terjual)</span>
                            </div>
                            
                            <div class="mt-3 flex items-end justify-between">
                                <div>
                                    <?php if ($product['discountPrice']): ?>
                                        <span class="text-xs text-gray-400 line-through"><?= rupiah($product['price']) ?></span>
                                        <div class="text-lg font-bold text-primary-600"><?= rupiah($product['discountPrice']) ?></div>
                                    <?php else: ?>
                                        <div class="text-lg font-bold text-primary-600"><?= rupiah($product['price']) ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($product['sellerPhone']): ?>
                                    <a href="https://wa.me/<?= $product['sellerPhone'] ?>?text=<?= urlencode('Halo, saya mau pesan ' . $product['name']) ?>" 
                                       target="_blank"
                                       class="whatsapp-btn p-2 text-white rounded-lg hover:shadow-lg transition-all">
                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                        </svg>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
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
