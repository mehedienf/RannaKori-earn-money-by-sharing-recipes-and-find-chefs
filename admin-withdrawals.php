<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Admin check
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare('SELECT role FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['role'] !== 'admin') {
    echo "❌ You do not have permission to access this page";
    exit;
}

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $withdrawalId = (int)($_POST['withdrawal_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($withdrawalId > 0 && in_array($action, ['approve', 'reject'])) {
        try {
            // Get withdrawal details
            $stmt = $pdo->prepare('SELECT * FROM withdrawals WHERE id = ?');
            $stmt->execute([$withdrawalId]);
            $withdrawal = $stmt->fetch();

            if ($withdrawal && $withdrawal['status'] === 'pending') {
                $newStatus = ($action === 'approve') ? 'completed' : 'rejected';

                // Update withdrawal status
                $stmt = $pdo->prepare('UPDATE withdrawals SET status = ? WHERE id = ?');
                $stmt->execute([$newStatus, $withdrawalId]);

                // If rejected, refund the points
                if ($action === 'reject') {
                    $stmt = $pdo->prepare('UPDATE users SET points = points + ? WHERE id = ?');
                    $stmt->execute([$withdrawal['points'], $withdrawal['user_id']]);
                }

                $_SESSION['message'] = ($action === 'approve') 
                    ? "✅ Withdrawal approved successfully!" 
                    : "❌ Withdrawal rejected and points refunded!";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "❌ Error processing withdrawal: " . $e->getMessage();
        }
    }
}

// Get filter status
$filterStatus = $_GET['status'] ?? 'all';
$validStatuses = ['all', 'pending', 'completed', 'rejected'];
$filterStatus = in_array($filterStatus, $validStatuses) ? $filterStatus : 'all';

// Fetch withdrawals
try {
    if ($filterStatus === 'all') {
        $stmt = $pdo->prepare('
            SELECT w.*, u.name, u.email 
            FROM withdrawals w 
            JOIN users u ON w.user_id = u.id 
            ORDER BY w.created_at DESC
        ');
    } else {
        $stmt = $pdo->prepare('
            SELECT w.*, u.name, u.email 
            FROM withdrawals w 
            JOIN users u ON w.user_id = u.id 
            WHERE w.status = ?
            ORDER BY w.created_at DESC
        ');
        $stmt->execute([$filterStatus]);
    }
    
    if ($filterStatus === 'all') {
        $stmt->execute();
    }
    
    $withdrawals = $stmt->fetchAll();

    // Get counts
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM withdrawals WHERE status = "pending"');
    $pendingCount = $stmt->fetch()['count'];

    $stmt = $pdo->query('SELECT COUNT(*) as count FROM withdrawals WHERE status = "completed"');
    $completedCount = $stmt->fetch()['count'];

    $stmt = $pdo->query('SELECT COUNT(*) as count FROM withdrawals WHERE status = "rejected"');
    $rejectedCount = $stmt->fetch()['count'];

    $stmt = $pdo->query('SELECT SUM(amount) as total FROM withdrawals WHERE status = "completed"');
    $totalPaid = $stmt->fetch()['total'] ?? 0;

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<main>
    <section class="container" style="max-width: 1200px; margin: 40px auto;">
        <h1>💰 Withdrawal Management</h1>

        <!-- Messages -->
        <?php if (!empty($_SESSION['message'])): ?>
            <div style="background: #e8f5e9; color: #2e7d32; padding: 16px; border-radius: 8px; margin-bottom: 16px; border-left: 4px solid #2e7d32;">
                <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['error'])): ?>
            <div style="background: #ffebee; color: #c62828; padding: 16px; border-radius: 8px; margin-bottom: 16px; border-left: 4px solid #c62828;">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 32px;">
            <div style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 20px; border-radius: 8px;">
                <div style="font-size: 1.5rem;">⏳</div>
                <h3 style="margin: 8px 0;">Pending</h3>
                <p style="margin: 0; font-size: 2rem; font-weight: bold;"><?php echo $pendingCount; ?></p>
            </div>

            <div style="background: linear-gradient(135deg, #f093fb, #f5576c); color: white; padding: 20px; border-radius: 8px;">
                <div style="font-size: 1.5rem;">✅</div>
                <h3 style="margin: 8px 0;">Completed</h3>
                <p style="margin: 0; font-size: 2rem; font-weight: bold;"><?php echo $completedCount; ?></p>
            </div>

            <div style="background: linear-gradient(135deg, #4facfe, #00f2fe); color: white; padding: 20px; border-radius: 8px;">
                <div style="font-size: 1.5rem;">❌</div>
                <h3 style="margin: 8px 0;">Rejected</h3>
                <p style="margin: 0; font-size: 2rem; font-weight: bold;"><?php echo $rejectedCount; ?></p>
            </div>

            <div style="background: linear-gradient(135deg, #a8edea, #fed6e3); color: #333; padding: 20px; border-radius: 8px;">
                <div style="font-size: 1.5rem;">💵</div>
                <h3 style="margin: 8px 0;">Total Paid</h3>
                <p style="margin: 0; font-size: 2rem; font-weight: bold;">৳<?php echo number_format($totalPaid, 2); ?></p>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div style="display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap;">
            <a href="?status=all" style="padding: 10px 16px; border-radius: 4px; text-decoration: none; background: <?php echo ($filterStatus === 'all') ? '#4CAF50' : '#ddd'; ?>; color: <?php echo ($filterStatus === 'all') ? 'white' : '#333'; ?>; font-weight: 600;">
                All (<?php echo count($withdrawals); ?>)
            </a>
            <a href="?status=pending" style="padding: 10px 16px; border-radius: 4px; text-decoration: none; background: <?php echo ($filterStatus === 'pending') ? '#FF9800' : '#ddd'; ?>; color: <?php echo ($filterStatus === 'pending') ? 'white' : '#333'; ?>; font-weight: 600;">
                Pending (<?php echo $pendingCount; ?>)
            </a>
            <a href="?status=completed" style="padding: 10px 16px; border-radius: 4px; text-decoration: none; background: <?php echo ($filterStatus === 'completed') ? '#4CAF50' : '#ddd'; ?>; color: <?php echo ($filterStatus === 'completed') ? 'white' : '#333'; ?>; font-weight: 600;">
                Completed (<?php echo $completedCount; ?>)
            </a>
            <a href="?status=rejected" style="padding: 10px 16px; border-radius: 4px; text-decoration: none; background: <?php echo ($filterStatus === 'rejected') ? '#f44336' : '#ddd'; ?>; color: <?php echo ($filterStatus === 'rejected') ? 'white' : '#333'; ?>; font-weight: 600;">
                Rejected (<?php echo $rejectedCount; ?>)
            </a>
        </div>

        <!-- Withdrawals Table -->
        <div style="overflow-x: auto; background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <?php if (empty($withdrawals)): ?>
                <div style="padding: 40px; text-align: center; color: #666;">
                    <p style="margin: 0; font-size: 1.1rem;">No withdrawal requests</p>
                </div>
            <?php else: ?>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f5f5f5; border-bottom: 2px solid #ddd;">
                            <th style="padding: 16px; text-align: left; font-weight: 600;">User</th>
                            <th style="padding: 16px; text-align: left; font-weight: 600;">Email</th>
                            <th style="padding: 16px; text-align: right; font-weight: 600;">Points</th>
                            <th style="padding: 16px; text-align: right; font-weight: 600;">Amount (৳)</th>
                            <th style="padding: 16px; text-align: left; font-weight: 600;">bKash Number</th>
                            <th style="padding: 16px; text-align: center; font-weight: 600;">Status</th>
                            <th style="padding: 16px; text-align: left; font-weight: 600;">Date</th>
                            <th style="padding: 16px; text-align: center; font-weight: 600;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($withdrawals as $w): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 16px;">
                                    <strong><?php echo htmlspecialchars($w['name']); ?></strong>
                                </td>
                                <td style="padding: 16px;">
                                    <?php echo htmlspecialchars($w['email']); ?>
                                </td>
                                <td style="padding: 16px; text-align: right;">
                                    ⭐ <?php echo $w['points']; ?>
                                </td>
                                <td style="padding: 16px; text-align: right;">
                                    <strong>৳<?php echo number_format($w['amount'], 2); ?></strong>
                                </td>
                                <td style="padding: 16px;">
                                    <code style="background: #f5f5f5; padding: 4px 8px; border-radius: 4px; font-family: monospace; font-weight: 600;">
                                        <?php echo htmlspecialchars($w['bkash_number'] ?? 'N/A'); ?>
                                    </code>
                                </td>
                                <td style="padding: 16px; text-align: center;">
                                    <?php 
                                        if ($w['status'] === 'pending') {
                                            echo '<span style="background: #fff3e0; color: #e65100; padding: 6px 12px; border-radius: 20px; font-weight: 600; font-size: 0.9rem;">⏳ Pending</span>';
                                        } elseif ($w['status'] === 'completed') {
                                            echo '<span style="background: #e8f5e9; color: #2e7d32; padding: 6px 12px; border-radius: 20px; font-weight: 600; font-size: 0.9rem;">✅ Completed</span>';
                                        } else {
                                            echo '<span style="background: #ffebee; color: #c62828; padding: 6px 12px; border-radius: 20px; font-weight: 600; font-size: 0.9rem;">❌ Rejected</span>';
                                        }
                                    ?>
                                </td>
                                <td style="padding: 16px;">
                                    <?php echo date('d/m/Y H:i', strtotime($w['created_at'])); ?>
                                </td>
                                <td style="padding: 16px; text-align: center;">
                                    <?php if ($w['status'] === 'pending'): ?>
                                        <div style="display: flex; gap: 8px; justify-content: center;">
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="withdrawal_id" value="<?php echo $w['id']; ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit" onclick="return confirm('Approve this withdrawal?')" style="background: #4CAF50; color: white; padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 0.9rem;">
                                                    ✅ Approve
                                                </button>
                                            </form>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="withdrawal_id" value="<?php echo $w['id']; ?>">
                                                <input type="hidden" name="action" value="reject">
                                                <button type="submit" onclick="return confirm('Reject this withdrawal?')" style="background: #f44336; color: white; padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 0.9rem;">
                                                    ❌ Reject
                                                </button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: #999; font-size: 0.9rem;">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Info Section -->
        <div style="margin-top: 32px; background: #f5f5f5; padding: 20px; border-radius: 8px; border-left: 4px solid #2196F3;">
            <h3>📋 Guidelines</h3>
            <ul style="margin: 0; padding-left: 20px; color: #666;">
                <li>After approval, user will receive a tracking number</li>
                <li>If rejected, points will be refunded to the user</li>
                <li>Completed requests cannot be modified</li>
            </ul>
        </div>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
