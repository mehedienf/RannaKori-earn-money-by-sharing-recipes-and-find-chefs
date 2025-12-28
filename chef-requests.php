<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/config/db.php';
session_start();

// Admin check
if (empty($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Handle approve/reject
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = (int)($_POST['request_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    
    if ($request_id && in_array($action, ['approve', 'reject'])) {
        try {
            if ($action === 'approve') {
                // Get the chef request
                $stmt = $pdo->prepare('SELECT * FROM chef_requests WHERE id = ?');
                $stmt->execute([$request_id]);
                $request = $stmt->fetch();
                
                // Create chef record
                $stmt = $pdo->prepare(
                    'INSERT INTO chefs (user_id, bio, experience_years, specialties) VALUES (?, ?, ?, ?)'
                );
                $stmt->execute([
                    $request['user_id'],
                    $request['bio'],
                    $request['experience_years'],
                    $request['specialties']
                ]);
                
                // Update request status
                $stmt = $pdo->prepare('UPDATE chef_requests SET status = ? WHERE id = ?');
                $stmt->execute(['approved', $request_id]);
                
                echo '<div style="color: green; padding: 10px; background: #d4edda; margin: 10px 0; border-radius: 4px;">✅ Chef approved!</div>';
            } else {
                // Reject
                $stmt = $pdo->prepare('UPDATE chef_requests SET status = ? WHERE id = ?');
                $stmt->execute(['rejected', $request_id]);
                
                echo '<div style="color: red; padding: 10px; background: #f8d7da; margin: 10px 0; border-radius: 4px;">❌ Chef request rejected!</div>';
            }
        } catch (PDOException $e) {
            echo '<div style="color: red;">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}

// Fetch pending chef requests
try {
    $stmt = $pdo->prepare('
        SELECT cr.*, u.name, u.email 
        FROM chef_requests cr
        JOIN users u ON cr.user_id = u.id
        WHERE cr.status = "pending"
        ORDER BY cr.created_at DESC
    ');
    $stmt->execute();
    $requests = $stmt->fetchAll();
} catch (PDOException $e) {
    $requests = [];
}
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<main>
    <section class="container" style="max-width: 1000px; margin: 40px auto;">
        <h1>👨‍🍳 Chef Requests</h1>
        <p style="color: #666;">Total pending: <strong><?php echo count($requests); ?></strong></p>
        
        <?php if (empty($requests)): ?>
            <div style="background: #f5f5f5; padding: 40px; text-align: center; border-radius: 8px;">
                <p style="font-size: 1.2rem; color: #666;">No pending chef requests</p>
            </div>
        <?php else: ?>
            <?php foreach ($requests as $request): ?>
                <div style="background: white; border: 1px solid #ddd; padding: 20px; margin-bottom: 20px; border-radius: 8px;">
                    
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 16px;">
                        <div>
                            <h3 style="margin: 0 0 4px 0;"><?php echo htmlspecialchars($request['name']); ?></h3>
                            <p style="color: #666; margin: 0; font-size: 0.9rem;"><?php echo htmlspecialchars($request['email']); ?></p>
                        </div>
                        <span style="background: #fff3cd; color: #856404; padding: 6px 12px; border-radius: 4px; font-size: 0.85rem;">
                            ⏳ Pending
                        </span>
                    </div>
                    
                    <p style="margin: 0 0 12px 0;"><strong>Experience:</strong> <?php echo $request['experience_years']; ?> years</p>
                    <p style="margin: 0 0 12px 0;"><strong>Specialties:</strong> <?php echo htmlspecialchars($request['specialties']); ?></p>
                    
                    <p style="margin: 0 0 16px 0; padding: 12px; background: #f5f5f5; border-radius: 4px; line-height: 1.5;">
                        <?php echo nl2br(htmlspecialchars($request['bio'])); ?>
                    </p>
                    
                    <div style="display: flex; gap: 12px;">
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" style="
                                background: #4CAF50;
                                color: white;
                                padding: 10px 20px;
                                border: none;
                                border-radius: 4px;
                                cursor: pointer;
                            ">
                                ✅ Approve
                            </button>
                        </form>
                        
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                            <input type="hidden" name="action" value="reject">
                            <button type="submit" style="
                                background: #f44336;
                                color: white;
                                padding: 10px 20px;
                                border: none;
                                border-radius: 4px;
                                cursor: pointer;
                            ">
                                ❌ Reject
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>