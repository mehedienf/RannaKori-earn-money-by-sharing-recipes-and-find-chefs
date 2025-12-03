<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// সব recipe + author নামসহ আনব
try {
    $stmt = $pdo->query(
        'SELECT r.*, u.name AS author_name
         FROM recipes r
         JOIN users u ON r.user_id = u.id
         ORDER BY r.created_at DESC'
    );
    $recipes = $stmt->fetchAll();
} catch (PDOException $e) {
    die('DB ERROR: ' . $e->getMessage());
}
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<main>
    <section class="container" style="max-width: 900px; margin: 40px auto;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1>All Recipes</h1>

            <?php if (!empty($_SESSION['user_id'])): ?>
                <a href="add-recipe.php" class="btn">Add New Recipe</a>
            <?php else: ?>
                <a href="login.php" class="btn">Login to add recipe</a>
            <?php endif; ?>
        </div>

        <?php if (empty($recipes)): ?>
            <p style="margin-top: 20px;">এখনো কোনো recipe নেই। প্রথম recipe টি তুমি create করতে পারো।</p>
        <?php else: ?>
            <div class="recipe-list" style="margin-top: 24px;">
                <?php foreach ($recipes as $recipe): ?>
                    <article class="recipe-card" style="border: 1px solid #ddd; padding: 16px; margin-bottom: 16px; border-radius: 8px;">
                        <h2>
                            <?php echo htmlspecialchars($recipe['title']); ?>
                        </h2>

                        <p style="color: #666; font-size: 0.9rem;">
                            By <?php echo htmlspecialchars($recipe['author_name']); ?>
                            · <?php echo htmlspecialchars($recipe['created_at']); ?>
                        </p>

                        <?php if (!empty($recipe['description'])): ?>
                            <p>
                                <?php echo nl2br(htmlspecialchars($recipe['description'])); ?>
                            </p>
                        <?php endif; ?>

                        <a href="recipe-details.php?id=<?php echo (int)$recipe['id']; ?>">
                            View Details
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>