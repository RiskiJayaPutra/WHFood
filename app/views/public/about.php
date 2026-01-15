<?php
declare(strict_types=1);

$pageTitle = 'Tentang Kami - WHFood';
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
    
    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-primary-600 to-primary-800 text-white py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="w-20 h-20 bg-white/20 backdrop-blur rounded-2xl flex items-center justify-center mx-auto mb-6">
                <i data-lucide="utensils" class="w-10 h-10 text-white"></i>
            </div>
            <h1 class="text-4xl font-bold mb-4">Tentang WHFood</h1>
            <p class="text-xl text-primary-100 max-w-2xl mx-auto">Marketplace Kuliner Desa Way Huwi - Menghubungkan UMKM Kuliner dengan Pelanggan</p>
        </div>
    </section>
    
    <!-- Content -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        <!-- About Card -->
        <div class="bg-white rounded-2xl shadow-sm p-8 md:p-12 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                <i data-lucide="info" class="w-6 h-6 text-primary-600"></i>
                Apa itu WHFood?
            </h2>
            <div class="prose prose-green max-w-none text-gray-600 space-y-4">
                <p><strong>WHFood (Way Huwi Food)</strong> adalah platform marketplace digital yang dirancang khusus untuk membantu pelapak dan UMKM kuliner di Desa Way Huwi, Lampung Selatan, mempromosikan dan menjual produk makanan mereka secara online.</p>
                <p>Kami percaya bahwa setiap usaha kuliner lokal layak mendapatkan kesempatan untuk dikenal lebih luas. Dengan WHFood, pembeli dapat dengan mudah menemukan berbagai pilihan makanan lezat dari warung-warung terdekat, melihat menu lengkap, dan memesan langsung via WhatsApp.</p>
            </div>
        </div>
        
        <!-- Mission & Vision -->
        <div class="grid md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-2xl shadow-sm p-8">
                <div class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center mb-4">
                    <i data-lucide="target" class="w-6 h-6 text-primary-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Misi Kami</h3>
                <ul class="text-gray-600 space-y-2">
                    <li class="flex items-start gap-2">
                        <i data-lucide="check-circle" class="w-5 h-5 text-primary-500 mt-0.5 flex-shrink-0"></i>
                        Membantu UMKM kuliner lokal berkembang di era digital
                    </li>
                    <li class="flex items-start gap-2">
                        <i data-lucide="check-circle" class="w-5 h-5 text-primary-500 mt-0.5 flex-shrink-0"></i>
                        Memudahkan warga menemukan kuliner lezat di sekitar mereka
                    </li>
                    <li class="flex items-start gap-2">
                        <i data-lucide="check-circle" class="w-5 h-5 text-primary-500 mt-0.5 flex-shrink-0"></i>
                        Menjadi jembatan antara penjual dan pembeli yang efisien
                    </li>
                </ul>
            </div>
            
            <div class="bg-white rounded-2xl shadow-sm p-8">
                <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center mb-4">
                    <i data-lucide="eye" class="w-6 h-6 text-amber-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Visi Kami</h3>
                <p class="text-gray-600">Menjadi platform marketplace kuliner terdepan di Desa Way Huwi dan sekitarnya, yang memberdayakan pelaku UMKM lokal dan memberikan pengalaman terbaik bagi pelanggan dalam menemukan serta menikmati kuliner khas daerah.</p>
            </div>
        </div>
        
        <!-- Features -->
        <div class="bg-white rounded-2xl shadow-sm p-8 md:p-12 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                <i data-lucide="sparkles" class="w-6 h-6 text-primary-600"></i>
                Fitur Unggulan
            </h2>
            <div class="grid sm:grid-cols-2 gap-6">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i data-lucide="store" class="w-5 h-5 text-green-600"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900">Profil Toko Lengkap</h4>
                        <p class="text-sm text-gray-500">Penjual dapat membuat profil toko dengan foto, lokasi, dan jam operasional</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i data-lucide="package" class="w-5 h-5 text-blue-600"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900">Katalog Produk</h4>
                        <p class="text-sm text-gray-500">Tampilkan menu dengan foto, deskripsi, dan harga yang menarik</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i data-lucide="message-circle" class="w-5 h-5 text-purple-600"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900">Order via WhatsApp</h4>
                        <p class="text-sm text-gray-500">Pembeli langsung terhubung dengan penjual melalui WhatsApp</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i data-lucide="star" class="w-5 h-5 text-amber-600"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900">Sistem Rating</h4>
                        <p class="text-sm text-gray-500">Pembeli dapat memberikan ulasan dan rating untuk produk</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 bg-rose-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i data-lucide="map-pin" class="w-5 h-5 text-rose-600"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900">Lokasi & Navigasi</h4>
                        <p class="text-sm text-gray-500">Integrasi peta untuk navigasi ke lokasi toko</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 bg-teal-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i data-lucide="credit-card" class="w-5 h-5 text-teal-600"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900">QRIS Payment</h4>
                        <p class="text-sm text-gray-500">Penjual dapat menampilkan QRIS untuk pembayaran digital</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Contact -->
        <div class="bg-gradient-to-br from-primary-600 to-primary-700 rounded-2xl shadow-sm p-8 md:p-12 text-white text-center">
            <h2 class="text-2xl font-bold mb-4">Ada Pertanyaan?</h2>
            <p class="text-primary-100 mb-6">Jangan ragu untuk menghubungi kami jika Anda memiliki pertanyaan atau ingin bergabung sebagai penjual di WHFood.</p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="mailto:support@whfood.id" class="inline-flex items-center gap-2 px-6 py-3 bg-white text-primary-700 font-semibold rounded-xl hover:bg-primary-50 transition-colors">
                    <i data-lucide="mail" class="w-5 h-5"></i>
                    Email Kami
                </a>
                <a href="https://wa.me/6281234567890" target="_blank" class="inline-flex items-center gap-2 px-6 py-3 bg-white/20 text-white font-semibold rounded-xl hover:bg-white/30 transition-colors">
                    <i data-lucide="message-circle" class="w-5 h-5"></i>
                    WhatsApp
                </a>
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
