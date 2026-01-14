<?php
declare(strict_types=1);

if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('admin/dashboard');
    }
    if (isSeller()) {
        redirect('seller/dashboard');
    }
    redirect('/');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrfToken'] ?? null)) {
        flashError('Sesi tidak valid, silakan coba lagi');
        redirect('masuk');
    }
    
    $email = input('email', 'email');
    $password = $_POST['password'] ?? '';
    
    $errors = validate([
        'email' => $email,
        'password' => $password
    ], [
        'email' => 'required|email',
        'password' => 'required|min:6'
    ]);
    
    if (empty($errors)) {
        $result = login($email, $password);
        
        if ($result['success']) {
            flashSuccess($result['message']);
            if (isAdmin()) {
                redirect('admin/dashboard');
            }
            if (isSeller()) {
                redirect('seller/dashboard');
            }
            redirect('/');
        } else {
            flashError($result['message']);
        }
    } else {
        flashError(array_values($errors)[0]);
    }
    
    flashOld($_POST);
    redirect('masuk');
}

$pageTitle = 'Masuk - WHFood';
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Masuk ke akun WHFood Anda">
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
                        accent: {
                            500: '#f59e0b', 600: '#d97706', 700: '#b45309',
                        }
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
        }
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
            <h2 class="text-2xl font-bold text-gray-900 text-center mb-2">Selamat Datang!</h2>
            <p class="text-gray-500 text-center mb-8">Masuk ke akun WHFood Anda</p>
            
            <?php if ($error = flash('error')): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-3">
                    <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i>
                    <span><?= e($error) ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($success = flash('success')): ?>
                <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl flex items-center gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5 flex-shrink-0"></i>
                    <span><?= e($success) ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="<?= url('masuk') ?>" class="space-y-5">
                <?= csrfField() ?>
                
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
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <i data-lucide="lock" class="w-5 h-5"></i>
                        </span>
                        <input type="password" id="password" name="password" required
                               class="w-full pl-12 pr-12 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all"
                               placeholder="Masukkan password">
                        <button type="button" onclick="togglePassword()"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i data-lucide="eye" id="eyeIcon" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>
                
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        <span class="text-sm text-gray-600">Ingat saya</span>
                    </label>
                    <a href="#" class="text-sm text-primary-600 hover:underline">Lupa password?</a>
                </div>
                
                <button type="submit"
                        class="w-full py-3.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all flex items-center justify-center gap-2">
                    <i data-lucide="log-in" class="w-5 h-5"></i>
                    Masuk
                </button>
            </form>
            
            <div class="relative my-8">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-200"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-4 bg-white text-gray-500">atau</span>
                </div>
            </div>
            
            <div class="space-y-3">
                <a href="<?= url('daftar') ?>"
                   class="w-full py-3 border-2 border-gray-200 hover:border-primary-300 hover:bg-primary-50 text-gray-700 font-medium rounded-xl transition-all flex items-center justify-center gap-2">
                    <i data-lucide="user-plus" class="w-5 h-5"></i>
                    Daftar sebagai Pembeli
                </a>
                <a href="<?= url('daftar-penjual') ?>"
                   class="w-full py-3 border-2 border-accent-300 hover:border-accent-400 hover:bg-accent-50 text-accent-700 font-medium rounded-xl transition-all flex items-center justify-center gap-2">
                    <i data-lucide="store" class="w-5 h-5"></i>
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
    
    <script>
        lucide.createIcons();
        
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.setAttribute('data-lucide', 'eye-off');
            } else {
                input.type = 'password';
                icon.setAttribute('data-lucide', 'eye');
            }
            lucide.createIcons();
        }
    </script>
</body>
</html>
<?php clearOld(); ?>
