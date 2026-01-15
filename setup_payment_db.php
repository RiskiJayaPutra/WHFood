<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::getInstance();
    $sql = file_get_contents(__DIR__ . '/sql/payment_methods.sql');
    
    // Split by statement if needed, or execute directly if single statement (PDO usually handles 1 stmt per call, but let's try direct)
    // Actually, SQL file has comments and multiple lines. PDO might fail if not split.
    // Simplifying: clean comments
    
    $statements = explode(';', $sql);
    foreach ($statements as $stmt) {
        $stmt = trim($stmt);
        if (!empty($stmt)) {
            $db->query($stmt);
        }
    }
    
    echo "Payment methods table created successfully.\n";
    
    // Add default 'Cash' method for existing sellers?
    // Not necessary, UI will handle empty state.
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
