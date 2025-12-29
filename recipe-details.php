<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/config/db.php';
session_start();

$id = (int)($_GET['id'] ?? 0);
$reviewError = '';
$reviewSuccess = isset($_GET['success']) ? "✅ Review submitted! You earned +10 points." : '';

// Fetch all recipes for sidebar or other uses

$stmt = $pdo->prepare('SELECT r.*, u.name as author_name, u.avatar as author_avatar FROM recipes r JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC');
$stmt->execute();
$recipes = $stmt->fetchAll();

// Like handling
$isLiked = false;
$likeCount = 0;

if (!empty($_SESSION['user_id'])) {
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM likes WHERE recipe_id = ? AND user_id = ?');
    $stmt->execute([$id, $_SESSION['user_id']]);
    $isLiked = (bool)$stmt->fetch()['count'];
}

$stmt = $pdo->prepare('SELECT COUNT(*) as count FROM likes WHERE recipe_id = ?');
$stmt->execute([$id]);
$likeCount = $stmt->fetch()['count'];

// Like/Unlike toggle
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'toggle_like') {
    if (empty($_SESSION['user_id'])) {
        echo "Login required";
        exit;
    }

    if ($isLiked) {
        // Unlike
        $stmt = $pdo->prepare('DELETE FROM likes WHERE recipe_id = ? AND user_id = ?');
        $stmt->execute([$id, $_SESSION['user_id']]);
    } else {
        // Like
        $stmt = $pdo->prepare('INSERT INTO likes (recipe_id, user_id) VALUES (?, ?)');
        $stmt->execute([$id, $_SESSION['user_id']]);

        // +1 point
        $pdo->query("UPDATE users SET points = points + 1 WHERE id = " . $_SESSION['user_id']);
        $_SESSION['user_points'] = ($_SESSION['user_points'] ?? 0) + 1;
    }

    header('Location: recipe-details.php?id=' . $id);
    exit;
}

