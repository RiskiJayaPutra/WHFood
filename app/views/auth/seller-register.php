<?php
declare(strict_types=1);

// Already logged in?
if (isLoggedIn()) {
    if (isSeller()) redirect('seller/dashboard');
    if (isAdmin()) redirect('admin/dashboard');
    redirect('/');
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrfToken'] ?? null)) {
        flashError('Sesi tidak valid, silakan coba lagi');
        redirect('daftar-penjual');
    }
    
    // 1. Validate Input
    $userData = [
        'fullName' => input('fullName'),
        'email' => input('email', 'email'),
        'phoneNumber' => input('phoneNumber'),
        'password' => $_POST['password'] ?? ''
    ];
    
    $sellerData = [
        'storeName' => input('storeName'),
        'storeDescription' => input('storeDescription'),
        'address' => input('address'),
        'village' => input('village'),
        'district' => input('district'),
        'regency' => input('regency'),
        'latitude' => input('latitude'),
        'longitude' => input('longitude'),
        'bankName' => input('bankName'),
        'bankAccountNumber' => input('bankAccountNumber'),
        'bankAccountName' => input('bankAccountName')
    ];
    
    $errors = validate($userData, [
        'fullName' => 'required|min:3',
        'email' => 'required|email',
        'phoneNumber' => 'required|phone',
        'password' => 'required|min:8'
    ]);
    
    if (empty($sellerData['storeName'])) {
        $errors['storeName'] = 'Nama toko wajib diisi';
    }
    
    if (empty($errors)) {
        // 2. Handle File Uploads
        $storeLogo = null;
        $storeBanner = null;
        
        // Upload Logo
        if (isset($_FILES['storeLogo']) && $_FILES['storeLogo']['error'] === UPLOAD_ERR_OK) {
            $upload = uploadFile($_FILES['storeLogo'], 'stores', 'logo');
            if ($upload['success']) {
                $storeLogo = $upload['path'];
                // Resize logo to 400x400
                resizeImage($storeLogo, 400, 400); 
            } else {
                $errors['storeLogo'] = $upload['message'];
            }
        }
        
        // Upload Banner
        if (isset($_FILES['storeBanner']) && $_FILES['storeBanner']['error'] === UPLOAD_ERR_OK) {
            $upload = uploadFile($_FILES['storeBanner'], 'stores', 'banner');
            if ($upload['success']) {
                $storeBanner = $upload['path'];
                // Resize banner to 1200x400
                resizeImage($storeBanner, 1200, 400);
            } else {
                $errors['storeBanner'] = $upload['message'];
            }
        }
        
        if (empty($errors)) {
            $sellerData['storeLogo'] = $storeLogo;
            $sellerData['storeBanner'] = $storeBanner;
            
            // 3. Register Seller
            $result = registerSeller($userData, $sellerData);
            
            if ($result['success']) {
                flashSuccess($result['message']);
                redirect('masuk');
            } else {
                flashError($result['message']);
            }
        } else {
            flashError('Gagal mengupload gambar. Silakan coba lagi.');
        }
    } else {
        flashError(array_values($errors)[0]);
        flashOld(array_merge($userData, $sellerData));
    }
}

