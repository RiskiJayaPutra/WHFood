<?php

/**
 * ============================================================================
 * WHFood - Front Controller & Router
 * ============================================================================
 * 
 * Single entry point untuk semua request.
 * Implementasi simple router tanpa framework.
 * 
 * @package     WHFood
 * @author      WHFood Development Team
 * @version     1.0.0
 */

declare(strict_types=1);

// ============================================================================
// CONFIGURATION
// ============================================================================

error_reporting(E_ALL);
ini_set('display_errors', '1'); // Set ke '0' di production

// Path constants
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', APP_PATH . '/config');
define('HELPERS_PATH', APP_PATH . '/helpers');
define('VIEWS_PATH', APP_PATH . '/views');
define('PUBLIC_PATH', __DIR__);

// ============================================================================
// LOAD DEPENDENCIES
// ============================================================================

require_once CONFIG_PATH . '/database.php';
require_once HELPERS_PATH . '/functions.php';
require_once HELPERS_PATH . '/auth.php';
require_once HELPERS_PATH . '/upload.php';

// Initialize session
initSession();

// ============================================================================
// ROUTING
// ============================================================================

// Ambil URL dari request
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';

// Hapus base path jika ada
$basePath = '/whfood';
$requestUri = str_replace($basePath, '', $requestUri);

// Hapus query string
$requestUri = strtok($requestUri, '?');
$requestUri = $requestUri !== false ? $requestUri : '/';

// Bersihkan URL
$requestUri = trim($requestUri, '/');
$requestUri = $requestUri ?: 'home';

// Parse segments
$segments = $requestUri ? explode('/', $requestUri) : [];
$page = $segments[0] ?? 'home';
$param1 = $segments[1] ?? null;
$param2 = $segments[2] ?? null;

// HTTP Method
$method = $_SERVER['REQUEST_METHOD'];

// ============================================================================
// ROUTE DEFINITIONS
// ============================================================================

// Route mapping: 'route' => 'view_file' atau callable
$routes = [
    // ==================== PUBLIC ROUTES ====================
    'home' => 'home/landing.php',
    '' => 'home/landing.php',
    
    // Auth (Indonesian)
    'masuk' => 'auth/login.php',
    'daftar' => 'auth/buyer-register.php',
    'daftar-penjual' => 'auth/seller-register.php',
    
    // Auth (English aliases)
    'login' => 'auth/login.php',
    'register' => 'auth/buyer-register.php',
    
    'keluar' => function() {
        logout();
        flashSuccess('Anda telah keluar');
        redirect('/');
    },
    
    // Public Product & Seller
    'produk' => 'public/products.php',
    'penjual' => 'public/sellers.php',
    
    // ==================== SELLER ROUTES ====================
    'seller' => function($param1 = null) {
        requireSeller();
        
        return match ($param1) {
            'dashboard', null, '' => 'seller/dashboard.php',
            'produk' => 'seller/products.php',
            'tambah-produk' => 'seller/product-form.php',
            'edit-produk' => 'seller/product-form.php',
            'pesanan' => 'seller/orders.php',
            'profil' => 'seller/profile.php',
            'pembayaran' => 'seller/payment-settings.php',
            default => '404'
        };
    },
    
    // ==================== ADMIN ROUTES ====================
    'admin' => function($param1 = null) {
        requireAdmin();
        
        return match ($param1) {
            'dashboard', null, '' => 'admin/dashboard.php',
            'sellers' => 'admin/sellers.php',
            'users' => 'admin/users.php',
            'products' => 'admin/products.php',
            'reviews' => 'admin/reviews.php',
            default => '404'
        };
    },
    
    // ==================== API ROUTES ====================
    'api' => function($param1 = null, $param2 = null) {
        header('Content-Type: application/json; charset=utf-8');
        
        $apiFile = match ($param1) {
            'produk', 'products' => 'api/products.php',
            'auth' => 'api/auth.php',
            'reviews' => 'api/reviews.php',
            default => null
        };
        
        if (!$apiFile) {
            jsonError('Endpoint tidak ditemukan', 404);
        }
        
        return $apiFile;
    },
];

// ============================================================================
// ROUTE MATCHING
// ============================================================================

/**
 * Find and load the appropriate view
 */
function handleRequest(string $page, array $routes, ?string $param1, ?string $param2): void
{
    // Cek apakah route ada
    if (!isset($routes[$page])) {
        // Coba cari sebagai detail page
        if ($page === 'produk' && $param1) {
            // Detail produk: /produk/{slug}
            $GLOBALS['productSlug'] = $param1;
            loadView('public/product-detail.php');
            return;
        }
        
        if ($page === 'penjual' && $param1) {
            // Detail penjual: /penjual/{slug}
            $GLOBALS['sellerSlug'] = $param1;
            loadView('public/seller-detail.php');
            return;
        }
        
        // 404
        http_response_code(404);
        loadView('errors/404.php');
        return;
    }
    
    $route = $routes[$page];
    
    // Jika callable, jalankan
    if (is_callable($route)) {
        $result = $route($param1, $param2);
        
        if ($result && $result !== '404') {
            loadView($result);
        } elseif ($result === '404') {
            http_response_code(404);
            loadView('errors/404.php');
        }
        return;
    }
    
    // Jika string, load view
    loadView($route);
}

/**
 * Load view file
 */
function loadView(string $viewPath): void
{
    $fullPath = VIEWS_PATH . '/' . $viewPath;
    
    if (file_exists($fullPath)) {
        require $fullPath;
    } else {
        // Jika view tidak ada, tampilkan error
        http_response_code(500);
        echo "<h1>Error</h1><p>View tidak ditemukan: {$viewPath}</p>";
    }
}

// ============================================================================
// HANDLE REQUEST
// ============================================================================

try {
    handleRequest($page, $routes, $param1, $param2);
} catch (PDOException $e) {
    http_response_code(500);
    error_log("Database Error: " . $e->getMessage());
    echo "<h1>Kesalahan Database</h1><p>Terjadi kesalahan pada sistem. Silakan coba beberapa saat lagi.</p>";
} catch (Exception $e) {
    http_response_code(500);
    error_log("Error: " . $e->getMessage());
    echo "<h1>Kesalahan Sistem</h1><p>Terjadi kesalahan. Silakan coba beberapa saat lagi.</p>";
}
