<?php
declare(strict_types=1);

requireSeller();

$currentPage = isset($_GET['id']) ? 'edit-produk' : 'tambah-produk';
$seller = sellerProfile();
$isEdit = false;
$product = null;

// Get product for edit
if (isset($_GET['id'])) {
    $db = Database::getInstance();
    $product = $db->selectOne(
        "SELECT * FROM products WHERE id = ? AND sellerId = ? AND status != 'deleted'",
        [(int)$_GET['id'], $seller['id']]
    );
    
    if (!$product) {
        flashError('Produk tidak ditemukan');
        redirect('seller/produk');
    }
    
    $isEdit = true;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrfToken'] ?? null)) {
        flashError('Sesi tidak valid');
        redirect($isEdit ? 'seller/edit-produk?id=' . $product['id'] : 'seller/tambah-produk');
    }
    
    $data = [
        'name' => input('name'),
        'description' => input('description'),
        'category' => input('category'),
        'price' => input('price', 'float'),
        'discountPrice' => input('discountPrice', 'float') ?: null,
        'stock' => input('stock', 'int'),
        'unit' => input('unit') ?: 'porsi',
        'status' => input('status') ?: 'active'
    ];
    
    // Validation
    $errors = validate($data, [
        'name' => 'required|min:3|max:150',
        'category' => 'required',
        'price' => 'required|numeric'
    ]);
    
    if (empty($errors)) {
        $db = Database::getInstance();
        
        // Handle image upload
        $imagePath = $product['primaryImage'] ?? null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadFile($_FILES['image'], 'products', 'product');
            if ($uploadResult['success']) {
                $imagePath = $uploadResult['path'];
                // Resize image
                resizeImage($imagePath, 800, 800, 85);
            } else {
                flashError($uploadResult['message']);
                flashOld($data);
                redirect($isEdit ? 'seller/edit-produk?id=' . $product['id'] : 'seller/tambah-produk');
            }
        }
        
        // Generate slug
        $slug = slugify($data['name']);
        
        // Calculate discount percentage
        $discountPercentage = null;
        if ($data['discountPrice'] && $data['discountPrice'] < $data['price']) {
            $discountPercentage = round((($data['price'] - $data['discountPrice']) / $data['price']) * 100);
        }
        
        $productData = [
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'],
            'category' => $data['category'],
            'price' => $data['price'],
            'discountPrice' => $data['discountPrice'],
            'discountPercentage' => $discountPercentage,
            'stock' => $data['stock'],
            'unit' => $data['unit'],
            'status' => $data['status'],
            'primaryImage' => $imagePath,
            'isAvailable' => $data['stock'] > 0 ? 1 : 0
        ];
        
        if ($isEdit) {
            // Update
            $db->update('products', $productData, 'id = ?', [$product['id']]);
            flashSuccess('Produk berhasil diperbarui');
        } else {
            // Insert
            $productData['sellerId'] = $seller['id'];
            $db->insert('products', $productData);
            flashSuccess('Produk berhasil ditambahkan');
        }
        
        redirect('seller/produk');
    } else {
        flashError(array_values($errors)[0]);
        flashOld($data);
        redirect($isEdit ? 'seller/edit-produk?id=' . $product['id'] : 'seller/tambah-produk');
    }
}

