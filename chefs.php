<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/config/db.php';
session_start();

// Fetch all approved chefs
try {
    $query = 'SELECT c.*, u.name, u.avatar 
              FROM chefs c
              JOIN users u ON c.user_id = u.id
              ORDER BY c.followers DESC, c.created_at DESC';

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $chefs = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Error: " . htmlspecialchars($e->getMessage());
    $chefs = [];
}
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<main>
    <section class="container" style="max-width: 1200px; margin: 40px auto;">
        <a href="become-chef.php">Become a Chef</a>
        <h1 style="text-align: center; margin-bottom: 10px;">👨‍🍳 Featured Chefs</h1>
        <p style="text-align: center; color: #666; margin-bottom: 40px;">
            Learn from our talented chefs and follow your favorites
        </p>

        <?php if (empty($chefs)): ?>
            <div style="background: #f5f5f5; padding: 40px; text-align: center; border-radius: 8px;">
                <p style="font-size: 1.2rem; color: #666;">No chefs yet</p>
                <p style="color: #999; margin: 16px 0;">Be the first to apply as a chef!</p>
                <?php if (!empty($_SESSION['user_id'])): ?>
                    <a href="become-chef.php" style="display: inline-block; background: #4CAF50; color: white; padding: 12px 32px; border-radius: 4px; text-decoration: none; margin-top: 16px;">
                        Apply as Chef
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 30px;">
                <?php foreach ($chefs as $chef): ?>
                    <article style="
                        background: white;
                        border: 1px solid #ddd;
                        border-radius: 10px;
                        overflow: hidden;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                        transition: all 0.3s ease;
                    "
                        onmouseover="this.style.boxShadow='0 8px 20px rgba(0,0,0,0.15)'; this.style.transform='translateY(-5px)'"
                        onmouseout="this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)'; this.style.transform='translateY(0)'">

                        <!-- Chef Avatar -->
                        <div style="
                            background: linear-gradient(135deg, #4CAF50, #45a049);
                            padding: 30px;
                            text-align: center;
                            color: white;
                        ">
                            <?php if ($chef['avatar']): ?>
                                <img src="<?php echo htmlspecialchars($chef['avatar']); ?>"
                                    style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 4px solid white;">
                            <?php else: ?>
                                <div style="width: 100px; height: 100px; border-radius: 50%; background: rgba(255,255,255,0.2); margin: 0 auto; display: flex; align-items: center; justify-content: center; font-size: 3rem;">
                                    👨‍🍳
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Chef Info -->
                        <div style="padding: 20px;">
                            <h3 style="margin: 0 0 8px 0;">
                                <a href="chef-profile.php?id=<?php echo $chef['user_id']; ?>" style="text-decoration: none; color: #333;">
                                    <?php echo htmlspecialchars($chef['name']); ?>
                                </a>
                                <span style="color: #FFD700; font-size: 1.1rem;">⭐</span>
                            </h3>

                            <p style="color: #666; font-size: 0.9rem; margin: 0 0 12px 0;">
                                <strong><?php echo !empty($chef['experience_years']) ? htmlspecialchars($chef['experience_years']) : '0'; ?></strong> years experience
                            </p>

                            <p style="color: #555; font-size: 0.85rem; margin: 0 0 12px 0; line-height: 1.4;">
                                <?php echo htmlspecialchars(substr($chef['bio'] ?? 'Passionate about cooking', 0, 100)); ?>...
                            </p>

                            <p style="color: #666; font-size: 0.85rem; margin: 0 0 12px 0;">
                                <strong>Specialties:</strong> <?php echo htmlspecialchars(substr($chef['specialties'] ?? 'Bengali Cuisine', 0, 80)); ?>
                            </p>

                            <!-- Stats -->
                            <div style="
                                display: grid;
                                grid-template-columns: 1fr 1fr;
                                gap: 12px;
                                padding: 12px;
                                background: #f5f5f5;
                                border-radius: 6px;
                                margin-bottom: 12px;
                                text-align: center;
                            ">
                                <div>
                                    <div style="font-weight: bold; color: #4CAF50; font-size: 1.3rem;">
                                        <?php echo $chef['followers']; ?>
                                    </div>
                                    <div style="color: #666; font-size: 0.8rem;">Followers</div>
                                </div>
                                <div>
                                    <div style="font-weight: bold; color: #4CAF50; font-size: 1.3rem;">
                                        <?php echo $chef['recipes_count']; ?>
                                    </div>
                                    <div style="color: #666; font-size: 0.8rem;">Recipes</div>
                                </div>
                            </div>

                            <!-- Follow Button -->
                            <button onclick="followChef(<?php echo $chef['user_id']; ?>)" style="
                                width: 100%;
                                background: #4CAF50;
                                color: white;
                                padding: 10px;
                                border: none;
                                border-radius: 4px;
                                cursor: pointer;
                                font-weight: 600;
                                transition: all 0.3s ease;
                            "
                                onmouseover="this.style.background='#45a049'"
                                onmouseout="this.style.background='#4CAF50'">
                                <i class="fas fa-plus"></i> Follow Chef
                            </button>

                            <a href="chef-profile.php?id=<?php echo $chef['user_id']; ?>" style="
                                display: block;
                                margin-top: 8px;
                                text-align: center;
                                background: #f0f0f0;
                                color: #333;
                                padding: 10px;
                                border-radius: 4px;
                                text-decoration: none;
                                transition: all 0.3s ease;
                            "
                                onmouseover="this.style.background='#e0e0e0'"
                                onmouseout="this.style.background='#f0f0f0'">
                                View Profile →
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php if (!in_array($_SESSION['user_id'], array_column($chefs, 'user_id')) && !empty($_SESSION['user_id'])): ?>
                <div style="text-align: center; margin-top: 40px;">
                    <p style="color: #666; margin-bottom: 16px;">Want to become a chef too?</p>
                    <a href="become-chef.php" style="
                        display: inline-block;
                        background: #4CAF50;
                        color: white;
                        padding: 12px 32px;
                        border-radius: 4px;
                        text-decoration: none;
                        font-weight: 600;
                    ">
                        👨‍🍳 Apply Now
                    </a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </section>
</main>

<script>
    function followChef(userId) {
        <?php if (empty($_SESSION['user_id'])): ?>
            window.location.href = 'login.php';
            return;
        <?php endif; ?>

        // Make AJAX call to follow chef
        fetch('api/follow-chef.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    chef_id: userId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Now following this chef!');
                    location.reload();
                } else {
                    alert('❌ ' + data.message);
                }
            });
    }
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>