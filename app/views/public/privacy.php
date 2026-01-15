<?php
declare(strict_types=1);

$pageTitle = 'Kebijakan Privasi - WHFood';
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
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Kebijakan Privasi</h1>
            <p class="text-gray-500 mb-8">Terakhir diperbarui: <?= date('d F Y') ?></p>
            
            <div class="prose prose-green max-w-none text-gray-600">
                <p>Di WHFood, kami menghargai privasi Anda. Kebijakan ini menjelaskan bagaimana kami mengumpulkan, menggunakan, dan melindungi informasi pribadi Anda.</p>
                
                <h3 class="text-lg font-bold text-gray-900 mt-6 mb-3">1. Informasi yang Kami Kumpulkan</h3>
                <p>Kami mengumpulkan informasi yang Anda berikan langsung kepada kami, seperti saat Anda membuat akun, mendaftar sebagai penjual, atau menghubungi kami. Informasi ini dapat mencakup nama, alamat email, nomor telepon, dan lokasi.</p>
                
                <h3 class="text-lg font-bold text-gray-900 mt-6 mb-3">2. Penggunaan Informasi</h3>
                <p>Kami menggunakan informasi tersebut untuk:</p>
                <ul class="list-disc pl-5 space-y-2">
                    <li>Menyediakan layanan marketplace kami.</li>
                    <li>Menghubungkan pembeli dengan penjual terdekat.</li>
                    <li>Mengirimkan notifikasi terkait akun atau transaksi.</li>
                    <li>Meningkatkan keamanan dan kenyamanan layanan.</li>
                </ul>
                
                <h3 class="text-lg font-bold text-gray-900 mt-6 mb-3">3. Berbagi Informasi</h3>
                <p>Kami tidak menjual data pribadi Anda kepada pihak ketiga. Kami hanya membagikan informasi (seperti nama dan alamat pengiriman) kepada Penjual/Pembeli yang terkait langsung dalam transaksi Anda.</p>
                
                <h3 class="text-lg font-bold text-gray-900 mt-6 mb-3">4. Keamanan Data</h3>
                <p>Kami menerapkan langkah-langkah keamanan yang wajar untuk melindungi data Anda dari akses yang tidak sah, namun perlu diingat bahwa tidak ada metode transmisi data internet yang 100% aman.</p>
                
                <h3 class="text-lg font-bold text-gray-900 mt-6 mb-3">5. Kontak Kami</h3>
                <p>Jika Anda memiliki pertanyaan tentang kebijakan privasi ini, silakan hubungi kami melalui kontak yang tersedia di website.</p>
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
