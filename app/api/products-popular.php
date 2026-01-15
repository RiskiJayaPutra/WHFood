<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$db = Database::getInstance();

if ($method === 'GET') {
    $category = input('category');
    $limit = input('limit', 'int') ?: 5;
    
    // Base query
    $query = "
        SELECT p.*, 
               u.storeName, 
               u.storeSlug, 
               u.isVerified as sellerVerified,
               COALESCE(AVG(r.rating), 0) as avgRating,
               COUNT(r.id) as reviewCount
        FROM products p
        JOIN users u ON p.sellerId = u.id
        LEFT JOIN reviews r ON p.id = r.productId
        WHERE p.status = 'active' AND p.isAvailable = 1 AND u.status = 'active'
    ";
    
    $params = [];
    
    if ($category && $category !== 'all') {
        $query .= " AND p.category = ?";
        $params[] = $category;
    }
    
    $query .= " GROUP BY p.id";
    $query .= " ORDER BY avgRating DESC, p.createdAt DESC";
    $query .= " LIMIT ?";
    $params[] = $limit;
    
    try {
        $products = $db->select($query, $params);
        
        // Format numeric values
        foreach ($products as &$product) {
            $product['price'] = (float) $product['price'];
            $product['discountPrice'] = $product['discountPrice'] ? (float) $product['discountPrice'] : null;
            $product['avgRating'] = round((float) $product['avgRating'], 1);
            $product['sellerVerified'] = (bool) $product['sellerVerified'];
        }
        
        jsonSuccess('Berhasil', ['products' => $products]);
    } catch (Exception $e) {
        error_log("Error fetching popular products: " . $e->getMessage());
        jsonError('Terjadi kesalahan server', 500);
    }
    
} else {
    jsonError('Method tidak diizinkan', 405);
}
