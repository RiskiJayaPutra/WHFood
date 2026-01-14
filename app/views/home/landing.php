<?php

/**
 * ============================================================================
 * WHFood - Premium Landing Page
 * ============================================================================
 * 
 * Landing page visual-heavy dengan design setara Grab/Gofood.
 * Menggunakan Food Psychology untuk memicu rasa lapar pengunjung.
 * 
 * @package     WHFood
 * @author      WHFood Development Team
 * @version     1.0.0
 * @since       2026-01-12
 */

declare(strict_types=1);

$pageTitle = 'WHFood - Way Huwi Food Marketplace';
$pageDescription = 'Pesan makanan enak dari UMKM lokal Desa Way Huwi, Lampung Selatan. Cepat, mudah, langsung via WhatsApp!';
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta name="theme-color" content="#059669">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta property="og:type" content="website">
    
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
                }
            }
        }
    </script>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        /* Base Styles */
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        
        /* Gradient Overlays */
        .hero-gradient {
            background: linear-gradient(135deg, rgba(5, 150, 105, 0.95) 0%, rgba(4, 120, 87, 0.9) 100%);
        }
        
        .food-gradient {
            background: linear-gradient(180deg, transparent 0%, rgba(0,0,0,0.7) 100%);
        }
        
        /* Glassmorphism */
        .glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
        
        .glass-dark {
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        
        /* Floating Animation */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .animate-float {
            animation: float 4s ease-in-out infinite;
        }
        
        /* Pulse Glow for CTA */
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(217, 119, 6, 0.4); }
            50% { box-shadow: 0 0 20px 10px rgba(217, 119, 6, 0.2); }
        }
        
        .animate-pulse-glow {
            animation: pulse-glow 2s ease-in-out infinite;
        }
        
        /* Staggered Animation Classes for GSAP/AOS */
        .stagger-1 { --stagger-delay: 0ms; }
        .stagger-2 { --stagger-delay: 100ms; }
        .stagger-3 { --stagger-delay: 200ms; }
        .stagger-4 { --stagger-delay: 300ms; }
        .stagger-5 { --stagger-delay: 400ms; }
        
        /* WhatsApp Button Hover Effect */
        .whatsapp-btn {
            background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
            transition: all 0.3s ease;
        }
        
        .whatsapp-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(37, 211, 102, 0.3);
        }
        
        /* Food Image Zoom Effect */
        .food-img-container:hover .food-img {
            transform: scale(1.1);
        }
        
        .food-img {
            transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* Badge Shine Effect */
        .badge-verified {
            position: relative;
            overflow: hidden;
        }
        
        .badge-verified::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: shine 3s infinite;
        }
        
        @keyframes shine {
            0% { left: -100%; }
            50% { left: 100%; }
            100% { left: 100%; }
        }
    </style>
