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

// Fetch current user data
$stmt = $pdo->prepare('SELECT id, name, email, avatar FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Update handling
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $avatarPath = $user['avatar'];
    
    // Validation
    if (empty($name)) {
        $error = "Name is required";
    } elseif (empty($email)) {
        $error = "Email is required";
    } else {
        // Check if email already exists (for other users)
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $stmt->execute([$email, $_SESSION['user_id']]);
        
        if ($stmt->fetch()) {
            $error = "Email already in use";
        } else {
            // Avatar upload
            if (!empty($_FILES['avatar']['name'])) {
                $file = $_FILES['avatar'];
                $uploadDir = __DIR__ . '/uploads/avatars/';
                
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $filename = 'avatar_' . $_SESSION['user_id'] . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
                $uploadPath = $uploadDir . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                    $avatarPath = 'uploads/avatars/' . $filename;
                }
            }
            
            // Update database
            try {
                if (!empty($password)) {
                    // Update with new password
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, avatar = ?, password = ? WHERE id = ?');
                    $stmt->execute([$name, $email, $avatarPath, $hashedPassword, $_SESSION['user_id']]);
                } else {
                    // Update without password
                    $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, avatar = ? WHERE id = ?');
                    $stmt->execute([$name, $email, $avatarPath, $_SESSION['user_id']]);
                }
                
                // Update session
                $_SESSION['user_name'] = $name;
                
                $success = "Profile updated successfully!";
                
                // Refresh user data
                $stmt = $pdo->prepare('SELECT id, name, email, avatar FROM users WHERE id = ?');
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch();
                
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<main>
    <section class="container" style="max-width: 600px; margin: 40px auto;">
        <h1>Edit Profile</h1>
        
        <?php if ($success): ?>
            <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 6px; margin-bottom: 16px;">
                ✅ <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 16px;">
                ❌ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="post" enctype="multipart/form-data">
            <div style="margin-bottom: 16px;">
                <label>Name:</label><br>
                <input type="text" name="name" required value="<?php echo htmlspecialchars($user['name']); ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            
            <div style="margin-bottom: 16px;">
                <label>Email:</label><br>
                <input type="email" name="email" required value="<?php echo htmlspecialchars($user['email']); ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            
            <div style="margin-bottom: 16px;">
                <label>Avatar:</label><br>
                <?php if ($user['avatar']): ?>
                    <div style="margin-bottom: 10px;">
                        <img src="<?php echo htmlspecialchars($user['avatar']); ?>" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 2px solid #ddd;">
                    </div>
                <?php endif; ?>
                <input type="file" name="avatar" accept="image/*" style="padding: 8px;">
                <small style="color: #666;">Upload new avatar to replace</small>
            </div>
            
            <div style="margin-bottom: 16px;">
                <label>New Password (leave blank to keep current):</label><br>
                <input type="password" name="password" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            
            <button type="submit" style="background: #4CAF50; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem;">
                Update Profile
            </button>
            
            <a href="profile.php" style="margin-left: 16px;">Back to Profile</a>
        </form>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>