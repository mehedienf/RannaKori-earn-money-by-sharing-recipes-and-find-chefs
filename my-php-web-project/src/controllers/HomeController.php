<?php
class HomeController {
    public function index() {
        // Load the home view
        require_once __DIR__ . '/../views/home.php';
    }
}
?>