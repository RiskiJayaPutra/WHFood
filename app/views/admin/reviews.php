<?php
declare(strict_types=1);

requireAdmin();

$currentPage = 'reviews';
$db = Database::getInstance();

$filter = input('filter') ?: 'all';
$search = input('search') ?: '';
$tableExists = true;
$reviews = [];
$stats = ['total' => 0, 'visible' => 0, 'hidden' => 0, 'avgRating' => 0];

// Check if reviews table exists
try {
    $db->select("SELECT 1 FROM reviews LIMIT 1");
} catch (Exception $e) {
    $tableExists = false;
}

// Handle actions
if ($tableExists && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrfToken'] ?? null)) {
        flashError('Sesi tidak valid');
        redirect('admin/reviews');
    }
    
    $action = $_POST['action'] ?? '';
    $reviewId = (int)($_POST['reviewId'] ?? 0);
    
    if ($reviewId) {
        if ($action === 'approve') {
            $db->update('reviews', ['isVisible' => 1], 'id = ?', [$reviewId]);
            flashSuccess('Ulasan disetujui');
        } elseif ($action === 'hide') {
            $db->update('reviews', ['isVisible' => 0], 'id = ?', [$reviewId]);
            flashSuccess('Ulasan disembunyikan');
        } elseif ($action === 'delete') {
            $db->delete('reviews', 'id = ?', [$reviewId]);
            flashSuccess('Ulasan dihapus');
        }
    }
    
    redirect('admin/reviews' . ($filter !== 'all' ? '?filter=' . $filter : ''));
}

