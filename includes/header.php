<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ranna-Kori - Bengali Recipe Sharing Platform</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
  <!-- ============================================
       Header/Navigation
       ============================================ -->
  <header>
    <div class="container">
      <div class="header-container">
        <div class="logo">
          <div class="logo-icon">🍳</div>
          <span>Ranna-Kori</span>
        </div>

        <nav>
          <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="recipes.php">All Recipes</a></li>
            <li><a href="#categories">Categories</a></li>
            <li><a href="#leaderboard">Leaderboard</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="recipe-details.php">View Details</a></li>
          </ul>
        </nav>

        <div class="nav-right">
          <div class="search-box">
            <input type="text" placeholder="Search recipes...">
            <i class="fas fa-search"></i>
          </div>
          <div class="user-menu">
            <div class="user-avatar">U</div>
          </div>
          <div class="auth-links">
                <?php if (!empty($_SESSION['user_id'])): ?>
                    <span>Hi, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
      </div>
    </div>
  </header>