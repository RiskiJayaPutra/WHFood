<?php
declare(strict_types=1);

requireSeller();

$currentPage = 'profil';
$seller = sellerProfile();
$currentUser = user();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrfToken'] ?? null)) {
        flashError('Sesi tidak valid');
        redirect('seller/profil');
    }
    
    $data = [
        'storeName' => input('storeName'),
        'storeDescription' => input('storeDescription'),
        'address' => input('address'),
        'village' => input('village'),
        'district' => input('district'),
        'regency' => input('regency'),
        'latitude' => input('latitude', 'float'),
        'longitude' => input('longitude', 'float'),
        'bankName' => input('bankName'),
        'bankAccountNumber' => input('bankAccountNumber'),
        'bankAccountName' => input('bankAccountName'),
        'isOpen' => isset($_POST['isOpen']) ? 1 : 0
    ];
    
    // Validation
    $errors = validate($data, [
        'storeName' => 'required|min:3|max:100',
        'address' => 'required|min:10'
    ]);
    
    if (empty($errors)) {
        $db = Database::getInstance();
        
        // Handle logo upload
        if (isset($_FILES['storeLogo']) && $_FILES['storeLogo']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = uploadFile($_FILES['storeLogo'], 'stores', 'logo');
            if ($uploadResult['success']) {
                $data['storeLogo'] = $uploadResult['path'];
                resizeImage($data['storeLogo'], 400, 400, 90);
            } else {
                $errors['storeLogo'] = $uploadResult['message'];
            }
        }
        
        // Handle banner upload
        if (isset($_FILES['storeBanner']) && $_FILES['storeBanner']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = uploadFile($_FILES['storeBanner'], 'stores', 'banner');
            if ($uploadResult['success']) {
                $data['storeBanner'] = $uploadResult['path'];
                resizeImage($data['storeBanner'], 1200, 400, 85);
            } else {
                $errors['storeBanner'] = $uploadResult['message'];
            }
        }
        
        if (!empty($errors)) {
             flashError(array_values($errors)[0]);
        } else {
             // Update seller profile
             $db->update('seller_profiles', $data, 'id = ?', [$seller['id']]);
             
             // Update user phone if provided
             $phone = input('phoneNumber');
             if ($phone) {
                 $db->update('users', ['phoneNumber' => $phone], 'id = ?', [$currentUser['id']]);
             }
             
             flashSuccess('Profil toko berhasil diperbarui');
             redirect('seller/profil');
        }
    } else {
        flashError(array_values($errors)[0]);
    }
}

