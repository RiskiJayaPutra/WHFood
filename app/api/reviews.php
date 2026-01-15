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
    try {
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
        
        // Handle image uploads (max 2)
        $uploadedImages = [];
        if (!empty($_FILES['images']) && is_array($_FILES['images']['name'])) {
        $fileCount = min(count($_FILES['images']['name']), 2);
            
            for ($i = 0; $i < $fileCount; $i++) {
                if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                    $file = [
                        'name' => $_FILES['images']['name'][$i],
                        'type' => $_FILES['images']['type'][$i],
                        'tmp_name' => $_FILES['images']['tmp_name'][$i],
                        'error' => $_FILES['images']['error'][$i],
                        'size' => $_FILES['images']['size'][$i]
                    ];
                    
                    $result = uploadFile($file, 'reviews', 'review_' . $productId . '_');
                    if ($result['success']) {
                        $uploadedImages[] = $result['path'];
                    }
                }
            }
        }
        
        $existingReview = $db->selectOne(
            "SELECT id, images FROM reviews WHERE productId = ? AND userId = ?",
            [$productId, $_SESSION['userId']]
        );
        
        if ($existingReview) {
            $updateData = [
                'rating' => $rating,
                'comment' => $comment
            ];
            
            // If new images uploaded, update them; otherwise keep existing
            if (!empty($uploadedImages)) {
                $updateData['images'] = json_encode($uploadedImages);
            }
            
            $db->update('reviews', $updateData, 'id = ?', [$existingReview['id']]);
            
            jsonSuccess('Review berhasil diperbarui');
        } else {
            $insertData = [
                'productId' => $productId,
                'userId' => $_SESSION['userId'],
                'rating' => $rating,
                'comment' => $comment
            ];
            
            if (!empty($uploadedImages)) {
                $insertData['images'] = json_encode($uploadedImages);
            }
            
            $db->insert('reviews', $insertData);
            
            jsonSuccess('Review berhasil ditambahkan');
        }
    } catch (Exception $e) {
        jsonError('Terjadi kesalahan: ' . $e->getMessage());
    }
    
} else {
    jsonError('Method tidak diizinkan', 405);
}
