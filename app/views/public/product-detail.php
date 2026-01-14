<?php
declare(strict_types=1);

$productSlug = $GLOBALS['productSlug'] ?? '';

if (!$productSlug) {
    redirect('produk');
}

$db = Database::getInstance();

$product = $db->selectOne("
    SELECT p.*, sp.storeName, sp.storeSlug, sp.storeLogo, sp.isVerified as sellerVerified, 
           sp.rating as sellerRating, sp.totalProducts as sellerTotalProducts,
           u.phoneNumber as sellerPhone, u.fullName as sellerName
    FROM products p
    JOIN seller_profiles sp ON p.sellerId = sp.id
    JOIN users u ON sp.userId = u.id
    WHERE p.slug = ? AND p.status = 'active'
", [$productSlug]);

if (!$product) {
    http_response_code(404);
    require VIEWS_PATH . '/errors/404.php';
    exit;
}

$relatedProducts = $db->select("
    SELECT p.*, sp.storeName
    FROM products p
    JOIN seller_profiles sp ON p.sellerId = sp.id
    WHERE p.category = ? AND p.id != ? AND p.status = 'active'
    ORDER BY p.rating DESC
    LIMIT 4
", [$product['category'], $product['id']]);

$db->update('products', ['viewCount' => $product['viewCount'] + 1], 'id = ?', [$product['id']]);

$pageTitle = $product['name'] . ' - WHFood';
$discountedPrice = $product['discountPrice'] ? rupiah($product['discountPrice']) : rupiah($product['price']);
$waMessage = urlencode("Halo, saya mau pesan *{$product['name']}* ({$discountedPrice})");
$waLink = "https://wa.me/{$product['sellerPhone']}?text={$waMessage}";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e($product['description'] ?: $product['name'] . ' dari ' . $product['storeName']) ?>">
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
        .whatsapp-btn:hover { box-shadow: 0 10px 25px rgba(37, 211, 102, 0.3); }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    
    <header class="bg-white border-b border-gray-100 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center h-16">
                <a href="<?= url('produk') ?>" class="flex items-center gap-2 text-gray-600 hover:text-gray-900">
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
    
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid lg:grid-cols-2 gap-8">
            
            <!-- Product Image -->
            <div class="space-y-4">
                <div class="aspect-square rounded-2xl overflow-hidden bg-white shadow-sm">
                    <img src="<?= uploadUrl($product['primaryImage']) ?>" 
                         alt="<?= e($product['name']) ?>"
                         class="w-full h-full object-cover">
                </div>
                
                <!-- Additional images would go here -->
            </div>
            
            <!-- Product Info -->
            <div class="space-y-6">
                <!-- Badges -->
                <div class="flex flex-wrap gap-2">
                    <?php if ($product['sellerVerified']): ?>
                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-primary-100 text-primary-700 text-sm font-medium rounded-full">
                            <i data-lucide="badge-check" class="w-4 h-4"></i>
                            Penjual Terverifikasi
                        </span>
                    <?php endif; ?>
                    
                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-gray-100 text-gray-700 text-sm font-medium rounded-full">
                        <?= ucfirst(str_replace('_', ' ', $product['category'])) ?>
                    </span>
                    
                    <?php if ($product['discountPrice']): ?>
                        <span class="inline-flex items-center px-3 py-1 bg-accent-100 text-accent-700 text-sm font-bold rounded-full">
                            Diskon <?= $product['discountPercentage'] ?>%
                        </span>
                    <?php endif; ?>
                </div>
                
                <!-- Title -->
                <h1 class="text-3xl font-bold text-gray-900"><?= e($product['name']) ?></h1>
                
                <!-- Rating & Stats -->
                <div class="flex items-center gap-4 text-sm">
                    <div class="flex items-center gap-1">
                        <i data-lucide="star" class="w-5 h-5 text-accent-500 fill-accent-500"></i>
                        <span class="font-semibold"><?= number_format($product['rating'], 1) ?></span>
                        <span class="text-gray-400">(<?= $product['totalReviews'] ?> ulasan)</span>
                    </div>
                    <span class="text-gray-300">|</span>
                    <span class="text-gray-500"><?= $product['totalSold'] ?> terjual</span>
                    <span class="text-gray-300">|</span>
                    <span class="text-gray-500"><?= $product['viewCount'] ?>x dilihat</span>
                </div>
                
                <!-- Price -->
                <div class="py-4 border-y border-gray-100">
                    <?php if ($product['discountPrice']): ?>
                        <div class="flex items-center gap-3">
                            <span class="text-xl text-gray-400 line-through"><?= rupiah($product['price']) ?></span>
                            <span class="px-2 py-0.5 bg-accent-500 text-white text-sm font-bold rounded">
                                -<?= $product['discountPercentage'] ?>%
                            </span>
                        </div>
                        <div class="text-4xl font-bold text-primary-600 mt-1"><?= rupiah($product['discountPrice']) ?></div>
                    <?php else: ?>
                        <div class="text-4xl font-bold text-primary-600"><?= rupiah($product['price']) ?></div>
                    <?php endif; ?>
                    <p class="text-gray-500 mt-1">per <?= e($product['unit']) ?></p>
                </div>
                
                <!-- Description -->
                <?php if ($product['description']): ?>
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-2">Deskripsi</h3>
                        <p class="text-gray-600 leading-relaxed"><?= nl2br(e($product['description'])) ?></p>
                    </div>
                <?php endif; ?>
                
                <!-- Stock -->
                <div class="flex items-center gap-4">
                    <span class="text-gray-500">Stok:</span>
                    <?php if ($product['stock'] > 0): ?>
                        <span class="font-semibold text-green-600"><?= $product['stock'] ?> <?= e($product['unit']) ?> tersedia</span>
                    <?php else: ?>
                        <span class="font-semibold text-red-600">Habis</span>
                    <?php endif; ?>
                </div>
                
                <!-- Order Button -->
                <?php if ($product['stock'] > 0 && $product['sellerPhone']): ?>
                    <a href="<?= $waLink ?>" target="_blank"
                       class="whatsapp-btn w-full py-4 text-white font-bold text-lg rounded-xl flex items-center justify-center gap-3 transition-all hover:-translate-y-1">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        Pesan via WhatsApp
                    </a>
                <?php endif; ?>
                
                <!-- Seller Card -->
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <a href="<?= url('penjual/' . $product['storeSlug']) ?>" class="flex items-center gap-4">
                        <?php if ($product['storeLogo']): ?>
                            <img src="<?= uploadUrl($product['storeLogo']) ?>" alt="<?= e($product['storeName']) ?>"
                                 class="w-14 h-14 rounded-xl object-cover">
                        <?php else: ?>
                            <div class="w-14 h-14 rounded-xl bg-primary-100 flex items-center justify-center">
                                <span class="text-2xl font-bold text-primary-600"><?= mb_substr($product['storeName'], 0, 1) ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <h4 class="font-semibold text-gray-900"><?= e($product['storeName']) ?></h4>
                                <?php if ($product['sellerVerified']): ?>
                                    <i data-lucide="badge-check" class="w-4 h-4 text-primary-600"></i>
                                <?php endif; ?>
                            </div>
                            <div class="flex items-center gap-3 text-sm text-gray-500 mt-1">
                                <span class="flex items-center gap-1">
                                    <i data-lucide="star" class="w-4 h-4 text-accent-500"></i>
                                    <?= number_format($product['sellerRating'], 1) ?>
                                </span>
                                <span><?= $product['sellerTotalProducts'] ?> produk</span>
                            </div>
                        </div>
                        
                        <i data-lucide="chevron-right" class="w-5 h-5 text-gray-400"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Reviews Section -->
        <section class="mt-16">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Ulasan Produk</h2>
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-1">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i data-lucide="star" class="w-5 h-5 <?= $i <= round($product['rating']) ? 'fill-accent-500 text-accent-500' : 'text-gray-300' ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <span class="font-semibold text-gray-900"><?= number_format($product['rating'], 1) ?></span>
                    <span class="text-gray-500">(<?= $product['totalReviews'] ?> ulasan)</span>
                </div>
            </div>
            
            <?php
            $reviews = $db->select("
                SELECT r.*, u.fullName, u.profileImage
                FROM reviews r
                JOIN users u ON r.userId = u.id
                WHERE r.productId = ? AND r.isVisible = 1
                ORDER BY r.createdAt DESC
                LIMIT 10
            ", [$product['id']]);
            
            $userReview = null;
            if (isLoggedIn()) {
                $userReview = $db->selectOne(
                    "SELECT * FROM reviews WHERE productId = ? AND userId = ?",
                    [$product['id'], $_SESSION['userId']]
                );
            }
            ?>
            
            <?php if (isLoggedIn()): ?>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 mb-6">
                    <h3 class="font-semibold text-gray-900 mb-4">
                        <?= $userReview ? 'Edit Ulasan Anda' : 'Tulis Ulasan' ?>
                    </h3>
                    <form id="reviewForm" class="space-y-4">
                        <input type="hidden" name="csrfToken" value="<?= csrfToken() ?>">
                        <input type="hidden" name="productId" value="<?= $product['id'] ?>">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
                            <div class="flex items-center gap-2" id="ratingStars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <button type="button" onclick="setRating(<?= $i ?>)" 
                                            class="rating-star p-1 hover:scale-110 transition-transform"
                                            data-rating="<?= $i ?>">
                                        <i data-lucide="star" class="w-8 h-8 <?= ($userReview && $i <= $userReview['rating']) ? 'fill-accent-500 text-accent-500' : 'text-gray-300 hover:text-accent-400' ?>"></i>
                                    </button>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" name="rating" id="ratingInput" value="<?= $userReview['rating'] ?? '' ?>">
                        </div>
                        
                        <div>
                            <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">Komentar (opsional)</label>
                            <textarea id="comment" name="comment" rows="3"
                                      class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-100 outline-none resize-none"
                                      placeholder="Ceritakan pengalaman Anda dengan produk ini..."><?= e($userReview['comment'] ?? '') ?></textarea>
                        </div>
                        
                        <button type="submit" id="submitReviewBtn"
                                class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-all">
                            <?= $userReview ? 'Perbarui Ulasan' : 'Kirim Ulasan' ?>
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <div class="bg-gray-50 rounded-2xl p-6 text-center mb-6">
                    <p class="text-gray-600 mb-3">Silakan login untuk memberikan ulasan</p>
                    <a href="<?= url('masuk') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-all">
                        <i data-lucide="log-in" class="w-4 h-4"></i>
                        Login
                    </a>
                </div>
            <?php endif; ?>
            
            <?php if (empty($reviews)): ?>
                <div class="bg-white rounded-2xl p-8 text-center shadow-sm">
                    <i data-lucide="message-square" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
                    <p class="text-gray-500">Belum ada ulasan. Jadilah yang pertama!</p>
                </div>
            <?php else: ?>
                <div class="space-y-4" id="reviewsList">
                    <?php foreach ($reviews as $review): ?>
                        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center flex-shrink-0">
                                    <?php if ($review['profileImage']): ?>
                                        <img src="<?= uploadUrl($review['profileImage']) ?>" class="w-full h-full rounded-full object-cover">
                                    <?php else: ?>
                                        <span class="text-gray-600 font-semibold"><?= mb_substr($review['fullName'], 0, 1) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-1">
                                        <h4 class="font-semibold text-gray-900"><?= e($review['fullName']) ?></h4>
                                        <span class="text-sm text-gray-500"><?= timeAgo($review['createdAt']) ?></span>
                                    </div>
                                    <div class="flex items-center gap-1 mb-2">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i data-lucide="star" class="w-4 h-4 <?= $i <= $review['rating'] ? 'fill-accent-500 text-accent-500' : 'text-gray-300' ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <?php if ($review['comment']): ?>
                                        <p class="text-gray-600"><?= nl2br(e($review['comment'])) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
        
        <!-- Related Products -->
        <?php if (!empty($relatedProducts)): ?>
            <section class="mt-16">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Produk Serupa</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
                    <?php foreach ($relatedProducts as $related): ?>
                        <a href="<?= url('produk/' . $related['slug']) ?>" class="group bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all overflow-hidden">
                            <div class="aspect-square overflow-hidden">
                                <img src="<?= uploadUrl($related['primaryImage']) ?>" 
                                     alt="<?= e($related['name']) ?>"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                            </div>
                            <div class="p-4">
                                <p class="text-xs text-gray-500 mb-1"><?= e($related['storeName']) ?></p>
                                <h3 class="font-semibold text-gray-900 line-clamp-2 group-hover:text-primary-600"><?= e($related['name']) ?></h3>
                                <div class="mt-2 font-bold text-primary-600"><?= rupiah($related['discountPrice'] ?: $related['price']) ?></div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </main>
    
    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-8 mt-16">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p class="text-gray-400">&copy; <?= date('Y') ?> WHFood - Way Huwi Food Marketplace</p>
        </div>
    </footer>
    
    <script>
        lucide.createIcons();
        
        // Star rating interaction
        function setRating(rating) {
            document.getElementById('ratingInput').value = rating;
            const stars = document.querySelectorAll('.rating-star i');
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.classList.remove('text-gray-300', 'hover:text-accent-400');
                    star.classList.add('fill-accent-500', 'text-accent-500');
                } else {
                    star.classList.remove('fill-accent-500', 'text-accent-500');
                    star.classList.add('text-gray-300', 'hover:text-accent-400');
                }
            });
            lucide.createIcons();
        }
        
        // Review form submission
        const reviewForm = document.getElementById('reviewForm');
        if (reviewForm) {
            reviewForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const formData = new FormData(reviewForm);
                const rating = formData.get('rating');
                
                if (!rating) {
                    alert('Silakan pilih rating terlebih dahulu');
                    return;
                }
                
                const btn = document.getElementById('submitReviewBtn');
                btn.disabled = true;
                btn.textContent = 'Mengirim...';
                
                try {
                    const response = await fetch('<?= url('api/reviews') ?>', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams(formData)
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        alert('Ulasan berhasil disimpan!');
                        location.reload();
                    } else {
                        alert(result.message || 'Terjadi kesalahan');
                    }
                } catch (error) {
                    alert('Gagal mengirim ulasan. Coba lagi.');
                } finally {
                    btn.disabled = false;
                    btn.textContent = 'Kirim Ulasan';
                }
            });
        }
    </script>
</body>
</html>
