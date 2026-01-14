<?php

/**
 * ============================================================================
 * WHFood - Products API
 * ============================================================================
 */

declare(strict_types=1);

require_once HELPERS_PATH . '/functions.php';
require_once CONFIG_PATH . '/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$db = Database::getInstance();

// Route: /api/produk atau /api/products
$productId = $param2 ?? null;

switch ($method) {
    case 'GET':
        if ($productId) {
            // Get single product
            $product = $db->selectOne("
                SELECT p.*, sp.storeName, sp.storeSlug, sp.isVerified as sellerVerified,
                       u.phoneNumber as sellerPhone
                FROM products p
                JOIN seller_profiles sp ON p.sellerId = sp.id
                JOIN users u ON sp.userId = u.id
                WHERE p.id = ? AND p.status = 'active'
            ", [(int)$productId]);
            
            if (!$product) {
                jsonError('Produk tidak ditemukan', 404);
            }
            
            jsonSuccess($product);
        } else {
            // Get all products with filters
            $category = input('kategori');
            $search = input('cari');
            $sort = input('urut') ?: 'terbaru';
            $page = max(1, input('halaman', 'int') ?: 1);
            $perPage = min(50, max(1, input('per_halaman', 'int') ?: 12));
            $offset = ($page - 1) * $perPage;
            
            $where = ["p.status = 'active'", "p.isAvailable = 1", "sp.isVerified = 1"];
            $params = [];
            
            if ($category) {
                $where[] = "p.category = ?";
                $params[] = $category;
            }
            
            if ($search) {
                $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
                $params[] = "%{$search}%";
                $params[] = "%{$search}%";
            }
            
            $whereClause = implode(' AND ', $where);
            
            $orderBy = match ($sort) {
                'populer' => 'p.totalSold DESC',
                'rating' => 'p.rating DESC',
                'harga-rendah' => 'COALESCE(p.discountPrice, p.price) ASC',
                'harga-tinggi' => 'COALESCE(p.discountPrice, p.price) DESC',
                default => 'p.createdAt DESC'
            };
            
            // Get total count
            $totalResult = $db->selectOne("
                SELECT COUNT(*) as total
                FROM products p
                JOIN seller_profiles sp ON p.sellerId = sp.id
                WHERE {$whereClause}
            ", $params);
            
            $total = $totalResult['total'] ?? 0;
            
            // Get products
            $products = $db->select("
                SELECT p.id, p.name, p.slug, p.price, p.discountPrice, p.discountPercentage,
                       p.rating, p.totalSold, p.primaryImage, p.category,
                       sp.storeName, sp.storeSlug, sp.isVerified as sellerVerified
                FROM products p
                JOIN seller_profiles sp ON p.sellerId = sp.id
                WHERE {$whereClause}
                ORDER BY {$orderBy}
                LIMIT {$perPage} OFFSET {$offset}
            ", $params);
            
            jsonSuccess([
                'data' => $products,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => ceil($total / $perPage)
                ]
            ]);
        }
        break;
        
    case 'POST':
        // Create product (requires seller auth)
        if (!isLoggedIn() || !isSeller()) {
            jsonError('Unauthorized', 401);
        }
        
        if (!verifyCsrf($_POST['csrfToken'] ?? null)) {
            jsonError('CSRF token tidak valid', 403);
        }
        
        $seller = sellerProfile();
        
        $data = [
            'sellerId' => $seller['id'],
            'name' => input('name'),
            'slug' => slugify(input('name')),
            'description' => input('description'),
            'category' => input('category'),
            'price' => input('price', 'float'),
            'discountPrice' => input('discountPrice', 'float') ?: null,
            'stock' => input('stock', 'int'),
            'unit' => input('unit') ?: 'porsi',
            'status' => 'active',
            'isAvailable' => 1
        ];
        
        // Validation
        $errors = validate($data, [
            'name' => 'required|min:3|max:150',
            'category' => 'required',
            'price' => 'required|numeric'
        ]);
        
        if (!empty($errors)) {
            jsonError(array_values($errors)[0], 422);
        }
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadFile($_FILES['image'], 'products', 'product');
            if ($uploadResult['success']) {
                $data['primaryImage'] = $uploadResult['path'];
            }
        }
        
        // Calculate discount percentage
        if ($data['discountPrice'] && $data['discountPrice'] < $data['price']) {
            $data['discountPercentage'] = round((($data['price'] - $data['discountPrice']) / $data['price']) * 100);
        }
        
        $productId = $db->insert('products', $data);
        
        if ($productId) {
            jsonSuccess(['id' => $productId], 'Produk berhasil ditambahkan');
        } else {
            jsonError('Gagal menambahkan produk', 500);
        }
        break;
        
    case 'PUT':
    case 'PATCH':
        // Update product
        if (!isLoggedIn() || !isSeller()) {
            jsonError('Unauthorized', 401);
        }
        
        if (!$productId) {
            jsonError('Product ID diperlukan', 400);
        }
        
        $seller = sellerProfile();
        
        // Check ownership
        $existing = $db->selectOne("SELECT id FROM products WHERE id = ? AND sellerId = ?", [(int)$productId, $seller['id']]);
        if (!$existing) {
            jsonError('Produk tidak ditemukan', 404);
        }
        
        // Parse JSON body for PUT/PATCH
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        
        $updateData = [];
        $allowedFields = ['name', 'description', 'category', 'price', 'discountPrice', 'stock', 'unit', 'status'];
        
        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $updateData[$field] = $input[$field];
            }
        }
        
        if (isset($updateData['name'])) {
            $updateData['slug'] = slugify($updateData['name']);
        }
        
        if (isset($updateData['discountPrice']) && isset($updateData['price'])) {
            if ($updateData['discountPrice'] && $updateData['discountPrice'] < $updateData['price']) {
                $updateData['discountPercentage'] = round((($updateData['price'] - $updateData['discountPrice']) / $updateData['price']) * 100);
            }
        }
        
        if (!empty($updateData)) {
            $db->update('products', $updateData, 'id = ?', [(int)$productId]);
            jsonSuccess(null, 'Produk berhasil diperbarui');
        } else {
            jsonError('Tidak ada data untuk diperbarui', 400);
        }
        break;
        
    case 'DELETE':
        // Delete product
        if (!isLoggedIn() || !isSeller()) {
            jsonError('Unauthorized', 401);
        }
        
        if (!$productId) {
            jsonError('Product ID diperlukan', 400);
        }
        
        $seller = sellerProfile();
        
        // Check ownership
        $existing = $db->selectOne("SELECT id FROM products WHERE id = ? AND sellerId = ?", [(int)$productId, $seller['id']]);
        if (!$existing) {
            jsonError('Produk tidak ditemukan', 404);
        }
        
        // Soft delete
        $db->update('products', ['status' => 'deleted'], 'id = ?', [(int)$productId]);
        jsonSuccess(null, 'Produk berhasil dihapus');
        break;
        
    default:
        jsonError('Method tidak diizinkan', 405);
}
