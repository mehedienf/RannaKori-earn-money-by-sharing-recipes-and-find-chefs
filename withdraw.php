<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch user data
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    echo "User not found!";
    exit;
}

$error = '';
$success = '';
$POINTS_TO_TAKA = 100; // 100 points = 1 TK

// Handle withdrawal request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $withdrawPoints = (int)($_POST['withdraw_points'] ?? 0);
    $bkashNumber = trim($_POST['bkash_number'] ?? '');

    if ($withdrawPoints <= 0) {
        $error = '❌ Points amount must be greater than 0';
    } elseif (empty($bkashNumber)) {
        $error = '❌ bKash number is required';
    } elseif (!preg_match('/^01[3-9]\d{8}$/', $bkashNumber)) {
        $error = '❌ Please provide a valid bKash number (e.g., 01XXXXXXXXX)';
    } elseif ($withdrawPoints > $user['points']) {
        $error = '❌ You do not have enough points';
    } else {
        // Calculate taka amount
        $takaAmount = $withdrawPoints / $POINTS_TO_TAKA;

        // Create withdrawal record
        try {
            $stmt = $pdo->prepare('
                INSERT INTO withdrawals (user_id, points, amount, bkash_number, status, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ');
            $stmt->execute([$_SESSION['user_id'], $withdrawPoints, $takaAmount, $bkashNumber, 'pending']);

            // Deduct points from user
            $stmt = $pdo->prepare('UPDATE users SET points = points - ? WHERE id = ?');
            $stmt->execute([$withdrawPoints, $_SESSION['user_id']]);

            // Update session points
            $_SESSION['user_points'] -= $withdrawPoints;

            $success = "✅ Request submitted successfully! ৳{$takaAmount} will be transferred to your bKash number soon.";
        } catch (Exception $e) {
            $error = '❌ Failed to process request. Please try again.';
        }
    }
}

// Fetch withdrawal history
$stmt = $pdo->prepare('
    SELECT id, points, amount, bkash_number, status, created_at 
    FROM withdrawals 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 10
');
$stmt->execute([$_SESSION['user_id']]);
$withdrawals = $stmt->fetchAll();
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<main>
    <section class="container" style="max-width: 900px; margin: 40px auto;">
        <h1 style="margin-bottom: 32px;">💰 Withdraw Money</h1>

        <!-- Balance Info -->
        <div style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 30px; border-radius: 8px; margin-bottom: 32px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <div>
                    <p style="margin: 0 0 8px 0; opacity: 0.9;">Total Points</p>
                    <h2 style="margin: 0; font-size: 2rem;">⭐ <?php echo $user['points']; ?></h2>
                </div>
                <div>
                    <p style="margin: 0 0 8px 0; opacity: 0.9;">Exchange Rate</p>
                    <h2 style="margin: 0; font-size: 2rem;">100 Points = 1 Taka</h2>
                </div>
                <div>
                    <p style="margin: 0 0 8px 0; opacity: 0.9;">Potential Amount</p>
                    <h2 style="margin: 0; font-size: 2rem;">৳<?php echo number_format($user['points'] / $POINTS_TO_TAKA, 2); ?></h2>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <?php if (!empty($error)): ?>
            <div style="background: #ffebee; color: #c62828; padding: 16px; border-radius: 8px; margin-bottom: 16px; border-left: 4px solid #c62828;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div style="background: #e8f5e9; color: #2e7d32; padding: 16px; border-radius: 8px; margin-bottom: 16px; border-left: 4px solid #2e7d32;">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <!-- Withdrawal Form -->
        <div style="background: white; border: 1px solid #ddd; padding: 30px; border-radius: 8px; margin-bottom: 32px;">
            <h2 style="margin-top: 0;">Submit New Request</h2>

            <form method="POST" style="max-width: 500px;">
                <div style="margin-bottom: 16px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">How many points do you want to withdraw?</label>
                    <input type="number" name="withdraw_points" 
                        min="100" 
                        max="<?php echo $user['points']; ?>" 
                        step="100"
                        placeholder="Minimum 100 points"
                        style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; box-sizing: border-box;"
                        required>
                    <small style="color: #666; display: block; margin-top: 8px;">
                        (You have: <?php echo $user['points']; ?> points)
                    </small>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">bKash Number</label>
                    <input type="tel" name="bkash_number" 
                        placeholder="01XXXXXXXXX"
                        pattern="01[3-9]\d{8}"
                        maxlength="11"
                        style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; box-sizing: border-box;"
                        required>
                    <small style="color: #666; display: block; margin-top: 8px;">
                        Please provide a valid bKash number (e.g., 01700000000)
                    </small>
                </div>

                <div id="conversionInfo" style="background: #f5f5f5; padding: 16px; border-radius: 4px; margin-bottom: 16px; display: none;">
                    <p style="margin: 0; color: #666;">
                        You will receive <strong id="convertedAmount">0</strong> Taka
                    </p>
                </div>

                <button type="submit" style="background: #4CAF50; color: white; padding: 12px 32px; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 1rem; width: 100%;">
                    ✅ Withdraw Money
                </button>
            </form>
        </div>

        <!-- Withdrawal History -->
        <div>
            <h2>Request History</h2>

            <?php if (empty($withdrawals)): ?>
                <div style="background: #f5f5f5; padding: 20px; border-radius: 8px; text-align: center;">
                    <p style="color: #666; margin: 0;">No withdrawal requests yet</p>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f5f5f5;">
                                <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Points</th>
                                <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Amount</th>
                                <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">bKash Number</th>
                                <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Status</th>
                                <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($withdrawals as $withdrawal): ?>
                                <tr>
                                    <td style="padding: 12px; border: 1px solid #ddd;">
                                        ⭐ <?php echo $withdrawal['points']; ?>
                                    </td>
                                    <td style="padding: 12px; border: 1px solid #ddd;">
                                        ৳<?php echo number_format($withdrawal['amount'], 2); ?>
                                    </td>
                                    <td style="padding: 12px; border: 1px solid #ddd;">
                                        <strong><?php echo htmlspecialchars($withdrawal['bkash_number'] ?? 'N/A'); ?></strong>
                                    </td>
                                    <td style="padding: 12px; border: 1px solid #ddd;">
                                        <?php 
                                            if ($withdrawal['status'] === 'completed') {
                                                echo '<span style="background: #e8f5e9; color: #2e7d32; padding: 4px 8px; border-radius: 4px; font-weight: 600;">✅ Completed</span>';
                                            } elseif ($withdrawal['status'] === 'pending') {
                                                echo '<span style="background: #fff3e0; color: #e65100; padding: 4px 8px; border-radius: 4px; font-weight: 600;">⏳ Pending</span>';
                                            } elseif ($withdrawal['status'] === 'rejected') {
                                                echo '<span style="background: #ffebee; color: #c62828; padding: 4px 8px; border-radius: 4px; font-weight: 600;">❌ Rejected</span>';
                                            }
                                        ?>
                                    </td>
                                    <td style="padding: 12px; border: 1px solid #ddd;">
                                        <?php echo date('d/m/Y', strtotime($withdrawal['created_at'])); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Info Section -->
        <div style="margin-top: 32px; background: #f5f5f5; padding: 20px; border-radius: 8px; border-left: 4px solid #2196F3;">
            <h3>📋 Important Information</h3>
            <ul style="margin: 0; padding-left: 20px; color: #666;">
                <li>Minimum 100 points required</li>
                <li>Admin verification required after submission</li>
                <li>Money will be transferred within 2-4 business days after approval</li>
                <li>Only valid bKash numbers accepted</li>
            </ul>
        </div>
    </section>
</main>

<script>
    // Real-time conversion calculation
    const input = document.querySelector('input[name="withdraw_points"]');
    const conversionInfo = document.getElementById('conversionInfo');
    const convertedAmount = document.getElementById('convertedAmount');

    input.addEventListener('input', function() {
        const points = parseInt(this.value) || 0;
        if (points > 0) {
            const taka = (points / 100).toFixed(2);
            convertedAmount.textContent = '৳' + taka;
            conversionInfo.style.display = 'block';
        } else {
            conversionInfo.style.display = 'none';
        }
    });
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
