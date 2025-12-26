<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/config/db.php';
session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch user data
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Fetch user's recipes
$stmt = $pdo->prepare('SELECT r.*, COUNT(l.id) as like_count FROM recipes r LEFT JOIN likes l ON r.id = l.recipe_id WHERE r.user_id = ? GROUP BY r.id ORDER BY r.created_at DESC');
$stmt->execute([$_SESSION['user_id']]);
$recipes = $stmt->fetchAll();
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<main>
    <section class="container" style="max-width: 900px; margin: 40px auto;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
            <div>
                <?php if ($user['avatar']): ?>
                    <img src="<?php echo htmlspecialchars($user['avatar']); ?>" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 3px solid #ddd; margin-bottom: 16px;">
                <?php endif; ?>
                
                <h1><?php echo htmlspecialchars($user['name']); ?></h1>
                <p style="color: #666; font-size: 1.1rem;">
                    📧 <?php echo htmlspecialchars($user['email']); ?>
                </p>
            </div>
            
            <div>
                <div style="background: #f5f5f5; padding: 20px; border-radius: 8px; text-align: center; margin-bottom: 16px;">
                    <h2 style="margin: 0; color: #4CAF50;">⭐ <?php echo $user['points']; ?></h2>
                    <p style="margin: 8px 0 0 0; color: #666;">Total Points</p>
                </div>
                
                <div style="display: flex; gap: 8px;">
                    <a href="edit-profile.php" style="background: #4CAF50; color: white; padding: 12px 24px; border: none; border-radius: 4px; text-decoration: none; cursor: pointer;">
                        ✏️ Edit Profile
                    </a>
                    <a href="logout.php" style="background: #dc3545; color: white; padding: 12px 24px; border: none; border-radius: 4px; text-decoration: none; cursor: pointer;">
                        🚪 Logout
                    </a>
                </div>
            </div>
        </div>
        
        <hr style="margin: 32px 0;">
        
        <h2>My Recipes (<?php echo count($recipes); ?>)</h2>
        
        <?php if (empty($recipes)): ?>
            <p style="color: #666;">You haven't created any recipes yet.</p>
            <a href="add-recipe.php">Create your first recipe</a>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
                <?php foreach ($recipes as $recipe): ?>
                    <article style="border: 1px solid #ddd; padding: 16px; border-radius: 8px;">
                        <?php if (!empty($recipe['image'])): ?>
                            <img src="<?php echo htmlspecialchars($recipe['image']); ?>" style="width: 100%; height: 180px; object-fit: cover; border-radius: 6px; margin-bottom: 12px;">
                        <?php endif; ?>
                        
                        <h3 style="margin-top: 0;">
                            <a href="recipe-details.php?id=<?php echo (int)$recipe['id']; ?>">
                                <?php echo htmlspecialchars($recipe['title']); ?>
                            </a>
                        </h3>
                        
                        <p style="color: #ff4444;">❤️ <?php echo $recipe['like_count']; ?> Likes</p>
                        
                        <div style="display: flex; gap: 8px;">
                            <a href="edit-recipe.php?id=<?php echo (int)$recipe['id']; ?>" style="flex: 1; padding: 8px; background: #4CAF50; color: white; text-align: center; border-radius: 4px; text-decoration: none;">
                                Edit
                            </a>
                            <a href="delete-recipe.php?id=<?php echo (int)$recipe['id']; ?>" onclick="return confirm('Delete this recipe?');" style="flex: 1; padding: 8px; background: #dc3545; color: white; text-align: center; border-radius: 4px; text-decoration: none;">
                                Delete
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>