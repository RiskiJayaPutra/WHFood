<?php
declare(strict_types=1);

if (isLoggedIn()) {
    redirect('/');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrfToken'] ?? null)) {
        flashError('Sesi tidak valid, silakan coba lagi');
        redirect('daftar');
    }
    
    $data = [
        'fullName' => input('fullName'),
        'email' => input('email', 'email'),
        'phoneNumber' => input('phoneNumber'),
        'password' => $_POST['password'] ?? '',
        'passwordConfirm' => $_POST['passwordConfirm'] ?? ''
    ];
    
    $errors = validate($data, [
        'fullName' => 'required|min:3|max:100',
        'email' => 'required|email',
        'phoneNumber' => 'required|phone',
        'password' => 'required|min:8|confirmed'
    ]);
    
    if (empty($errors)) {
        $result = registerBuyer($data);
        
        if ($result['success']) {
            flashSuccess('Registrasi berhasil! Silakan login.');
            redirect('masuk');
        } else {
            flashError($result['message']);
        }
    } else {
        flashError(array_values($errors)[0]);
    }
    
    flashOld($data);
    redirect('daftar');
}

$pageTitle = 'Daftar - WHFood';
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Daftar akun WHFood untuk mulai memesan makanan">
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
                        },
                        accent: { 500: '#f59e0b', 600: '#d97706', 700: '#b45309' }
                    },
                    fontFamily: { sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'] },
                }
            }
        }
    </script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px); }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-primary-600 via-primary-700 to-primary-800 flex items-center justify-center p-4">
    
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-white/10 rounded-full blur-3xl"></div>
    </div>
    
    <div class="w-full max-w-md relative z-10">
        <div class="text-center mb-8">
            <a href="<?= url('/') ?>" class="inline-flex items-center gap-3">
                <div class="w-14 h-14 bg-white rounded-xl flex items-center justify-center shadow-lg">
                    <i data-lucide="utensils" class="w-7 h-7 text-primary-600"></i>
                </div>
                <div class="text-left text-white">
                    <h1 class="text-2xl font-bold">WHFood</h1>
                    <p class="text-sm text-white/70">Way Huwi Marketplace</p>
                </div>
            </a>
        </div>
        
        <div class="glass rounded-2xl shadow-2xl p-8">
            <h2 class="text-2xl font-bold text-gray-900 text-center mb-2">Buat Akun Baru</h2>
            <p class="text-gray-500 text-center mb-8">Daftar untuk mulai memesan makanan</p>
            
            <?php if ($error = flash('error')): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-3">
                    <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i>
                    <span><?= e($error) ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="<?= url('daftar') ?>" class="space-y-5">
                <?= csrfField() ?>
                
                <div>
                    <label for="fullName" class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <i data-lucide="user" class="w-5 h-5"></i>
                        </span>
                        <input type="text" id="fullName" name="fullName" required
                               value="<?= e(old('fullName')) ?>"
                               class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all"
                               placeholder="Masukkan nama lengkap">
                    </div>
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <i data-lucide="mail" class="w-5 h-5"></i>
                        </span>
                        <input type="email" id="email" name="email" required
                               value="<?= e(old('email')) ?>"
                               class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all"
                               placeholder="email@contoh.com">
                    </div>
                </div>
                
                <div>
                    <label for="phoneNumber" class="block text-sm font-medium text-gray-700 mb-2">Nomor Telepon</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <i data-lucide="phone" class="w-5 h-5"></i>
                        </span>
                        <input type="tel" id="phoneNumber" name="phoneNumber" required
                               value="<?= e(old('phoneNumber')) ?>"
                               class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all"
                               placeholder="08xxxxxxxxxx">
                    </div>
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <i data-lucide="lock" class="w-5 h-5"></i>
                        </span>
                        <input type="password" id="password" name="password" required minlength="8"
                               class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all"
                               placeholder="Minimal 8 karakter">
                    </div>
                </div>
                
                <div>
                    <label for="passwordConfirm" class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <i data-lucide="lock" class="w-5 h-5"></i>
                        </span>
                        <input type="password" id="passwordConfirm" name="passwordConfirm" required
                               class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all"
                               placeholder="Ulangi password">
                    </div>
                </div>
                
                <div class="flex items-start gap-3">
                    <input type="checkbox" id="terms" name="terms" required
                           class="mt-1 w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    <label for="terms" class="text-sm text-gray-600">
                        Saya setuju dengan <a href="<?= url('syarat-ketentuan') ?>" target="_blank" class="text-primary-600 hover:underline">Syarat & Ketentuan</a>
                        dan <a href="<?= url('kebijakan-privasi') ?>" target="_blank" class="text-primary-600 hover:underline">Kebijakan Privasi</a>
                    </label>
                </div>
                
                <button type="submit"
                        class="w-full py-3.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all flex items-center justify-center gap-2">
                    <i data-lucide="user-plus" class="w-5 h-5"></i>
                    Daftar Sekarang
                </button>
            </form>
            
            <p class="text-center mt-6 text-gray-600">
                Sudah punya akun? 
                <a href="<?= url('masuk') ?>" class="text-primary-600 hover:underline font-medium">Masuk</a>
            </p>
            
            <div class="mt-6 pt-6 border-t border-gray-200 text-center">
                <p class="text-sm text-gray-500 mb-3">Ingin berjualan?</p>
                <a href="<?= url('daftar-penjual') ?>"
                   class="inline-flex items-center gap-2 text-accent-600 hover:text-accent-700 font-medium">
                    <i data-lucide="store" class="w-4 h-4"></i>
                    Daftar sebagai Penjual
                </a>
            </div>
        </div>
        
        <p class="text-center mt-6">
            <a href="<?= url('/') ?>" class="text-white/80 hover:text-white flex items-center justify-center gap-2">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Kembali ke Beranda
            </a>
        </p>
    </div>
    
    <script>lucide.createIcons();</script>
</body>
</html>
<?php clearOld(); ?>