// ============ REVIEW SUBMIT ============
if ($_POST) {
    $rating = (int)($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    $user_id = (int)($_SESSION['user_id'] ?? 0);

    if ($rating > 0 && $user_id > 0 && $id > 0) {
        try {
            $stmt = $pdo->prepare('INSERT INTO reviews (recipe_id, user_id, rating, comment) VALUES (?, ?, ?, ?)');
            $ok = $stmt->execute([$id, $user_id, $rating, $comment]);

            if ($ok) {
                // Points update
                $pdo->query("UPDATE users SET points = points + 10 WHERE id = $user_id");
                $_SESSION['user_points'] = ($_SESSION['user_points'] ?? 0) + 10;

                header('Location: recipe-details.php?id=' . $id . '&success=1');
                exit;
            }
        } catch (PDOException $e) {
            $reviewError = "Database error: " . $e->getMessage();
        }
    } else {
        $reviewError = "Invalid data!";
    }
}

// ============ FETCH RECIPE ============
try {
    $stmt = $pdo->prepare('SELECT r.*, u.name AS author_name, u.avatar AS author_avatar FROM recipes r JOIN users u ON r.user_id = u.id WHERE r.id = ?');
    $stmt->execute([$id]);
    $recipe = $stmt->fetch();

    if ($recipe) {
        $stmt = $pdo->prepare('SELECT AVG(rating) AS avg_rating, COUNT(*) AS total_reviews FROM reviews WHERE recipe_id = ?');
        $stmt->execute([$id]);
        $ratingData = $stmt->fetch();
        $avgRating = $ratingData['avg_rating'] ? round($ratingData['avg_rating'], 1) : 0;
        $totalReviews = $ratingData['total_reviews'];

        $stmt = $pdo->prepare('SELECT r.*, u.name AS reviewer_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.recipe_id = ? ORDER BY r.created_at DESC');
        $stmt->execute([$id]);
        $reviews = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    die('ERROR: ' . $e->getMessage());
}
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<main>
    <section style="max-width: 900px; margin: 40px auto; font-family: Arial;">

        <?php if (!$recipe): ?>
            <h1>Recipe Not Found</h1>
            <a href="recipes.php">Back to Recipes</a>
        <?php else: ?>
            <?php if ($_SESSION['user_id'] === $recipe['user_id']): ?>
                <div>
                    <button onclick="window.location.href='edit-recipe.php?id=<?php echo $id; ?>'">Edit</button>
                    <button onclick="window.location.href='delete-recipe.php?id=<?php echo $id; ?>'">Delete</button>
                </div>
            <?php endif; ?>

            <h1><?php echo htmlspecialchars($recipe['title']); ?></h1>
            <div style="margin-bottom: 16px;">
                <div>
                    <p style="color: #666; margin-bottom: 16px;">
                        <img src="<?php echo htmlspecialchars($recipe['author_avatar'] ?? ''); ?>" alt="<?php echo htmlspecialchars($recipe['author_name']); ?>" style="width: 40px; height: 40px; border-radius: 50%; vertical-align: middle; margin-right: 8px;">
                        By <a href="profile.php?user_id=<?php echo $recipe['user_id']; ?>"><?php echo htmlspecialchars($recipe['author_name']); ?></a>
                        · <?php echo htmlspecialchars($recipe['created_at']); ?>
                    </p>
                </div>

                <!-- Like Button -->
                <form method="post" style="display: inline;">
                    <button type="submit" name="action" value="toggle_like"
                        style="background: <?php echo $isLiked ? '#ff4444' : '#ddd'; ?>; 
                        color: <?php echo $isLiked ? 'white' : 'black'; ?>; 
                        padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer;">
                        <?php echo $isLiked ? '❤️ Liked' : '🤍 Like'; ?> (<?php echo $likeCount; ?>)
                    </button>
                </form>

                <!-- Average Rating -->
                <span style="background: #ffcc00; color: white; padding: 6px 12px; border-radius: 4px; font-weight: bold;">
                    ⭐ <?php echo $avgRating; ?> (<?php echo $totalReviews; ?> Reviews)
                </span>

                <br><br>

                <?php if ($recipe['image']): ?>
                    <img src="<?php echo $recipe['image']; ?>" style="max-width: 100%; height: auto; margin: 20px 0;">
                <?php endif; ?>

                <h3>Description</h3>
                <p><?php echo nl2br(htmlspecialchars($recipe['description'])); ?></p>

                <h3>Ingredients</h3>
                <pre style="background: #f5f5f5; padding: 10px;"><?php echo htmlspecialchars($recipe['ingredients']); ?></pre>

                <h3>Instructions</h3>
                <pre style="background: #f5f5f5; padding: 10px;"><?php echo htmlspecialchars($recipe['instructions']); ?></pre>

                <hr>

                <h2>Reviews</h2>

                <!-- Success message from redirect -->
                <!-- <?php if (isset($_GET['success'])): ?>
            <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 6px; margin-bottom: 16px;">
                ✅ Review submitted successfully! You earned +10 points.
            </div>
        <?php endif; ?> -->

                <!-- Error Message -->
                <?php if ($reviewError): ?>
                    <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 16px;">
                        ❌ <?php echo htmlspecialchars($reviewError); ?> 
                    </div>
                <?php endif; ?>

                <!-- Review Form -->
                <?php if (!empty($_SESSION['user_id']) && $_SESSION['user_id'] != $recipe['user_id']): ?>
                    <div style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 32px;">
                        <h3>Write a Review & Earn +10 Points</h3>
                        <form method="post" action="">
                            <div style="margin-bottom: 16px;">
                                <label style="display: block; margin-bottom: 8px; font-weight: bold;">Rating:</label>
                                <select name="rating" required style="padding: 8px; font-size: 1rem; border: 1px solid #ddd; border-radius: 4px;">
                                    <option value="">-- Select Rating --</option>
                                    <option value="5">⭐⭐⭐⭐⭐ (5 - Excellent)</option>
                                    <option value="4">⭐⭐⭐⭐ (4 - Good)</option>
                                    <option value="3">⭐⭐⭐ (3 - Average)</option>
                                    <option value="2">⭐⭐ (2 - Poor)</option>
                                    <option value="1">⭐ (1 - Very Poor)</option>
                                </select>
                            </div>

                            <div style="margin-bottom: 16px;">
                                <label style="display: block; margin-bottom: 8px; font-weight: bold;">Comment (optional):</label>
                                <textarea name="comment" rows="4" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"></textarea>
                            </div>

                            <button type="submit" style="background: #4CAF50; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem;">
                                Submit Review
                            </button>
                        </form>
                    </div>

                <?php elseif (empty($_SESSION['user_id'])): ?>
                    <p style="background: #fff3cd; padding: 12px; border-radius: 6px; margin-bottom: 32px;">
                        <a href="login.php">Login</a> to write a review and earn +10 points.
                    </p>

                <?php endif; ?>

                <h3>All Reviews:</h3>
                <?php if (empty($reviews)): ?>
                    <p>No reviews yet</p>
                <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                        <div style="border-bottom: 1px solid #ddd; padding: 10px 0;">
                            <strong><?php echo htmlspecialchars($review['reviewer_name']); ?></strong>
                            <span style="color: #ffd700;"><?php echo str_repeat('⭐', $review['rating']); ?></span>
                            <?php if ($review['comment']): ?>
                                <p><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                            <?php endif; ?>
                            <small style="color: #999;"><?php echo $review['created_at']; ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <p><a href="recipes.php">← Back</a></p>

            <?php endif; ?>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>