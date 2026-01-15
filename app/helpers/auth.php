<?php



declare(strict_types=1);

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../config/database.php';






function initSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        
        ini_set('session.cookie_httponly', '1');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_samesite', 'Lax');
        
        session_start();
    }
}


function isLoggedIn(): bool
{
    initSession();
    return isset($_SESSION['userId']);
}


function isSeller(): bool
{
    initSession();
    return isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'seller';
}


function isAdmin(): bool
{
    initSession();
    return isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'admin';
}


function user(): ?array
{
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = Database::getInstance();
    return $db->selectOne(
        "SELECT id, email, fullName, phoneNumber, profileImage, role, status 
         FROM users WHERE id = ?",
        [$_SESSION['userId']]
    );
}


function sellerProfile(): ?array
{
    if (!isSeller()) {
        return null;
    }
    
    $db = Database::getInstance();
    return $db->selectOne(
        "SELECT * FROM seller_profiles WHERE userId = ?",
        [$_SESSION['userId']]
    );
}


function requireLogin(): void
{
    if (!isLoggedIn()) {
        flashError('Silakan login terlebih dahulu');
        redirect('masuk');
    }
}


function requireSeller(): void
{
    requireLogin();
    if (!isSeller()) {
        flashError('Anda tidak memiliki akses ke halaman ini');
        redirect('/');
    }
}


function requireAdmin(): void
{
    requireLogin();
    if (!isAdmin()) {
        flashError('Anda tidak memiliki akses ke halaman ini');
        redirect('/');
    }
}






function registerBuyer(array $data): array
{
    $db = Database::getInstance();
    
    
    $existing = $db->selectOne("SELECT id FROM users WHERE email = ?", [$data['email']]);
    if ($existing) {
        return ['success' => false, 'message' => 'Email sudah terdaftar', 'userId' => null];
    }
    
    
    $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
    
    
    $userId = $db->insert('users', [
        'email' => $data['email'],
        'password' => $hashedPassword,
        'fullName' => $data['fullName'],
        'phoneNumber' => $data['phoneNumber'] ?? null,
        'role' => 'buyer',
        'status' => 'active'
    ]);
    
    if ($userId) {
        return ['success' => true, 'message' => 'Registrasi berhasil!', 'userId' => $userId];
    }
    
    return ['success' => false, 'message' => 'Terjadi kesalahan, silakan coba lagi', 'userId' => null];
}


function registerSeller(array $userData, array $sellerData): array
{
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    
    $existing = $db->selectOne("SELECT id FROM users WHERE email = ?", [$userData['email']]);
    if ($existing) {
        return ['success' => false, 'message' => 'Email sudah terdaftar', 'userId' => null];
    }
    
    
    $existingStore = $db->selectOne("SELECT id FROM seller_profiles WHERE storeName = ?", [$sellerData['storeName']]);
    if ($existingStore) {
        return ['success' => false, 'message' => 'Nama toko sudah digunakan', 'userId' => null];
    }
    
    try {
        $conn->beginTransaction();
        
        
        $hashedPassword = password_hash($userData['password'], PASSWORD_BCRYPT);
        
        
        $userId = $db->insert('users', [
            'email' => $userData['email'],
            'password' => $hashedPassword,
            'fullName' => $userData['fullName'],
            'phoneNumber' => $userData['phoneNumber'] ?? null,
            'role' => 'seller',
            'status' => 'pending' 
        ]);
        
        if (!$userId) {
            throw new Exception('Gagal membuat akun');
        }
        
        
        $storeSlug = slugify($sellerData['storeName']);
        
        
        $slugCheck = $db->selectOne("SELECT id FROM seller_profiles WHERE storeSlug = ?", [$storeSlug]);
        if ($slugCheck) {
            $storeSlug .= '-' . $userId;
        }
        
        
        $sellerId = $db->insert('seller_profiles', [
            'userId' => $userId,
            'storeName' => $sellerData['storeName'],
            'storeSlug' => $storeSlug,
            'storeDescription' => $sellerData['storeDescription'] ?? null,
            'storeLogo' => $sellerData['storeLogo'] ?? null,
            'storeBanner' => $sellerData['storeBanner'] ?? null,
            'nik' => $sellerData['nik'] ?? null,
            'ownerName' => $sellerData['ownerName'] ?? null,
            'address' => $sellerData['address'],
            'village' => $sellerData['village'] ?? 'Way Huwi',
            'district' => $sellerData['district'] ?? 'Jati Agung',
            'regency' => $sellerData['regency'] ?? 'Lampung Selatan',
            'latitude' => $sellerData['latitude'] ?? null,
            'longitude' => $sellerData['longitude'] ?? null,
            'bankName' => $sellerData['bankName'] ?? null,
            'bankAccountNumber' => $sellerData['bankAccountNumber'] ?? null,
            'bankAccountName' => $sellerData['bankAccountName'] ?? null,
            'isOpen' => 1
        ]);
        
        if (!$sellerId) {
            throw new Exception('Gagal membuat profil toko');
        }
        
        $conn->commit();
        
        return [
            'success' => true, 
            'message' => 'Registrasi berhasil! Akun Anda akan diverifikasi oleh admin.', 
            'userId' => $userId
        ];
        
    } catch (Exception $e) {
        $conn->rollBack();
        return ['success' => false, 'message' => $e->getMessage(), 'userId' => null];
    }
}






