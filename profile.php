<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/config/db.php';
session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user_id from URL or use current user
$profileUserId = $_GET['user_id'] ?? $_SESSION['user_id'];
$profileUserId = (int)$profileUserId;

// Fetch user data
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$profileUserId]);
$user = $stmt->fetch();

if (!$user) {
    echo "User not found!";
    exit;
}

// Check if viewing own profile
$isOwnProfile = ($profileUserId === $_SESSION['user_id']);

// Check if user is following this profile user
$isFollowing = false;
if (!$isOwnProfile) {
    $stmt = $pdo->prepare('SELECT id FROM chef_followers WHERE user_id = ? AND chef_id = (SELECT id FROM chefs WHERE user_id = ?)');
    $stmt->execute([$_SESSION['user_id'], $profileUserId]);
    $isFollowing = (bool)$stmt->fetch();
}

// Fetch user's recipes
$stmt = $pdo->prepare('SELECT r.*, COUNT(l.id) as like_count FROM recipes r LEFT JOIN likes l ON r.id = l.recipe_id WHERE r.user_id = ? GROUP BY r.id ORDER BY r.created_at DESC');
$stmt->execute([$profileUserId]);
$recipes = $stmt->fetchAll();

// Fetch following list (only if viewing own profile)
$following = [];
if ($isOwnProfile) {
    $stmt = $pdo->prepare('
        SELECT DISTINCT u.id, u.name, u.avatar FROM users u 
        INNER JOIN chefs c ON u.id = c.user_id 
        INNER JOIN chef_followers cf ON c.id = cf.chef_id 
        WHERE cf.user_id = ? 
        ORDER BY u.name
    ');
    $stmt->execute([$_SESSION['user_id']]);
    $following = $stmt->fetchAll();
}
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<main>
    <section class="container" style="max-width: 900px; margin: 40px auto;">

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
            <div>
                <img src="<?php echo htmlspecialchars(!empty($user['avatar']) ? $user['avatar'] : 'https://via.placeholder.com/120?text=' . urlencode($user['name'])); ?>" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 3px solid #ddd; margin-bottom: 16px;" alt="<?php echo htmlspecialchars($user['name']); ?>">

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
                    <?php if ($isOwnProfile): ?>
                        <a href="edit-profile.php" style="background: #4CAF50; color: white; padding: 12px 24px; border: none; border-radius: 4px; text-decoration: none; cursor: pointer;">
                            ✏️ Edit Profile
                        </a>
                        <a href="logout.php" style="background: #f44336; color: white; padding: 12px 24px; border: none; border-radius: 4px; text-decoration: none; cursor: pointer;">
                            🚪 Logout
                        </a>
                    <?php else: ?>
                        <button id="followBtn" onclick="toggleFollow(<?php echo $profileUserId; ?>)" style="background: <?php echo $isFollowing ? '#ff6b6b' : '#4CAF50'; ?>; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">
                            <?php echo $isFollowing ? '❤️ Following' : '🤍 Follow'; ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <hr style="margin: 32px 0;">

        <?php if ($isOwnProfile && !empty($following)): ?>
            <h2>Following (<?php echo count($following); ?>)</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 16px; margin-bottom: 32px;">
                <?php foreach ($following as $chef): ?>
                    <div style="text-align: center; padding: 12px; border: 1px solid #ddd; border-radius: 8px;">
                        <a href="profile.php?user_id=<?php echo $chef['id']; ?>" style="text-decoration: none;">
                            <img src="<?php echo htmlspecialchars(!empty($chef['avatar']) ? $chef['avatar'] : 'https://via.placeholder.com/80?text=' . urlencode($chef['name'])); ?>" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; margin-bottom: 8px; display: block; margin-left: auto; margin-right: auto;">
                            <p style="margin: 8px 0; font-weight: 600;"><?php echo htmlspecialchars($chef['name']); ?></p>
                        </a>
                        <button onclick="unfollowChef(<?php echo $chef['id']; ?>)" style="background: #f44336; color: white; padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9rem;">
                            ❌ Unfollow
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>

            <hr style="margin: 32px 0;">
        <?php endif; ?>

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

                        <?php if ($isOwnProfile): ?>
                            <div style="display: flex; gap: 8px; margin-top: 12px;">
                                <a href="edit-recipe.php?id=<?php echo (int)$recipe['id']; ?>" style="flex: 1; padding: 8px; background: #4CAF50; color: white; text-align: center; border-radius: 4px; text-decoration: none; font-weight: 600;">
                                    ✏️ Edit
                                </a>
                                <a href="delete-recipe.php?id=<?php echo (int)$recipe['id']; ?>" onclick="return confirm('Delete this recipe?');" style="flex: 1; padding: 8px; background: #dc3545; color: white; text-align: center; border-radius: 4px; text-decoration: none; font-weight: 600;">
                                    🗑️ Delete
                                </a>
                            </div>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<script>
function toggleFollow(userId) {
    fetch('api/follow-chef.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ chef_id: userId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const btn = document.getElementById('followBtn');
            if (data.action === 'follow') {
                btn.innerHTML = '❤️ Following';
                btn.style.background = '#ff6b6b';
            } else {
                btn.innerHTML = '🤍 Follow';
                btn.style.background = '#4CAF50';
            }
        } else {
            alert('Error: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(err => alert('Error: ' + err));
}

function unfollowChef(chefUserId) {
    if (confirm('Unfollow this user?')) {
        fetch('api/follow-chef.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ chef_id: chefUserId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(err => alert('Error: ' + err));
    }
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>