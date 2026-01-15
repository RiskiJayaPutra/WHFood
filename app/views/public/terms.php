<?php
declare(strict_types=1);

$pageTitle = 'Syarat & Ketentuan - WHFood';
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
    
    <!-- Navbar -->
    <header class="bg-white border-b border-gray-100 sticky top-0 z-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="<?= url('/') ?>" class="flex items-center gap-2">
                    <div class="w-10 h-10 bg-primary-600 rounded-xl flex items-center justify-center">
                        <i data-lucide="utensils" class="w-5 h-5 text-white"></i>
                    </div>
                    <span class="font-bold text-gray-900">WHFood</span>
                </a>
                
                <a href="<?= url('/') ?>" class="text-gray-500 hover:text-gray-900 flex items-center gap-2 text-sm font-medium">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Kembali
                </a>
            </div>
        </div>
    </header>
    
    <!-- Content -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="bg-white rounded-2xl shadow-sm p-8 md:p-12">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Syarat & Ketentuan</h1>
            <p class="text-gray-500 mb-8">Terakhir diperbarui: <?= date('d F Y') ?></p>
            
            <div class="prose prose-green max-w-none text-gray-600">
                <p>Selamat datang di WHFood. Harap membaca Syarat & Ketentuan ini dengan seksama sebelum menggunakan layanan kami.</p>
                
                <h3 class="text-lg font-bold text-gray-900 mt-6 mb-3">1. Pendahuluan</h3>
                <p>WHFood adalah platform marketplace yang menghubungkan pelapak/UMKM kuliner di Desa Way Huwi dengan pembeli. Dengan mengakses atau menggunakan WHFood, Anda setuju untuk terikat dengan syarat dan ketentuan ini.</p>
                
                <h3 class="text-lg font-bold text-gray-900 mt-6 mb-3">2. Akun Pengguna</h3>
                <ul class="list-disc pl-5 space-y-2">
                    <li>Anda bertanggung jawab untuk menjaga kerahasiaan akun dan password Anda.</li>
                    <li>Anda setuju untuk memberikan informasi yang akurat, lengkap, dan terbaru saat mendaftar.</li>
                    <li>Kami berhak menonaktifkan akun jika ditemukan pelanggaran terhadap syarat ini.</li>
                </ul>
                
                <h3 class="text-lg font-bold text-gray-900 mt-6 mb-3">3. Transaksi</h3>
                <p>WHFood hanya bertindak sebagai perantara informasi. Transaksi pembayaran dan pengiriman dilakukan langsung antara Penjual dan Pembeli atau melalui metode yang disepakati bersama. Kami tidak menyimpan dana pengguna.</p>
                
                <h3 class="text-lg font-bold text-gray-900 mt-6 mb-3">4. Konten Larangan</h3>
                <p>Pengguna dilarang memuat konten yang mengandung unsur SARA, pornografi, ujaran kebencian, atau hal ilegal lainnya. Penjual wajib memastikan makanan yang dijual aman dan halal (jika diklaim halal).</p>
                
                <h3 class="text-lg font-bold text-gray-900 mt-6 mb-3">5. Perubahan Ketentuan</h3>
                <p>Kami dapat mengubah Syarat & Ketentuan ini sewaktu-waktu. Perubahan akan berlaku efektif segera setelah diposting di halaman ini.</p>
            </div>
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="text-center py-8 text-sm text-gray-500">
        <p>&copy; <?= date('Y') ?> WHFood - Way Huwi Marketplace</p>
    </footer>
    
    <script>lucide.createIcons();</script>
</body>
</html>
