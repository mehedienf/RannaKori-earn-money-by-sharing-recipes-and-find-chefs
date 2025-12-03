<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// সব session data clear
$_SESSION = [];
session_destroy();

// home এ পাঠিয়ে দেই
header('Location: index.php');
exit;