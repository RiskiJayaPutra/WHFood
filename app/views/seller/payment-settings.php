<?php
declare(strict_types=1);

requireSeller();

$currentPage = 'pembayaran';
$seller = sellerProfile();
$db = Database::getInstance();

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrfToken'] ?? null)) {
        flashError('Sesi tidak valid');
        redirect('seller/pembayaran');
    }

    $action = $_POST['action'] ?? '';

    // ADD / UPDATE Payment Method
    if ($action === 'save_method') {
        $type = input('type'); // bank_transfer, ewallet
        $provider = input('provider');
        $number = input('number');
        $name = input('name');

        if ($type && $provider && $number && $name) {
            $db->insert('seller_payment_methods', [
                'sellerId' => $seller['id'],
                'paymentType' => $type,
                'providerName' => $provider,
                'accountNumber' => $number,
                'accountName' => $name,
                'isActive' => 1
            ]);
            flashSuccess('Metode pembayaran berhasil ditambahkan');
        } else {
            flashError('Mohon lengkapi data');
        }
        redirect('seller/pembayaran');
    }

    // TOGGLE Cash
    if ($action === 'toggle_cash') {
        $isActive = isset($_POST['isActive']) ? 1 : 0;
        
        // Check if exists
        $cash = $db->selectOne("SELECT id FROM seller_payment_methods WHERE sellerId = ? AND paymentType = 'cash'", [$seller['id']]);
        
        if ($cash) {
            $db->update('seller_payment_methods', ['isActive' => $isActive], 'id = ?', [$cash['id']]);
        } else {
            $db->insert('seller_payment_methods', [
                'sellerId' => $seller['id'],
                'paymentType' => 'cash',
                'isActive' => $isActive
            ]);
        }
        flashSuccess('Status pembayaran Tunai diperbarui');
        redirect('seller/pembayaran');
    }

    // UPLOAD QRIS
    if ($action === 'upload_qris') {
        if (isset($_FILES['qrisImage']) && $_FILES['qrisImage']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = uploadFile($_FILES['qrisImage'], 'qris', 'qris_' . $seller['id']);
            
            if ($uploadResult['success']) {
                $qrisPath = $uploadResult['path'];
                
                // Resize for optimization
                resizeImage($qrisPath, 800, 800, 90);

                // Check Update or Insert
                $existing = $db->selectOne("SELECT id FROM seller_payment_methods WHERE sellerId = ? AND paymentType = 'qris'", [$seller['id']]);

                if ($existing) {
                    $db->update('seller_payment_methods', ['qrImage' => $qrisPath, 'isActive' => 1], 'id = ?', [$existing['id']]);
                } else {
                    $db->insert('seller_payment_methods', [
                        'sellerId' => $seller['id'],
                        'paymentType' => 'qris',
                        'qrImage' => $qrisPath,
                        'isActive' => 1
                    ]);
                }
                flashSuccess('QRIS berhasil diupload');
            } else {
                flashError($uploadResult['message']);
            }
        } else {
            flashError('Pilih file gambar QRIS');
        }
        redirect('seller/pembayaran');
    }
    
    // DELETE Method
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $db->query("DELETE FROM seller_payment_methods WHERE id = ? AND sellerId = ?", [$id, $seller['id']]);
        flashSuccess('Metode dihapus');
        redirect('seller/pembayaran');
    }
}

// Fetch Data
$methods = $db->select("SELECT * FROM seller_payment_methods WHERE sellerId = ?", [$seller['id']]);

$qris = null;
$cash = null;
$banks = [];
$ewallets = [];

foreach ($methods as $m) {
    if ($m['paymentType'] === 'qris') $qris = $m;
    elseif ($m['paymentType'] === 'cash') $cash = $m;
    elseif ($m['paymentType'] === 'bank_transfer') $banks[] = $m;
    elseif ($m['paymentType'] === 'ewallet') $ewallets[] = $m;
}

