<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // Top users fetch (points অনুযায়ী descending)
    $stmt = $pdo->query(
        'SELECT 
            u.id,
            u.name,
            u.points,
            u.created_at,
            COUNT(r.id) AS total_recipes
         FROM users u
         LEFT JOIN recipes r ON u.id = r.user_id
         GROUP BY u.id
         ORDER BY u.points DESC, u.created_at ASC
         LIMIT 50'
    );
    $topUsers = $stmt->fetchAll();

} catch (PDOException $e) {
    die('DB ERROR: ' . $e->getMessage());
}

// Medal/badge for top 3
function getRankBadge($rank) {
    switch($rank) {
        case 1: return '🥇';
        case 2: return '🥈';
        case 3: return '🥉';
        default: return '#' . $rank;
    }
}
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<main>
    <section class="container" style="max-width: 900px; margin: 40px auto;">
        
        <h1>🏆 Leaderboard - Top Recipe Creators</h1>
        <p style="color: #666; margin-bottom: 32px;">
            Top users ranked by points. Create recipes and earn points to climb the leaderboard!
        </p>

        <?php if (empty($topUsers)): ?>
            <p>কোনো user এখনো নেই।</p>
        <?php else: ?>
            <table class="leaderboard-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f5f5f5; text-align: left;">
                        <th style="padding: 12px; border-bottom: 2px solid #ddd;">Rank</th>
                        <th style="padding: 12px; border-bottom: 2px solid #ddd;">User</th>
                        <th style="padding: 12px; border-bottom: 2px solid #ddd;">Points</th>
                        <th style="padding: 12px; border-bottom: 2px solid #ddd;">Recipes</th>
                        <th style="padding: 12px; border-bottom: 2px solid #ddd;">Member Since</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $rank = 1;
                    foreach ($topUsers as $user): 
                        // নিজের row highlight করা
                        $isCurrentUser = !empty($_SESSION['user_id']) && $_SESSION['user_id'] == $user['id'];
                        $rowStyle = $isCurrentUser 
                            ? 'background: #fffbf0; font-weight: bold; border-left: 4px solid #ffd700;' 
                            : '';
                    ?>
                        <tr style="<?php echo $rowStyle; ?>">
                            <td style="padding: 12px; border-bottom: 1px solid #eee; font-size: 1.2rem;">
                                <?php echo getRankBadge($rank); ?>
                            </td>
                            <td style="padding: 12px; border-bottom: 1px solid #eee;">
                                <?php echo htmlspecialchars($user['name']); ?>
                                <?php if ($isCurrentUser): ?>
                                    <span style="color: #4CAF50; font-size: 0.85rem;">(You)</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px; border-bottom: 1px solid #eee; color: #ffd700; font-weight: bold;">
                                ⭐ <?php echo (int)$user['points']; ?>
                            </td>
                            <td style="padding: 12px; border-bottom: 1px solid #eee;">
                                🍳 <?php echo (int)$user['total_recipes']; ?>
                            </td>
                            <td style="padding: 12px; border-bottom: 1px solid #eee; color: #999; font-size: 0.9rem;">
                                <?php echo date('M Y', strtotime($user['created_at'])); ?>
                            </td>
                        </tr>
                    <?php 
                        $rank++;
                    endforeach; 
                    ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div style="margin-top: 32px; padding: 16px; background: #f0f8ff; border-radius: 8px;">
            <h3>📊 How to Earn Points:</h3>
            <ul style="margin-top: 12px; line-height: 1.8;">
                <li>✅ Create a recipe: <strong>+50 points</strong></li>
                <li>⭐ Write a review: <strong>+10 points</strong></li>
                <li>❤️ Get a like: <strong>+1 point</strong> (coming soon)</li>
            </ul>
            <p style="margin-top: 16px;">
                <a href="add-recipe.php" class="btn" style="display: inline-block; background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;">
                    ➕ Create Recipe & Earn Points
                </a>
            </p>
        </div>

    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>