$pageTitle = 'Profil Toko - WHFood';
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
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen">
    
    <?php require VIEWS_PATH . '/components/seller-sidebar.php'; ?>
    
    <main class="ml-64 p-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Profil Toko</h1>
            <p class="text-gray-500">Kelola informasi dan tampilan toko Anda</p>
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
        
        <form method="POST" enctype="multipart/form-data" class="max-w-4xl space-y-6">
            <?= csrfField() ?>
            
            <!-- Store Images -->
            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Gambar Toko</h2>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Logo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Logo Toko</label>
                        <input type="file" id="storeLogo" name="storeLogo" accept="image/*" class="hidden" onchange="previewLogo(this)">
                        <label for="storeLogo" class="block cursor-pointer">
                            <div id="logoPreview" class="w-32 h-32 rounded-xl border-2 border-dashed border-gray-300 hover:border-primary-400 flex items-center justify-center overflow-hidden bg-gray-50 transition-colors">
                                <?php if ($seller['storeLogo']): ?>
                                    <img src="<?= uploadUrl($seller['storeLogo']) ?>" alt="Logo" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <i data-lucide="image-plus" class="w-8 h-8 text-gray-400"></i>
                                <?php endif; ?>
                            </div>
                        </label>
                        <p class="text-xs text-gray-500 mt-2">Rasio 1:1, maks 5MB</p>
                    </div>
                    
                    <!-- Banner -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Banner Toko</label>
                        <input type="file" id="storeBanner" name="storeBanner" accept="image/*" class="hidden" onchange="previewBanner(this)">
                        <label for="storeBanner" class="block cursor-pointer">
                            <div id="bannerPreview" class="w-full h-32 rounded-xl border-2 border-dashed border-gray-300 hover:border-primary-400 flex items-center justify-center overflow-hidden bg-gray-50 transition-colors">
                                <?php if ($seller['storeBanner']): ?>
                                    <img src="<?= uploadUrl($seller['storeBanner']) ?>" alt="Banner" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <i data-lucide="image-plus" class="w-8 h-8 text-gray-400"></i>
                                <?php endif; ?>
                            </div>
                        </label>
                        <p class="text-xs text-gray-500 mt-2">Rasio 3:1, maks 5MB</p>
                    </div>
                </div>
            </div>
            
            <!-- Store Info -->
            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Informasi Toko</h2>
                
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label for="storeName" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Toko <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="storeName" name="storeName" required
                               value="<?= e($seller['storeName'] ?? '') ?>"
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="storeDescription" class="block text-sm font-medium text-gray-700 mb-2">
                            Deskripsi Toko
                        </label>
                        <textarea id="storeDescription" name="storeDescription" rows="3"
                                  class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all resize-none"
                                  placeholder="Ceritakan tentang toko Anda..."><?= e($seller['storeDescription'] ?? '') ?></textarea>
                    </div>
                    
                    <div>
                        <label for="phoneNumber" class="block text-sm font-medium text-gray-700 mb-2">
                            Nomor WhatsApp
                        </label>
                        <input type="tel" id="phoneNumber" name="phoneNumber"
                               value="<?= e($currentUser['phoneNumber'] ?? '') ?>"
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all"
                               placeholder="08xxxxxxxxxx">
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="isOpen" class="sr-only peer" <?= ($seller['isOpen'] ?? true) ? 'checked' : '' ?>>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:ring-4 peer-focus:ring-primary-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                            <span class="ml-3 text-sm font-medium text-gray-700">Toko Buka</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Location -->
            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Lokasi Toko</h2>
                
                <div class="mb-4">
                    <div id="mapPicker" class="w-full h-64 rounded-xl border border-gray-200"></div>
                </div>
                
                <input type="hidden" id="latitude" name="latitude" value="<?= e($seller['latitude'] ?? '-5.3698') ?>">
                <input type="hidden" id="longitude" name="longitude" value="<?= e($seller['longitude'] ?? '105.2486') ?>">
                
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                            Alamat Lengkap <span class="text-red-500">*</span>
                        </label>
                        <textarea id="address" name="address" rows="2" required
                                  class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all resize-none"><?= e($seller['address'] ?? '') ?></textarea>
                    </div>
                    
                    <div>
                        <label for="village" class="block text-sm font-medium text-gray-700 mb-2">Desa</label>
                        <input type="text" id="village" name="village" value="<?= e($seller['village'] ?? 'Way Huwi') ?>"
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all">
                    </div>
                    
                    <div>
                        <label for="district" class="block text-sm font-medium text-gray-700 mb-2">Kecamatan</label>
                        <input type="text" id="district" name="district" value="<?= e($seller['district'] ?? 'Jati Agung') ?>"
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all">
                    </div>
                    
                    <div>
                        <label for="regency" class="block text-sm font-medium text-gray-700 mb-2">Kabupaten</label>
                        <input type="text" id="regency" name="regency" value="<?= e($seller['regency'] ?? 'Lampung Selatan') ?>"
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all">
                    </div>
                </div>
            </div>
            
            <!-- Bank Info -->
            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Informasi Pembayaran</h2>
                
                <div class="grid md:grid-cols-3 gap-4">
                    <div>
                        <label for="bankName" class="block text-sm font-medium text-gray-700 mb-2">Nama Bank</label>
                        <select id="bankName" name="bankName"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all bg-white">
                            <option value="">Pilih Bank</option>
                            <?php foreach (['BCA', 'BRI', 'BNI', 'Mandiri', 'BSI', 'DANA', 'OVO', 'GoPay'] as $bank): ?>
                                <option value="<?= $bank ?>" <?= ($seller['bankName'] ?? '') === $bank ? 'selected' : '' ?>><?= $bank ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="bankAccountNumber" class="block text-sm font-medium text-gray-700 mb-2">Nomor Rekening</label>
                        <input type="text" id="bankAccountNumber" name="bankAccountNumber"
                               value="<?= e($seller['bankAccountNumber'] ?? '') ?>"
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all">
                    </div>
                    
                    <div>
                        <label for="bankAccountName" class="block text-sm font-medium text-gray-700 mb-2">Nama Pemilik</label>
                        <input type="text" id="bankAccountName" name="bankAccountName"
                               value="<?= e($seller['bankAccountName'] ?? '') ?>"
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all">
                    </div>
                </div>
            </div>
            
            <!-- Submit -->
            <div class="flex justify-end">
                <button type="submit"
                        class="px-8 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-all flex items-center gap-2">
                    <i data-lucide="save" class="w-5 h-5"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </main>
    
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        lucide.createIcons();
        
        // Image preview functions
        function previewLogo(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    document.getElementById('logoPreview').innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        function previewBanner(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    document.getElementById('bannerPreview').innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        // Map initialization
        const lat = parseFloat(document.getElementById('latitude').value) || -5.3698;
        const lng = parseFloat(document.getElementById('longitude').value) || 105.2486;
        
        const map = L.map('mapPicker').setView([lat, lng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);
        
        const marker = L.marker([lat, lng], { draggable: true }).addTo(map);
        
        marker.on('dragend', function(e) {
            const pos = e.target.getLatLng();
            document.getElementById('latitude').value = pos.lat.toFixed(8);
            document.getElementById('longitude').value = pos.lng.toFixed(8);
        });
        
        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            document.getElementById('latitude').value = e.latlng.lat.toFixed(8);
            document.getElementById('longitude').value = e.latlng.lng.toFixed(8);
        });
    </script>
</body>
</html>