$pageTitle = 'Metode Pembayaran - WHFood';
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
    
    <?php require VIEWS_PATH . '/components/seller-sidebar.php'; ?>
    
    <main class="transition-all duration-300 md:ml-64 p-4 md:p-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Metode Pembayaran</h1>
            <p class="text-gray-500">Atur opsi pembayaran yang diterima toko Anda</p>
        </div>
        
        <!-- Flash Messages -->
        <?php if ($msg = flash('success')): ?>
            <div class="mb-6 p-4 bg-green-50 text-green-700 rounded-xl flex items-center gap-3 border border-green-200">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
                <?= e($msg) ?>
            </div>
        <?php endif; ?>
        <?php if ($msg = flash('error')): ?>
            <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-xl flex items-center gap-3 border border-red-200">
                <i data-lucide="alert-circle" class="w-5 h-5"></i>
                <?= e($msg) ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <!-- LEFT COLUMN: QRIS & Cash -->
            <div class="space-y-8">
                
                <!-- QRIS Section -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                            <i data-lucide="qr-code" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900">QRIS Standard</h3>
                            <p class="text-sm text-gray-500">Upload kode QRIS toko Anda</p>
                        </div>
                    </div>

                    <form method="POST" enctype="multipart/form-data" class="space-y-4">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="upload_qris">
                        
                        <div class="border-2 border-dashed border-gray-200 rounded-xl p-4 text-center hover:bg-gray-50 transition-colors relative group">
                            <?php if ($qris && $qris['qrImage']): ?>
                                <img src="<?= uploadUrl($qris['qrImage']) ?>" class="max-h-64 mx-auto rounded-lg shadow-sm">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center rounded-xl">
                                    <p class="text-white font-medium">Klik untuk ganti</p>
                                </div>
                            <?php else: ?>
                                <div class="py-8">
                                    <i data-lucide="upload-cloud" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
                                    <p class="text-gray-500 font-medium">Upload gambar QRIS</p>
                                    <p class="text-xs text-gray-400">Format PNG/JPG, Maks 5MB</p>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="qrisImage" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="this.form.submit()">
                        </div>
                    </form>
                </div>

                <!-- Cash Section -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-green-50 flex items-center justify-center text-green-600">
                                <i data-lucide="banknote" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900">Tunai / COD</h3>
                                <p class="text-sm text-gray-500">Terima pembayaran tunai saat pesanan sampai</p>
                            </div>
                        </div>
                        <form method="POST">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="toggle_cash">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="isActive" class="sr-only peer" onchange="this.form.submit()" <?= ($cash && $cash['isActive']) ? 'checked' : '' ?>>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                            </label>
                        </form>
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN: Banks & E-Wallets -->
            <div class="space-y-8">
                
                <!-- Bank List -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-purple-50 flex items-center justify-center text-purple-600">
                                <i data-lucide="landmark" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900">Transfer Bank</h3>
                                <p class="text-sm text-gray-500">Rekening Bank (BCA, BRI, Mandiri)</p>
                            </div>
                        </div>
                        <button onclick="openModal('bank_transfer')" class="p-2 hover:bg-gray-100 rounded-lg text-primary-600 font-medium text-sm transition-colors">
                            <i data-lucide="plus" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <div class="space-y-3">
                        <?php if (empty($banks)): ?>
                            <p class="text-center text-gray-400 py-4 text-sm italic">Belum ada rekening bank</p>
                        <?php else: ?>
                            <?php foreach ($banks as $b): ?>
                                <div class="flex items-center justify-between p-3 border border-gray-100 rounded-xl hover:shadow-sm transition-all bg-gray-50/50">
                                    <div class="flex items-center gap-3">
                                        <!-- Logo Placeholder -->
                                        <div class="w-10 h-8 bg-white border border-gray-200 rounded flex items-center justify-center font-bold text-xs text-gray-600">
                                            <?= e($b['providerName']) ?>
                                        </div>
                                        <div>
                                            <p class="font-mono text-sm text-gray-900 font-medium"><?= e($b['accountNumber']) ?></p>
                                            <p class="text-xs text-gray-500 uppercase"><?= e($b['accountName']) ?></p>
                                        </div>
                                    </div>
                                    <form method="POST" onsubmit="return confirm('Hapus rekening ini?')">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                        <button class="text-gray-400 hover:text-red-500 transition-colors"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- E-Wallet List -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-orange-50 flex items-center justify-center text-orange-600">
                                <i data-lucide="smartphone" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900">E-Wallet</h3>
                                <p class="text-sm text-gray-500">DANA, OVO, GoPay, ShopeePay</p>
                            </div>
                        </div>
                        <button onclick="openModal('ewallet')" class="p-2 hover:bg-gray-100 rounded-lg text-primary-600 font-medium text-sm transition-colors">
                            <i data-lucide="plus" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <div class="space-y-3">
                        <?php if (empty($ewallets)): ?>
                            <p class="text-center text-gray-400 py-4 text-sm italic">Belum ada akun e-wallet</p>
                        <?php else: ?>
                            <?php foreach ($ewallets as $w): ?>
                                <div class="flex items-center justify-between p-3 border border-gray-100 rounded-xl hover:shadow-sm transition-all bg-gray-50/50">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-8 bg-white border border-gray-200 rounded flex items-center justify-center font-bold text-xs text-gray-600">
                                            <?= e($w['providerName']) ?>
                                        </div>
                                        <div>
                                            <p class="font-mono text-sm text-gray-900 font-medium"><?= e($w['accountNumber']) ?></p>
                                            <p class="text-xs text-gray-500 uppercase"><?= e($w['accountName']) ?></p>
                                        </div>
                                    </div>
                                    <form method="POST" onsubmit="return confirm('Hapus akun ini?')">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $w['id'] ?>">
                                        <button class="text-gray-400 hover:text-red-500 transition-colors"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- Modal Add Method (Premium Design) -->
    <div id="methodModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>

        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                
                <!-- Modal Panel -->
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    
                    <!-- Decor Header -->
                    <div class="bg-gradient-to-r from-primary-50 to-white px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-white shadow-sm flex items-center justify-center text-primary-600 ring-1 ring-gray-100 transition-colors" id="modalIconContainer">
                                <i data-lucide="landmark" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900" id="modalTitle">Tambah Metode</h3>
                                <p class="text-xs text-gray-500" id="modalSubtitle">Masukkan detail pembayaran baru</p>
                            </div>
                        </div>
                        <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-500 transition-colors bg-white hover:bg-gray-100 rounded-lg p-1">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <div class="px-6 py-6">
                        <form method="POST" class="space-y-5">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="save_method">
                            <input type="hidden" name="type" id="methodType">
                            
                            <!-- Provider Selection -->
                            <div class="space-y-1.5">
                                <label class="block text-sm font-semibold text-gray-700">Provider Layanan</label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                        <i data-lucide="building-2" class="w-5 h-5 text-gray-400 group-focus-within:text-primary-500 transition-colors"></i>
                                    </div>
                                    <select name="provider" id="providerSelect" class="block w-full rounded-xl border-gray-200 pl-11 pr-10 py-3 text-base focus:border-primary-500 focus:ring-primary-500 sm:text-sm bg-gray-50 hover:bg-white transition-colors cursor-pointer appearance-none" required>
                                        <!-- Options generated -->
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i data-lucide="chevron-down" class="w-5 h-5 text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Account Number -->
                            <div class="space-y-1.5">
                                <label class="block text-sm font-semibold text-gray-700" id="numberLabel">Nomor Rekening</label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                        <i data-lucide="credit-card" class="w-5 h-5 text-gray-400 group-focus-within:text-primary-500 transition-colors"></i>
                                    </div>
                                    <input type="text" name="number" class="block w-full rounded-xl border-gray-200 pl-11 py-3 text-gray-900 placeholder:text-gray-400 focus:border-primary-500 focus:ring-primary-500 sm:text-sm bg-gray-50 hover:bg-white transition-colors" required placeholder="Contoh: 1234567890">
                                </div>
                            </div>
                            
                            <!-- Account Name -->
                            <div class="space-y-1.5">
                                <label class="block text-sm font-semibold text-gray-700">Nama Pemilik Akun</label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                        <i data-lucide="user" class="w-5 h-5 text-gray-400 group-focus-within:text-primary-500 transition-colors"></i>
                                    </div>
                                    <input type="text" name="name" class="block w-full rounded-xl border-gray-200 pl-11 py-3 text-gray-900 placeholder:text-gray-400 focus:border-primary-500 focus:ring-primary-500 sm:text-sm bg-gray-50 hover:bg-white transition-colors" required placeholder="Sesuai buku tabungan / aplikasi">
                                </div>
                            </div>
                            
                            <!-- Actions -->
                            <div class="pt-4 flex items-center gap-3">
                                <button type="button" onclick="closeModal()" class="w-full justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200 focus:ring-offset-2 transition-all">
                                    Batal
                                </button>
                                <button type="submit" class="w-full justify-center rounded-xl border border-transparent bg-primary-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-all flex items-center gap-2">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                    Simpan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        const banks = ['BCA', 'BRI', 'Mandiri', 'BNI', 'BSI', 'CIMB Niaga', 'Danamon', 'Permata', 'Bank Jago', 'SeaBank'];
        const ewallets = ['DANA', 'GoPay', 'OVO', 'ShopeePay', 'LinkAja'];

        function openModal(type) {
            const modal = document.getElementById('methodModal');
            const title = document.getElementById('modalTitle');
            const subtitle = document.getElementById('modalSubtitle');
            const typeInput = document.getElementById('methodType');
            const select = document.getElementById('providerSelect');
            const numberLabel = document.getElementById('numberLabel');
            const iconContainer = document.getElementById('modalIconContainer');
            
            modal.classList.remove('hidden');
            typeInput.value = type;
            select.innerHTML = '';
            
            if (type === 'bank_transfer') {
                title.textContent = 'Tambah Rekening Bank';
                subtitle.textContent = 'Pastikan nama pemilik sesuai rekening';
                numberLabel.textContent = 'Nomor Rekening';
                iconContainer.innerHTML = '<i data-lucide="landmark" class="w-5 h-5"></i>';
                iconContainer.className = 'w-10 h-10 rounded-full bg-purple-50 flex items-center justify-center text-purple-600 ring-1 ring-purple-100';
                
                banks.forEach(b => {
                    select.add(new Option(b, b));
                });
            } else {
                title.textContent = 'Tambah Akun E-Wallet';
                subtitle.textContent = 'Hubungkan dengan dompet digital Anda';
                numberLabel.textContent = 'Nomor HP / Akun';
                iconContainer.innerHTML = '<i data-lucide="smartphone" class="w-5 h-5"></i>';
                iconContainer.className = 'w-10 h-10 rounded-full bg-orange-50 flex items-center justify-center text-orange-600 ring-1 ring-orange-100';
                
                ewallets.forEach(e => {
                    select.add(new Option(e, e));
                });
            }
            lucide.createIcons();
        }

        function closeModal() {
            document.getElementById('methodModal').classList.add('hidden');
        }

        // Close on escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeModal();
        });
    </script>
</body>
</html>
