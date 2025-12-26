<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/config/db.php';
session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch liked recipes for current user
try {
    $query = 'SELECT r.id, r.title, r.description, r.image, r.created_at, r.user_id,
                     u.name AS author_name,
                     COUNT(DISTINCT l.id) as like_count
              FROM recipes r
              JOIN users u ON r.user_id = u.id
              LEFT JOIN likes l ON r.id = l.recipe_id
              WHERE r.id IN (SELECT recipe_id FROM likes WHERE user_id = ?)
              GROUP BY r.id
              ORDER BY r.created_at DESC';
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    $recipes = $stmt->fetchAll();
    
} catch (PDOException $e) {
    echo "DB ERROR: " . htmlspecialchars($e->getMessage());
    $recipes = [];
}
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<main>
    <section class="container" style="max-width: 1000px; margin: 40px auto;">
        <h1>❤️ My Favorite Recipes</h1>
        <p style="color: #666; margin-bottom: 32px;">
            You have liked <strong><?php echo count($recipes); ?></strong> recipe<?php echo count($recipes) !== 1 ? 's' : ''; ?>
        </p>
        
        <?php if (empty($recipes)): ?>
            <div style="background: #f5f5f5; padding: 40px; text-align: center; border-radius: 8px;">
                <p style="font-size: 1.2rem; color: #666;">
                    💔 You haven't liked any recipes yet
                </p>
                <p style="color: #999; margin: 16px 0;">
                    Start exploring recipes and like your favorites!
                </p>
                <a href="recipes.php" style="display: inline-block; background: #4CAF50; color: white; padding: 12px 32px; border-radius: 4px; text-decoration: none; margin-top: 16px;">
                    Browse Recipes
                </a>
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
                        
                        <p style="color: #ff4444; margin: 12px 0; font-weight: bold;">
                            ❤️ <?php echo (int)$recipe['like_count']; ?> Likes
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