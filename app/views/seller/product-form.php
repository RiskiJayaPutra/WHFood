<?php
declare(strict_types=1);

requireSeller();

$currentPage = isset($_GET['id']) ? 'edit-produk' : 'tambah-produk';
$seller = sellerProfile();
$isEdit = false;
$product = [];

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
    
    // Get gallery images
    $gallery = $db->select("SELECT * FROM product_images WHERE productId = ?", [$product['id']]);
    
    // Handle Delete Gallery Image (Quick Action)
    if (isset($_GET['action']) && $_GET['action'] === 'delete-image' && isset($_GET['image_id'])) {
        $imgId = (int)$_GET['image_id'];
        
        // Verify ownership & get path
        $img = $db->selectOne(
            "SELECT pi.* FROM product_images pi 
             JOIN products p ON pi.productId = p.id 
             WHERE pi.id = ? AND p.sellerId = ?", 
            [$imgId, $seller['id']]
        );
        
        if ($img) {
            deleteFile($img['imagePath']);
            $db->query("DELETE FROM product_images WHERE id = ?", [$imgId]);
            flashSuccess('Foto berhasil dihapus');
        }
        
        redirect('seller/edit-produk?id=' . $product['id']);
    }
}

// Default values for create mode to prevent undefined index warnings
if (!$isEdit && empty($product)) {
    $product = [
        'id' => null,
        'name' => '',
        'description' => '',
        'category' => '',
        'price' => '',
        'discountPrice' => '',
        'stock' => 0,
        'unit' => 'porsi',
        'weight' => '',
        'preparationTime' => '',
        'status' => 'active',
        'primaryImage' => null
    ];
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
        'weight' => input('weight', 'int') ?: null,
        'preparationTime' => input('preparationTime', 'int') ?: null,
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
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = uploadFile($_FILES['image'], 'products', 'product');
            if ($uploadResult['success']) {
                $imagePath = $uploadResult['path'];
                resizeImage($imagePath, 800, 800, 85);
            } else {
                flashError($uploadResult['message']);
                flashOld($data);
                redirect($isEdit ? 'seller/edit-produk?id=' . $product['id'] : 'seller/tambah-produk');
            }
        }
        
        // Handle gallery upload
        $galleryPaths = [];
        if (isset($_FILES['gallery'])) {
            $fileCount = count(array_filter($_FILES['gallery']['name']));
            if ($fileCount > 0) {
                $galleryUpload = uploadMultipleFiles($_FILES['gallery'], 'products', 'gallery', 5);
                if ($galleryUpload['success']) {
                    $galleryPaths = $galleryUpload['paths'];
                    foreach ($galleryPaths as $path) {
                        resizeImage($path, 800, 800, 85);
                    }
                } else {
                    $errors['gallery'] = $galleryUpload['message'];
                }
            }
        }
        
        // Generate slug & Calculations
        $slug = slugify($data['name']);
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
            'weight' => $data['weight'],
            'preparationTime' => $data['preparationTime'],
            'status' => $data['status'],
            'primaryImage' => $imagePath,
            'isAvailable' => $data['stock'] > 0 ? 1 : 0
        ];
        
        if ($isEdit) {
            $db->update('products', $productData, 'id = ?', [$product['id']]);
            flashSuccess('Produk berhasil diperbarui');
        } else {
            $productData['sellerId'] = $seller['id'];
            $productId = $db->insert('products', $productData);
            flashSuccess('Produk berhasil ditambahkan');
        }
        
        // Insert gallery images
        if (!empty($galleryPaths)) {
            $targetId = $isEdit ? $product['id'] : $productId;
            foreach ($galleryPaths as $path) {
                $db->insert('product_images', [
                    'productId' => $targetId,
                    'imagePath' => $path
                ]);
            }
        }
        
        redirect('seller/produk');
    } else {
        flashError(array_values($errors)[0]);
        flashOld($data);
        redirect($isEdit ? 'seller/edit-produk?id=' . $product['id'] : 'seller/tambah-produk');
    }
}

