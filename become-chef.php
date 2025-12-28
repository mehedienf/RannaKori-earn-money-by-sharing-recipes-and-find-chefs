<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/config/db.php';
session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

// Check if user already applied
try {
    $stmt = $pdo->prepare('SELECT id, status FROM chef_requests WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $existingRequest = $stmt->fetch();
} catch (PDOException $e) {
    $existingRequest = null;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bio = trim($_POST['bio'] ?? '');
    $experience_years = (int)($_POST['experience_years'] ?? 0);
    $specialties = trim($_POST['specialties'] ?? '');
    
    if (!$bio || !$experience_years || !$specialties) {
        $error = "All fields are required";
    } else {
        try {
            if ($existingRequest) {
                // Update existing request
                $stmt = $pdo->prepare(
                    'UPDATE chef_requests SET bio=?, experience_years=?, specialties=? WHERE user_id=?'
                );
                $stmt->execute([$bio, $experience_years, $specialties, $_SESSION['user_id']]);
                $success = "Application updated successfully!";
            } else {
                // Create new request
                $stmt = $pdo->prepare(
                    'INSERT INTO chef_requests (user_id, bio, experience_years, specialties) VALUES (?, ?, ?, ?)'
                );
                $stmt->execute([$_SESSION['user_id'], $bio, $experience_years, $specialties]);
                $success = "Application submitted! Waiting for admin approval.";
            }
            
            // Reload to show success message
            header('Location: become-chef.php?success=1');
            exit;
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Get current request if exists
if ($existingRequest) {
    $stmt = $pdo->prepare('SELECT * FROM chef_requests WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $currentRequest = $stmt->fetch();
}
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<main>
    <section class="container" style="max-width: 700px; margin: 40px auto;">
        <h1>👨‍🍳 Become a Chef</h1>
        
        <?php if (!empty($_GET['success'])): ?>
            <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 6px; margin-bottom: 16px;">
                ✅ Application submitted successfully!
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 16px;">
                ❌ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($existingRequest): ?>
            <div style="background: #e7f3ff; border-left: 4px solid #2196F3; padding: 16px; border-radius: 6px; margin-bottom: 24px;">
                <p style="margin: 0; color: #1976D2;">
                    <strong>Status:</strong> 
                    <?php if ($existingRequest['status'] === 'pending'): ?>
                        ⏳ Pending (Waiting for admin approval)
                    <?php elseif ($existingRequest['status'] === 'approved'): ?>
                        ✅ Approved - You are now a chef!
                    <?php else: ?>
                        ❌ Rejected - Try again with more details
                    <?php endif; ?>
                </p>
            </div>
        <?php endif; ?>
        
        <form method="post">
            <div style="margin-bottom: 16px;">
                <label style="font-weight: 600; margin-bottom: 8px; display: block;">Bio:</label>
                <textarea name="bio" required rows="5" style="
                    width: 100%; 
                    padding: 12px; 
                    border: 1px solid #ddd; 
                    border-radius: 4px;
                    font-family: Arial, sans-serif;
                    font-size: 14px;
                "><?php echo isset($currentRequest) ? htmlspecialchars($currentRequest['bio']) : ''; ?></textarea>
                <small style="color: #666; display: block; margin-top: 4px;">
                    Tell us about your cooking experience, background, and passion
                </small>
            </div>
            
            <div style="margin-bottom: 16px;">
                <label style="font-weight: 600; margin-bottom: 8px; display: block;">Years of Experience:</label>
                <input type="number" name="experience_years" required min="0" max="70" style="
                    width: 100%; 
                    padding: 10px; 
                    border: 1px solid #ddd; 
                    border-radius: 4px;
                    font-size: 14px;
                " value="<?php echo isset($currentRequest) ? $currentRequest['experience_years'] : ''; ?>">
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="font-weight: 600; margin-bottom: 8px; display: block;">Specialties:</label>
                <input type="text" name="specialties" required placeholder="e.g., Bengali, Italian, Vegan, Desserts" style="
                    width: 100%; 
                    padding: 10px; 
                    border: 1px solid #ddd; 
                    border-radius: 4px;
                    font-size: 14px;
                " value="<?php echo isset($currentRequest) ? htmlspecialchars($currentRequest['specialties']) : ''; ?>">
                <small style="color: #666; display: block; margin-top: 4px;">
                    Comma-separated list of your cooking specialties
                </small>
            </div>
            
            <button type="submit" style="
                background: #4CAF50; 
                color: white; 
                padding: 12px 32px; 
                border: none; 
                border-radius: 4px; 
                cursor: pointer;
                font-weight: 600;
                font-size: 16px;
            ">
                <?php echo $existingRequest ? '📝 Update Application' : '✅ Submit Application'; ?>
            </button>
        </form>
        
        <div style="background: #f5f5f5; padding: 20px; border-radius: 8px; margin-top: 40px;">
            <h3 style="margin-top: 0;">Why become a chef?</h3>
            <ul style="margin: 15px 0; padding-left: 20px;">
                <li>📺 Get featured on the platform</li>
                <li>👥 Build a follower base</li>
                <li>💰 Earn more points per recipe</li>
                <li>⭐ Special chef badge on your profile</li>
                <li>🎯 Access to exclusive features</li>
            </ul>
        </div>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>