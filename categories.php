<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$slug = trim($_GET['slug'] ?? '');
$category = null;
$recipes = [];

if (!empty($slug)) {
    // Fetch category
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE slug = ?');
    $stmt->execute([$slug]);
    $category = $stmt->fetch();

    if ($category) {
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
    }
}
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<main>
    <?php if (empty($slug)): ?>
        <!-- All Categories View -->
        <section style="padding: 60px 20px; background: #f5f5f5;">
            <div class="container" style="max-width: 1200px; margin: 0 auto;">
                <h1 style="font-size: 2.5rem; margin-bottom: 40px; text-align: center;">📚 All Categories</h1>

                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px;">
                    <?php
                    try {
                        $stmt = $pdo->prepare('SELECT id, name, slug, icon FROM categories ORDER BY name');
                        $stmt->execute();
                        $allCategories = $stmt->fetchAll();

                        foreach ($allCategories as $cat):
                            $countStmt = $pdo->prepare('SELECT COUNT(*) as count FROM recipes WHERE category_id = ?');
                            $countStmt->execute([$cat['id']]);
                            $count = $countStmt->fetch()['count'];
                    ?>
                        <a href="categories.php?slug=<?php echo htmlspecialchars($cat['slug']); ?>"
                            style="display: block; background: linear-gradient(135deg, #4CAF50, #45a049); color: white; padding: 30px; border-radius: 8px; text-align: center; text-decoration: none; transition: transform 0.3s; cursor: pointer;"
                            onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 16px rgba(0,0,0,0.2)'"
                            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                            <div style="font-size: 2.5rem; margin-bottom: 12px;"><?php echo $cat['icon']; ?></div>
                            <div style="font-weight: bold; margin-bottom: 8px;"><?php echo htmlspecialchars($cat['name']); ?></div>
                            <div style="font-size: 0.9rem; opacity: 0.9;"><?php echo $count; ?> Recipe<?php echo $count !== 1 ? 's' : ''; ?></div>
                        </a>
                    <?php endforeach; ?>
                    <?php } catch (PDOException $e) { ?>
                        <div style="color: red; grid-column: 1 / -1;">❌ Error: Unable to load categories</div>
                    <?php } ?>
                </div>
            </div>
        </section>

    <?php elseif (!empty($category)): ?>
        <!-- Category Recipes View -->
        <section style="padding: 60px 20px; background: #f5f5f5;">
            <div class="container" style="max-width: 1200px; margin: 0 auto;">
                <div style="margin-bottom: 40px;">
                    <a href="categories.php" style="color: #4CAF50; text-decoration: none; font-weight: 600;">← Back to Categories</a>
                    <h1 style="font-size: 2.5rem; margin: 16px 0 8px 0;">
                        <?php echo $category['icon']; ?> <?php echo htmlspecialchars($category['name']); ?>
                    </h1>
                    <p style="color: #666; margin: 0;"><?php echo count($recipes); ?> Recipe<?php echo count($recipes) !== 1 ? 's' : ''; ?></p>
                </div>

                <?php if (!empty($recipes)): ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                        <?php foreach ($recipes as $recipe): ?>
                            <article style="background: white; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: all 0.3s ease;"
                                onmouseover="this.style.boxShadow='0 8px 16px rgba(0,0,0,0.15)'; this.style.transform='translateY(-5px)'"
                                onmouseout="this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)'; this.style.transform='translateY(0)'">

                                <?php if (!empty($recipe['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($recipe['image']); ?>" style="width: 100%; height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <div style="width: 100%; height: 200px; background: #ddd; display: flex; align-items: center; justify-content: center; font-size: 3rem;">
                                        🍳
                                    </div>
                                <?php endif; ?>

                                <div style="padding: 16px;">
                                    <h3 style="margin: 0 0 8px 0;">
                                        <a href="recipe-details.php?id=<?php echo $recipe['id']; ?>" style="text-decoration: none; color: #333;">
                                            <?php echo htmlspecialchars($recipe['title']); ?>
                                        </a>
                                    </h3>

                                    <p style="color: #666; font-size: 0.9rem; margin: 0 0 8px 0;">
                                        By <strong><?php echo htmlspecialchars($recipe['author_name']); ?></strong>
                                    </p>

                                    <?php if (!empty($recipe['description'])): ?>
                                        <p style="color: #555; font-size: 0.9rem; margin: 0 0 12px 0; line-height: 1.4;">
                                            <?php echo htmlspecialchars(substr($recipe['description'], 0, 100)); ?>...
                                        </p>
                                    <?php endif; ?>

                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <p style="color: #ff4444; margin: 0; font-weight: bold;">
                                            ❤️ <?php echo $recipe['like_count']; ?> Like<?php echo $recipe['like_count'] !== 1 ? 's' : ''; ?>
                                        </p>
                                        <a href="recipe-details.php?id=<?php echo $recipe['id']; ?>" style="background: #4CAF50; color: white; padding: 8px 16px; border-radius: 4px; text-decoration: none; font-size: 0.9rem; font-weight: 600;">
                                            View →
                                        </a>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 8px;">
                        <p style="color: #666; font-size: 1.1rem; margin-bottom: 20px;">No recipes in this category yet</p>
                        <a href="recipes.php" style="display: inline-block; background: #4CAF50; color: white; padding: 12px 32px; border-radius: 4px; text-decoration: none; font-weight: 600;">
                            View Other Recipes
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </section>

    <?php else: ?>
        <!-- Category Not Found -->
        <section style="padding: 60px 20px; text-align: center;">
            <h1 style="color: #d32f2f;">❌ Category Not Found</h1>
            <p style="color: #666; margin: 16px 0;">The requested category does not exist.</p>
            <a href="categories.php" style="display: inline-block; background: #4CAF50; color: white; padding: 12px 32px; border-radius: 4px; text-decoration: none; font-weight: 600; margin-top: 20px;">
                ← Back to All Categories
            </a>
        </section>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>