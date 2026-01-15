<?php
declare(strict_types=1);

requireAdmin();

$currentPage = 'products';
$db = Database::getInstance();

$filter = input('filter') ?: 'all';
$search = input('search') ?: '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrfToken'] ?? null)) {
        flashError('Sesi tidak valid');
        redirect('admin/products');
    }
    
    $action = $_POST['action'] ?? '';
    $productId = (int)($_POST['productId'] ?? 0);
    
    if ($productId) {
        if ($action === 'activate') {
            $db->update('products', ['status' => 'active'], 'id = ?', [$productId]);
            flashSuccess('Produk diaktifkan');
        } elseif ($action === 'deactivate') {
            $db->update('products', ['status' => 'inactive'], 'id = ?', [$productId]);
            flashSuccess('Produk dinonaktifkan');
        } elseif ($action === 'delete') {
            $db->delete('products', 'id = ?', [$productId]);
            flashSuccess('Produk dihapus');
        }
    }
    
    redirect('admin/products' . ($filter !== 'all' ? '?filter=' . $filter : ''));
}

// Build WHERE clause
$whereClause = "1=1";
$params = [];

if ($filter === 'active') {
    $whereClause .= " AND p.status = 'active'";
} elseif ($filter === 'inactive') {
    $whereClause .= " AND p.status = 'inactive'";
}

if ($search) {
    $whereClause .= " AND (p.name LIKE ? OR sp.storeName LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$products = $db->select("
    SELECT p.*, sp.storeName, sp.storeSlug, u.fullName as sellerName
    FROM products p
    LEFT JOIN seller_profiles sp ON p.sellerId = sp.id
    LEFT JOIN users u ON sp.userId = u.id
    WHERE {$whereClause}
    ORDER BY p.createdAt DESC
    LIMIT 100
", $params);

$stats = [
    'total' => $db->select("SELECT COUNT(*) as count FROM products")[0]['count'] ?? 0,
    'active' => $db->select("SELECT COUNT(*) as count FROM products WHERE status = 'active'")[0]['count'] ?? 0,
    'inactive' => $db->select("SELECT COUNT(*) as count FROM products WHERE status = 'inactive'")[0]['count'] ?? 0,
];

$pageTitle = 'Kelola Produk - Admin WHFood';
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
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Kelola Produk</h1>
                <p class="text-gray-500"><?= count($products) ?> produk ditemukan</p>
            </div>
        </div>
        
        <!-- Stats -->
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i data-lucide="package" class="w-5 h-5 text-blue-600"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900"><?= $stats['total'] ?></p>
                        <p class="text-sm text-gray-500">Total Produk</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900"><?= $stats['active'] ?></p>
                        <p class="text-sm text-gray-500">Aktif</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                        <i data-lucide="pause-circle" class="w-5 h-5 text-gray-600"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900"><?= $stats['inactive'] ?></p>
                        <p class="text-sm text-gray-500">Nonaktif</p>
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
                <a href="<?= url('admin/products') ?>" 
                   class="px-4 py-2 rounded-xl font-medium transition-all <?= $filter === 'all' ? 'bg-primary-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' ?>">
                    Semua
                </a>
                <a href="<?= url('admin/products?filter=active') ?>" 
                   class="px-4 py-2 rounded-xl font-medium transition-all <?= $filter === 'active' ? 'bg-green-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' ?>">
                    Aktif
                </a>
                <a href="<?= url('admin/products?filter=inactive') ?>" 
                   class="px-4 py-2 rounded-xl font-medium transition-all <?= $filter === 'inactive' ? 'bg-gray-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' ?>">
                    Nonaktif
                </a>
            </div>
            
            <form method="GET" class="flex-1 max-w-md ml-auto">
                <input type="hidden" name="filter" value="<?= e($filter) ?>">
                <div class="relative">
                    <input type="text" name="search" value="<?= e($search) ?>"
                           class="w-full px-4 py-2 pl-10 rounded-xl border border-gray-200 focus:border-primary-500 focus:outline-none"
                           placeholder="Cari produk atau toko...">
                    <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                </div>
            </form>
        </div>
        
        <!-- Products Table -->
        <?php if (empty($products)): ?>
            <div class="bg-white rounded-2xl p-12 text-center shadow-sm">
                <i data-lucide="package-x" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Tidak Ada Produk</h3>
                <p class="text-gray-500">Tidak ada produk dengan filter yang dipilih</p>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Produk</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Penjual</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Harga</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Rating</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($products as $product): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <?php if (!empty($product['primaryImage'])): ?>
                                            <img src="<?= uploadUrl($product['primaryImage']) ?>" alt="" class="w-12 h-12 object-cover rounded-lg">
                                        <?php else: ?>
                                            <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                                                <i data-lucide="image" class="w-5 h-5 text-gray-400"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <h4 class="font-medium text-gray-900 line-clamp-1"><?= e($product['name']) ?></h4>
                                            <p class="text-xs text-gray-500"><?= e($product['category'] ?? 'Uncategorized') ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-900"><?= e($product['storeName'] ?? '-') ?></p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm font-medium text-gray-900">Rp <?= number_format((float)$product['price'], 0, ',', '.') ?></p>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-1">
                                        <i data-lucide="star" class="w-4 h-4 text-yellow-500 fill-yellow-500"></i>
                                        <span class="text-sm text-gray-900"><?= number_format((float)($product['rating'] ?? 0), 1) ?></span>
                                        <span class="text-xs text-gray-500">(<?= $product['totalReviews'] ?? 0 ?>)</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($product['status'] === 'active'): ?>
                                        <span class="px-2.5 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full">Aktif</span>
                                    <?php else: ?>
                                        <span class="px-2.5 py-1 bg-gray-100 text-gray-700 text-xs font-medium rounded-full">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <?php if ($product['status'] === 'active'): ?>
                                            <form method="POST" class="inline">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="action" value="deactivate">
                                                <input type="hidden" name="productId" value="<?= $product['id'] ?>">
                                                <button type="submit" class="p-2 text-gray-500 hover:bg-gray-100 rounded-lg transition-colors" title="Nonaktifkan">
                                                    <i data-lucide="pause" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" class="inline">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="action" value="activate">
                                                <input type="hidden" name="productId" value="<?= $product['id'] ?>">
                                                <button type="submit" class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors" title="Aktifkan">
                                                    <i data-lucide="play" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <a href="<?= url('produk/' . $product['slug']) ?>" target="_blank" 
                                           class="p-2 text-gray-500 hover:bg-gray-50 rounded-lg transition-colors" title="Lihat Produk">
                                            <i data-lucide="external-link" class="w-4 h-4"></i>
                                        </a>
                                        
                                        <form method="POST" class="inline" onsubmit="return confirm('Hapus produk ini?')">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="productId" value="<?= $product['id'] ?>">
                                            <button type="submit" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
    
    <script>lucide.createIcons();</script>
</body>
</html>