if ($tableExists) {
    // Build WHERE clause
    $whereClause = "1=1";
    $params = [];

    if ($filter === 'visible') {
        $whereClause .= " AND r.isVisible = 1";
    } elseif ($filter === 'hidden') {
        $whereClause .= " AND r.isVisible = 0";
    } elseif ($filter === 'high') {
        $whereClause .= " AND r.rating >= 4";
    } elseif ($filter === 'low') {
        $whereClause .= " AND r.rating <= 2";
    }

    if ($search) {
        $whereClause .= " AND (r.comment LIKE ? OR p.name LIKE ? OR u.fullName LIKE ?)";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
    }

    $reviews = $db->select("
        SELECT r.*, 
               p.name as productName, p.slug as productSlug, p.primaryImage as productImage,
               u.fullName as userName, u.email as userEmail
        FROM reviews r
        LEFT JOIN products p ON r.productId = p.id
        LEFT JOIN users u ON r.userId = u.id
        WHERE {$whereClause}
        ORDER BY r.createdAt DESC
        LIMIT 100
    ", $params);

    $stats = [
        'total' => $db->select("SELECT COUNT(*) as count FROM reviews")[0]['count'] ?? 0,
        'visible' => $db->select("SELECT COUNT(*) as count FROM reviews WHERE isVisible = 1")[0]['count'] ?? 0,
        'hidden' => $db->select("SELECT COUNT(*) as count FROM reviews WHERE isVisible = 0")[0]['count'] ?? 0,
        'avgRating' => $db->select("SELECT ROUND(AVG(rating), 1) as avg FROM reviews")[0]['avg'] ?? 0,
    ];
}

$pageTitle = 'Kelola Ulasan - Admin WHFood';
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
    
    <main class="ml-64 p-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Kelola Ulasan</h1>
                <p class="text-gray-500"><?= count($reviews) ?> ulasan ditemukan</p>
            </div>
        </div>
        
        <!-- Stats -->
        <div class="grid grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i data-lucide="message-square" class="w-5 h-5 text-blue-600"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900"><?= $stats['total'] ?></p>
                        <p class="text-sm text-gray-500">Total Ulasan</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <i data-lucide="eye" class="w-5 h-5 text-green-600"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900"><?= $stats['visible'] ?></p>
                        <p class="text-sm text-gray-500">Visible</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                        <i data-lucide="eye-off" class="w-5 h-5 text-gray-600"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900"><?= $stats['hidden'] ?></p>
                        <p class="text-sm text-gray-500">Hidden</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i data-lucide="star" class="w-5 h-5 text-yellow-600"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900"><?= $stats['avgRating'] ?: '0' ?></p>
                        <p class="text-sm text-gray-500">Rata-rata Rating</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Flash Messages -->
        <?php if ($success = flash('success')): ?>
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl flex items-center gap-3">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
                <span><?= e($success) ?></span>
            </div>
        <?php endif; ?>
        
        <!-- Filters & Search -->
        <div class="flex flex-wrap items-center gap-4 mb-6">
            <div class="flex items-center gap-2">
                <a href="<?= url('admin/reviews') ?>" 
                   class="px-4 py-2 rounded-xl font-medium transition-all <?= $filter === 'all' ? 'bg-primary-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' ?>">
                    Semua
                </a>
                <a href="<?= url('admin/reviews?filter=visible') ?>" 
                   class="px-4 py-2 rounded-xl font-medium transition-all <?= $filter === 'visible' ? 'bg-green-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' ?>">
                    Visible
                </a>
                <a href="<?= url('admin/reviews?filter=hidden') ?>" 
                   class="px-4 py-2 rounded-xl font-medium transition-all <?= $filter === 'hidden' ? 'bg-gray-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' ?>">
                    Hidden
                </a>
                <a href="<?= url('admin/reviews?filter=high') ?>" 
                   class="px-4 py-2 rounded-xl font-medium transition-all <?= $filter === 'high' ? 'bg-yellow-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' ?>">
                    Rating Tinggi
                </a>
                <a href="<?= url('admin/reviews?filter=low') ?>" 
                   class="px-4 py-2 rounded-xl font-medium transition-all <?= $filter === 'low' ? 'bg-orange-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' ?>">
                    Rating Rendah
                </a>
            </div>
            
            <form method="GET" class="flex-1 max-w-md ml-auto">
                <input type="hidden" name="filter" value="<?= e($filter) ?>">
                <div class="relative">
                    <input type="text" name="search" value="<?= e($search) ?>"
                           class="w-full px-4 py-2 pl-10 rounded-xl border border-gray-200 focus:border-primary-500 focus:outline-none"
                           placeholder="Cari ulasan, produk, atau pengguna...">
                    <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                </div>
            </form>
        </div>
        
        <!-- Reviews List -->
        <?php if (!$tableExists): ?>
            <div class="bg-orange-50 border border-orange-200 rounded-2xl p-8 text-center">
                <i data-lucide="alert-triangle" class="w-12 h-12 text-orange-500 mx-auto mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Tabel Reviews Belum Dibuat</h3>
                <p class="text-gray-600 mb-4">Silakan import file <code class="bg-gray-100 px-2 py-1 rounded">sql/reviews.sql</code> ke database</p>
                <p class="text-sm text-gray-500">Gunakan phpMyAdmin atau command line MySQL untuk import</p>
            </div>
        <?php elseif (empty($reviews)): ?>
            <div class="bg-white rounded-2xl p-12 text-center shadow-sm">
                <i data-lucide="message-square-off" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Tidak Ada Ulasan</h3>
                <p class="text-gray-500">Tidak ada ulasan dengan filter yang dipilih</p>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($reviews as $review): ?>
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 <?= !$review['isVisible'] ? 'opacity-60' : '' ?>">
                        <div class="flex gap-4">
                            <!-- Product Image -->
                            <div class="flex-shrink-0">
                                <?php if ($review['productImage']): ?>
                                    <img src="<?= e($review['productImage']) ?>" alt="" class="w-16 h-16 object-cover rounded-lg">
                                <?php else: ?>
                                    <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                                        <i data-lucide="image" class="w-6 h-6 text-gray-400"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-4 mb-2">
                                    <div>
                                        <h4 class="font-semibold text-gray-900"><?= e($review['productName'] ?? 'Produk Dihapus') ?></h4>
                                        <p class="text-sm text-gray-500">
                                            oleh <span class="font-medium"><?= e($review['userName'] ?? 'User') ?></span>
                                            • <?= date('d M Y H:i', strtotime($review['createdAt'])) ?>
                                        </p>
                                    </div>
                                    
                                    <!-- Rating Stars -->
                                    <div class="flex items-center gap-1">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i data-lucide="star" class="w-4 h-4 <?= $i <= $review['rating'] ? 'text-yellow-500 fill-yellow-500' : 'text-gray-300' ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                
                                <!-- Comment -->
                                <?php if ($review['comment']): ?>
                                    <p class="text-gray-700 mb-3"><?= nl2br(e($review['comment'])) ?></p>
                                <?php else: ?>
                                    <p class="text-gray-400 italic mb-3">Tidak ada komentar</p>
                                <?php endif; ?>
                                
                                <!-- Status & Actions -->
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <?php if ($review['isVisible']): ?>
                                            <span class="px-2.5 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full flex items-center gap-1">
                                                <i data-lucide="eye" class="w-3 h-3"></i> Visible
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2.5 py-1 bg-gray-100 text-gray-700 text-xs font-medium rounded-full flex items-center gap-1">
                                                <i data-lucide="eye-off" class="w-3 h-3"></i> Hidden
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="flex items-center gap-2">
                                        <?php if ($review['isVisible']): ?>
                                            <form method="POST" class="inline">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="action" value="hide">
                                                <input type="hidden" name="reviewId" value="<?= $review['id'] ?>">
                                                <button type="submit" class="px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors flex items-center gap-1">
                                                    <i data-lucide="eye-off" class="w-3.5 h-3.5"></i>
                                                    Sembunyikan
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" class="inline">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="action" value="approve">
                                                <input type="hidden" name="reviewId" value="<?= $review['id'] ?>">
                                                <button type="submit" class="px-3 py-1.5 text-xs font-medium text-green-600 bg-green-50 hover:bg-green-100 rounded-lg transition-colors flex items-center gap-1">
                                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                                    Tampilkan
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <?php if ($review['productSlug']): ?>
                                            <a href="<?= url('produk/' . $review['productSlug']) ?>" target="_blank"
                                               class="p-2 text-gray-500 hover:bg-gray-50 rounded-lg transition-colors" title="Lihat Produk">
                                                <i data-lucide="external-link" class="w-4 h-4"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <form method="POST" class="inline" onsubmit="return confirm('Hapus ulasan ini?')">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="reviewId" value="<?= $review['id'] ?>">
                                            <button type="submit" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
    
    <script>lucide.createIcons();</script>
</body>
</html>
