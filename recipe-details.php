<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// URL থেকে id পড়ি
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $recipe = null;
} else {
    try {
        $stmt = $pdo->prepare(
            'SELECT r.*, u.name AS author_name
             FROM recipes r
             JOIN users u ON r.user_id = u.id
             WHERE r.id =?;'
        );
        $stmt->execute([$id]);
        $recipe = $stmt->fetch();
    } catch (PDOException $e) {
        die('DB ERROR: ' . $e->getMessage());
    }
}
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<main>
    <section class="container" style="max-width: 900px; margin: 40px auto;">

        <?php if (!$recipe): ?>
            <h1>Recipe not found</h1>
            <p>দুঃখিত, এই ID-এর কোনো recipe পাওয়া যায়নি।</p>
            <a href="recipes.php">Back to all recipes</a>
        <?php else: ?>
            <h1><?php echo htmlspecialchars($recipe['title']); ?></h1>

            <p style="color: #666; font-size: 0.9rem; margin-bottom: 16px;">
                By <?php echo htmlspecialchars($recipe['author_name']); ?>
                · <?php echo htmlspecialchars($recipe['created_at']); ?>
            </p>

            <?php if (!empty($_SESSION['user_id']) && $_SESSION['user_id'] == $recipe['user_id']): ?>
                <p style="margin-bottom: 16px;">
                    <a href="edit-recipe.php?id=<?php echo (int)$recipe['id']; ?>">Edit</a>
                    |
                    <a href="delete-recipe.php?id=<?php echo (int)$recipe['id']; ?>">Delete</a>
                </p>
            <?php endif; ?>

            <?php if (!empty($recipe['description'])): ?>
                <h3>Description</h3>
                <p><?php echo nl2br(htmlspecialchars($recipe['description'])); ?></p>
            <?php endif; ?>

            <?php if (!empty($recipe['ingredients'])): ?>
                <h3>Ingredients</h3>
                <pre style="white-space: pre-wrap; background:#f7f7f7; padding: 10px; border-radius: 6px;">
<?php echo htmlspecialchars($recipe['ingredients']); ?>
                </pre>
            <?php endif; ?>

            <?php if (!empty($recipe['instructions'])): ?>
                <h3>Instructions</h3>
                <pre style="white-space: pre-wrap; background:#f7f7f7; padding: 10px; border-radius: 6px;">
<?php echo htmlspecialchars($recipe['instructions']); ?>
                </pre>
            <?php endif; ?>

            <p style="margin-top: 20px;">
                <a href="recipes.php">← Back to all recipes</a>
            </p>
        <?php endif; ?>

    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>