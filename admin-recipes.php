<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/config/db.php';
session_start();

// Check if user is admin
if (empty($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Handle delete recipe
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'delete_recipe') {
    $recipe_id = (int)($_POST['recipe_id'] ?? 0);
    
    if ($recipe_id > 0) {
        try {
            // Delete likes
            $stmt = $pdo->prepare('DELETE FROM likes WHERE recipe_id = ?');
            $stmt->execute([$recipe_id]);
            
            // Delete reviews
            $stmt = $pdo->prepare('DELETE FROM reviews WHERE recipe_id = ?');
            $stmt->execute([$recipe_id]);
            
            // Delete recipe
            $stmt = $pdo->prepare('DELETE FROM recipes WHERE id = ?');
            $stmt->execute([$recipe_id]);
            
            echo '<div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 6px; margin-bottom: 16px;">✅ Recipe deleted successfully!</div>';
        } catch (PDOException $e) {
            echo '<div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 16px;">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}

// Search and filter
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$user_filter = $_GET['user'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

// Build query
$query = 'SELECT r.*, u.name as author_name, u.email, u.avatar, COUNT(l.id) as like_count, COUNT(rv.id) as review_count
          FROM recipes r
          JOIN users u ON r.user_id = u.id
          LEFT JOIN likes l ON r.id = l.recipe_id
          LEFT JOIN reviews rv ON r.id = rv.recipe_id
          WHERE 1=1';

$params = [];

if (!empty($search)) {
    $query .= ' AND (r.title LIKE ? OR r.description LIKE ? OR u.name LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

if (!empty($category)) {
    $query .= ' AND r.category_id = ?';
    $params[] = (int)$category;
}

if (!empty($user_filter)) {
    $query .= ' AND r.user_id = ?';
    $params[] = (int)$user_filter;
}

$query .= ' GROUP BY r.id';

// Sort
if ($sort === 'oldest') {
    $query .= ' ORDER BY r.created_at ASC';
} elseif ($sort === 'most_liked') {
    $query .= ' ORDER BY like_count DESC';
} elseif ($sort === 'most_reviewed') {
    $query .= ' ORDER BY review_count DESC';
} else {
    $query .= ' ORDER BY r.created_at DESC';
}

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $recipes = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Error: " . htmlspecialchars($e->getMessage());
    $recipes = [];
}

// Fetch categories for filter
try {
    $stmt = $pdo->query('SELECT id, name FROM categories ORDER BY name');
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}

// Get total stats
try {
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM recipes');
    $totalRecipes = $stmt->fetch()['count'];
    
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM likes');
    $totalLikes = $stmt->fetch()['count'];
    
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM reviews');
    $totalReviews = $stmt->fetch()['count'];
} catch (PDOException $e) {
    $totalRecipes = $totalLikes = $totalReviews = 0;
}
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<main>
    <section class="container" style="max-width: 1400px; margin: 40px auto;">
        
        <h1 style="margin-bottom: 32px;">🍳 Manage Recipes</h1>

        <!-- Statistics Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px; margin-bottom: 40px;">
            
            <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 20px; border-radius: 8px;">
                <div style="font-size: 2rem; font-weight: bold; margin-bottom: 4px;"><?php echo $totalRecipes; ?></div>
                <div style="font-size: 0.9rem; opacity: 0.9;">Total Recipes</div>
            </div>

            <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 20px; border-radius: 8px;">
                <div style="font-size: 2rem; font-weight: bold; margin-bottom: 4px;">❤️ <?php echo $totalLikes; ?></div>
                <div style="font-size: 0.9rem; opacity: 0.9;">Total Likes</div>
            </div>

            <div style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; padding: 20px; border-radius: 8px;">
                <div style="font-size: 2rem; font-weight: bold; margin-bottom: 4px;">⭐ <?php echo $totalReviews; ?></div>
                <div style="font-size: 0.9rem; opacity: 0.9;">Total Reviews</div>
            </div>
        </div>

        <!-- Filters Section -->
        <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 32px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
            <h3 style="margin-top: 0;">🔍 Search & Filter</h3>
            
            <form method="get" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 16px;">
                
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Recipe Name/Description:</label>
                    <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                </div>

                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Category:</label>
                    <select name="category" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Sort By:</label>
                    <select name="sort" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                        <option value="most_liked" <?php echo $sort === 'most_liked' ? 'selected' : ''; ?>>Most Liked</option>
                        <option value="most_reviewed" <?php echo $sort === 'most_reviewed' ? 'selected' : ''; ?>>Most Reviewed</option>
                    </select>
                </div>

                <div style="display: flex; gap: 8px; align-items: flex-end;">
                    <button type="submit" style="flex: 1; background: #4CAF50; color: white; padding: 10px; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">
                        🔍 Search
                    </button>
                    <a href="admin-recipes.php" style="flex: 1; background: #999; color: white; padding: 10px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; text-align: center; font-weight: 600;">
                        ✕ Clear
                    </a>
                </div>
            </form>

            <p style="color: #666; font-size: 0.9rem; margin: 0;">Found: <strong><?php echo count($recipes); ?></strong> recipe(s)</p>
        </div>

        <!-- Recipes List -->
        <?php if (empty($recipes)): ?>
            <div style="background: #f5f5f5; padding: 40px; text-align: center; border-radius: 8px;">
                <p style="color: #666;">No recipes found</p>
            </div>
        <?php else: ?>
            <div style="display: grid; gap: 16px;">
                <?php foreach ($recipes as $recipe): ?>
                    <div style="background: white; border: 1px solid #ddd; border-radius: 8px; padding: 20px; display: grid; grid-template-columns: 150px 1fr auto; gap: 20px; align-items: center;">
                        
                        <!-- Recipe Image -->
                        <div style="width: 150px; height: 150px; border-radius: 8px; overflow: hidden; background: #f5f5f5; display: flex; align-items: center; justify-content: center;">
                            <?php if ($recipe['image']): ?>
                                <img src="<?php echo htmlspecialchars($recipe['image']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <div style="font-size: 3rem;">🍳</div>
                            <?php endif; ?>
                        </div>

                        <!-- Recipe Info -->
                        <div>
                            <h3 style="margin: 0 0 8px 0;">
                                <a href="recipe-details.php?id=<?php echo $recipe['id']; ?>" style="color: #333; text-decoration: none;">
                                    <?php echo htmlspecialchars($recipe['title']); ?>
                                </a>
                            </h3>

                            <p style="margin: 0 0 8px 0; color: #666; font-size: 0.9rem;">
                                By <strong><?php echo htmlspecialchars($recipe['author_name']); ?></strong>
                                <br>
                                📧 <?php echo htmlspecialchars($recipe['email']); ?>
                            </p>

                            <p style="margin: 0 0 12px 0; color: #555; font-size: 0.9rem;">
                                <?php echo htmlspecialchars(substr($recipe['description'], 0, 150)); ?>...
                            </p>

                            <div style="display: flex; gap: 20px; font-size: 0.85rem; color: #666;">
                                <span>❤️ <?php echo $recipe['like_count']; ?> Likes</span>
                                <span>⭐ <?php echo $recipe['review_count']; ?> Reviews</span>
                                <span>📅 <?php echo $recipe['created_at']; ?></span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <a href="recipe-details.php?id=<?php echo $recipe['id']; ?>" style="background: #2196F3; color: white; padding: 10px 16px; border: none; border-radius: 4px; text-decoration: none; text-align: center; cursor: pointer; font-weight: 600;">
                                👁️ View
                            </a>

                            <form method="post" style="display: inline;">
                                <input type="hidden" name="recipe_id" value="<?php echo $recipe['id']; ?>">
                                <button type="submit" name="action" value="delete_recipe" onclick="return confirm('Delete this recipe? This action cannot be undone.');" style="width: 100%; background: #f44336; color: white; padding: 10px 16px; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">
                                    🗑️ Delete
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Back Button -->
        <div style="margin-top: 40px;">
            <a href="admin-dashboard.php" style="background: #f0f0f0; color: #333; padding: 12px 24px; border-radius: 4px; text-decoration: none; font-weight: 600;">
                ← Back to Admin Dashboard
            </a>
        </div>

    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
