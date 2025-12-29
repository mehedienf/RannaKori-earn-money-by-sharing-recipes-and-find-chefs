<?php
require 'config/db.php';

try {
    $result = $pdo->query("SHOW TABLES LIKE 'withdrawals'")->fetch();

    if (!$result) {
        $sql = "
        CREATE TABLE IF NOT EXISTS withdrawals (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            points INT NOT NULL,
            amount DECIMAL(10, 2) NOT NULL,
            status ENUM('pending', 'completed', 'rejected') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $pdo->exec($sql);
        echo "✅ withdrawals table created successfully\n";
    } else {
        echo "✅ withdrawals table already exists\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
