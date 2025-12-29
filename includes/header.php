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

<body >

    <!-- Header/Navigation -->
    <header>
      <div>
        <a href="index.php" style="font-size: 1.5rem; font-weight: bold; text-decoration: none; color: #4CAF50;">
          🍳 Ranna Kori
        </a>
      </div>

      <nav>
        <a href="index.php">Home</a>
        <a href="recipes.php">Recipes</a>
        <a href="categories.php">Categories</a>
        <a href="chefs.php">Chefs</a>
        <?php if (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
          <a href="admin-dashboard.php">Admin Dashboard</a>
        <?php endif; ?>
      </nav>

      <div>
        <!-- Search Bar in Header -->
        <form method="get" action="recipes.php" style="display: flex; gap: 8px;">
          <input name="search" placeholder="Search..." class="search-input" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
          <button type="submit" class="search-button">
            <i class="fas fa-search"></i>
          </button>
        </form>
      </div>

      <div style="display: flex; gap: 20px; align-items: center;">
        <?php if (!empty($_SESSION['user_id'])): ?>
          <div style="position: relative; display: flex; align-items: center; gap: 8px; cursor: pointer;" id="profileMenu">
            <?php if ($userAvatar): ?>
              <img src="<?php echo htmlspecialchars($userAvatar); ?>"
                style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #ddd;">
            <?php else: ?>
              <div style="width: 40px; height: 40px; border-radius: 50%; background: #ddd; display: flex; align-items: center; justify-content: center;">
                👤
              </div>
            <?php endif; ?>

            <div>
              <a href="profile.php" style="text-decoration: none; display: block;">
                <?php echo htmlspecialchars($_SESSION['user_name']); ?>
              </a>
              <small style="color: #666;">
                (<?php echo $_SESSION['user_points']; ?> pts)
              </small>
            </div>

            <!-- Dropdown Menu -->
            <div id="profileDropdown" style="display: none; position: absolute; top: 100%; right: 0; background: white; border: 1px solid #ddd; border-radius: 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); min-width: 180px; z-index: 1000; margin-top: 8px;">
              <a href="profile.php" style="display: block; padding: 12px 16px; color: #333; text-decoration: none; border-bottom: 1px solid #eee; transition: background 0.2s;" onmouseover="this.style.background='#f5f5f5'" onmouseout="this.style.background='white'">
                👤 My Profile
              </a>
              <a href="favorites.php" style="display: block; padding: 12px 16px; color: #333; text-decoration: none; border-bottom: 1px solid #eee; transition: background 0.2s;" onmouseover="this.style.background='#f5f5f5'" onmouseout="this.style.background='white'">
                ❤️ Favorites
              </a>
              <a href="leaderboard.php" style="display: block; padding: 12px 16px; color: #333; text-decoration: none; border-bottom: 1px solid #eee; transition: background 0.2s;" onmouseover="this.style.background='#f5f5f5'" onmouseout="this.style.background='white'">
                🏆 Leaderboard
              </a>
              <a href="withdraw.php" style="display: block; padding: 12px 16px; color: #333; text-decoration: none; border-bottom: 1px solid #eee; transition: background 0.2s;" onmouseover="this.style.background='#f5f5f5'" onmouseout="this.style.background='white'">
                💰 Withdraw
              </a>
              <a href="logout.php" style="display: block; padding: 12px 16px; color: #d32f2f; text-decoration: none; transition: background 0.2s;" onmouseover="this.style.background='#f5f5f5'" onmouseout="this.style.background='white'">
                🚪 Logout
              </a>
            </div>
          </div>
        <?php else: ?>

          <a href="login.php">Login</a>
          <a href="register.php">Register</a>
        <?php endif; ?>
      </div>

      <script>
        const profileMenu = document.getElementById('profileMenu');
        const profileDropdown = document.getElementById('profileDropdown');

        profileMenu.addEventListener('click', function(e) {
          e.stopPropagation();
          profileDropdown.style.display = profileDropdown.style.display === 'none' ? 'block' : 'none';
        });

        document.addEventListener('click', function(e) {
          if (!profileMenu.contains(e.target)) {
            profileDropdown.style.display = 'none';
          }
        });
      </script>

    </header>