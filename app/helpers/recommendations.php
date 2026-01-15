<?php
/**
 * ============================================================================
 * WHFood - Recommendation Helper Functions
 * ============================================================================
 * 
 * Functions for tracking user behavior and generating product recommendations.
 */

declare(strict_types=1);

/**
 * Get or create session ID for tracking
 */
function getTrackingSessionId(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['trackingId'])) {
        $_SESSION['trackingId'] = bin2hex(random_bytes(16));
    }
    
    return $_SESSION['trackingId'];
}

/**
 * Track user search query
 */
function trackSearch(string $keyword): void
{
    if (empty(trim($keyword))) return;
    
    $db = Database::getInstance();
    $db->insert('user_searches', [
        'userId' => $_SESSION['userId'] ?? null,
        'sessionId' => getTrackingSessionId(),
        'keyword' => trim($keyword)
    ]);
}

/**
 * Track product view
 */
function trackProductView(int $productId): void
{
    $db = Database::getInstance();
    
    // Avoid duplicate tracking within 5 minutes
    $recent = $db->selectOne("
        SELECT id FROM user_product_views 
        WHERE sessionId = ? AND productId = ? AND viewedAt > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
    ", [getTrackingSessionId(), $productId]);
    
    if (!$recent) {
        $db->insert('user_product_views', [
            'userId' => $_SESSION['userId'] ?? null,
            'sessionId' => getTrackingSessionId(),
            'productId' => $productId
        ]);
    }
}

/**
 * Get recently viewed products for current user/session
 */
function getRecentlyViewed(int $limit = 6): array
{
    $db = Database::getInstance();
    $sessionId = getTrackingSessionId();
    $userId = $_SESSION['userId'] ?? null;
    
    $condition = $userId 
        ? "(v.userId = ? OR v.sessionId = ?)" 
        : "v.sessionId = ?";
    $params = $userId ? [$userId, $sessionId] : [$sessionId];
    
    return $db->select("
        SELECT DISTINCT p.*, 
               (SELECT pi.imagePath FROM product_images pi WHERE pi.productId = p.id AND pi.isPrimary = 1 LIMIT 1) as primaryImage,
               s.storeName
        FROM user_product_views v
        JOIN products p ON v.productId = p.id
        JOIN seller_profiles s ON p.sellerId = s.userId
        WHERE {$condition} AND p.isActive = 1
        GROUP BY p.id
        ORDER BY MAX(v.viewedAt) DESC
        LIMIT ?
    ", array_merge($params, [$limit]));
}

/**
 * Get recommended products based on user behavior
 */
function getRecommendedProducts(int $limit = 8): array
{
    $db = Database::getInstance();
    $sessionId = getTrackingSessionId();
    $userId = $_SESSION['userId'] ?? null;
    
    // 1. Get categories from recently viewed products
    $condition = $userId 
        ? "(v.userId = ? OR v.sessionId = ?)" 
        : "v.sessionId = ?";
    $params = $userId ? [$userId, $sessionId] : [$sessionId];
    
    $viewedCategories = $db->select("
        SELECT DISTINCT p.categoryId
        FROM user_product_views v
        JOIN products p ON v.productId = p.id
        WHERE {$condition}
        ORDER BY v.viewedAt DESC
        LIMIT 5
    ", $params);
    
    // 2. Get keywords from recent searches
    $searchKeywords = $db->select("
        SELECT DISTINCT keyword
        FROM user_searches
        WHERE " . ($userId ? "(userId = ? OR sessionId = ?)" : "sessionId = ?") . "
        ORDER BY createdAt DESC
        LIMIT 5
    ", $params);
    
    // 3. Build recommendation query
    $recommendations = [];
    $excludeIds = [];
    
    // Get viewed product IDs to exclude
    $viewedProducts = $db->select("
        SELECT DISTINCT productId FROM user_product_views 
        WHERE {$condition}
        ORDER BY viewedAt DESC LIMIT 20
    ", $params);
    $excludeIds = array_column($viewedProducts, 'productId');
    
    // Recommend by category
    if (!empty($viewedCategories)) {
        $categoryIds = array_column($viewedCategories, 'categoryId');
        $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
        $excludePlaceholders = empty($excludeIds) ? '' : ' AND p.id NOT IN (' . implode(',', array_fill(0, count($excludeIds), '?')) . ')';
        
        $categoryRecs = $db->select("
            SELECT p.*, 
                   (SELECT pi.imagePath FROM product_images pi WHERE pi.productId = p.id AND pi.isPrimary = 1 LIMIT 1) as primaryImage,
                   s.storeName,
                   'category' as recommendationType
            FROM products p
            JOIN seller_profiles s ON p.sellerId = s.userId
            WHERE p.categoryId IN ({$placeholders}) 
              AND p.isActive = 1
              {$excludePlaceholders}
            ORDER BY p.rating DESC, p.totalReviews DESC
            LIMIT ?
        ", array_merge($categoryIds, $excludeIds, [($limit / 2)]));
        
        $recommendations = array_merge($recommendations, $categoryRecs);
        $excludeIds = array_merge($excludeIds, array_column($categoryRecs, 'id'));
    }
    
    // Recommend by search keywords
    if (!empty($searchKeywords) && count($recommendations) < $limit) {
        foreach ($searchKeywords as $search) {
            $keyword = '%' . $search['keyword'] . '%';
            $excludePlaceholders = empty($excludeIds) ? '' : ' AND p.id NOT IN (' . implode(',', array_fill(0, count($excludeIds), '?')) . ')';
            
            $keywordRecs = $db->select("
                SELECT p.*, 
                       (SELECT pi.imagePath FROM product_images pi WHERE pi.productId = p.id AND pi.isPrimary = 1 LIMIT 1) as primaryImage,
                       s.storeName,
                       'search' as recommendationType
                FROM products p
                JOIN seller_profiles s ON p.sellerId = s.userId
                WHERE (p.name LIKE ? OR p.description LIKE ?)
                  AND p.isActive = 1
                  {$excludePlaceholders}
                ORDER BY p.rating DESC
                LIMIT ?
            ", array_merge([$keyword, $keyword], $excludeIds, [2]));
            
            $recommendations = array_merge($recommendations, $keywordRecs);
            $excludeIds = array_merge($excludeIds, array_column($keywordRecs, 'id'));
            
            if (count($recommendations) >= $limit) break;
        }
    }
    
    // Fill remaining with popular products
    if (count($recommendations) < $limit) {
        $remaining = $limit - count($recommendations);
        $excludePlaceholders = empty($excludeIds) ? '' : ' AND p.id NOT IN (' . implode(',', array_fill(0, count($excludeIds), '?')) . ')';
        
        $popularRecs = $db->select("
            SELECT p.*, 
                   (SELECT pi.imagePath FROM product_images pi WHERE pi.productId = p.id AND pi.isPrimary = 1 LIMIT 1) as primaryImage,
                   s.storeName,
                   'popular' as recommendationType
            FROM products p
            JOIN seller_profiles s ON p.sellerId = s.userId
            WHERE p.isActive = 1
              {$excludePlaceholders}
            ORDER BY p.rating DESC, p.totalReviews DESC, RAND()
            LIMIT ?
        ", array_merge($excludeIds, [$remaining]));
        
        $recommendations = array_merge($recommendations, $popularRecs);
    }
    
    // Remove duplicates and limit
    $uniqueRecs = [];
    $seenIds = [];
    foreach ($recommendations as $rec) {
        if (!in_array($rec['id'], $seenIds)) {
            $uniqueRecs[] = $rec;
            $seenIds[] = $rec['id'];
        }
        if (count($uniqueRecs) >= $limit) break;
    }
    
    return $uniqueRecs;
}

/**
 * Check if user has any tracking data
 */
function hasUserActivity(): bool
{
    $db = Database::getInstance();
    $sessionId = getTrackingSessionId();
    $userId = $_SESSION['userId'] ?? null;
    
    $condition = $userId 
        ? "(userId = ? OR sessionId = ?)" 
        : "sessionId = ?";
    $params = $userId ? [$userId, $sessionId] : [$sessionId];
    
    $views = $db->selectOne("SELECT COUNT(*) as cnt FROM user_product_views WHERE {$condition}", $params);
    $searches = $db->selectOne("SELECT COUNT(*) as cnt FROM user_searches WHERE {$condition}", $params);
    
    return ($views['cnt'] ?? 0) > 0 || ($searches['cnt'] ?? 0) > 0;
}
