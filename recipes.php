<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

require __DIR__ . '/config/db.php';
session_start();

$search = $_GET['search'] ?? '';
$recipes = [];

if ($search) {
    $stmt = $pdo->prepare('SELECT r.*, u.name as author_name, u.avatar as author_avatar FROM recipes r JOIN users u ON r.user_id = u.id WHERE r.title LIKE ? OR r.description LIKE ? OR r.ingredients LIKE ? OR r.instructions LIKE ? ORDER BY r.created_at DESC');
    $stmt->execute(['%' . $search . '%', '%' . $search . '%', '%' . $search . '%', '%' . $search . '%']);
} else {
    $stmt = $pdo->prepare('SELECT r.*, u.name as author_name, u.avatar as author_avatar FROM recipes r JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC');
    $stmt->execute();
}

$recipes = $stmt->fetchAll();

foreach ($recipes as &$r) {
    $s = $pdo->prepare('SELECT COUNT(*) as cnt FROM likes WHERE recipe_id = ?');
    $s->execute([$r['id']]);
    $r['like_count'] = $s->fetch()['cnt'] ?? 0;
}
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<main>
    <section class="container" style="max-width: 1000px; margin: 40px auto;">
        <h1>🍳 Recipes</h1>

        <?php if (!empty($_SESSION['user_id'])): ?>
            <div style="margin-bottom: 24px;">
                <a href="add-recipe.php" style="display: inline-block; background: #4CAF50; color: white; padding: 12px 24px; border-radius: 4px; text-decoration: none; font-weight: 600;">
                    ➕ Add New Recipe
                </a>
            </div>
        <?php endif; ?>

        <form method="get" style="margin-bottom: 32px;">
            <div style="display: flex; gap: 8px;">
                <input type="text" name="search" placeholder="Search recipes..." value="<?php echo htmlspecialchars($search); ?>" style="flex: 1; padding: 12px; border: 1px solid #ddd; border-radius: 4px;">
                <button type="submit" style="background: #4CAF50; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">🔍 Search</button>
                <?php if ($search): ?>
                    <a href="recipes.php" style="background: #999; color: white; padding: 12px 24px; border-radius: 4px; text-decoration: none; font-weight: 600;">✕ Clear</a>
                <?php endif; ?>
            </div>
        </form>

        <?php if (empty($recipes)): ?>
            <div style="background: #f5f5f5; padding: 60px 20px; text-align: center; border-radius: 8px;">
                <p style="font-size: 1.3rem; color: #666;">📝 No recipes found</p>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px;">
                <?php
                foreach ($recipes as $recipe) {
                    echo '<div style="background: white; border: 1px solid #eee; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">';

                    echo '<div style="height: 200px; background: #f0f0f0; overflow: hidden;">';
                    if ($recipe['image']) {
                        echo '<img src="' . htmlspecialchars($recipe['image']) . '" style="width: 100%; height: 100%; object-fit: cover;" alt="' . htmlspecialchars($recipe['title']) . '">';
                    } else {
                        echo '<div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 3rem;">🍳</div>';
                    }
                    echo '</div>';

                    echo '<div style="padding: 16px;">';
                    echo '<h3 style="margin: 0 0 12px 0;"><a href="recipe-details.php?id=' . $recipe['id'] . '" style="color: #333; text-decoration: none;">' . htmlspecialchars($recipe['title']) . '</a></h3>';

                    echo '<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #f0f0f0;">';
                    if ($recipe['author_avatar']) {
                        echo '<img src="' . htmlspecialchars($recipe['author_avatar']) . '" style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover;">';
                    } else {
                        echo '<div style="width: 36px; height: 36px; border-radius: 50%; background: #ddd; display: flex; align-items: center; justify-content: center;">👤</div>';
                    }
                    echo '<div><a href="profile.php?user_id=' . $recipe['user_id'] . '" style="color: #4CAF50; text-decoration: none; font-weight: 600; display: block;">' . htmlspecialchars($recipe['author_name']) . '</a><span style="color: #999; font-size: 0.8rem;">' . date('M d, Y', strtotime($recipe['created_at'])) . '</span></div>';
                    echo '</div>';

                    if ($recipe['description']) {
                        echo '<p style="color: #666; font-size: 0.9rem; margin: 0 0 12px 0;">' . htmlspecialchars(substr($recipe['description'], 0, 70)) . '...</p>';
                    }

                    echo '<div style="display: flex; justify-content: space-between;">';
                    echo '<span style="color: #ff4444; font-weight: 600;">❤️ ' . $recipe['like_count'] . '</span>';
                    echo '<a href="recipe-details.php?id=' . $recipe['id'] . '" style="background: #4CAF50; color: white; padding: 8px 16px; border-radius: 4px; text-decoration: none; font-size: 0.9rem;">View</a>';
                    echo '</div>';

                    echo '</div></div>';
                }
                ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>