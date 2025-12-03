<?php
// index.php - Entry point of the web application

// Load configuration
require_once '../src/config/config.php';

// Autoload classes
spl_autoload_register(function ($class_name) {
    include '../src/' . str_replace('\\', '/', $class_name) . '.php';
});

// Initialize the HomeController
$controller = new src\controllers\HomeController();

// Route the request to the appropriate method
$controller->index();
?>