</head>
<body class="bg-gray-50 antialiased">

    <!-- ========================================================================
         HERO SECTION
         Premium hero with floating search bar, food images, and staggered text
         ======================================================================== -->
    <section class="relative min-h-screen overflow-hidden">
        <!-- Background Image with Overlay -->
        <div class="absolute inset-0 z-0">
            <img src="https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=1920&q=80" 
                 alt="Delicious Food Background" 
                 class="w-full h-full object-cover">
            <div class="absolute inset-0 hero-gradient"></div>
        </div>
        
        <!-- Floating Food Elements (Decorative) -->
        <div class="absolute top-20 right-10 w-32 h-32 opacity-80 animate-float hidden lg:block" style="animation-delay: 0s;">
            <img src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=200&q=80" 
                 alt="Burger" class="w-full h-full object-cover rounded-full shadow-2xl">
        </div>
        <div class="absolute bottom-32 left-10 w-24 h-24 opacity-80 animate-float hidden lg:block" style="animation-delay: 1s;">
            <img src="https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=200&q=80" 
                 alt="Pizza" class="w-full h-full object-cover rounded-full shadow-2xl">
        </div>
        <div class="absolute top-1/3 right-1/4 w-20 h-20 opacity-60 animate-float hidden xl:block" style="animation-delay: 2s;">
            <img src="https://images.unsplash.com/photo-1551024601-bec78aea704b?w=200&q=80" 
                 alt="Donut" class="w-full h-full object-cover rounded-full shadow-2xl">
        </div>
        
        <!-- Navigation -->
        <nav class="relative z-20 px-4 sm:px-6 lg:px-8 py-6">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <!-- Logo -->
                <a href="/" class="flex items-center gap-3 group">
                    <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-all">
                        <i data-lucide="utensils" class="w-6 h-6 text-primary-600"></i>
                    </div>
                    <div class="text-white">
                        <h1 class="text-xl font-bold">WHFood</h1>
                        <p class="text-xs text-white/70">Way Huwi Marketplace</p>
                    </div>
                </a>
                
                <!-- Nav Links -->
                <div class="hidden md:flex items-center gap-8">
                    <a href="#menu" class="text-white/90 hover:text-white font-medium transition-colors">Menu</a>
                    <a href="#sellers" class="text-white/90 hover:text-white font-medium transition-colors">Penjual</a>
                    <a href="#about" class="text-white/90 hover:text-white font-medium transition-colors">Tentang</a>
                </div>
                
                <!-- CTA Buttons -->
                <div class="flex items-center gap-3">
                    <?php if (isLoggedIn()): ?>
                        <?php $currentUser = user(); ?>
                        <div class="hidden sm:flex items-center gap-3 text-white/90 font-medium mr-2">
                            <span>Hai, <?= e(explode(' ', $currentUser['fullName'])[0]) ?></span>
                        </div>
                        
                        <?php if (isAdmin()): ?>
                            <a href="<?= url('admin/dashboard') ?>" 
                               class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all">
                                Admin Panel
                            </a>
                        <?php elseif (isSeller()): ?>
                            <a href="<?= url('seller/dashboard') ?>" 
                               class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all">
                                Dashboard Toko
                            </a>
                        <?php endif; ?>
                        
                        <a href="<?= url('keluar') ?>" 
                           class="px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white font-semibold rounded-xl backdrop-blur-sm transition-all border border-white/20">
                            Keluar
                        </a>
                    <?php else: ?>
                        <a href="<?= url('masuk') ?>" class="hidden sm:inline-flex text-white/90 hover:text-white font-medium transition-colors">
                            Masuk
                        </a>
                        <a href="<?= url('daftar-penjual') ?>" 
                           class="px-5 py-2.5 bg-accent-500 hover:bg-accent-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all">
                            Jual Makanan
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
        
        <!-- Hero Content -->
        <div class="relative z-10 px-4 sm:px-6 lg:px-8 pt-16 pb-32 md:pt-24 md:pb-40">
            <div class="max-w-4xl mx-auto text-center">
                <!-- Badge -->
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-white/10 glass-dark rounded-full text-white text-sm mb-8 stagger-1"
                     data-aos="fade-up" data-aos-delay="0">
                    <span class="w-2 h-2 bg-accent-400 rounded-full animate-pulse"></span>
                    Platform UMKM Kuliner Desa Way Huwi
                </div>
                
                <!-- Main Headline -->
                <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-extrabold text-white leading-tight mb-6">
                    <span class="block stagger-2" data-aos="fade-up" data-aos-delay="100">
                        Laper? Pesan
                    </span>
                    <span class="block text-accent-300 stagger-3" data-aos="fade-up" data-aos-delay="200">
                        Makanan Enak
                    </span>
                    <span class="block stagger-4" data-aos="fade-up" data-aos-delay="300">
                        dari UMKM Lokal
                    </span>
                </h1>
                
                <!-- Subheadline -->
                <p class="text-lg sm:text-xl text-white/80 max-w-2xl mx-auto mb-10 stagger-5"
                   data-aos="fade-up" data-aos-delay="400">
                    Nikmati cita rasa autentik dari warung-warung terbaik di sekitarmu. 
                    Pesan langsung via WhatsApp, cepat dan mudah!
                </p>
                
                <!-- Floating Search Bar -->
                <div class="max-w-2xl mx-auto stagger-5" data-aos="fade-up" data-aos-delay="500">
                    <div class="relative glass rounded-2xl shadow-2xl p-2">
                        <div class="flex items-center gap-2">
                            <!-- Search Icon -->
                            <div class="pl-4 text-gray-400">
                                <i data-lucide="search" class="w-5 h-5"></i>
                            </div>
                            
                            <!-- Search Input -->
                            <input type="text" 
                                   placeholder="Cari makanan atau warung favorit..." 
                                   class="flex-1 px-4 py-4 bg-transparent text-gray-800 placeholder-gray-400 focus:outline-none text-lg">
                            
                            <!-- Location Button -->
                            <button class="hidden sm:flex items-center gap-2 px-4 py-2 text-gray-500 hover:text-primary-600 transition-colors border-l border-gray-200">
                                <i data-lucide="map-pin" class="w-5 h-5"></i>
                                <span class="font-medium">Way Huwi</span>
                            </button>
                            
                            <!-- Search Button -->
                            <button class="px-6 py-4 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-all flex items-center gap-2">
                                <span class="hidden sm:inline">Cari</span>
                                <i data-lucide="arrow-right" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Popular Searches -->
                    <div class="flex flex-wrap items-center justify-center gap-2 mt-6">
                        <span class="text-white/60 text-sm">Populer:</span>
                        <a href="#" class="px-3 py-1.5 bg-white/10 hover:bg-white/20 text-white text-sm rounded-full transition-colors">Nasi Uduk</a>
                        <a href="#" class="px-3 py-1.5 bg-white/10 hover:bg-white/20 text-white text-sm rounded-full transition-colors">Ayam Geprek</a>
                        <a href="#" class="px-3 py-1.5 bg-white/10 hover:bg-white/20 text-white text-sm rounded-full transition-colors">Es Teh</a>
                        <a href="#" class="px-3 py-1.5 bg-white/10 hover:bg-white/20 text-white text-sm rounded-full transition-colors">Martabak</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Stats Bar -->
        <div class="absolute bottom-0 left-0 right-0 z-20">
            <div class="max-w-5xl mx-auto px-4 transform translate-y-1/2">
                <div class="glass rounded-2xl shadow-xl p-6 md:p-8 grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div class="text-center" data-aos="fade-up" data-aos-delay="100">
                        <div class="text-3xl md:text-4xl font-bold text-primary-600">50+</div>
                        <div class="text-gray-500 text-sm mt-1">UMKM Terdaftar</div>
                    </div>
                    <div class="text-center" data-aos="fade-up" data-aos-delay="200">
                        <div class="text-3xl md:text-4xl font-bold text-primary-600">200+</div>
                        <div class="text-gray-500 text-sm mt-1">Menu Tersedia</div>
                    </div>
                    <div class="text-center" data-aos="fade-up" data-aos-delay="300">
                        <div class="text-3xl md:text-4xl font-bold text-primary-600">1000+</div>
                        <div class="text-gray-500 text-sm mt-1">Pesanan Sukses</div>
                    </div>
                    <div class="text-center" data-aos="fade-up" data-aos-delay="400">
                        <div class="text-3xl md:text-4xl font-bold text-accent-600">4.8</div>
                        <div class="text-gray-500 text-sm mt-1">Rating Rata-rata</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========================================================================
         PRODUCT CARDS SECTION
         Food cards with hover effects, verified badges, and WhatsApp CTA
         ======================================================================== -->
    <section id="menu" class="pt-32 pb-20 px-4 sm:px-6 lg:px-8 bg-gray-50">
        <div class="max-w-7xl mx-auto">
            <!-- Section Header -->
            <div class="text-center mb-12" data-aos="fade-up">
                <span class="inline-block px-4 py-1.5 bg-primary-100 text-primary-700 text-sm font-semibold rounded-full mb-4">
                    <i data-lucide="flame" class="w-4 h-4 inline-block mr-1"></i>
                    Paling Diminati
                </span>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Menu <span class="text-primary-600">Populer</span> Hari Ini
                </h2>
                <p class="text-gray-500 max-w-xl mx-auto">
                    Pilihan favorit dari warung-warung terbaik di Way Huwi
                </p>
            </div>
            
            <!-- Category Tabs -->
            <div class="flex flex-wrap justify-center gap-3 mb-10" data-aos="fade-up" data-aos-delay="100">
                <button class="px-5 py-2.5 bg-primary-600 text-white font-semibold rounded-xl transition-all">
                    Semua
                </button>
                <button class="px-5 py-2.5 bg-white hover:bg-gray-100 text-gray-700 font-medium rounded-xl border border-gray-200 transition-all">
                    Makanan Berat
                </button>
                <button class="px-5 py-2.5 bg-white hover:bg-gray-100 text-gray-700 font-medium rounded-xl border border-gray-200 transition-all">
                    Snack
                </button>
                <button class="px-5 py-2.5 bg-white hover:bg-gray-100 text-gray-700 font-medium rounded-xl border border-gray-200 transition-all">
                    Minuman
                </button>
                <button class="px-5 py-2.5 bg-white hover:bg-gray-100 text-gray-700 font-medium rounded-xl border border-gray-200 transition-all">
                    Dessert
                </button>
            </div>
            
            <!-- Product Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                
                <!-- ============================================
                     PRODUCT CARD COMPONENT - VERIFIED SELLER
                     ============================================ -->
                <article class="group bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden transform hover:scale-105"
                         data-aos="fade-up" data-aos-delay="100">
                    <!-- Image Container -->
                    <div class="relative food-img-container aspect-square overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400&q=80" 
                             alt="Nasi Uduk Komplit" 
                             class="food-img w-full h-full object-cover">
                        
                        <!-- Gradient Overlay -->
                        <div class="absolute inset-0 food-gradient opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        
                        <!-- Verified Badge -->
                        <div class="badge-verified absolute top-3 left-3 flex items-center gap-1.5 px-3 py-1.5 bg-primary-600 text-white text-xs font-semibold rounded-full shadow-lg">
                            <i data-lucide="badge-check" class="w-3.5 h-3.5"></i>
                            Terverifikasi
                        </div>
                        
                        <!-- Discount Badge (Optional) -->
                        <div class="absolute top-3 right-3 px-2.5 py-1 bg-accent-500 text-white text-xs font-bold rounded-lg shadow-lg">
                            -20%
                        </div>
                        
                        <!-- Quick Action - Favorite -->
                        <button class="absolute bottom-3 right-3 w-10 h-10 bg-white/90 hover:bg-white rounded-full flex items-center justify-center shadow-lg opacity-0 group-hover:opacity-100 transition-all transform translate-y-2 group-hover:translate-y-0">
                            <i data-lucide="heart" class="w-5 h-5 text-gray-600 hover:text-red-500 transition-colors"></i>
                        </button>
                    </div>
                    
                    <!-- Card Content -->
                    <div class="p-5">
                        <!-- Seller Info -->
                        <div class="flex items-center gap-2 mb-3">
                            <img src="https://ui-avatars.com/api/?name=Bu+Dewi&background=059669&color=fff&size=32" 
                                 alt="Bu Dewi" 
                                 class="w-6 h-6 rounded-full">
                            <span class="text-sm text-gray-500">Warung Bu Dewi</span>
                            <div class="flex items-center gap-1 ml-auto">
                                <i data-lucide="star" class="w-4 h-4 text-accent-500 fill-accent-500"></i>
                                <span class="text-sm font-medium text-gray-700">4.9</span>
                            </div>
                        </div>
                        
                        <!-- Product Name -->
                        <h3 class="font-bold text-gray-900 text-lg mb-2 line-clamp-2 group-hover:text-primary-600 transition-colors">
                            Nasi Uduk Komplit
                        </h3>
                        
                        <!-- Product Description -->
                        <p class="text-gray-500 text-sm mb-4 line-clamp-2">
                            Nasi uduk gurih dengan ayam goreng renyah, tempe orek, dan sambal pedas
                        </p>
                        
                        <!-- Price & CTA -->
                        <div class="flex items-end justify-between">
                            <div>
                                <span class="text-xs text-gray-400 line-through">Rp18.000</span>
                                <div class="text-xl font-bold text-primary-600">Rp15.000</div>
                            </div>
                            
                            <!-- WhatsApp Order Button -->
                            <a href="https://wa.me/6281234567890?text=Halo,%20saya%20mau%20pesan%20Nasi%20Uduk%20Komplit" 
                               target="_blank"
                               class="whatsapp-btn flex items-center gap-2 px-4 py-2.5 text-white font-semibold rounded-xl">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                                <span class="hidden sm:inline">Pesan</span>
                            </a>
                        </div>
                    </div>
                </article>
                
                <!-- ============================================
                     PRODUCT CARD COMPONENT - REGULAR SELLER
                     ============================================ -->
                <article class="group bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden transform hover:scale-105"
                         data-aos="fade-up" data-aos-delay="200">
                    <div class="relative food-img-container aspect-square overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1626804475297-41608ea09aeb?w=400&q=80" 
                             alt="Ayam Geprek" 
                             class="food-img w-full h-full object-cover">
                        <div class="absolute inset-0 food-gradient opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        
                        <!-- Best Seller Badge -->
                        <div class="absolute top-3 left-3 flex items-center gap-1.5 px-3 py-1.5 bg-accent-500 text-white text-xs font-semibold rounded-full shadow-lg">
                            <i data-lucide="flame" class="w-3.5 h-3.5"></i>
                            Best Seller
                        </div>
                        
                        <button class="absolute bottom-3 right-3 w-10 h-10 bg-white/90 hover:bg-white rounded-full flex items-center justify-center shadow-lg opacity-0 group-hover:opacity-100 transition-all transform translate-y-2 group-hover:translate-y-0">
                            <i data-lucide="heart" class="w-5 h-5 text-gray-600 hover:text-red-500 transition-colors"></i>
                        </button>
                    </div>
                    
                    <div class="p-5">
                        <div class="flex items-center gap-2 mb-3">
                            <img src="https://ui-avatars.com/api/?name=Geprek+Mantap&background=d97706&color=fff&size=32" 
                                 alt="Geprek Mantap" 
                                 class="w-6 h-6 rounded-full">
                            <span class="text-sm text-gray-500">Geprek Mantap</span>
                            <div class="flex items-center gap-1 ml-auto">
                                <i data-lucide="star" class="w-4 h-4 text-accent-500 fill-accent-500"></i>
                                <span class="text-sm font-medium text-gray-700">4.8</span>
                            </div>
                        </div>
                        
                        <h3 class="font-bold text-gray-900 text-lg mb-2 line-clamp-2 group-hover:text-primary-600 transition-colors">
                            Ayam Geprek Level 5
                        </h3>
                        
                        <p class="text-gray-500 text-sm mb-4 line-clamp-2">
                            Ayam geprek super pedas dengan sambal level 5 dan nasi hangat
                        </p>
                        
                        <div class="flex items-end justify-between">
                            <div class="text-xl font-bold text-primary-600">Rp17.000</div>
                            
                            <a href="https://wa.me/6281234567890?text=Halo,%20saya%20mau%20pesan%20Ayam%20Geprek" 
                               target="_blank"
                               class="whatsapp-btn flex items-center gap-2 px-4 py-2.5 text-white font-semibold rounded-xl">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                                <span class="hidden sm:inline">Pesan</span>
                            </a>
                        </div>
                    </div>
                </article>
                
                <!-- ============================================
                     PRODUCT CARD - VERIFIED + NEW
                     ============================================ -->
                <article class="group bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden transform hover:scale-105"
                         data-aos="fade-up" data-aos-delay="300">
                    <div class="relative food-img-container aspect-square overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1544145945-f90425340c7e?w=400&q=80" 
                             alt="Es Kopi Susu" 
                             class="food-img w-full h-full object-cover">
                        <div class="absolute inset-0 food-gradient opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        
                        <div class="badge-verified absolute top-3 left-3 flex items-center gap-1.5 px-3 py-1.5 bg-primary-600 text-white text-xs font-semibold rounded-full shadow-lg">
                            <i data-lucide="badge-check" class="w-3.5 h-3.5"></i>
                            Terverifikasi
                        </div>
                        
                        <div class="absolute top-3 right-3 px-2.5 py-1 bg-blue-500 text-white text-xs font-bold rounded-lg shadow-lg">
                            BARU
                        </div>
                        
                        <button class="absolute bottom-3 right-3 w-10 h-10 bg-white/90 hover:bg-white rounded-full flex items-center justify-center shadow-lg opacity-0 group-hover:opacity-100 transition-all transform translate-y-2 group-hover:translate-y-0">
                            <i data-lucide="heart" class="w-5 h-5 text-gray-600 hover:text-red-500 transition-colors"></i>
                        </button>
                    </div>
                    
                    <div class="p-5">
                        <div class="flex items-center gap-2 mb-3">
                            <img src="https://ui-avatars.com/api/?name=Kopi+Nusantara&background=059669&color=fff&size=32" 
                                 alt="Kopi Nusantara" 
                                 class="w-6 h-6 rounded-full">
                            <span class="text-sm text-gray-500">Kopi Nusantara</span>
                            <div class="flex items-center gap-1 ml-auto">
                                <i data-lucide="star" class="w-4 h-4 text-accent-500 fill-accent-500"></i>
                                <span class="text-sm font-medium text-gray-700">5.0</span>
                            </div>
                        </div>
                        
                        <h3 class="font-bold text-gray-900 text-lg mb-2 line-clamp-2 group-hover:text-primary-600 transition-colors">
                            Es Kopi Susu Gula Aren
                        </h3>
                        
                        <p class="text-gray-500 text-sm mb-4 line-clamp-2">
                            Kopi robusta pilihan dengan susu segar dan gula aren asli
                        </p>
                        
                        <div class="flex items-end justify-between">
                            <div class="text-xl font-bold text-primary-600">Rp12.000</div>
                            
                            <a href="https://wa.me/6281234567890?text=Halo,%20saya%20mau%20pesan%20Es%20Kopi%20Susu" 
                               target="_blank"
                               class="whatsapp-btn flex items-center gap-2 px-4 py-2.5 text-white font-semibold rounded-xl">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                                <span class="hidden sm:inline">Pesan</span>
                            </a>
                        </div>
                    </div>
                </article>
                
                <!-- ============================================
                     PRODUCT CARD - SOLD OUT STATE
                     ============================================ -->
                <article class="group bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden transform hover:scale-105"
                         data-aos="fade-up" data-aos-delay="400">
                    <div class="relative food-img-container aspect-square overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1565299543923-37dd37887442?w=400&q=80" 
                             alt="Martabak Manis" 
                             class="food-img w-full h-full object-cover grayscale-[30%]">
                        <div class="absolute inset-0 bg-black/40"></div>
                        
                        <!-- Sold Out Overlay -->
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="px-6 py-3 bg-gray-900/80 text-white font-bold text-lg rounded-xl">
                                HABIS
                            </span>
                        </div>
                    </div>
                    
                    <div class="p-5 opacity-60">
                        <div class="flex items-center gap-2 mb-3">
                            <img src="https://ui-avatars.com/api/?name=Martabak+99&background=6b7280&color=fff&size=32" 
                                 alt="Martabak 99" 
                                 class="w-6 h-6 rounded-full">
                            <span class="text-sm text-gray-500">Martabak 99</span>
                            <div class="flex items-center gap-1 ml-auto">
                                <i data-lucide="star" class="w-4 h-4 text-accent-500 fill-accent-500"></i>
                                <span class="text-sm font-medium text-gray-700">4.7</span>
                            </div>
                        </div>
                        
                        <h3 class="font-bold text-gray-900 text-lg mb-2 line-clamp-2">
                            Martabak Manis Special
                        </h3>
                        
                        <p class="text-gray-500 text-sm mb-4 line-clamp-2">
                            Martabak tebal dengan coklat, keju, kacang, dan susu
                        </p>
                        
                        <div class="flex items-end justify-between">
                            <div class="text-xl font-bold text-gray-400">Rp35.000</div>
                            
                            <button disabled 
                                    class="flex items-center gap-2 px-4 py-2.5 bg-gray-300 text-gray-500 font-semibold rounded-xl cursor-not-allowed">
                                <i data-lucide="clock" class="w-5 h-5"></i>
                                <span class="hidden sm:inline">Habis</span>
                            </button>
                        </div>
                    </div>
                </article>
                
            </div>
            
            <!-- View All Button -->
            <div class="text-center mt-12" data-aos="fade-up">
                <a href="/menu" 
                   class="inline-flex items-center gap-2 px-8 py-4 bg-white hover:bg-gray-50 text-gray-800 font-semibold rounded-xl border-2 border-gray-200 hover:border-primary-300 transition-all shadow-sm hover:shadow-md">
                    Lihat Semua Menu
                    <i data-lucide="arrow-right" class="w-5 h-5"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- ========================================================================
         CTA SECTION
         Call to action for sellers
         ======================================================================== -->
    <section class="py-20 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-primary-600 via-primary-700 to-primary-800 relative overflow-hidden">
        <!-- Decorative Elements -->
        <div class="absolute top-0 left-0 w-64 h-64 bg-white/10 rounded-full -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-white/5 rounded-full translate-x-1/3 translate-y-1/3"></div>
        
        <div class="max-w-4xl mx-auto text-center relative z-10">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6" data-aos="fade-up">
                Punya Warung atau UMKM?
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                Gabung dengan 50+ penjual lainnya dan jangkau lebih banyak pelanggan di Way Huwi dan sekitarnya!
            </p>
            <a href="<?= url('daftar-penjual') ?>" 
               class="inline-flex items-center gap-3 px-8 py-4 bg-accent-500 hover:bg-accent-600 text-white font-bold text-lg rounded-xl shadow-xl hover:shadow-2xl transition-all animate-pulse-glow"
               data-aos="fade-up" data-aos-delay="200">
                <i data-lucide="store" class="w-6 h-6"></i>
                Daftar Jadi Penjual - GRATIS!
            </a>
        </div>
    </section>

    <!-- ========================================================================
         FOOTER
         ======================================================================== -->
    <footer class="bg-gray-900 text-white py-16 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="grid md:grid-cols-4 gap-10 mb-12">
                <!-- Brand -->
                <div class="md:col-span-2">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 bg-primary-600 rounded-xl flex items-center justify-center">
                            <i data-lucide="utensils" class="w-6 h-6 text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold">WHFood</h3>
                            <p class="text-sm text-gray-400">Way Huwi Food Marketplace</p>
                        </div>
                    </div>
                    <p class="text-gray-400 mb-6 max-w-md">
                        Platform marketplace kuliner untuk UMKM lokal di Desa Way Huwi, Lampung Selatan. Menghubungkan penjual makanan dengan pelanggan terdekat.
                    </p>
                    <div class="flex gap-4">
                        <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-primary-600 rounded-lg flex items-center justify-center transition-colors">
                            <i data-lucide="instagram" class="w-5 h-5"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-primary-600 rounded-lg flex items-center justify-center transition-colors">
                            <i data-lucide="facebook" class="w-5 h-5"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-green-600 rounded-lg flex items-center justify-center transition-colors">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                        </a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div>
                    <h4 class="font-semibold mb-4">Menu</h4>
                    <ul class="space-y-3 text-gray-400">
                        <li><a href="#" class="hover:text-white transition-colors">Semua Menu</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Makanan Berat</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Snack & Jajanan</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Minuman</a></li>
                    </ul>
                </div>
                
                <!-- Contact -->
                <div>
                    <h4 class="font-semibold mb-4">Kontak</h4>
                    <ul class="space-y-3 text-gray-400">
                        <li class="flex items-center gap-2">
                            <i data-lucide="map-pin" class="w-4 h-4"></i>
                            Way Huwi, Lampung Selatan
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                            hello@whfood.id
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="phone" class="w-4 h-4"></i>
                            +62 812-3456-7890
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Copyright -->
            <div class="border-t border-gray-800 pt-8 text-center text-gray-500 text-sm">
                <p>&copy; <?= date('Y') ?> WHFood. Way Huwi, Lampung Selatan</p>
            </div>
        </div>
    </footer>

    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <!-- Initialize Scripts -->
    <script>
        // Initialize Lucide Icons
        lucide.createIcons();
        
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-out-cubic',
            once: true,
            offset: 50
        });
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    </script>
</body>
</html>
