<?php
declare(strict_types=1);

requireAdmin();

$currentPage = 'sellers';
$db = Database::getInstance();

$filter = input('filter') ?: 'all';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrfToken'] ?? null)) {
        flashError('Sesi tidak valid');
        redirect('admin/sellers');
    }
    
    $action = $_POST['action'] ?? '';
    $userId = (int)($_POST['userId'] ?? 0);
    $sellerId = (int)($_POST['sellerId'] ?? 0);
    
    if ($action === 'verify' && $userId && $sellerId) {
        $db->update('users', ['status' => 'active'], 'id = ?', [$userId]);
        $db->update('seller_profiles', ['isVerified' => 1, 'verifiedAt' => date('Y-m-d H:i:s')], 'id = ?', [$sellerId]);
        flashSuccess('Penjual berhasil diverifikasi');
    } elseif ($action === 'reject' && $userId) {
        $db->update('users', ['status' => 'suspended'], 'id = ?', [$userId]);
        flashError('Penjual ditolak');
    } elseif ($action === 'unverify' && $sellerId) {
        $db->update('seller_profiles', ['isVerified' => 0, 'verifiedAt' => null], 'id = ?', [$sellerId]);
        flashSuccess('Status verifikasi dicabut');
    } elseif ($action === 'activate' && $userId) {
        $db->update('users', ['status' => 'active'], 'id = ?', [$userId]);
        flashSuccess('Akun diaktifkan');
    } elseif ($action === 'suspend' && $userId) {
        $db->update('users', ['status' => 'suspended'], 'id = ?', [$userId]);
        flashSuccess('Akun dinonaktifkan');
    }
    
    redirect('admin/sellers' . ($filter !== 'all' ? '?filter=' . $filter : ''));
}

$whereClause = "u.role = 'seller'";
if ($filter === 'pending') {
    $whereClause .= " AND u.status = 'pending'";
} elseif ($filter === 'verified') {
    $whereClause .= " AND sp.isVerified = 1";
} elseif ($filter === 'unverified') {
    // Show all unverified sellers (Active but unverified OR Pending)
    $whereClause .= " AND (sp.isVerified = 0 OR sp.isVerified IS NULL OR u.status = 'pending')";
}