$pageTitle = ($isEdit ? 'Edit' : 'Tambah') . ' Produk - WHFood';

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
    
    <main class="ml-64 p-8">
        <!-- Header -->
        <div class="flex items-center gap-4 mb-8">
            <a href="<?= url('seller/produk') ?>" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <i data-lucide="arrow-left" class="w-5 h-5 text-gray-600"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900"><?= $isEdit ? 'Edit' : 'Tambah' ?> Produk</h1>
                <p class="text-gray-500"><?= $isEdit ? 'Perbarui informasi produk Anda' : 'Tambahkan produk baru ke toko Anda' ?></p>
            </div>
        </div>
        
        <!-- Flash Messages -->
        <?php if ($error = flash('error')): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-3">
                <i data-lucide="alert-circle" class="w-5 h-5"></i>
                <span><?= e($error) ?></span>
            </div>
        <?php endif; ?>
        
        <!-- Form -->
        <form method="POST" enctype="multipart/form-data" class="max-w-3xl">
            <?= csrfField() ?>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Main Form -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Basic Info -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Informasi Dasar</h2>
                        
                        <div class="space-y-4">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nama Produk <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="name" name="name" required
                                       value="<?= e(old('name', $product['name'] ?? '')) ?>"
                                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all"
                                       placeholder="Contoh: Nasi Uduk Komplit">
                            </div>
                            
                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                    Deskripsi
                                </label>
                                <textarea id="description" name="description" rows="4"
                                          class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all resize-none"
                                          placeholder="Jelaskan produk Anda..."><?= e(old('description', $product['description'] ?? '')) ?></textarea>
                            </div>
                            
                            <!-- Category -->
                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                                    Kategori <span class="text-red-500">*</span>
                                </label>
                                <select id="category" name="category" required
                                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all bg-white">
                                    <option value="">Pilih Kategori</option>
                                    <?php foreach ($categories as $value => $label): ?>
                                        <option value="<?= $value ?>" <?= (old('category', $product['category'] ?? '') === $value) ? 'selected' : '' ?>>
                                            <?= $label ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pricing -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Harga & Stok</h2>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Price -->
                            <div>
                                <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
                                    Harga <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">Rp</span>
                                    <input type="number" id="price" name="price" required min="0"
                                           value="<?= e(old('price', $product['price'] ?? '')) ?>"
                                           class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all"
                                           placeholder="0">
                                </div>
                            </div>
                            
                            <!-- Discount Price -->
                            <div>
                                <label for="discountPrice" class="block text-sm font-medium text-gray-700 mb-2">
                                    Harga Diskon <span class="text-gray-400">(opsional)</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">Rp</span>
                                    <input type="number" id="discountPrice" name="discountPrice" min="0"
                                           value="<?= e(old('discountPrice', $product['discountPrice'] ?? '')) ?>"
                                           class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all"
                                           placeholder="0">
                                </div>
                            </div>
                            
                            <!-- Stock -->
                            <div>
                                <label for="stock" class="block text-sm font-medium text-gray-700 mb-2">
                                    Stok
                                </label>
                                <input type="number" id="stock" name="stock" min="0"
                                       value="<?= e(old('stock', $product['stock'] ?? 0)) ?>"
                                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all"
                                       placeholder="0">
                            </div>
                            
                            <!-- Unit -->
                            <div>
                                <label for="unit" class="block text-sm font-medium text-gray-700 mb-2">
                                    Satuan
                                </label>
                                <input type="text" id="unit" name="unit"
                                       value="<?= e(old('unit', $product['unit'] ?? 'porsi')) ?>"
                                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all"
                                       placeholder="porsi, pcs, kg, dll">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Image Upload -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Foto Produk</h2>
                        
                        <div class="relative">
                            <input type="file" id="image" name="image" accept="image/*" class="hidden" onchange="previewImage(this)">
                            
                            <label for="image" class="block cursor-pointer">
                                <div id="imagePreview" 
                                     class="aspect-square rounded-xl border-2 border-dashed border-gray-300 hover:border-primary-400 flex flex-col items-center justify-center transition-colors overflow-hidden bg-gray-50">
                                    <?php if ($product && $product['primaryImage']): ?>
                                        <img src="<?= uploadUrl($product['primaryImage']) ?>" alt="Preview" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <i data-lucide="image-plus" class="w-10 h-10 text-gray-400 mb-2"></i>
                                        <span class="text-sm text-gray-500">Klik untuk upload</span>
                                        <span class="text-xs text-gray-400 mt-1">JPG, PNG, WebP (max 5MB)</span>
                                    <?php endif; ?>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Status -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Status</h2>
                        
                        <select id="status" name="status"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all bg-white">
                            <option value="active" <?= (old('status', $product['status'] ?? 'active') === 'active') ? 'selected' : '' ?>>Aktif</option>
                            <option value="inactive" <?= (old('status', $product['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>Nonaktif</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-2">Produk aktif akan tampil di halaman utama</p>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex flex-col gap-3">
                        <button type="submit"
                                class="w-full py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-all flex items-center justify-center gap-2">
                            <i data-lucide="<?= $isEdit ? 'save' : 'plus' ?>" class="w-5 h-5"></i>
                            <?= $isEdit ? 'Simpan Perubahan' : 'Tambah Produk' ?>
                        </button>
                        <a href="<?= url('seller/produk') ?>"
                           class="w-full py-3 border border-gray-200 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-all text-center">
                            Batal
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </main>
    
    <script>
        lucide.createIcons();
        
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('imagePreview');
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview" class="w-full h-full object-cover">`;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
<?php clearOld(); ?>
