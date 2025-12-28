<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/config/db.php';
session_start();

// Get chef user_id from URL
$chefUserId = $_GET['id'] ?? 0;
$chefUserId = (int)$chefUserId;

if (!$chefUserId) {
    echo "Chef not found!";
    exit;
}

try {
    // Fetch chef data
    $stmt = $pdo->prepare('
        SELECT c.*, u.name, u.avatar, u.email, u.points
        FROM chefs c
        JOIN users u ON c.user_id = u.id
        WHERE c.user_id = ?
    ');
    $stmt->execute([$chefUserId]);
    $chef = $stmt->fetch();

    if (!$chef) {
        echo "Chef not found!";
        exit;
    }

    // Fetch all recipes by this chef
    $stmt = $pdo->prepare('
        SELECT r.*, COUNT(l.id) as like_count, AVG(rv.rating) as avg_rating
        FROM recipes r
        LEFT JOIN likes l ON r.id = l.recipe_id
        LEFT JOIN reviews rv ON r.id = rv.recipe_id
        WHERE r.user_id = ?
        GROUP BY r.id
        ORDER BY r.created_at DESC
    ');
    $stmt->execute([$chefUserId]);
    $recipes = $stmt->fetchAll();

    // Check if current user follows this chef
    $isFollowing = false;
    if (!empty($_SESSION['user_id'])) {
        $stmt = $pdo->prepare('
            SELECT COUNT(*) as count FROM chef_followers
            WHERE chef_id = ? AND user_id = ?
        ');
        $stmt->execute([$chefUserId, $_SESSION['user_id']]);
        $isFollowing = $stmt->fetch()['count'] > 0;
    }

} catch (PDOException $e) {
    echo "Error: " . htmlspecialchars($e->getMessage());
    exit;
}
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<main>
    <section class="container" style="max-width: 1000px; margin: 40px auto;">
        
        <!-- Chef Header -->
        <div style="
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            padding: 40px;
            border-radius: 10px;
            margin-bottom: 40px;
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 30px;
            align-items: center;
        ">
            <!-- Avatar -->
            <div style="text-align: center;">
                <?php if ($chef['avatar']): ?>
                    <img src="<?php echo htmlspecialchars($chef['avatar']); ?>" 
                        style="width: 180px; height: 180px; border-radius: 50%; object-fit: cover; border: 5px solid white;">
                <?php else: ?>
                    <div style="width: 180px; height: 180px; border-radius: 50%; background: rgba(255,255,255,0.2); margin: 0 auto; display: flex; align-items: center; justify-content: center; font-size: 5rem;">
                        👨‍🍳
                    </div>
                <?php endif; ?>
            </div>

            <!-- Info -->
            <div>
                <h1 style="margin: 0 0 8px 0; font-size: 2.5rem;">
                    <?php echo htmlspecialchars($chef['name']); ?>
                    <span style="color: #FFD700;">⭐</span>
                </h1>
                
                <p style="margin: 0 0 16px 0; font-size: 1.1rem; opacity: 0.95;">
                    👨‍🍳 Chef with <strong><?php echo $chef['experience_years']; ?></strong> years of experience
                </p>

                <p style="margin: 0 0 16px 0; line-height: 1.6; font-size: 1rem;">
                    <?php echo htmlspecialchars($chef['bio']); ?>
                </p>

                <p style="margin: 0 0 16px 0; font-size: 0.95rem;">
                    <strong>Specialties:</strong> <?php echo htmlspecialchars($chef['specialties']); ?>
                </p>

                <!-- Stats -->
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 20px;">
                    <div>
                        <div style="font-size: 2rem; font-weight: bold;"><?php echo $chef['followers']; ?></div>
                        <div style="font-size: 0.9rem; opacity: 0.9;">Followers</div>
                    </div>
                    <div>
                        <div style="font-size: 2rem; font-weight: bold;"><?php echo $chef['recipes_count']; ?></div>
                        <div style="font-size: 0.9rem; opacity: 0.9;">Recipes</div>
                    </div>
                    <div>
                        <div style="font-size: 2rem; font-weight: bold;">⭐ <?php echo $chef['points']; ?></div>
                        <div style="font-size: 0.9rem; opacity: 0.9;">Points</div>
                    </div>
                </div>

                <!-- Follow Button -->
                <?php if (!empty($_SESSION['user_id']) && $_SESSION['user_id'] !== $chefUserId): ?>
                    <button onclick="toggleFollowChef(<?php echo $chefUserId; ?>)" style="
                        background: <?php echo $isFollowing ? '#ff9800' : 'white'; ?>;
                        color: <?php echo $isFollowing ? 'white' : '#4CAF50'; ?>;
                        padding: 12px 32px;
                        border: none;
                        border-radius: 4px;
                        font-size: 1rem;
                        font-weight: 600;
                        cursor: pointer;
                        transition: all 0.3s ease;
                    "
                        id="followBtn"
                        onmouseover="this.style.transform='scale(1.05)'"
                        onmouseout="this.style.transform='scale(1)'">
                        <?php echo $isFollowing ? '✓ Following' : '+ Follow Chef'; ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Contact Info -->
        <div style="background: #f5f5f5; padding: 20px; border-radius: 8px; margin-bottom: 40px;">
            <h3 style="margin-top: 0;">📧 Contact</h3>
            <p style="margin: 8px 0;">
                Email: <a href="mailto:<?php echo htmlspecialchars($chef['email']); ?>" style="color: #4CAF50; text-decoration: none;">
                    <?php echo htmlspecialchars($chef['email']); ?>
                </a>
            </p>
        </div>

        <!-- Recipes Section -->
        <div>
            <h2>🍳 Recipes by <?php echo htmlspecialchars($chef['name']); ?></h2>
            
            <?php if (empty($recipes)): ?>
                <div style="background: #f5f5f5; padding: 40px; text-align: center; border-radius: 8px;">
                    <p style="color: #666; font-size: 1.1rem;">No recipes yet</p>
                </div>
            <?php else: ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 24px; margin-bottom: 40px;">
                    <?php foreach ($recipes as $recipe): ?>
                        <article style="
                            background: white;
                            border: 1px solid #ddd;
                            border-radius: 8px;
                            overflow: hidden;
                            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                            transition: all 0.3s ease;
                        "
                            onmouseover="this.style.boxShadow='0 8px 16px rgba(0,0,0,0.15)'; this.style.transform='translateY(-5px)'"
                            onmouseout="this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)'; this.style.transform='translateY(0)'">

                            <!-- Recipe Image -->
                            <div style="
                                background: linear-gradient(135deg, #f5f5f5, #e0e0e0);
                                height: 180px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                overflow: hidden;
                                font-size: 3rem;
                            ">
                                <?php if ($recipe['image']): ?>
                                    <img src="<?php echo htmlspecialchars($recipe['image']); ?>" 
                                        style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    🍳
                                <?php endif; ?>
                            </div>

                            <!-- Recipe Info -->
                            <div style="padding: 16px;">
                                <h3 style="margin: 0 0 8px 0; line-height: 1.3;">
                                    <a href="recipe-details.php?id=<?php echo $recipe['id']; ?>" style="color: #333; text-decoration: none;">
                                        <?php echo htmlspecialchars(substr($recipe['title'], 0, 50)); ?>
                                    </a>
                                </h3>

                                <p style="color: #666; font-size: 0.9rem; margin: 0 0 12px 0; line-height: 1.4;">
                                    <?php echo htmlspecialchars(substr($recipe['description'], 0, 80)); ?>...
                                </p>

                                <!-- Rating -->
                                <?php 
                                    $rating = $recipe['avg_rating'] ? round($recipe['avg_rating'], 1) : 0;
                                    $stars = '';
                                    for ($i = 0; $i < 5; $i++) {
                                        $stars .= $i < round($rating) ? '⭐' : '☆';
                                    }
                                ?>
                                <p style="color: #666; font-size: 0.85rem; margin: 0 0 8px 0;">
                                    <?php echo $stars; ?> <?php echo $rating; ?>/5
                                </p>

                                <!-- Engagement -->
                                <p style="color: #666; font-size: 0.85rem; margin: 0;">
                                    ❤️ <?php echo $recipe['like_count']; ?> Likes
                                </p>

                                <!-- View Button -->
                                <a href="recipe-details.php?id=<?php echo $recipe['id']; ?>" style="
                                    display: block;
                                    margin-top: 12px;
                                    background: #4CAF50;
                                    color: white;
                                    padding: 10px;
                                    border-radius: 4px;
                                    text-align: center;
                                    text-decoration: none;
                                    font-size: 0.9rem;
                                    transition: all 0.3s ease;
                                "
                                    onmouseover="this.style.background='#45a049'"
                                    onmouseout="this.style.background='#4CAF50'">
                                    View Recipe →
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Back Button -->
        <div style="margin-bottom: 40px;">
            <a href="chefs.php" style="
                display: inline-block;
                background: #f0f0f0;
                color: #333;
                padding: 12px 24px;
                border-radius: 4px;
                text-decoration: none;
                transition: all 0.3s ease;
            "
                onmouseover="this.style.background='#e0e0e0'"
                onmouseout="this.style.background='#f0f0f0'">
                ← Back to Chefs
            </a>
        </div>
    </section>
</main>

<script>
    function toggleFollowChef(chefId) {
        <?php if (empty($_SESSION['user_id'])): ?>
            window.location.href = 'login.php';
            return;
        <?php endif; ?>

        const btn = document.getElementById('followBtn');
        const isFollowing = btn.textContent.includes('Following');

        fetch('api/follow-chef.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                chef_id: chefId,
                action: isFollowing ? 'unfollow' : 'follow'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.action === 'follow') {
                    btn.textContent = '✓ Following';
                    btn.style.background = '#ff9800';
                    btn.style.color = 'white';
                } else {
                    btn.textContent = '+ Follow Chef';
                    btn.style.background = 'white';
                    btn.style.color = '#4CAF50';
                }
            } else {
                alert('❌ ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ An error occurred');
        });
    }
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