$sellers = $db->select("
    SELECT u.id as userId, u.fullName, u.email, u.phoneNumber, u.status, u.createdAt,
           sp.id as sellerId, sp.storeName, sp.storeSlug, sp.address, sp.isVerified, sp.rating, sp.totalProducts
    FROM users u
    LEFT JOIN seller_profiles sp ON u.id = sp.userId
    WHERE {$whereClause}
    ORDER BY u.createdAt DESC
");

$pageTitle = 'Kelola Penjual - Admin WHFood';
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
    
    <main class="transition-all duration-300 md:ml-64 p-4 md:p-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Kelola Penjual</h1>
                <p class="text-gray-500"><?= count($sellers) ?> penjual ditemukan</p>
            </div>
        </div>
        
        <?php if ($success = flash('success')): ?>
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl flex items-center gap-3">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
                <span><?= e($success) ?></span>
            </div>
        <?php endif; ?>
        
        <?php if ($error = flash('error')): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-3">
                <i data-lucide="alert-circle" class="w-5 h-5"></i>
                <span><?= e($error) ?></span>
            </div>
        <?php endif; ?>
        
        <div class="flex flex-wrap items-center gap-3 mb-6">
            <a href="<?= url('admin/sellers') ?>" 
               class="px-4 py-2 rounded-xl font-medium transition-all <?= $filter === 'all' ? 'bg-primary-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' ?>">
                Semua
            </a>
            <a href="<?= url('admin/sellers?filter=pending') ?>" 
               class="px-4 py-2 rounded-xl font-medium transition-all <?= $filter === 'pending' ? 'bg-orange-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' ?>">
                Pending
            </a>
            <a href="<?= url('admin/sellers?filter=verified') ?>" 
               class="px-4 py-2 rounded-xl font-medium transition-all <?= $filter === 'verified' ? 'bg-green-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' ?>">
                Terverifikasi
            </a>
            <a href="<?= url('admin/sellers?filter=unverified') ?>" 
               class="px-4 py-2 rounded-xl font-medium transition-all <?= $filter === 'unverified' ? 'bg-gray-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' ?>">
                Belum Verifikasi
            </a>
        </div>
        
        <?php if (empty($sellers)): ?>
            <div class="bg-white rounded-2xl p-12 text-center shadow-sm">
                <i data-lucide="store" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Tidak Ada Penjual</h3>
                <p class="text-gray-500">Tidak ada penjual dengan filter yang dipilih</p>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Penjual</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kontak</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Produk</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($sellers as $seller): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center">
                                            <span class="text-gray-600 font-semibold"><?= mb_substr($seller['storeName'] ?? $seller['fullName'], 0, 1) ?></span>
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-gray-900"><?= e($seller['storeName'] ?? '-') ?></h4>
                                            <p class="text-sm text-gray-500"><?= e($seller['fullName']) ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-900"><?= e($seller['email']) ?></p>
                                    <p class="text-sm text-gray-500"><?= e($seller['phoneNumber'] ?? '-') ?></p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-gray-900 font-medium"><?= $seller['totalProducts'] ?? 0 ?></span>
                                    <span class="text-gray-500 text-sm">produk</span>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($seller['status'] === 'pending'): ?>
                                        <span class="px-2.5 py-1 bg-orange-100 text-orange-700 text-xs font-medium rounded-full">Menunggu Verifikasi</span>
                                    <?php elseif ($seller['status'] === 'suspended'): ?>
                                        <span class="px-2.5 py-1 bg-red-100 text-red-700 text-xs font-medium rounded-full">Dinonaktifkan</span>
                                    <?php elseif ($seller['isVerified']): ?>
                                        <span class="px-2.5 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full">Terverifikasi</span>
                                    <?php else: ?>
                                        <span class="px-2.5 py-1 bg-gray-100 text-gray-700 text-xs font-medium rounded-full">Aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <?php if ($seller['status'] === 'pending'): ?>
                                            <form method="POST" class="inline">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="action" value="verify">
                                                <input type="hidden" name="userId" value="<?= $seller['userId'] ?>">
                                                <input type="hidden" name="sellerId" value="<?= $seller['sellerId'] ?>">
                                                <button type="submit" class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors" title="Verifikasi">
                                                    <i data-lucide="check" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                            <form method="POST" class="inline">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="action" value="reject">
                                                <input type="hidden" name="userId" value="<?= $seller['userId'] ?>">
                                                <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Tolak">
                                                    <i data-lucide="x" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        <?php elseif ($seller['status'] === 'suspended'): ?>
                                            <form method="POST" class="inline">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="action" value="activate">
                                                <input type="hidden" name="userId" value="<?= $seller['userId'] ?>">
                                                <button type="submit" class="px-3 py-1.5 text-xs font-medium text-green-600 bg-green-50 hover:bg-green-100 rounded-lg transition-colors">
                                                    Aktifkan
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <?php if ($seller['isVerified']): ?>
                                                <form method="POST" class="inline">
                                                    <?= csrfField() ?>
                                                    <input type="hidden" name="action" value="unverify">
                                                    <input type="hidden" name="sellerId" value="<?= $seller['sellerId'] ?>">
                                                    <button type="submit" class="px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                                                        Cabut Verifikasi
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <form method="POST" class="inline">
                                                    <?= csrfField() ?>
                                                    <input type="hidden" name="action" value="verify">
                                                    <input type="hidden" name="userId" value="<?= $seller['userId'] ?>">
                                                    <input type="hidden" name="sellerId" value="<?= $seller['sellerId'] ?>">
                                                    <button type="submit" class="px-3 py-1.5 text-xs font-medium text-green-600 bg-green-50 hover:bg-green-100 rounded-lg transition-colors">
                                                        Verifikasi
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <form method="POST" class="inline">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="action" value="suspend">
                                                <input type="hidden" name="userId" value="<?= $seller['userId'] ?>">
                                                <button type="submit" class="px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                                                    Nonaktifkan
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if ($seller['storeSlug']): ?>
                                            <a href="<?= url('penjual/' . $seller['storeSlug']) ?>" target="_blank" 
                                               class="p-2 text-gray-500 hover:bg-gray-50 rounded-lg transition-colors" title="Lihat Toko">
                                                <i data-lucide="external-link" class="w-4 h-4"></i>
                                            </a>
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