$pageTitle = ($isEdit ? 'Edit' : 'Tambah') . ' Produk - WHFood';

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
    
    <main class="transition-all duration-300 md:ml-64 p-4 md:p-8">
        <!-- Header -->
        <div class="flex items-center gap-4 mb-8">
            <a href="<?= url('seller/produk') ?>" class="p-2 hover:bg-gray-100 rounded-lg transition-colors group">
                <i data-lucide="arrow-left" class="w-5 h-5 text-gray-600 group-hover:text-gray-900"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900"><?= $isEdit ? 'Edit Produk' : 'Tambah Produk Baru' ?></h1>
                <p class="text-gray-500">Lengkapi informasi produk Anda untuk mulai berjualan</p>
            </div>
        </div>
        
        <!-- Flash Messages -->
        <?php if ($error = flash('error')): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-3 animate-fade-in">
                <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i>
                <span class="font-medium"><?= e($error) ?></span>
            </div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" class="max-w-7xl mx-auto">
            <?= csrfField() ?>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Left Column (Main Info) -->
                <div class="lg:col-span-2 space-y-8">
                    
                    <!-- 1. Informasi Dasar -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100/50">
                        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-100">
                            <div class="w-10 h-10 rounded-full bg-primary-50 flex items-center justify-center">
                                <i data-lucide="package-search" class="w-5 h-5 text-primary-600"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">Informasi Dasar</h2>
                                <p class="text-sm text-gray-500">Detail utama produk Anda</p>
                            </div>
                        </div>
                        
                        <div class="space-y-6">
                            <!-- Name -->
                            <div class="space-y-2">
                                <label for="name" class="block text-sm font-semibold text-gray-700">
                                    Nama Produk <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i data-lucide="shopping-bag" class="w-5 h-5 text-gray-400"></i>
                                    </div>
                                    <input type="text" id="name" name="name" required
                                           value="<?= e(old('name', $product['name'] ?? '')) ?>"
                                           class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 text-gray-900 focus:border-primary-500 focus:ring-2 focus:ring-primary-100 outline-none transition-all placeholder:text-gray-300 shadow-sm"
                                           placeholder="Contoh: Nasi Goreng Spesial">
                                </div>
                            </div>
                            
                            <!-- Description -->
                            <div class="space-y-2">
                                <label for="description" class="block text-sm font-semibold text-gray-700">
                                    Deskripsi Produk
                                </label>
                                <div class="relative">
                                    <div class="absolute top-3 left-4 pointer-events-none">
                                        <i data-lucide="align-left" class="w-5 h-5 text-gray-400"></i>
                                    </div>
                                    <textarea id="description" name="description" rows="4"
                                              class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 text-gray-900 focus:border-primary-500 focus:ring-2 focus:ring-primary-100 outline-none transition-all resize-none placeholder:text-gray-300 shadow-sm"
                                              placeholder="Jelaskan rasa, bahan utama, dan keunggulan produk Anda..."><?= e(old('description', $product['description'] ?? '')) ?></textarea>
                                </div>
                            </div>
                            
                            <!-- Category -->
                            <div class="space-y-2">
                                <label for="category" class="block text-sm font-semibold text-gray-700">
                                    Kategori <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i data-lucide="tag" class="w-5 h-5 text-gray-400"></i>
                                    </div>
                                    <select id="category" name="category" required
                                            class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 text-gray-900 focus:border-primary-500 focus:ring-2 focus:ring-primary-100 outline-none transition-all bg-white appearance-none cursor-pointer shadow-sm">
                                        <option value="">Pilih Kategori Produk</option>
                                        <?php foreach ($categories as $value => $label): ?>
                                            <option value="<?= $value ?>" <?= (old('category', $product['category'] ?? '') === $value) ? 'selected' : '' ?>>
                                                <?= $label ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                        <i data-lucide="chevron-down" class="w-5 h-5 text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 2. Harga & Inventaris -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100/50">
                        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-100">
                            <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center">
                                <i data-lucide="coins" class="w-5 h-5 text-blue-600"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">Harga & Inventaris</h2>
                                <p class="text-sm text-gray-500">Atur harga jual dan ketersediaan stok</p>
                            </div>
                        </div>
                        
                        <div class="grid md:grid-cols-2 gap-6">
                            <!-- Price -->
                            <div class="space-y-2">
                                <label for="price" class="block text-sm font-semibold text-gray-700">
                                    Harga Normal <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <span class="text-gray-500 font-bold">Rp</span>
                                    </div>
                                    <input type="number" id="price" name="price" required min="0" step="100"
                                           value="<?= e(old('price', $product['price'] ?? '')) ?>"
                                           class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 text-gray-900 focus:border-primary-500 focus:ring-2 focus:ring-primary-100 outline-none transition-all placeholder:text-gray-300 shadow-sm"
                                           placeholder="0">
                                </div>
                            </div>
                            
                            <!-- Discount Price -->
                            <div class="space-y-2">
                                <label for="discountPrice" class="block text-sm font-semibold text-gray-700">
                                    Harga Coret <span class="text-xs text-gray-400 font-normal">(Opsional)</span>
                                </label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <span class="text-gray-500 font-bold">Rp</span>
                                    </div>
                                    <input type="number" id="discountPrice" name="discountPrice" min="0" step="100"
                                           value="<?= e(old('discountPrice', $product['discountPrice'] ?? '')) ?>"
                                           class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 text-gray-900 focus:border-accent-500 focus:ring-2 focus:ring-accent-100 outline-none transition-all placeholder:text-gray-300 shadow-sm"
                                           placeholder="0">
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Harga ini akan dicoret (diskon)</p>
                            </div>
                            
                            <!-- Stock -->
                            <div class="space-y-2">
                                <label for="stock" class="block text-sm font-semibold text-gray-700">
                                    Stok Tersedia
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i data-lucide="package" class="w-5 h-5 text-gray-400"></i>
                                    </div>
                                    <input type="number" id="stock" name="stock" min="0"
                                           value="<?= e(old('stock', $product['stock'] ?? 0)) ?>"
                                           class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 text-gray-900 focus:border-primary-500 focus:ring-2 focus:ring-primary-100 outline-none transition-all placeholder:text-gray-300 shadow-sm"
                                           placeholder="0">
                                </div>
                            </div>
                            
                            <!-- Unit -->
                            <div class="space-y-2">
                                <label for="unit" class="block text-sm font-semibold text-gray-700">
                                    Satuan Produk
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i data-lucide="scale" class="w-5 h-5 text-gray-400"></i>
                                    </div>
                                    <input type="text" id="unit" name="unit" list="unit-suggestions"
                                           value="<?= e(old('unit', $product['unit'] ?? 'porsi')) ?>"
                                           class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 text-gray-900 focus:border-primary-500 focus:ring-2 focus:ring-primary-100 outline-none transition-all placeholder:text-gray-300 shadow-sm"
                                           placeholder="porsi">
                                    <datalist id="unit-suggestions">
                                        <option value="porsi">
                                        <option value="pcs">
                                        <option value="box">
                                        <option value="kg">
                                        <option value="gram">
                                    </datalist>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 3. Pengiriman & Waktu -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100/50">
                        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-100">
                            <div class="w-10 h-10 rounded-full bg-green-50 flex items-center justify-center">
                                <i data-lucide="truck" class="w-5 h-5 text-green-600"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">Pengiriman & Waktu</h2>
                                <p class="text-sm text-gray-500">Estimasi berat dan waktu penyajian</p>
                            </div>
                        </div>
                        
                        <div class="grid md:grid-cols-2 gap-6">
                            <!-- Weight -->
                            <div class="space-y-2">
                                <label for="weight" class="block text-sm font-semibold text-gray-700">
                                    Berat Produk <span class="text-gray-400 font-normal">(gram)</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i data-lucide="weight" class="w-5 h-5 text-gray-400"></i>
                                    </div>
                                    <input type="number" id="weight" name="weight" min="0"
                                           value="<?= e(old('weight', $product['weight'] ?? '')) ?>"
                                           class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 text-gray-900 focus:border-primary-500 focus:ring-2 focus:ring-primary-100 outline-none transition-all placeholder:text-gray-300 shadow-sm"
                                           placeholder="500">
                                </div>
                            </div>
                            
                            <!-- Preparation Time -->
                            <div class="space-y-2">
                                <label for="preparationTime" class="block text-sm font-semibold text-gray-700">
                                    Waktu Persiapan <span class="text-gray-400 font-normal">(menit)</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i data-lucide="clock" class="w-5 h-5 text-gray-400"></i>
                                    </div>
                                    <input type="number" id="preparationTime" name="preparationTime" min="0"
                                           value="<?= e(old('preparationTime', $product['preparationTime'] ?? '')) ?>"
                                           class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 text-gray-900 focus:border-primary-500 focus:ring-2 focus:ring-primary-100 outline-none transition-all placeholder:text-gray-300 shadow-sm"
                                           placeholder="15">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column (Sidebar) -->
                <div class="space-y-8">
                    
                    <!-- 4. Status Produk -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100/50">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-8 h-8 rounded-full bg-orange-50 flex items-center justify-center">
                                <i data-lucide="toggle-left" class="w-4 h-4 text-orange-600"></i>
                            </div>
                            <h2 class="text-base font-bold text-gray-900">Status Produk</h2>
                        </div>
                        
                        <div class="relative">
                            <select id="status" name="status"
                                    class="w-full px-4 py-3 pl-10 rounded-xl border border-gray-200 text-gray-900 focus:border-primary-500 focus:ring-2 focus:ring-primary-100 outline-none transition-all bg-white appearance-none cursor-pointer shadow-sm">
                                <option value="active" <?= (old('status', $product['status'] ?? 'active') === 'active') ? 'selected' : '' ?>>Aktif (Tampil)</option>
                                <option value="inactive" <?= (old('status', $product['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>Nonaktif (Sembunyi)</option>
                            </select>
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <i data-lucide="eye" class="w-4 h-4 text-gray-400"></i>
                            </div>
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 5. Foto Utama -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100/50">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-8 h-8 rounded-full bg-purple-50 flex items-center justify-center">
                                <i data-lucide="image" class="w-4 h-4 text-purple-600"></i>
                            </div>
                            <h2 class="text-base font-bold text-gray-900">Foto Utama</h2>
                        </div>
                        
                        <div class="relative group">
                            <input type="file" id="image" name="image" accept="image/*" class="hidden" onchange="previewImage(this)">
                            
                            <label for="image" class="block cursor-pointer">
                                <div id="imagePreview" 
                                     class="aspect-square rounded-xl border-2 border-dashed border-gray-300 group-hover:border-primary-500 group-hover:bg-primary-50/30 flex flex-col items-center justify-center transition-all overflow-hidden bg-gray-50 relative">
                                    <?php if ($product && $product['primaryImage']): ?>
                                        <img src="<?= uploadUrl($product['primaryImage']) ?>" alt="Preview" class="w-full h-full object-cover">
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity text-white font-medium text-sm">
                                            <i data-lucide="upload" class="w-4 h-4 mr-2"></i> Ganti Foto
                                        </div>
                                    <?php else: ?>
                                        <div class="w-12 h-12 rounded-full bg-white shadow-sm flex items-center justify-center mb-3">
                                            <i data-lucide="camera" class="w-6 h-6 text-primary-500"></i>
                                        </div>
                                        <span class="text-sm font-semibold text-gray-700">Upload Foto</span>
                                        <span class="text-xs text-center text-gray-400 mt-1 px-4">JPG, PNG, WebP<br>Max 5MB</span>
                                    <?php endif; ?>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <!-- 6. Galeri Foto -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100/50">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-8 h-8 rounded-full bg-pink-50 flex items-center justify-center">
                                <i data-lucide="images" class="w-4 h-4 text-pink-600"></i>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-gray-900">Galeri Produk</h2>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-3 gap-2 mb-3" id="galleryPreviewContainer">
                            <?php if ($isEdit && !empty($gallery)): ?>
                                <?php foreach ($gallery as $img): ?>
                                    <div class="aspect-square rounded-lg overflow-hidden border border-gray-200 relative group">
                                        <img src="<?= uploadUrl($img['imagePath']) ?>" class="w-full h-full object-cover">
                                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                            <a href="<?= url('seller/edit-produk?id=' . $product['id'] . '&action=delete-image&image_id=' . $img['id']) ?>" 
                                               class="p-1.5 bg-red-500 rounded-lg text-white hover:bg-red-600 transition-colors shadow-sm"
                                               onclick="return confirm('Hapus foto ini permanently?')"
                                               title="Hapus Foto">
                                                <i data-lucide="trash-2" class="w-3 h-3"></i>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="relative">
                            <input type="file" id="gallery" name="gallery[]" accept="image/*" multiple class="hidden" onchange="previewGallery(this)">
                            <label for="gallery" class="block cursor-pointer">
                                <div class="py-3 px-4 border-2 border-dashed border-gray-300 rounded-xl hover:border-primary-500 hover:bg-primary-50/30 transition-all text-center group">
                                    <div class="flex items-center justify-center gap-2 text-gray-600 group-hover:text-primary-600 font-medium text-sm">
                                        <i data-lucide="plus" class="w-4 h-4"></i>
                                        <span>Tambah (Max 5)</span>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex flex-col gap-3 sticky top-6">
                        <button type="submit"
                                class="w-full py-3.5 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl shadow-lg shadow-primary-600/20 transition-all hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2">
                            <i data-lucide="<?= $isEdit ? 'save' : 'plus-circle' ?>" class="w-5 h-5"></i>
                            <?= $isEdit ? 'Simpan Perubahan' : 'Buat Produk Baru' ?>
                        </button>
                        <a href="<?= url('seller/produk') ?>"
                           class="w-full py-3.5 border border-gray-200 bg-white text-gray-700 font-semibold rounded-xl hover:bg-gray-50 transition-all text-center flex items-center justify-center gap-2">
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
                    preview.innerHTML = `
                        <img src="${e.target.result}" alt="Preview" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-black/40 opacity-0 hover:opacity-100 flex items-center justify-center transition-opacity text-white font-medium text-sm">
                            <i data-lucide="upload" class="w-4 h-4 mr-2"></i> Ganti Foto
                        </div>
                    `;
                    lucide.createIcons();
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        function previewGallery(input) {
            const container = document.getElementById('galleryPreviewContainer');
            if (input.files) {
                const oldPreviews = container.querySelectorAll('.preview-item');
                oldPreviews.forEach(el => el.remove());

                Array.from(input.files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const div = document.createElement('div');
                        div.className = 'aspect-square rounded-lg overflow-hidden border border-gray-200 relative preview-item';
                        div.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                        container.appendChild(div);
                    };
                    reader.readAsDataURL(file);
                });
            }
        }
    </script>
</body>
</html>
<?php clearOld(); ?>
