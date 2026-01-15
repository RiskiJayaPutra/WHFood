<?php
require_once __DIR__ . '/app/config/database.php';
$db = Database::getInstance();
$sellers = $db->select("SELECT id, storeName, storeSlug FROM seller_profiles");
echo "Total Sellers: " . count($sellers) . "\n";
foreach ($sellers as $s) {
    echo "ID: {$s['id']} | Name: {$s['storeName']} | Slug: '{$s['storeSlug']}'\n";
}
