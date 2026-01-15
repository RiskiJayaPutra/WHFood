<?php
declare(strict_types=1);

requireSeller();

$currentPage = 'produk';
$seller = sellerProfile();

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (!verifyCsrf($_POST['csrfToken'] ?? null)) {
        flashError('Sesi tidak valid');
        redirect('seller/produk');
    }
    
    $productId = (int)($_POST['productId'] ?? 0);
    
    $db = Database::getInstance();
    $product = $db->selectOne("SELECT id, primaryImage FROM products WHERE id = ? AND sellerId = ?", [$productId, $seller['id']]);
    
    if ($product) {
        // Delete product
        $db->update('products', ['status' => 'deleted'], 'id = ?', [$productId]);
        flashSuccess('Produk berhasil dihapus');
    } else {
        flashError('Produk tidak ditemukan');
    }
    
    redirect('seller/produk');
}

// Get products
$db = Database::getInstance();
$products = $db->select(
    "SELECT * FROM products WHERE sellerId = ? AND status != 'deleted' ORDER BY createdAt DESC",
    [$seller['id'] ?? 0]
);

$pageTitle = 'Kelola Produk - WHFood';
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
    
    <?php require VIEWS_PATH . '/components/seller-sidebar.php'; ?>
    
    <main class="transition-all duration-300 md:ml-64 p-4 md:p-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Produk Saya</h1>
                <p class="text-gray-500"><?= count($products) ?> produk terdaftar</p>
            </div>
            <a href="<?= url('seller/tambah-produk') ?>" 
               class="flex items-center gap-2 px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-all">
                <i data-lucide="plus" class="w-5 h-5"></i>
                Tambah Produk
            </a>
        </div>
        
        <!-- Flash Messages -->
        <?php if ($error = flash('error')): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-3">
                <i data-lucide="alert-circle" class="w-5 h-5"></i>
                <span><?= e($error) ?></span>
            </div>
        <?php endif; ?>
        
        <?php if ($success = flash('success')): ?>
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl flex items-center gap-3">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
                <span><?= e($success) ?></span>
            </div>
        <?php endif; ?>
        
        <!-- Products Table -->
        <?php if (empty($products)): ?>
            <div class="bg-white rounded-2xl p-12 text-center shadow-sm">
                <i data-lucide="package-x" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Belum Ada Produk</h3>
                <p class="text-gray-500 mb-6">Mulai tambahkan produk pertama Anda untuk mulai berjualan</p>
                <a href="<?= url('seller/tambah-produk') ?>" 
                   class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-all">
                    <i data-lucide="plus" class="w-5 h-5"></i>
                    Tambah Produk Pertama
                </a>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Produk</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kategori</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Harga</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Stok</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($products as $product): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <img src="<?= uploadUrl($product['primaryImage']) ?>" 
                                             alt="<?= e($product['name']) ?>"
                                             class="w-12 h-12 rounded-lg object-cover">
                                        <div>
                                            <h4 class="font-medium text-gray-900"><?= e($product['name']) ?></h4>
                                            <p class="text-sm text-gray-500"><?= $product['totalSold'] ?> terjual</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 bg-gray-100 text-gray-700 text-xs font-medium rounded-full">
                                        <?= ucfirst(str_replace('_', ' ', $product['category'])) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($product['discountPrice']): ?>
                                        <span class="text-sm text-gray-400 line-through"><?= rupiah($product['price']) ?></span>
                                        <br>
                                        <span class="font-semibold text-primary-600"><?= rupiah($product['discountPrice']) ?></span>
                                    <?php else: ?>
                                        <span class="font-semibold text-gray-900"><?= rupiah($product['price']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="<?= $product['stock'] > 0 ? 'text-gray-900' : 'text-red-600' ?>">
                                        <?= $product['stock'] ?> <?= e($product['unit']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                    $statusColors = [
                                        'active' => 'bg-green-100 text-green-700',
                                        'inactive' => 'bg-gray-100 text-gray-700',
                                        'soldout' => 'bg-red-100 text-red-700'
                                    ];
                                    $statusLabels = [
                                        'active' => 'Aktif',
                                        'inactive' => 'Nonaktif',
                                        'soldout' => 'Habis'
                                    ];
                                    ?>
                                    <span class="px-2.5 py-1 <?= $statusColors[$product['status']] ?? 'bg-gray-100 text-gray-700' ?> text-xs font-medium rounded-full">
                                        <?= $statusLabels[$product['status']] ?? $product['status'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="<?= url('seller/edit-produk?id=' . $product['id']) ?>" 
                                           class="p-2 text-gray-500 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors"
                                           title="Edit">
                                            <i data-lucide="edit" class="w-4 h-4"></i>
                                        </a>
                                        <button onclick="confirmDelete(<?= $product['id'] ?>, '<?= e($product['name']) ?>')"
                                                class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                                title="Hapus">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
    
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full mx-4 shadow-xl">
            <div class="text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="trash-2" class="w-8 h-8 text-red-600"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Hapus Produk?</h3>
                <p class="text-gray-500 mb-6">Apakah Anda yakin ingin menghapus <strong id="productName"></strong>?</p>
                
                <form id="deleteForm" method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="productId" id="productIdInput">
                    
                    <div class="flex gap-3">
                        <button type="button" onclick="closeModal()"
                                class="flex-1 py-2.5 border border-gray-200 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                class="flex-1 py-2.5 bg-red-600 hover:bg-red-700 text-white font-medium rounded-xl transition-colors">
                            Hapus
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        lucide.createIcons();
        
        function confirmDelete(productId, productName) {
            document.getElementById('productIdInput').value = productId;
            document.getElementById('productName').textContent = productName;
            document.getElementById('deleteModal').classList.remove('hidden');
        }
        
        function closeModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }
        
        // Close modal on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeModal();
        });
    </script>
</body>
</html>
