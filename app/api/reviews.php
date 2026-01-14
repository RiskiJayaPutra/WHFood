<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$db = Database::getInstance();

if ($method === 'GET') {
    $productId = input('productId', 'int');
    
    if (!$productId) {
        jsonError('Product ID diperlukan');
    }
    
    $reviews = $db->select("
        SELECT r.*, u.fullName, u.profileImage
        FROM reviews r
        JOIN users u ON r.userId = u.id
        WHERE r.productId = ? AND r.isVisible = 1
        ORDER BY r.createdAt DESC
        LIMIT 20
    ", [$productId]);
    
    jsonSuccess('Berhasil', ['reviews' => $reviews]);
    
} elseif ($method === 'POST') {
    if (!isLoggedIn()) {
        jsonError('Silakan login terlebih dahulu', 401);
    }
    
    if (!verifyCsrf(input('csrfToken'))) {
        jsonError('Sesi tidak valid', 403);
    }
    
    $productId = input('productId', 'int');
    $rating = input('rating', 'int');
    $comment = input('comment');
    
    if (!$productId || !$rating) {
        jsonError('Product ID dan rating diperlukan');
    }
    
    if ($rating < 1 || $rating > 5) {
        jsonError('Rating harus 1-5');
    }
    
    $product = $db->selectOne("SELECT id, sellerId FROM products WHERE id = ?", [$productId]);
    if (!$product) {
        jsonError('Produk tidak ditemukan', 404);
    }
    
    $existingReview = $db->selectOne(
        "SELECT id FROM reviews WHERE productId = ? AND userId = ?",
        [$productId, $_SESSION['userId']]
    );
    
    if ($existingReview) {
        $db->update('reviews', [
            'rating' => $rating,
            'comment' => $comment
        ], 'id = ?', [$existingReview['id']]);
        
        jsonSuccess('Review berhasil diperbarui');
    } else {
        $db->insert('reviews', [
            'productId' => $productId,
            'userId' => $_SESSION['userId'],
            'rating' => $rating,
            'comment' => $comment
        ]);
        
        jsonSuccess('Review berhasil ditambahkan');
    }
    
} else {
    jsonError('Method tidak diizinkan', 405);
}
