<?php



declare(strict_types=1);





error_reporting(E_ALL);
ini_set('display_errors', '1'); 


define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', APP_PATH . '/config');
define('HELPERS_PATH', APP_PATH . '/helpers');
define('VIEWS_PATH', APP_PATH . '/views');
define('PUBLIC_PATH', __DIR__);





require_once CONFIG_PATH . '/database.php';
require_once HELPERS_PATH . '/functions.php';
require_once HELPERS_PATH . '/auth.php';
require_once HELPERS_PATH . '/upload.php';
require_once HELPERS_PATH . '/recommendations.php';


initSession();






$requestUri = $_SERVER['REQUEST_URI'] ?? '/';


$basePath = '/whfood';
$requestUri = str_replace($basePath, '', $requestUri);


$requestUri = strtok($requestUri, '?');
$requestUri = $requestUri !== false ? $requestUri : '/';


$requestUri = trim($requestUri, '/');
$requestUri = $requestUri ?: 'home';


$segments = $requestUri ? explode('/', $requestUri) : [];
$page = $segments[0] ?? 'home';
$param1 = $segments[1] ?? null;
$param2 = $segments[2] ?? null;


$method = $_SERVER['REQUEST_METHOD'];






$routes = [
    
    'home' => 'home/landing.php',
    '' => 'home/landing.php',
    
    
    'masuk' => 'auth/login.php',
    'daftar' => 'auth/buyer-register.php',
    'daftar-penjual' => 'auth/seller-register.php',
    
    
    'login' => 'auth/login.php',
    'register' => 'auth/buyer-register.php',
    
    'keluar' => function() {
        logout();
        flashSuccess('Anda telah keluar');
        redirect('/');
    },
    
    
    'produk' => function($slug = null) {
        if ($slug) {
            $GLOBALS['productSlug'] = $slug;
            return 'public/product-detail.php';
        }
        return 'public/products.php';
    },

    'penjual' => function($slug = null) {
        if ($slug) {
            $GLOBALS['sellerSlug'] = $slug;
            return 'public/seller-detail.php';
        }
        return 'public/sellers.php';
    },
    'syarat-ketentuan' => 'public/terms.php',
    'kebijakan-privasi' => 'public/privacy.php',
    'tentang' => 'public/about.php',
    
    
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
    
    
    'api' => function($param1 = null, $param2 = null) {
        header('Content-Type: application/json; charset=utf-8');
        
        $apiFile = match ($param1) {
            'produk', 'products' => 'api/products.php',
            'products-popular' => 'api/products-popular.php',
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






function handleRequest(string $page, array $routes, ?string $param1, ?string $param2): void
{
    
    if (!isset($routes[$page])) {
        
        http_response_code(404);
        loadView('errors/404.php');
        return;
    }

    
    $route = $routes[$page];
    
    
    if (is_callable($route)) {
        $result = $route($param1, $param2);
        
        if ($result && $result !== '404') {
            // Check if this is an API route
            if (str_starts_with($result, 'api/')) {
                loadApi($result);
            } else {
                loadView($result);
            }
        } elseif ($result === '404') {
            http_response_code(404);
            loadView('errors/404.php');
        }
        return;
    }
    
    // Check if this is an API route (string route)
    if (str_starts_with($route, 'api/')) {
        loadApi($route);
    } else {
        loadView($route);
    }
}


function loadView(string $viewPath): void
{
    $fullPath = VIEWS_PATH . '/' . $viewPath;
    
    if (file_exists($fullPath)) {
        require $fullPath;
    } else {
        
        http_response_code(500);
        echo "<h1>Error</h1><p>View tidak ditemukan: {$viewPath}</p>";
    }
}

/**
 * Load API file from app/api directory
 */
function loadApi(string $apiPath): void
{
    $fullPath = APP_PATH . '/' . $apiPath;
    
    if (file_exists($fullPath)) {
        require $fullPath;
    } else {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => "API tidak ditemukan: {$apiPath}"]);
    }
}





try {
    handleRequest($page, $routes, $param1, $param2);
} catch (PDOException $e) {
    http_response_code(500);
    error_log("Database Error: " . $e->getMessage());
    echo "<h1>Kesalahan Database</h1><p>Terjadi kesalahan pada sistem. Silakan coba beberapa saat lagi.</p><p>Error: " . $e->getMessage() . "</p>";
} catch (Exception $e) {
    http_response_code(500);
    error_log("Error: " . $e->getMessage());
    echo "<h1>Kesalahan Sistem</h1><p>Terjadi kesalahan. Silakan coba beberapa saat lagi.</p>";
}
