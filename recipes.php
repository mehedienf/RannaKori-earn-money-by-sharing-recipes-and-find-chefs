<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/config/db.php';
session_start();

// Get search query
$search = trim($_GET['search'] ?? '');
$recipes = [];

// Fetch recipes based on search
try {
    $searchTerm = '%' . $search . '%';
    
    if (!empty($search)) {
        // Search in title
        $query = 'SELECT r.id, r.title, r.description, r.image, r.created_at, r.user_id, 
                         u.name AS author_name
                  FROM recipes r 
                  JOIN users u ON r.user_id = u.id 
                  WHERE r.title LIKE ? 
                  ORDER BY r.created_at DESC';
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$searchTerm]);
        $recipes = $stmt->fetchAll();
    } else {
        // Show all recipes
        $query = 'SELECT r.id, r.title, r.description, r.image, r.created_at, r.user_id, 
                         u.name AS author_name
                  FROM recipes r 
                  JOIN users u ON r.user_id = u.id 
                  ORDER BY r.created_at DESC';
        
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $recipes = $stmt->fetchAll();
    }
    
    // Get like counts separately
    foreach ($recipes as &$recipe) {
        $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM likes WHERE recipe_id = ?');
        $stmt->execute([$recipe['id']]);
        $likeCount = $stmt->fetch();
        $recipe['like_count'] = $likeCount['count'];
    }
    
} catch (PDOException $e) {
    echo "<div style='color: red; padding: 20px;'><strong>Database Error:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<main>
    <section class="container" style="max-width: 1000px; margin: 40px auto;">
        <h1>🍳 Recipes</h1>
        <!-- Add Recipe Button (if logged in) -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <div style="margin-bottom: 24px;">
            <a href="add-recipe.php" style="display: inline-block; background: #4CAF50; color: white; padding: 12px 24px; border-radius: 4px; text-decoration: none; font-size: 1rem;">
                ➕ Add New Recipe
            </a>
            </div>
        <?php endif; ?>
        <!-- Search Bar -->
        <form method="get" action="recipes.php" style="margin-bottom: 32px;">
            <div style="display: flex; gap: 8px;">
                <input type="text" name="search" placeholder="Search recipes..." 
                       value="<?php echo htmlspecialchars($search); ?>"
                       style="flex: 1; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
                <button type="submit" style="background: #4CAF50; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem;">
                    🔍 Search
                </button>
                <?php if (!empty($search)): ?>
                    <a href="recipes.php" style="background: #ddd; color: black; padding: 12px 24px; border: none; border-radius: 4px; text-decoration: none;">
                        ✕ Clear
                    </a>
                <?php endif; ?>
            </div>
        </form>
        
        <!-- Search Results -->
        <?php if (!empty($search)): ?>
            <p style="color: #666; margin-bottom: 16px; font-size: 1.1rem;">
                <strong>Found <?php echo count($recipes); ?> recipe(s) for "<?php echo htmlspecialchars($search); ?>"</strong>
            </p>
        <?php endif; ?>
        
        <!-- Recipes Grid -->
        <?php if (empty($recipes)): ?>
            <div style="background: #f5f5f5; padding: 40px; text-align: center; border-radius: 8px;">
                <p style="font-size: 1.2rem; color: #666;">
                    <?php echo !empty($search) ? '❌ No recipes found' : '📝 No recipes yet'; ?>
                </p>
                <p style="color: #999; margin-top: 8px;">
                    <?php echo !empty($search) ? 'Try a different search keyword' : 'Be the first to add a recipe!'; ?>
                </p>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                <?php foreach ($recipes as $recipe): ?>
                    <article style="border: 1px solid #ddd; padding: 16px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        
                        <?php if (!empty($recipe['image'])): ?>
                            <img src="<?php echo htmlspecialchars($recipe['image']); ?>" 
                                 style="width: 100%; height: 200px; object-fit: cover; border-radius: 6px; margin-bottom: 12px;">
                        <?php else: ?>
                            <div style="width: 100%; height: 200px; background: #ddd; border-radius: 6px; margin-bottom: 12px; display: flex; align-items: center; justify-content: center; font-size: 3rem;">
                                🍳
                            </div>
                        <?php endif; ?>
                        
                        <h3 style="margin: 0 0 8px 0;">
                            <a href="recipe-details.php?id=<?php echo (int)$recipe['id']; ?>" style="text-decoration: none; color: #333;">
                                <?php echo htmlspecialchars($recipe['title']); ?>
                            </a>
                        </h3>
                        
                        <p style="color: #666; font-size: 0.9rem; margin: 0 0 8px 0;">
                            By <strong><?php echo htmlspecialchars($recipe['author_name']); ?></strong>
                        </p>
                        
                        <?php if (!empty($recipe['description'])): ?>
                            <p style="color: #555; font-size: 0.9rem; margin: 0 0 8px 0;">
                                <?php echo htmlspecialchars(substr($recipe['description'], 0, 60)); ?>...
                            </p>
                        <?php endif; ?>
                        
                        <p style="color: #ff4444; margin: 0 0 12px 0; font-weight: bold;">
                            ❤️ <?php echo (int)($recipe['like_count'] ?? 0); ?> Likes
                        </p>
                        
                        <a href="recipe-details.php?id=<?php echo (int)$recipe['id']; ?>" 
                           style="display: inline-block; background: #4CAF50; color: white; padding: 10px 16px; border-radius: 4px; text-decoration: none;">
                            View Recipe →
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>