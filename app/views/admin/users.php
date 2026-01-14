<?php
declare(strict_types=1);

requireAdmin();

$currentPage = 'users';
$db = Database::getInstance();

$filter = input('filter') ?: 'all';
$search = input('search');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrfToken'] ?? null)) {
        flashError('Sesi tidak valid');
        redirect('admin/users');
    }
    
    $action = $_POST['action'] ?? '';
    $userId = (int)($_POST['userId'] ?? 0);
    
    if ($action === 'activate' && $userId) {
        $db->update('users', ['status' => 'active'], 'id = ?', [$userId]);
        flashSuccess('Akun berhasil diaktifkan');
    } elseif ($action === 'suspend' && $userId) {
        $db->update('users', ['status' => 'suspended'], 'id = ?', [$userId]);
        flashSuccess('Akun berhasil dinonaktifkan');
    } elseif ($action === 'delete' && $userId) {
        $db->delete('users', 'id = ?', [$userId]);
        flashSuccess('Akun berhasil dihapus');
    }
    
    redirect('admin/users' . ($filter !== 'all' ? '?filter=' . $filter : ''));
}

$whereClause = "1=1";
$params = [];

if ($filter === 'buyers') {
    $whereClause .= " AND role = 'buyer'";
} elseif ($filter === 'sellers') {
    $whereClause .= " AND role = 'seller'";
} elseif ($filter === 'admins') {
    $whereClause .= " AND role = 'admin'";
} elseif ($filter === 'suspended') {
    $whereClause .= " AND status = 'suspended'";
}

if ($search) {
    $whereClause .= " AND (fullName LIKE ? OR email LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$users = $db->select("
    SELECT id, email, fullName, phoneNumber, role, status, lastLoginAt, createdAt
    FROM users
    WHERE {$whereClause}
    ORDER BY createdAt DESC
    LIMIT 100
", $params);

$pageTitle = 'Kelola Pengguna - Admin WHFood';
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
    
    <?php require VIEWS_PATH . '/components/admin-sidebar.php'; ?>
    
    <main class="ml-64 p-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Kelola Pengguna</h1>
                <p class="text-gray-500"><?= count($users) ?> pengguna ditemukan</p>
            </div>
        </div>
        
        <?php if ($success = flash('success')): ?>
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl flex items-center gap-3">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
                <span><?= e($success) ?></span>
            </div>
        <?php endif; ?>
        
        <div class="flex flex-wrap items-center gap-4 mb-6">
            <div class="flex flex-wrap items-center gap-3">
                <a href="<?= url('admin/users') ?>" 
                   class="px-4 py-2 rounded-xl font-medium transition-all <?= $filter === 'all' ? 'bg-primary-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' ?>">
                    Semua
                </a>
                <a href="<?= url('admin/users?filter=buyers') ?>" 
                   class="px-4 py-2 rounded-xl font-medium transition-all <?= $filter === 'buyers' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' ?>">
                    Pembeli
                </a>
                <a href="<?= url('admin/users?filter=sellers') ?>" 
                   class="px-4 py-2 rounded-xl font-medium transition-all <?= $filter === 'sellers' ? 'bg-green-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' ?>">
                    Penjual
                </a>
                <a href="<?= url('admin/users?filter=admins') ?>" 
                   class="px-4 py-2 rounded-xl font-medium transition-all <?= $filter === 'admins' ? 'bg-purple-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' ?>">
                    Admin
                </a>
                <a href="<?= url('admin/users?filter=suspended') ?>" 
                   class="px-4 py-2 rounded-xl font-medium transition-all <?= $filter === 'suspended' ? 'bg-red-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' ?>">
                    Suspended
                </a>
            </div>
            
            <form method="GET" class="ml-auto flex gap-2">
                <input type="hidden" name="filter" value="<?= e($filter) ?>">
                <div class="relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Cari nama atau email..."
                           class="pl-10 pr-4 py-2 w-64 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-100 outline-none">
                </div>
                <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
        
        <?php if (empty($users)): ?>
            <div class="bg-white rounded-2xl p-12 text-center shadow-sm">
                <i data-lucide="users" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Tidak Ada Pengguna</h3>
                <p class="text-gray-500">Tidak ada pengguna dengan filter yang dipilih</p>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Pengguna</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Login Terakhir</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($users as $user): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                                            <span class="text-gray-600 font-semibold"><?= mb_substr($user['fullName'], 0, 1) ?></span>
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-gray-900"><?= e($user['fullName']) ?></h4>
                                            <p class="text-sm text-gray-500"><?= e($user['email']) ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                    $roleColors = [
                                        'admin' => 'bg-purple-100 text-purple-700',
                                        'seller' => 'bg-green-100 text-green-700',
                                        'buyer' => 'bg-blue-100 text-blue-700'
                                    ];
                                    $roleLabels = [
                                        'admin' => 'Admin',
                                        'seller' => 'Penjual',
                                        'buyer' => 'Pembeli'
                                    ];
                                    ?>
                                    <span class="px-2.5 py-1 <?= $roleColors[$user['role']] ?? 'bg-gray-100 text-gray-700' ?> text-xs font-medium rounded-full">
                                        <?= $roleLabels[$user['role']] ?? $user['role'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                    $statusColors = [
                                        'active' => 'bg-green-100 text-green-700',
                                        'pending' => 'bg-orange-100 text-orange-700',
                                        'suspended' => 'bg-red-100 text-red-700',
                                        'inactive' => 'bg-gray-100 text-gray-700'
                                    ];
                                    $statusLabels = [
                                        'active' => 'Aktif',
                                        'pending' => 'Pending',
                                        'suspended' => 'Suspended',
                                        'inactive' => 'Nonaktif'
                                    ];
                                    ?>
                                    <span class="px-2.5 py-1 <?= $statusColors[$user['status']] ?? 'bg-gray-100 text-gray-700' ?> text-xs font-medium rounded-full">
                                        <?= $statusLabels[$user['status']] ?? $user['status'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?= $user['lastLoginAt'] ? timeAgo($user['lastLoginAt']) : 'Belum pernah' ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <?php if ($user['status'] === 'suspended'): ?>
                                            <form method="POST" class="inline">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="action" value="activate">
                                                <input type="hidden" name="userId" value="<?= $user['id'] ?>">
                                                <button type="submit" class="px-3 py-1.5 text-xs font-medium text-green-600 bg-green-50 hover:bg-green-100 rounded-lg transition-colors">
                                                    Aktifkan
                                                </button>
                                            </form>
                                        <?php elseif ($user['role'] !== 'admin'): ?>
                                            <form method="POST" class="inline">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="action" value="suspend">
                                                <input type="hidden" name="userId" value="<?= $user['id'] ?>">
                                                <button type="submit" class="px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                                                    Suspend
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
    
    <script>lucide.createIcons();</script>
</body>
</html>