function login(string $email, string $password): array
{
    $db = Database::getInstance();
    
    
    $user = $db->selectOne(
        "SELECT id, email, password, fullName, role, status FROM users WHERE email = ?",
        [$email]
    );
    
    if (!$user) {
        return ['success' => false, 'message' => 'Email atau password salah'];
    }
    
    
    if (!password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Email atau password salah'];
    }
    
    
    if ($user['status'] === 'pending') {
        return ['success' => false, 'message' => 'Akun Anda belum diverifikasi. Silakan tunggu konfirmasi dari admin.'];
    }
    
    if ($user['status'] === 'suspended') {
        return ['success' => false, 'message' => 'Akun Anda telah dinonaktifkan. Hubungi admin untuk informasi lebih lanjut.'];
    }
    
    if ($user['status'] !== 'active') {
        return ['success' => false, 'message' => 'Akun Anda tidak aktif'];
    }
    
    
    initSession();
    $_SESSION['userId'] = $user['id'];
    $_SESSION['userEmail'] = $user['email'];
    $_SESSION['userName'] = $user['fullName'];
    $_SESSION['userRole'] = $user['role'];
    
    
    $db->update('users', ['lastLoginAt' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);
    
    
    session_regenerate_id(true);
    
    return ['success' => true, 'message' => 'Login berhasil!'];
}


function logout(): void
{
    initSession();
    
    
    $_SESSION = [];
    
    
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    
    session_destroy();
}






function generateResetToken(string $email): array
{
    $db = Database::getInstance();
    
    $user = $db->selectOne("SELECT id FROM users WHERE email = ?", [$email]);
    
    if (!$user) {
        
        return ['success' => true, 'message' => 'Jika email terdaftar, link reset akan dikirim', 'token' => null];
    }
    
    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    $db->update('users', [
        'resetToken' => $token,
        'resetTokenExpiresAt' => $expiresAt
    ], 'id = ?', [$user['id']]);
    
    return ['success' => true, 'message' => 'Link reset password telah dikirim', 'token' => $token];
}


function resetPassword(string $token, string $newPassword): array
{
    $db = Database::getInstance();
    
    $user = $db->selectOne(
        "SELECT id FROM users WHERE resetToken = ? AND resetTokenExpiresAt > NOW()",
        [$token]
    );
    
    if (!$user) {
        return ['success' => false, 'message' => 'Token tidak valid atau sudah kadaluarsa'];
    }
    
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    
    $db->update('users', [
        'password' => $hashedPassword,
        'resetToken' => null,
        'resetTokenExpiresAt' => null
    ], 'id = ?', [$user['id']]);
    
    return ['success' => true, 'message' => 'Password berhasil diubah'];
}
