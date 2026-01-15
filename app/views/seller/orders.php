<?php
declare(strict_types=1);

requireSeller();

$currentPage = 'pesanan';
$seller = sellerProfile();
$currentUser = user();
$db = Database::getInstance();

// Handle Manual Order Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrfToken'] ?? null)) {
        flashError('Sesi tidak valid');
        redirect('seller/pesanan');
    }
    
    $productId = (int)($_POST['productId'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 0);
    $notes = strip_tags($_POST['notes'] ?? '');
    
    if ($productId && $quantity > 0) {
        // Get product to check stock
        $product = $db->selectOne("SELECT stock, name FROM products WHERE id = ? AND sellerId = ?", [$productId, $seller['id']]);
        
        if ($product) {
            if ($product['stock'] >= $quantity) {
                // Determine new stock
                $newStock = $product['stock'] - $quantity;
                
                // Update product: decrease stock, increase totalSold
                $db->query(
                    "UPDATE products SET stock = ?, totalSold = totalSold + ? WHERE id = ?", 
                    [$newStock, $quantity, $productId]
                );
                
                // Here we could insert into an 'orders' table if we had one.
                // For now, just tracking stats.
                
                flashSuccess("Penjualan berhasil dicatat! Stok {$product['name']} berkurang {$quantity}.");
            } else {
                flashError("Stok tidak cukup. Stok saat ini: {$product['stock']}");
            }
        } else {
             flashError('Produk tidak ditemukan');
        }
    } else {
        flashError('Mohon pilih produk dan jumlah yang valid');
    }
    redirect('seller/pesanan');
}

// Get Products for Dropdown
$products = $db->select("SELECT id, name, stock, price FROM products WHERE sellerId = ? AND status = 'active' ORDER BY name ASC", [$seller['id']]);

$pageTitle = 'Pesanan - WHFood';
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
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Pesanan Masuk</h1>
            <p class="text-gray-500">Kelola pesanan WhatsApp dan catat penjualan manual</p>
        </div>
        
        <!-- Info Card -->
        <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6 mb-8 flex items-start gap-4">
            <div class="bg-blue-100 p-3 rounded-xl">
                <i data-lucide="message-circle" class="w-6 h-6 text-blue-600"></i>
            </div>
            <div>
                <h3 class="font-semibold text-blue-900 mb-1">Sistem Pesanan via WhatsApp</h3>
                <p class="text-blue-800 text-sm leading-relaxed">
                    Saat ini, pembeli akan menghubungi Anda langsung melalui WhatsApp untuk memesan. 
                    Pastikan nomor WhatsApp Anda aktif. Setelah transaksi selesai, 
                    <span class="font-bold">harap catat pesanan secara manual di bawah ini</span> 
                    agar stok produk dan statistik penjualan Anda tetap akurat.
                </p>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if ($success = flash('success')): ?>
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl flex items-center gap-3">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
                <span><?= e($success) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($error = flash('error')): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-3">
                <i data-lucide="alert-circle" class="w-5 h-5"></i>
                <span><?= e($error) ?></span>
            </div>
        <?php endif; ?>

        <!-- Manual Entry Form -->
        <div class="bg-white rounded-2xl p-6 shadow-sm max-w-2xl">
            <h2 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                <i data-lucide="clipboard-list" class="w-5 h-5 text-gray-400"></i>
                Catat Penjualan Manual
            </h2>
            
            <form method="POST" class="space-y-4">
                <?= csrfField() ?>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Produk Terjual</label>
                    <select name="productId" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 outline-none bg-white">
                        <option value="">-- Pilih Produk --</option>
                        <?php foreach ($products as $p): ?>
                            <option value="<?= $p['id'] ?>">
                                <?= e($p['name']) ?> (Stok: <?= $p['stock'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Terjual</label>
                    <input type="number" name="quantity" min="1" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 outline-none" placeholder="Contoh: 2">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                    <textarea name="notes" rows="2" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 outline-none resize-none" placeholder="Nama pembeli atau detail pesanan..."></textarea>
                </div>
                
                <button type="submit" class="w-full py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-all shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
                    <i data-lucide="save" class="w-5 h-5"></i>
                    Simpan & Update Stok
                </button>
            </form>
        </div>
    </main>
    
    <script>lucide.createIcons();</script>
</body>
</html>
