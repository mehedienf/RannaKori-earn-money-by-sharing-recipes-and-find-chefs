<?php
require 'config/db.php';

try {
    $result = $pdo->query("DESCRIBE withdrawals")->fetchAll();
    $columnExists = false;
    
    foreach ($result as $row) {
        if ($row['Field'] === 'bkash_number') {
            $columnExists = true;
            break;
        }
    }
    
    if (!$columnExists) {
        $sql = "ALTER TABLE withdrawals ADD COLUMN bkash_number VARCHAR(20) DEFAULT NULL AFTER amount";
        $pdo->exec($sql);
        echo "✅ bkash_number column added successfully\n";
    } else {
        echo "✅ bkash_number column already exists\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
