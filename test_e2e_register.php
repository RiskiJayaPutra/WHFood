<?php
// test_e2e_register.php
declare(strict_types=1);

require_once __DIR__ . '/app/config/database.php';

// Prepare data
$url = 'http://localhost:8080/daftar-penjual';
$data = [
    'csrfToken' => '', // fetch first? We need to fetch page to get CSRF
    'fullName' => 'E2E Test Seller',
    'email' => 'e2e.seller@whfood.local',
    'phoneNumber' => '081234567890',
    'password' => 'password123',
    'storeName' => 'Warung E2E',
    'storeDescription' => 'Test Description',
    'address' => 'Jl. E2E No. 1',
    'latitude' => -5.3,
    'longitude' => 105.2,
    // Add dummy file uploads manually or skip if validation allows (validation checks UPLOAD_ERR_OK only if isset)
    // But our code does: if isset and error=ok. If not set, it might skip?
    // Let's check validation logic. It does check uploads?
    // No, only if isset. BUT 'storeLogo' will be null if not uploaded.
    // RegisterSeller allows null logo.
];

// Step 1: Get CSRF Token
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
$response = curl_exec($ch);

if (preg_match('/name="csrfToken" value="([^"]+)"/', $response, $matches)) {
    $csrfToken = $matches[1];
    echo "CSRF Token found: " . substr($csrfToken, 0, 10) . "...\n";
    $data['csrfToken'] = $csrfToken;
} else {
    echo "Failed to get CSRF token.\n";
    exit(1);
}

// Step 2: Post Data
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_HEADER, true); // To check redirects
$response = curl_exec($ch);
$info = curl_getinfo($ch);

echo "HTTP Code: " . $info['http_code'] . "\n";

if ($info['http_code'] == 302 || $info['http_code'] == 200) {
    echo "Response OK (Redirect or Success)\n";
} else {
    echo "Response Failed.\n";
    // echo $response;
}

curl_close($ch);

// Step 3: Verify Database
try {
    $db = Database::getInstance();
    $user = $db->selectOne("SELECT * FROM users WHERE email = ?", ['e2e.seller@whfood.local']);
    
    if ($user) {
        echo "✅ User created successfully in DB!\n";
        echo "Role: " . $user['role'] . "\n";
        
        $seller = $db->selectOne("SELECT * FROM seller_profiles WHERE userId = ?", [$user['id']]);
        if ($seller) {
            echo "✅ Seller profile created!\n";
            echo "Store Name: " . $seller['storeName'] . "\n";
        } else {
            echo "❌ Seller profile MISSING.\n";
        }
        
        // Clean up
        $db->query("DELETE FROM seller_profiles WHERE userId = ?", [$user['id']]);
        $db->query("DELETE FROM users WHERE id = ?", [$user['id']]);
        echo "Cleaned up test data.\n";
        
    } else {
        echo "❌ User NOT found in DB.\n";
    }
    
} catch (Exception $e) {
    echo "DB Check Error: " . $e->getMessage() . "\n";
}
