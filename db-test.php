<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/config/db.php';

echo "Database connected successfully.<br>";

// একটা ছোট কুয়েরি: users টেবিল থেকে সব row আনতে চেষ্টা করব
$stmt = $pdo->query("SELECT COUNT(*) AS user_count FROM users");
$row = $stmt->fetch();

echo "Total users: " . $row['user_count'];

?>
