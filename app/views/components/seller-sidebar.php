<?php
declare(strict_types=1);
$currentPage = $currentPage ?? '';
$seller = sellerProfile();
$currentUser = user();
?>
<!-- Mobile Toggle Button -->
<button type="button" 
        onclick="toggleSidebar()"
        class="md:hidden fixed top-4 left-4 z-50 p-2 bg-white text-gray-900 rounded-lg shadow-lg border border-gray-100 hover:bg-gray-50 transition-all">
    <i data-lucide="menu" class="w-6 h-6"></i>
</button>

<!-- Mobile Backdrop -->
<div id="sidebar-backdrop" 
     onclick="toggleSidebar()"
     class="fixed inset-0 bg-black/50 z-30 opacity-0 pointer-events-none transition-opacity duration-300 md:hidden glass-dark">
</div>

<!-- Sidebar -->
<aside id="sidebar-panel" 
       class="w-64 bg-white border-r border-gray-200 min-h-screen fixed left-0 top-0 z-40 flex flex-col transition-transform duration-300 -translate-x-full md:translate-x-0 shadow-2xl md:shadow-none">
    <div class="p-6 border-b border-gray-100 flex items-center justify-between">
        <a href="<?= url('/') ?>" class="flex items-center gap-3">
            <div class="w-10 h-10 bg-primary-600 rounded-xl flex items-center justify-center">
                <i data-lucide="utensils" class="w-5 h-5 text-white"></i>
            </div>
            <div>
                <h1 class="font-bold text-gray-900">WHFood</h1>
                <p class="text-xs text-gray-500">Panel Penjual</p>
            </div>
        </a>
        <!-- Close Button (Mobile) -->
        <button type="button" onclick="toggleSidebar()" class="md:hidden p-1 text-gray-400 hover:text-gray-900">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>
    </div>
    
    <div class="p-4 mx-4 mt-4 bg-gradient-to-r from-primary-50 to-primary-100 rounded-xl">
        <div class="flex items-center gap-3">
            <?php if ($seller && $seller['storeLogo']): ?>
                <img src="<?= uploadUrl($seller['storeLogo']) ?>" alt="Logo" class="w-12 h-12 rounded-lg object-cover">
            <?php else: ?>
                <div class="w-12 h-12 bg-primary-600 rounded-lg flex items-center justify-center text-white text-xl">
                    <?= mb_substr($seller['storeName'] ?? 'T', 0, 1) ?>
                </div>
            <?php endif; ?>
            <div class="flex-1 min-w-0">
                <h3 class="font-semibold text-gray-900 truncate"><?= e($seller['storeName'] ?? 'Toko Anda') ?></h3>
                <p class="text-xs text-gray-500 flex items-center gap-1">
                    <?php if ($seller && $seller['isVerified']): ?>
                        <i data-lucide="badge-check" class="w-3 h-3 text-primary-600"></i>
                        <span class="text-primary-600">Terverifikasi</span>
                    <?php else: ?>
                        <span class="text-gray-400">Belum Terverifikasi</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
    
    <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-3 px-3">Menu Utama</p>
        
        <a href="<?= url('seller/dashboard') ?>" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all <?= $currentPage === 'dashboard' ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50' ?>">
            <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
            <span class="font-medium">Dashboard</span>
        </a>
        
        <a href="<?= url('seller/produk') ?>" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all <?= $currentPage === 'produk' ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50' ?>">
            <i data-lucide="package" class="w-5 h-5"></i>
            <span class="font-medium">Produk Saya</span>
            <?php if ($seller): ?>
                <span class="ml-auto text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full"><?= $seller['totalProducts'] ?></span>
            <?php endif; ?>
        </a>

        <a href="<?= url('seller/pesanan') ?>" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all <?= $currentPage === 'pesanan' ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50' ?>">
            <i data-lucide="shopping-bag" class="w-5 h-5"></i>
            <span class="font-medium">Pesanan</span>
            <!-- Pending Orders Badge Count Logic could go here -->
        </a>
        
        <a href="<?= url('seller/tambah-produk') ?>" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all <?= $currentPage === 'tambah-produk' ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50' ?>">
            <i data-lucide="plus-circle" class="w-5 h-5"></i>
            <span class="font-medium">Tambah Produk</span>
        </a>
        
        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mt-6 mb-3 px-3">Pengaturan</p>
        
        <a href="<?= url('seller/profil') ?>" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all <?= $currentPage === 'profil' ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50' ?>">
            <i data-lucide="store" class="w-5 h-5"></i>
            <span class="font-medium">Profil Toko</span>
        </a>
        
        <a href="<?= url('seller/pembayaran') ?>" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all <?= $currentPage === 'pembayaran' ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50' ?>">
            <i data-lucide="wallet" class="w-5 h-5"></i>
            <span class="font-medium">Metode Pembayaran</span>
        </a>
        
        <a href="<?= url('/') ?>" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-600 hover:bg-gray-50 transition-all">
            <i data-lucide="globe" class="w-5 h-5"></i>
            <span class="font-medium">Lihat Website</span>
        </a>
    </nav>
    
    <div class="p-4 border-t border-gray-100 bg-gray-50/50">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 bg-white border border-gray-200 rounded-full flex items-center justify-center shadow-sm">
                <i data-lucide="user" class="w-5 h-5 text-gray-500"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-medium text-gray-900 truncate"><?= e($currentUser['fullName'] ?? 'User') ?></p>
                <p class="text-xs text-gray-500 truncate"><?= e($currentUser['email'] ?? '') ?></p>
            </div>
        </div>
        <a href="<?= url('keluar') ?>" 
           class="flex items-center justify-center gap-2 w-full py-2.5 bg-white border border-gray-200 text-red-600 hover:bg-red-50 hover:border-red-200 rounded-xl transition-all shadow-sm">
            <i data-lucide="log-out" class="w-4 h-4"></i>
            <span class="font-medium">Keluar</span>
        </a>
    </div>
</aside>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar-panel');
    const backdrop = document.getElementById('sidebar-backdrop');
    
    if (sidebar.classList.contains('-translate-x-full')) {
        // Open
        sidebar.classList.remove('-translate-x-full');
        backdrop.classList.remove('opacity-0', 'pointer-events-none');
    } else {
        // Close
        sidebar.classList.add('-translate-x-full');
        backdrop.classList.add('opacity-0', 'pointer-events-none');
    }
}
</script>
