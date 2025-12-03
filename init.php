<?php
// সব পেজ থেকে include করব

// error দেখানোর জন্য (development stage)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// session চালু
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// database connection load
require __DIR__ . '/config/db.php';