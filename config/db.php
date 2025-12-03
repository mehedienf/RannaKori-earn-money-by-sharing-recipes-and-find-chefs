<?php
$host = '127.0.0.1';
$db   = 'ranna_kori';
$user = 'root';
$pass = ''; // যদি XAMPP MySQL-এর root পাসওয়ার্ড না থাকে, ফাঁকা রাখো

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // error হলে exception ছুঁড়বে
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // fetch করলে associative array পাবে
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // production এ এভাবে দেখাব না, but এখন debug এর জন্য:
    die('Database connection failed: ' . $e->getMessage());
}
?>