// Session already started by router via initSession()
if (!isset($_SESSION['csrfToken'])) {
    $_SESSION['csrfToken'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['csrfToken'];
$pageTitle = 'Daftar Sebagai Penjual - WHFood';
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Daftar sebagai penjual di WHFood - Platform marketplace UMKM kuliner Desa Way Huwi, Lampung Selatan">
    <meta name="theme-color" content="#059669">
    
    <title><?= htmlspecialchars($pageTitle) ?></title>
    
    <!-- Google Fonts - Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            200: '#a7f3d0',
                            300: '#6ee7b7',
                            400: '#34d399',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                        },
                        accent: {
                            50: '#fffbeb',
                            100: '#fef3c7',
                            200: '#fde68a',
                            300: '#fcd34d',
                            400: '#fbbf24',
                            500: '#f59e0b',
                            600: '#d97706',
                            700: '#b45309',
                            800: '#92400e',
                            900: '#78350f',
                        }
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'],
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'pulse-slow': 'pulse 3s ease-in-out infinite',
                    }
                }
            }
        }
    </script>
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" 
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" 
          crossorigin="">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #059669 0%, #10b981 50%, #34d399 100%);
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .input-focus-ring:focus {
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.2);
        }
        
        .map-shadow {
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25),
                        0 10px 20px -5px rgba(5, 150, 105, 0.1);
        }
        
        .glass { 
            background: rgba(255, 255, 255, 0.95); 
            backdrop-filter: blur(20px); 
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        @media (max-width: 640px) {
            .leaflet-control-attribution {
                font-size: 8px;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-primary-600 via-primary-700 to-primary-800">
    <!-- Background Decoration -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-white/10 rounded-full blur-3xl"></div>
    </div>

    <!-- Header -->
    <header class="py-6 px-4 relative z-10">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <a href="<?= url('/') ?>" class="flex items-center gap-3 group">
                <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-lg">
                    <i data-lucide="utensils" class="w-6 h-6 text-primary-600"></i>
                </div>
                <div class="text-white">
                    <h1 class="text-xl font-bold">WHFood</h1>
                    <p class="text-sm text-white/70">Way Huwi Marketplace</p>
                </div>
            </a>
            <a href="<?= url('/') ?>" class="text-sm text-white/80 hover:text-white flex items-center gap-2 transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Kembali
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="py-8 px-4 relative z-10">
        <div class="max-w-4xl mx-auto">
            <!-- Page Title -->
            <div class="text-center mb-10">
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-3">
                    Daftar Sebagai Penjual
                </h2>
                <p class="text-white/80 max-w-xl mx-auto">
                    Bergabunglah dengan UMKM kuliner lainnya di Desa Way Huwi. Jangkau lebih banyak pelanggan dengan WHFood!
                </p>
            </div>

            <!-- Registration Form -->
            <form id="sellerRegistrationForm" method="POST" 
                  class="space-y-8" enctype="multipart/form-data">
                
                <!-- CSRF Token -->
                <input type="hidden" name="csrfToken" value="<?= htmlspecialchars($csrfToken) ?>">
                
                <!-- Single Form Card -->
                <div class="glass rounded-2xl p-6 md:p-8 shadow-2xl">
                    
                    <!-- Section 1: Account Information -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-sm font-bold">1</span>
                            Informasi Akun
                        </h3>
                        
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label for="fullName" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nama Lengkap <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="fullName" name="fullName" required
                                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:outline-none input-focus-ring transition-all"
                                       placeholder="Masukkan nama lengkap">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <input type="email" id="email" name="email" required
                                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:outline-none input-focus-ring transition-all"
                                       placeholder="email@contoh.com">
                            </div>
                            <div>
                                <label for="phoneNumber" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nomor Telepon <span class="text-red-500">*</span>
                                </label>
                                <input type="tel" id="phoneNumber" name="phoneNumber" required
                                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:outline-none input-focus-ring transition-all"
                                       placeholder="08xxxxxxxxxx" pattern="^08[0-9]{8,12}$">
                            </div>
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                    Password <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="password" id="password" name="password" required
                                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:outline-none input-focus-ring transition-all pr-12"
                                           placeholder="Min. 8 karakter" minlength="8">
                                    <button type="button" onclick="togglePassword('password')" 
                                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                        <i data-lucide="eye" class="w-5 h-5"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="border-gray-200 mb-8">
                    
                    <!-- Section 2: Store Information -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-sm font-bold">2</span>
                            Informasi Toko
                        </h3>
                        
                        <div class="grid md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label for="storeName" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nama Toko/Warung <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="storeName" name="storeName" required
                                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:outline-none input-focus-ring transition-all"
                                       placeholder="Contoh: Warung Makan Bu Dewi">
                            </div>
                            <div class="md:col-span-2">
                                <label for="storeDescription" class="block text-sm font-medium text-gray-700 mb-2">
                                    Deskripsi Toko
                                </label>
                                <textarea id="storeDescription" name="storeDescription" rows="2"
                                          class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:outline-none input-focus-ring transition-all resize-none"
                                          placeholder="Ceritakan tentang toko dan menu andalan Anda..."></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="border-gray-200 mb-8">
                    
                    <!-- Section 3: Location -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-sm font-bold">3</span>
                            Lokasi Toko
                        </h3>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Pilih Lokasi di Peta <span class="text-red-500">*</span>
                            </label>
                            
                            <!-- Search Box with Autocomplete -->
                            <div class="mb-3 relative">
                                <div class="relative">
                                    <input type="text" id="locationSearch" autocomplete="off"
                                           class="w-full px-4 py-3 pl-11 pr-20 rounded-xl border border-gray-200 focus:border-primary-500 focus:outline-none input-focus-ring transition-all"
                                           placeholder="Ketik untuk mencari lokasi...">
                                    <i data-lucide="search" class="w-5 h-5 text-gray-400 absolute left-4 top-1/2 -translate-y-1/2"></i>
                                    <div id="searchSpinner" class="hidden absolute right-4 top-1/2 -translate-y-1/2">
                                        <div class="w-5 h-5 border-2 border-primary-600 border-t-transparent rounded-full animate-spin"></div>
                                    </div>
                                </div>
                                <!-- Autocomplete Results -->
                                <div id="searchResults" class="hidden absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-60 overflow-y-auto">
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Ketik minimal 3 karakter untuk melihat saran lokasi</p>
                            </div>
                            
                            <div class="relative rounded-xl overflow-hidden border border-gray-200">
                                <div id="mapPicker" class="w-full h-[300px] z-0"></div>
                                <div class="absolute top-3 left-3 z-[1000]">
                                    <button type="button" id="useLocationBtn" onclick="mapPicker.useCurrentLocation()"
                                            class="flex items-center gap-2 px-3 py-2 bg-white hover:bg-gray-50 text-gray-700 rounded-lg shadow-md text-sm font-medium">
                                        <i data-lucide="locate" class="w-4 h-4 text-primary-600"></i>
                                        GPS
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" id="latitude" name="latitude" value="-5.3698" required>
                            <input type="hidden" id="longitude" name="longitude" value="105.2486" required>
                        </div>
                        
                        <div class="grid md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                                    Alamat Lengkap <span class="text-red-500">*</span>
                                </label>
                                <textarea id="address" name="address" rows="2" required
                                          class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:outline-none input-focus-ring transition-all resize-none"
                                          placeholder="Alamat akan terisi otomatis saat memilih lokasi..."></textarea>
                            </div>
                            <div>
                                <label for="village" class="block text-sm font-medium text-gray-700 mb-2">Desa/Kelurahan</label>
                                <input type="text" id="village" name="village" value="Way Huwi"
                                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:outline-none input-focus-ring transition-all">
                            </div>
                            <div>
                                <label for="district" class="block text-sm font-medium text-gray-700 mb-2">Kecamatan</label>
                                <input type="text" id="district" name="district" value="Jati Agung"
                                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:outline-none input-focus-ring transition-all">
                            </div>
                        </div>
                    </div>
                    
                    <hr class="border-gray-200 mb-8">
                    
                    <!-- Section 4: Bank Information -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-sm font-bold">4</span>
                            Informasi Pembayaran
                            <span class="text-xs font-normal text-gray-500">(Opsional)</span>
                        </h3>
                        
                        <div class="grid md:grid-cols-3 gap-4">
                            <div>
                                <label for="bankName" class="block text-sm font-medium text-gray-700 mb-2">Nama Bank</label>
                                <select id="bankName" name="bankName"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:outline-none input-focus-ring transition-all bg-white">
                                    <option value="">Pilih Bank</option>
                                    <option value="BCA">BCA</option>
                                    <option value="BRI">BRI</option>
                                    <option value="BNI">BNI</option>
                                    <option value="Mandiri">Mandiri</option>
                                    <option value="BSI">BSI</option>
                                    <option value="DANA">DANA</option>
                                    <option value="OVO">OVO</option>
                                    <option value="GoPay">GoPay</option>
                                </select>
                            </div>
                            <div>
                                <label for="bankAccountNumber" class="block text-sm font-medium text-gray-700 mb-2">Nomor Rekening</label>
                                <input type="text" id="bankAccountNumber" name="bankAccountNumber"
                                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:outline-none input-focus-ring transition-all"
                                       placeholder="Nomor rekening">
                            </div>
                            <div>
                                <label for="bankAccountName" class="block text-sm font-medium text-gray-700 mb-2">Nama Pemilik</label>
                                <input type="text" id="bankAccountName" name="bankAccountName"
                                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:outline-none input-focus-ring transition-all"
                                       placeholder="Sesuai buku rekening">
                            </div>
                        </div>
                    </div>
                    
                    <hr class="border-gray-200 my-8">
                    
                    <!-- Terms & Submit -->
                    <div class="space-y-6">
                        <!-- Terms Checkbox -->
                        <div class="flex items-start gap-3">
                            <input type="checkbox" id="terms" name="terms" required
                                   class="mt-1 w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            <label for="terms" class="text-sm text-gray-600">
                                Saya setuju dengan <a href="#" class="text-primary-600 hover:underline">Syarat & Ketentuan</a>
                                dan <a href="#" class="text-primary-600 hover:underline">Kebijakan Privasi</a>
                            </label>
                        </div>
                        
                        <!-- Submit Button -->
                        <button type="submit"
                                class="w-full py-4 px-6 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all flex items-center justify-center gap-2">
                            <i data-lucide="check-circle" class="w-5 h-5"></i>
                            Daftar Sekarang
                        </button>
                        
                        <!-- Login Link -->
                        <p class="text-center text-sm text-gray-600">
                            Sudah punya akun? 
                            <a href="<?= url('masuk') ?>" class="text-primary-600 hover:underline font-medium">Masuk di sini</a>
                        </p>
                    </div>
                    
                </div>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-8 px-4 mt-12 relative z-10">
        <div class="max-w-4xl mx-auto text-center text-sm text-white/60">
            <p>&copy; <?= date('Y') ?> WHFood - Platform UMKM Kuliner Desa Way Huwi, Lampung Selatan</p>
        </div>
    </footer>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" 
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" 
            crossorigin=""></script>
    
    <!-- Smart Map Picker JS -->
    <script src="/assets/js/smart-map-picker.js"></script>
    
    <!-- Initialize Map -->
    <script>
        // Global map picker instance
        let mapPicker;
        
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Smart Map Picker
            mapPicker = new SmartMapPicker('mapPicker', {
                latInputId: 'latitude',
                lngInputId: 'longitude',
                addressInputId: 'address',
                initialLat: -5.3698,  // Way Huwi, Lampung Selatan
                initialLng: 105.2486,
                zoom: 15
            });
            
            // Handle window resize untuk responsive map
            let resizeTimeout;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(function() {
                    if (mapPicker) {
                        mapPicker.invalidateSize();
                    }
                }, 250);
            });
            
            // Cleanup saat user meninggalkan halaman
            window.addEventListener('beforeunload', function() {
                if (mapPicker) {
                    mapPicker.destroy();
                }
            });
        });
        
        // Toggle password visibility
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            input.type = input.type === 'password' ? 'text' : 'password';
        }
        
        // Form validation
        document.getElementById('sellerRegistrationForm').addEventListener('submit', function(e) {
            const lat = document.getElementById('latitude').value;
            const lng = document.getElementById('longitude').value;
            
            if (!lat || !lng) {
                e.preventDefault();
                alert('Silakan pilih lokasi toko di peta');
                return;
            }
            
            // Additional validation can be added here
        });
        
        // Location Search with Autocomplete
        const searchInput = document.getElementById('locationSearch');
        const searchResults = document.getElementById('searchResults');
        const searchSpinner = document.getElementById('searchSpinner');
        let searchTimeout;
        
        // Debounced search as user types
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            clearTimeout(searchTimeout);
            
            if (query.length < 3) {
                searchResults.classList.add('hidden');
                searchResults.innerHTML = '';
                return;
            }
            
            searchTimeout = setTimeout(() => searchLocations(query), 400);
        });
        
        async function searchLocations(query) {
            searchSpinner.classList.remove('hidden');
            
            try {
                // Use Nominatim with Indonesian language and country code
                const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5&addressdetails=1&countrycodes=id&accept-language=id`, {
                    headers: { 'Accept-Language': 'id' }
                });
                const results = await response.json();
                
                if (results.length > 0) {
                    searchResults.innerHTML = results.map((r, i) => `
                        <div class="search-result-item px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-0 flex items-start gap-3"
                             data-lat="${r.lat}" data-lng="${r.lon}" data-address="${r.display_name}">
                            <i data-lucide="map-pin" class="w-4 h-4 text-primary-600 flex-shrink-0 mt-0.5"></i>
                            <div>
                                <p class="text-sm font-medium text-gray-900">${r.display_name.split(',')[0]}</p>
                                <p class="text-xs text-gray-500 line-clamp-1">${r.display_name}</p>
                            </div>
                        </div>
                    `).join('');
                    searchResults.classList.remove('hidden');
                    lucide.createIcons();
                    
                    // Add click handlers
                    document.querySelectorAll('.search-result-item').forEach(item => {
                        item.addEventListener('click', function() {
                            selectLocation(
                                parseFloat(this.dataset.lat),
                                parseFloat(this.dataset.lng),
                                this.dataset.address
                            );
                        });
                    });
                } else {
                    searchResults.innerHTML = `
                        <div class="px-4 py-3 text-sm text-gray-500 text-center">
                            <i data-lucide="search-x" class="w-5 h-5 mx-auto mb-1 text-gray-400"></i>
                            Tidak ada hasil. Coba kata kunci lain.
                        </div>
                    `;
                    searchResults.classList.remove('hidden');
                    lucide.createIcons();
                }
            } catch (error) {
                searchResults.innerHTML = `
                    <div class="px-4 py-3 text-sm text-red-500 text-center">
                        Gagal mencari. Periksa koneksi internet.
                    </div>
                `;
                searchResults.classList.remove('hidden');
            } finally {
                searchSpinner.classList.add('hidden');
            }
        }
        
        function selectLocation(lat, lng, address) {
            // Update map
            if (mapPicker && mapPicker.map) {
                mapPicker.map.setView([lat, lng], 17);
                if (mapPicker.marker) {
                    mapPicker.marker.setLatLng([lat, lng]);
                }
            }
            
            // Update form fields
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;
            document.getElementById('address').value = address;
            
            // Clear search
            searchInput.value = '';
            searchResults.classList.add('hidden');
            searchResults.innerHTML = '';
        }
        
        // Hide results when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.classList.add('hidden');
            }
        });
        
        // Initialize Lucide icons
        lucide.createIcons();
    </script>
</body>
</html>
