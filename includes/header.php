<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get user avatar if logged in
$userAvatar = null;
if (!empty($_SESSION['user_id'])) {
  $stmt = $pdo->prepare('SELECT avatar FROM users WHERE id = ?');
  $stmt->execute([$_SESSION['user_id']]);
  $userRow = $stmt->fetch();
  $userAvatar = $userRow['avatar'] ?? null;
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
  <!-- Header/Navigation -->
  <header>
    <div>
      <a href="recipes.php" style="font-size: 1.5rem; font-weight: bold; text-decoration: none; color: #4CAF50;">
        🍳 Ranna Kori
      </a>
    </div>

    <nav>
      <a href="index.php">Home</a>
      <a href="recipes.php">Recipes</a>
      <a href="categories.php">Categories</a>
      <a href="chefs.php">Chefs</a>
      <?php if (!empty($_SESSION['user_id'])): ?>
        <a href="favorites.php">Favorites</a>
        <a href="leaderboard.php">Leaderboard</a>
        <?php if (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
          <a href="admin-dashboard.php">Admin Dashboard</a>
        <?php endif; ?>
      <?php endif; ?>

    </nav>

    <div>
      <!-- Search Bar in Header -->
      <form method="get" action="recipes.php" style="display: flex; gap: 8px;">
        <input name="search" placeholder="Search recipes..." class="search-input" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
        <button type="submit" class="search-button">
          <i class="fas fa-search"></i>
        </button>
      </form>
    </div>

    <div style="display: flex; gap: 20px; align-items: center;">
      <?php if (!empty($_SESSION['user_id'])): ?>
        <div style="display: flex; align-items: center; gap: 8px;">
          <?php if ($userAvatar): ?>
            <img src="<?php echo htmlspecialchars($userAvatar); ?>"
              style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #ddd;">
          <?php else: ?>
            <div style="width: 40px; height: 40px; border-radius: 50%; background: #ddd; display: flex; align-items: center; justify-content: center;">
              👤
            </div>
          <?php endif; ?>

          <a href="profile.php" style="text-decoration: none;">
            <?php echo htmlspecialchars($_SESSION['user_name']); ?> (<?php echo $_SESSION['user_points']; ?> pts)
          </a>
        </div>

        <a href="logout.php" style="color: red;">Logout</a>
      <?php else: ?>

        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
      <?php endif; ?>
    </div>

  </header>