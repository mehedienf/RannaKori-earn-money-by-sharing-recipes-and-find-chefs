<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/config/db.php';
session_start();

// Check if user is admin
if (empty($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Handle approve/reject chef requests
if ($_POST) {
    $action = $_POST['action'] ?? '';
    $user_id = (int)($_POST['user_id'] ?? 0);

    if ($action === 'approve' && $user_id > 0) {
        try {
            // Create chef entry
            $stmt = $pdo->prepare('INSERT INTO chefs (user_id, followers, created_at) VALUES (?, 0, NOW())');
            $stmt->execute([$user_id]);
            
            // Update user role to chef
            $stmt = $pdo->prepare('UPDATE users SET role = "chef" WHERE id = ?');
            $stmt->execute([$user_id]);
            
            echo '<div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 6px; margin-bottom: 16px;">✅ Chef approved successfully!</div>';
        } catch (PDOException $e) {
            echo '<div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 16px;">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }

    if ($action === 'reject' && $user_id > 0) {
        try {
            // Delete chef request (if exists)
            $stmt = $pdo->prepare('DELETE FROM chef_requests WHERE user_id = ?');
            $stmt->execute([$user_id]);
            
            echo '<div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 6px; margin-bottom: 16px;">✅ Chef request rejected!</div>';
        } catch (PDOException $e) {
            echo '<div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 16px;">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }

    if ($action === 'delete_chef' && $user_id > 0) {
        try {
            // Get chef_id
            $stmt = $pdo->prepare('SELECT id FROM chefs WHERE user_id = ?');
            $stmt->execute([$user_id]);
            $chef = $stmt->fetch();
            
            if ($chef) {
                // Delete chef followers
                $stmt = $pdo->prepare('DELETE FROM chef_followers WHERE chef_id = ?');
                $stmt->execute([$chef['id']]);
                
                // Delete chef
                $stmt = $pdo->prepare('DELETE FROM chefs WHERE id = ?');
                $stmt->execute([$chef['id']]);
                
                // Update user role back to user
                $stmt = $pdo->prepare('UPDATE users SET role = "user" WHERE id = ?');
                $stmt->execute([$user_id]);
                
                echo '<div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 6px; margin-bottom: 16px;">✅ Chef deleted successfully!</div>';
            }
        } catch (PDOException $e) {
            echo '<div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 16px;">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}

// Fetch pending chef requests
try {
    $stmt = $pdo->query('
        SELECT cr.*, u.name, u.email, u.avatar
        FROM chef_requests cr
        JOIN users u ON cr.user_id = u.id
        ORDER BY cr.created_at DESC
    ');
    $requests = $stmt->fetchAll();
} catch (PDOException $e) {
    $requests = [];
}

// Fetch all approved chefs
try {
    $stmt = $pdo->query('
        SELECT c.*, u.name, u.email, u.avatar
        FROM chefs c
        JOIN users u ON c.user_id = u.id
        ORDER BY c.followers DESC
    ');
    $chefs = $stmt->fetchAll();
} catch (PDOException $e) {
    $chefs = [];
}
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<main>
    <section class="container" style="max-width: 1200px; margin: 40px auto;">
        
        <h1 style="margin-bottom: 32px;">👨‍🍳 Manage Chefs & Requests</h1>

        <!-- PENDING REQUESTS SECTION -->
        <div style="margin-bottom: 60px;">
            <h2 style="margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid #ddd;">📋 Pending Chef Requests (<?php echo count($requests); ?>)</h2>

            <?php if (empty($requests)): ?>
                <div style="background: #f5f5f5; padding: 40px; text-align: center; border-radius: 8px;">
                    <p style="color: #666;">No pending requests</p>
                </div>
            <?php else: ?>
                <div style="display: grid; gap: 20px;">
                    <?php foreach ($requests as $req): ?>
                        <div style="background: white; border: 1px solid #ddd; border-radius: 8px; padding: 20px; display: flex; justify-content: space-between; align-items: center;">
                            
                            <div style="display: flex; gap: 16px; align-items: center; flex: 1;">
                                <?php if ($req['avatar']): ?>
                                    <img src="<?php echo htmlspecialchars($req['avatar']); ?>" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover;">
                                <?php else: ?>
                                    <div style="width: 60px; height: 60px; border-radius: 50%; background: #ddd; display: flex; align-items: center; justify-content: center;">👤</div>
                                <?php endif; ?>
                                
                                <div>
                                    <h3 style="margin: 0; color: #333;"><?php echo htmlspecialchars($req['name']); ?></h3>
                                    <p style="margin: 4px 0 0 0; color: #666; font-size: 0.9rem;">📧 <?php echo htmlspecialchars($req['email']); ?></p>
                                    <p style="margin: 4px 0 0 0; color: #999; font-size: 0.85rem;">Requested: <?php echo $req['created_at']; ?></p>
                                </div>
                            </div>

                            <div style="display: flex; gap: 8px;">
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $req['user_id']; ?>">
                                    <button type="submit" name="action" value="approve" style="background: #4CAF50; color: white; padding: 10px 24px; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">
                                        ✅ Approve
                                    </button>
                                </form>
                                
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $req['user_id']; ?>">
                                    <button type="submit" name="action" value="reject" style="background: #f44336; color: white; padding: 10px 24px; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">
                                        ❌ Reject
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- APPROVED CHEFS SECTION -->
        <div>
            <h2 style="margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid #ddd;">✨ Approved Chefs (<?php echo count($chefs); ?>)</h2>

            <?php if (empty($chefs)): ?>
                <div style="background: #f5f5f5; padding: 40px; text-align: center; border-radius: 8px;">
                    <p style="color: #666;">No approved chefs yet</p>
                </div>
            <?php else: ?>
                <div style="display: grid; gap: 20px;">
                    <?php foreach ($chefs as $chef): ?>
                        <div style="background: white; border: 1px solid #ddd; border-radius: 8px; padding: 20px; display: flex; justify-content: space-between; align-items: center;">
                            
                            <div style="display: flex; gap: 16px; align-items: center; flex: 1;">
                                <?php if ($chef['avatar']): ?>
                                    <img src="<?php echo htmlspecialchars($chef['avatar']); ?>" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover;">
                                <?php else: ?>
                                    <div style="width: 60px; height: 60px; border-radius: 50%; background: #ddd; display: flex; align-items: center; justify-content: center;">👨‍🍳</div>
                                <?php endif; ?>
                                
                                <div style="flex: 1;">
                                    <h3 style="margin: 0; color: #333;">
                                        <a href="chef-profile.php?id=<?php echo $chef['user_id']; ?>" style="text-decoration: none; color: #333;">
                                            <?php echo htmlspecialchars($chef['name']); ?>
                                        </a>
                                    </h3>
                                    <p style="margin: 4px 0 0 0; color: #666; font-size: 0.9rem;">📧 <?php echo htmlspecialchars($chef['email']); ?></p>
                                    <p style="margin: 4px 0 0 0; color: #999; font-size: 0.85rem;">
                                        👥 <?php echo $chef['followers']; ?> followers | 🍳 <?php echo $chef['recipes_count']; ?> recipes | ⭐ <?php echo $chef['points']; ?> points
                                    </p>
                                </div>
                            </div>

                            <div style="display: flex; gap: 8px;">
                                <a href="chef-profile.php?id=<?php echo $chef['user_id']; ?>" style="background: #2196F3; color: white; padding: 10px 24px; border: none; border-radius: 4px; text-decoration: none; font-weight: 600; cursor: pointer;">
                                    👁️ View
                                </a>
                                
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $chef['user_id']; ?>">
                                    <button type="submit" name="action" value="delete_chef" onclick="return confirm('Are you sure you want to delete this chef? This will remove their chef status and all related data.');" style="background: #f44336; color: white; padding: 10px 24px; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">
                                        🗑️ Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Back Button -->
        <div style="margin-top: 40px;">
            <a href="admin-dashboard.php" style="background: #f0f0f0; color: #333; padding: 12px 24px; border-radius: 4px; text-decoration: none; font-weight: 600;">
                ← Back to Admin Dashboard
            </a>
        </div>

    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
