<?php
declare(strict_types=1);

$sellerSlug = $GLOBALS['sellerSlug'] ?? '';

if (!$sellerSlug) {
    redirect('penjual');
}

$db = Database::getInstance();


$seller = $db->selectOne("
    SELECT sp.*, u.phoneNumber, u.fullName as ownerName, u.email
    FROM seller_profiles sp
    JOIN users u ON sp.userId = u.id
    WHERE sp.storeSlug = ? AND sp.isVerified = 1
", [$sellerSlug]);

if (!$seller) {
    http_response_code(404);
    require VIEWS_PATH . '/errors/404.php';
    exit;
}


$products = $db->select("
    SELECT * FROM products 
    WHERE sellerId = ? AND status = 'active' AND isAvailable = 1
    ORDER BY totalSold DESC
", [$seller['id']]);

$pageTitle = $seller['storeName'] . ' - WHFood';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e($seller['storeDescription'] ?: $seller['storeName'] . ' - Toko makanan di Way Huwi') ?>">
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
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .whatsapp-btn { background: linear-gradient(135deg, #25D366 0%, #128C7E 100%); }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    
    <header class="bg-white border-b border-gray-100 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center h-16">
                <a href="<?= url('penjual') ?>" class="flex items-center gap-2 text-gray-600 hover:text-gray-900">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                    <span class="hidden sm:inline">Kembali</span>
                </a>
                
                <a href="<?= url('/') ?>" class="flex items-center gap-2 mx-auto">
                    <div class="w-10 h-10 bg-primary-600 rounded-xl flex items-center justify-center">
                        <i data-lucide="utensils" class="w-5 h-5 text-white"></i>
                    </div>
                    <span class="font-bold text-gray-900">WHFood</span>
                </a>
                
                <div class="w-20"></div>
            </div>
        </div>
    </header>
    
    <!-- Banner -->
    <div class="h-48 md:h-64 bg-gradient-to-br from-primary-400 to-primary-600 relative">
        <?php if ($seller['storeBanner']): ?>
            <img src="<?= uploadUrl($seller['storeBanner']) ?>" alt="" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
        <?php endif; ?>
    </div>
    
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-24 relative z-10">
        
        <!-- Store Info Card -->
        <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8">
            <div class="flex flex-col md:flex-row gap-6">
                <!-- Logo -->
                <?php if ($seller['storeLogo']): ?>
                    <img src="<?= uploadUrl($seller['storeLogo']) ?>" alt="<?= e($seller['storeName']) ?>"
                         class="w-28 h-28 rounded-2xl object-cover shadow-lg flex-shrink-0">
                <?php else: ?>
                    <div class="w-28 h-28 rounded-2xl bg-primary-100 shadow-lg flex items-center justify-center flex-shrink-0">
                        <span class="text-5xl font-bold text-primary-600"><?= mb_substr($seller['storeName'], 0, 1) ?></span>
                    </div>
                <?php endif; ?>
                
                <!-- Info -->
                <div class="flex-1">
                    <div class="flex flex-wrap items-center gap-3 mb-2">
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-900"><?= e($seller['storeName']) ?></h1>
                        <?php if ($seller['isVerified']): ?>
                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-primary-100 text-primary-700 text-sm font-medium rounded-full">
                                <i data-lucide="badge-check" class="w-4 h-4"></i>
                                Terverifikasi
                            </span>
                        <?php endif; ?>
                        <?php if ($seller['isOpen']): ?>
                            <span class="px-3 py-1 bg-green-100 text-green-700 text-sm font-medium rounded-full">Buka</span>
                        <?php else: ?>
                            <span class="px-3 py-1 bg-gray-100 text-gray-600 text-sm font-medium rounded-full">Tutup</span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($seller['storeDescription']): ?>
                        <p class="text-gray-600 mb-4"><?= e($seller['storeDescription']) ?></p>
                    <?php endif; ?>
                    
                    <!-- Stats -->
                    <div class="flex flex-wrap gap-6 mb-4">
                        <div class="flex items-center gap-2">
                            <i data-lucide="star" class="w-5 h-5 text-accent-500 fill-accent-500"></i>
                            <span class="font-semibold text-lg"><?= number_format((float)$seller['rating'], 1) ?></span>
                            <span class="text-gray-500">(<?= $seller['totalReviews'] ?> ulasan)</span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-500">
                            <i data-lucide="package" class="w-5 h-5"></i>
                            <span><?= $seller['totalProducts'] ?> produk</span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-500">
                            <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                            <span><?= $seller['totalSales'] ?> penjualan</span>
                        </div>
                    </div>
                    
                    <!-- Location & Owner -->
                    <div class="space-y-2 mt-4 pt-4 border-t border-gray-100">
                        <div class="flex items-center gap-2 text-gray-500">
                            <i data-lucide="map-pin" class="w-5 h-5 text-gray-400"></i>
                            <span><?= e($seller['address']) ?></span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-500">
                            <i data-lucide="user" class="w-5 h-5 text-gray-400"></i>
                            <span>Pemilik: <span class="font-medium text-gray-900"><?= e($seller['ownerName']) ?></span></span>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="flex flex-col gap-3 flex-shrink-0">
                    <?php if ($seller['phoneNumber']): ?>
                        <a href="https://wa.me/<?= $seller['phoneNumber'] ?>?text=<?= urlencode('Halo, saya dari WHFood') ?>" 
                           target="_blank"
                           class="whatsapp-btn px-6 py-3 text-white font-semibold rounded-xl flex items-center justify-center gap-2 hover:shadow-lg transition-all">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                            Hubungi
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Map -->
        <?php if ($seller['latitude'] && $seller['longitude']): ?>
            <div class="mt-8 bg-white rounded-2xl shadow-sm overflow-hidden">
                <a href="https://www.google.com/maps/dir/?api=1&destination=<?= $seller['latitude'] ?>,<?= $seller['longitude'] ?>" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   class="block relative group">
                    <div id="storeMap" class="w-full h-64"></div>
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors flex items-center justify-center">
                        <div class="opacity-0 group-hover:opacity-100 transition-opacity bg-white/90 backdrop-blur px-4 py-2 rounded-xl flex items-center gap-2 shadow-lg">
                            <i data-lucide="navigation" class="w-5 h-5 text-primary-600"></i>
                            <span class="font-semibold text-gray-900">Buka di Google Maps</span>
                        </div>
                    </div>
                </a>
            </div>
        <?php endif; ?>
        
        <!-- Products -->
        <section class="mt-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Produk dari <?= e($seller['storeName']) ?></h2>
            
            <?php if (empty($products)): ?>
                <div class="bg-white rounded-2xl p-12 text-center">
                    <i data-lucide="package-x" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Belum Ada Produk</h3>
                    <p class="text-gray-500">Penjual belum menambahkan produk</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
                    <?php foreach ($products as $product): ?>
                        <a href="<?= url('produk/' . $product['slug']) ?>" class="group bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all overflow-hidden">
                            <div class="aspect-square overflow-hidden">
                                <img src="<?= uploadUrl($product['primaryImage']) ?>" 
                                     alt="<?= e($product['name']) ?>"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                            </div>
                            <div class="p-4">
                                <h3 class="font-semibold text-gray-900 line-clamp-2 group-hover:text-primary-600"><?= e($product['name']) ?></h3>
                                <div class="flex items-center gap-1 mt-2">
                                    <i data-lucide="star" class="w-4 h-4 text-accent-500 fill-accent-500"></i>
                                    <span class="text-sm"><?= number_format((float)$product['rating'], 1) ?></span>
                                    <span class="text-xs text-gray-400">(<?= $product['totalSold'] ?> terjual)</span>
                                </div>
                                <div class="mt-2">
                                    <?php if ($product['discountPrice']): ?>
                                        <span class="text-xs text-gray-400 line-through"><?= rupiah((float)$product['price']) ?></span>
                                        <div class="text-lg font-bold text-primary-600"><?= rupiah((float)$product['discountPrice']) ?></div>
                                    <?php else: ?>
                                        <div class="text-lg font-bold text-primary-600"><?= rupiah((float)$product['price']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
    
    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-8 mt-16">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p class="text-gray-400">&copy; <?= date('Y') ?> WHFood - Way Huwi Food Marketplace</p>
        </div>
    </footer>
    
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        lucide.createIcons();
        
        <?php if ($seller['latitude'] && $seller['longitude']): ?>
        // Map
        const lat = <?= $seller['latitude'] ?>;
        const lng = <?= $seller['longitude'] ?>;
        const map = L.map('storeMap').setView([lat, lng], 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);
        L.marker([lat, lng]).addTo(map)
            .bindPopup('<strong><?= e($seller['storeName']) ?></strong><br><?= e($seller['address']) ?>');
        <?php endif; ?>
    </script>
</body>
</html>
