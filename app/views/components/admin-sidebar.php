<?php
declare(strict_types=1);

$currentPage = $currentPage ?? '';
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
            <div class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center">
                <i data-lucide="utensils" class="w-5 h-5 text-white"></i>
            </div>
            <div>
                <h1 class="font-bold text-gray-900">WHFood</h1>
                <p class="text-xs text-emerald-600 font-medium">Admin Panel</p>
            </div>
        </a>
        <!-- Close Button (Mobile) -->
        <button type="button" onclick="toggleSidebar()" class="md:hidden p-1 text-gray-400 hover:text-gray-900">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>
    </div>
    
    <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-3 px-3">Dashboard</p>
        
        <a href="<?= url('admin/dashboard') ?>" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all <?= $currentPage === 'dashboard' ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:bg-gray-50' ?>">
            <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
            <span class="font-medium">Dashboard</span>
        </a>
        
        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mt-6 mb-3 px-3">Manajemen</p>
        
        <a href="<?= url('admin/sellers') ?>" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all <?= $currentPage === 'sellers' ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:bg-gray-50' ?>">
            <i data-lucide="store" class="w-5 h-5"></i>
            <span class="font-medium">Penjual</span>
            <?php 
            $db = Database::getInstance();
            $pendingCount = $db->selectOne("SELECT COUNT(*) as count FROM users WHERE role = 'seller' AND status = 'pending'");
            if ($pendingCount && $pendingCount['count'] > 0): 
            ?>
                <span class="ml-auto text-xs bg-orange-500 text-white px-2 py-0.5 rounded-full"><?= $pendingCount['count'] ?></span>
            <?php endif; ?>
        </a>
        
        <a href="<?= url('admin/users') ?>" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all <?= $currentPage === 'users' ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:bg-gray-50' ?>">
            <i data-lucide="users" class="w-5 h-5"></i>
            <span class="font-medium">Pengguna</span>
        </a>
        
        <a href="<?= url('admin/products') ?>" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all <?= $currentPage === 'products' ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:bg-gray-50' ?>">
            <i data-lucide="package" class="w-5 h-5"></i>
            <span class="font-medium">Produk</span>
        </a>
        
        <a href="<?= url('admin/reviews') ?>" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all <?= $currentPage === 'reviews' ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:bg-gray-50' ?>">
            <i data-lucide="star" class="w-5 h-5"></i>
            <span class="font-medium">Ulasan</span>
        </a>
        
        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mt-6 mb-3 px-3">Lainnya</p>
        
        <a href="<?= url('/') ?>" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-600 hover:bg-gray-50 transition-all">
            <i data-lucide="globe" class="w-5 h-5"></i>
            <span class="font-medium">Lihat Website</span>
        </a>
    </nav>
    
    <div class="p-4 border-t border-gray-100 bg-emerald-50/50">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 bg-white border border-gray-200 rounded-full flex items-center justify-center shadow-sm">
                <i data-lucide="shield" class="w-5 h-5 text-emerald-600"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-medium text-gray-900 truncate"><?= e($currentUser['fullName'] ?? 'Admin') ?></p>
                <p class="text-xs text-emerald-600 font-medium">Administrator</p>
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

