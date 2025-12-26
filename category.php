<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/config/db.php';
session_start();

$slug = trim($_GET['slug'] ?? '');

if (empty($slug)) {
    header('Location: recipes.php');
    exit;
}

// Fetch category
$stmt = $pdo->prepare('SELECT * FROM categories WHERE slug = ?');
$stmt->execute([$slug]);
$category = $stmt->fetch();

if (!$category) {
    die('Category not found');
}

// Fetch recipes in this category
$stmt = $pdo->prepare(
    'SELECT r.*, u.name AS author_name 
     FROM recipes r 
     JOIN users u ON r.user_id = u.id 
     WHERE r.category_id = ? 
     ORDER BY r.created_at DESC'
);
$stmt->execute([$category['id']]);
$recipes = $stmt->fetchAll();

// Get like counts
foreach ($recipes as &$recipe) {
    $stmtLikes = $pdo->prepare('SELECT COUNT(*) as count FROM likes WHERE recipe_id = ?');
    $stmtLikes->execute([$recipe['id']]);
    $likeResult = $stmtLikes->fetch();
    $recipe['like_count'] = $likeResult['count'] ?? 0;
}
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<main>
    <section class="container" style="max-width: 1000px; margin: 40px auto;">
        <h1><?php echo $category['icon']; ?> <?php echo htmlspecialchars($category['name']); ?> Recipes</h1>
        <p style="color: #666; margin-bottom: 32px;">
            Found <?php echo count($recipes); ?> recipe(s)
        </p>
        
        <?php if (empty($recipes)): ?>
            <div style="background: #f5f5f5; padding: 40px; text-align: center; border-radius: 8px;">
                <p style="font-size: 1.1rem; color: #666;">No recipes in this category yet.</p>
                <a href="recipes.php">Browse all recipes</a>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                <?php foreach ($recipes as $recipe): ?>
                    <article style="border: 1px solid #ddd; padding: 16px; border-radius: 8px;">
                        
                        <?php if (!empty($recipe['image'])): ?>
                            <img src="<?php echo htmlspecialchars($recipe['image']); ?>" 
                                 style="width: 100%; height: 200px; object-fit: cover; border-radius: 6px; margin-bottom: 12px;">
                        <?php else: ?>
                            <div style="width: 100%; height: 200px; background: #ddd; border-radius: 6px; margin-bottom: 12px; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                                🍳
                            </div>
                        <?php endif; ?>
                        
                        <h3 style="margin-top: 0;">
                            <a href="recipe-details.php?id=<?php echo (int)$recipe['id']; ?>" style="text-decoration: none; color: #333;">
                                <?php echo htmlspecialchars($recipe['title']); ?>
                            </a>
                        </h3>
                        
                        <p style="color: #666; font-size: 0.9rem;">
                            By <strong><?php echo htmlspecialchars($recipe['author_name']); ?></strong>
                        </p>
                        
                        <p style="color: #ff4444;">
                            ❤️ <?php echo (int)$recipe['like_count']; ?> Likes
                        </p>
                        
                        <a href="recipe-details.php?id=<?php echo (int)$recipe['id']; ?>" 
                           style="display: inline-block; background: #4CAF50; color: white; padding: 8px 16px; border-radius: 4px; text-decoration: none;">
                            View Recipe →
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>