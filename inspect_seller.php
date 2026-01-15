<?php
require_once __DIR__ . '/app/config/database.php';
$db = Database::getInstance();

$term = '%Way%';
$sellers = $db->select("SELECT id, storeName, storeSlug FROM seller_profiles WHERE storeName LIKE ?", [$term]);

echo "Found " . count($sellers) . " sellers matching '$term':\n";
foreach ($sellers as $s) {
    echo "ID: " . $s['id'] . "\n";
    echo "Name: [" . $s['storeName'] . "]\n";
    echo "Slug: [" . $s['storeSlug'] . "]\n";
    echo "URL encoded Slug: " . urlencode($s['storeSlug']) . "\n";
    echo "-------------------\n";
}
