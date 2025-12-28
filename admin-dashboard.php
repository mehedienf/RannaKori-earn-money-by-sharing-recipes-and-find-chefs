<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/config/db.php';
session_start();

// Admin check
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if user is admin
$stmt = $pdo->prepare('SELECT role FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['role'] !== 'admin') {
    echo "❌ You do not have permission to access this page.";
    exit;
}

// Get statistics
try {
    // Total users
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM users');
    $totalUsers = $stmt->fetch()['count'];

    // Total recipes
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM recipes');
    $totalRecipes = $stmt->fetch()['count'];

    // Total chefs
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM chefs');
    $totalChefs = $stmt->fetch()['count'];

    // Pending chef requests
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM chef_requests WHERE status = "pending"');
    $pendingChefs = $stmt->fetch()['count'];

    // Total reviews
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM reviews');
    $totalReviews = $stmt->fetch()['count'];

    // Active users (total for now)
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM users WHERE status = "active"');
    $activeUsers = $stmt->fetch()['count'];
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<main>
    <section class="container" style="max-width: 1200px; margin: 40px auto;">
        <h1>👨‍💼 Admin Dashboard</h1>
        <p style="color: #666; margin-bottom: 32px;">Welcome, Admin! Manage the platform here.</p>

        <!-- Statistics Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 40px;">

            <!-- Total Users -->
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                <div style="font-size: 2.5rem; margin-bottom: 8px;">👥</div>
                <div style="font-size: 2rem; font-weight: bold; margin-bottom: 4px;"><?php echo $totalUsers; ?></div>
                <div style="font-size: 0.9rem; opacity: 0.9;">Total Users</div>
            </div>

            <!-- Total Recipes -->
            <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                <div style="font-size: 2.5rem; margin-bottom: 8px;">🍳</div>
                <div style="font-size: 2rem; font-weight: bold; margin-bottom: 4px;"><?php echo $totalRecipes; ?></div>
                <div style="font-size: 0.9rem; opacity: 0.9;">Total Recipes</div>
            </div>

            <!-- Total Chefs -->
            <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                <div style="font-size: 2.5rem; margin-bottom: 8px;">👨‍🍳</div>
                <div style="font-size: 2rem; font-weight: bold; margin-bottom: 4px;"><?php echo $totalChefs; ?></div>
                <div style="font-size: 0.9rem; opacity: 0.9;">Total Chefs</div>
            </div>

            <!-- Pending Chefs -->
            <div style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                <div style="font-size: 2.5rem; margin-bottom: 8px;">⏳</div>
                <div style="font-size: 2rem; font-weight: bold; margin-bottom: 4px;"><?php echo $pendingChefs; ?></div>
                <div style="font-size: 0.9rem; opacity: 0.9;">Pending Chef Requests</div>
            </div>

            <!-- Total Reviews -->
            <div style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #333; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                <div style="font-size: 2.5rem; margin-bottom: 8px;">⭐</div>
                <div style="font-size: 2rem; font-weight: bold; margin-bottom: 4px;"><?php echo $totalReviews; ?></div>
                <div style="font-size: 0.9rem; opacity: 0.9;">Total Reviews</div>
            </div>

            <!-- Active Users -->
            <div style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%); color: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                <div style="font-size: 2.5rem; margin-bottom: 8px;">📈</div>
                <div style="font-size: 2rem; font-weight: bold; margin-bottom: 4px;"><?php echo $activeUsers; ?></div>
                <div style="font-size: 0.9rem; opacity: 0.9;">Active (Last 7 Days)</div>
            </div>
        </div>

        <!-- Admin Controls -->
        <h2 style="margin-top: 0;">Admin Controls</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">

            <a href="chef-requests.php" style="
                background: #4CAF50;
                color: white;
                padding: 20px;
                border-radius: 8px;
                text-decoration: none;
                text-align: center;
                transition: all 0.3s ease;
            "
                onmouseover="this.style.background='#45a049'; this.style.transform='scale(1.05)'"
                onmouseout="this.style.background='#4CAF50'; this.style.transform='scale(1)'">
                <div style="font-size: 2rem; margin-bottom: 8px;">👨‍🍳</div>
                <div style="font-weight: 600;">Manage Chef Requests</div>
                <div style="font-size: 0.85rem; opacity: 0.9; margin-top: 8px;">
                    Pending: <strong><?php echo $pendingChefs; ?></strong>
                </div>
            </a>

            <a href="admin-users.php" style="
                background: #2196F3;
                color: white;
                padding: 20px;
                border-radius: 8px;
                text-decoration: none;
                text-align: center;
                transition: all 0.3s ease;
            "
                onmouseover="this.style.background='#0b7dda'; this.style.transform='scale(1.05)'"
                onmouseout="this.style.background='#2196F3'; this.style.transform='scale(1)'">
                <div style="font-size: 2rem; margin-bottom: 8px;">👥</div>
                <div style="font-weight: 600;">Manage Users</div>
                <div style="font-size: 0.85rem; opacity: 0.9; margin-top: 8px;">
                    Total: <strong><?php echo $totalUsers; ?></strong>
                </div>
            </a>

            <a href="admin-recipes.php" style="
                background: #FF9800;
                color: white;
                padding: 20px;
                border-radius: 8px;
                text-decoration: none;
                text-align: center;
                transition: all 0.3s ease;
            "
                onmouseover="this.style.background='#e68900'; this.style.transform='scale(1.05)'"
                onmouseout="this.style.background='#FF9800'; this.style.transform='scale(1)'">
                <div style="font-size: 2rem; margin-bottom: 8px;">🍳</div>
                <div style="font-weight: 600;">Manage Recipes</div>
                <div style="font-size: 0.85rem; opacity: 0.9; margin-top: 8px;">
                    Total: <strong><?php echo $totalRecipes; ?></strong>
                </div>
            </a>

            <a href="admin-reports.php" style="
                background: #f44336;
                color: white;
                padding: 20px;
                border-radius: 8px;
                text-decoration: none;
                text-align: center;
                transition: all 0.3s ease;
            "
                onmouseover="this.style.background='#da190b'; this.style.transform='scale(1.05)'"
                onmouseout="this.style.background='#f44336'; this.style.transform='scale(1)'">
                <div style="font-size: 2rem; margin-bottom: 8px;">⚠️</div>
                <div style="font-weight: 600;">User Reports</div>
                <div style="font-size: 0.85rem; opacity: 0.9; margin-top: 8px;">
                    Review reports
                </div>
            </a>

            <a href="admin-settings.php" style="
                background: #9C27B0;
                color: white;
                padding: 20px;
                border-radius: 8px;
                text-decoration: none;
                text-align: center;
                transition: all 0.3s ease;
            "
                onmouseover="this.style.background='#7b1fa2'; this.style.transform='scale(1.05)'"
                onmouseout="this.style.background='#9C27B0'; this.style.transform='scale(1)'">
                <div style="font-size: 2rem; margin-bottom: 8px;">⚙️</div>
                <div style="font-weight: 600;">Settings</div>
                <div style="font-size: 0.85rem; opacity: 0.9; margin-top: 8px;">
                    Platform settings
                </div>
            </a>
        </div>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>