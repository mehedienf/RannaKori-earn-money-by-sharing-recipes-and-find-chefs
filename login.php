<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Already logged in
if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = "Email and password required";
    } else {
        try {
            $stmt = $pdo->prepare('SELECT id, name, role, email, password_hash, points, status FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user) {
                $error = "Invalid email or password";
            } elseif (!password_verify($password, $user['password_hash'])) {
                $error = "Invalid email or password";
            } elseif ($user['status'] === 'banned') {
                // Check if banned BEFORE logging in
                $error = "Your account has been banned. Contact support for more information.";
            } else {
                // Login successful - set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_points'] = $user['points'];
                $_SESSION['user_status'] = $user['status'];
                $_SESSION['logged_in_at'] = time();

                // Admin check
                if ($_SESSION['user_role'] === 'admin') {
                    header('Location: admin-dashboard.php');
                    exit;
                } else {
                    header('Location: index.php');
                    exit;
                }
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<main>
    <section class="container" style="max-width: 500px; margin: 40px auto;">
        <h1>Login</h1>

        <?php if ($error): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 16px;">
                ❌ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <div style="margin-bottom: 16px;">
                <label for="email">Email:</label><br>
                <input type="email" id="email" name="email" required
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                    style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>

            <div style="margin-bottom: 16px;">
                <label for="password">Password:</label><br>
                <input type="password" id="password" name="password" required
                    style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>

            <button type="submit" style="background: #4CAF50; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-size: 1rem;">
                Login
            </button>
        </form>

        <p style="margin-top: 16px;">
            Don't have an account? <a href="register.php">Register here</a>
        </p>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>