<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        echo "MISSING EMAIL OR PASSWORD<br>";
        exit;
    }

    try {
        $stmt = $pdo->prepare('SELECT id, name, email, password_hash FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            echo "NO USER FOUND WITH THIS EMAIL<br>";
            exit;
        }

        if (!password_verify($password, $user['password_hash'])) {
            echo "WRONG PASSWORD<br>";
            exit;
        }

        // login success
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];

        header('Location: index.php');
        exit;

    } catch (PDOException $e) {
        echo "DB ERROR: " . $e->getMessage();
        exit;
    }
}
?>

<form method="post" action="">
    <input type="email" name="email" placeholder="Email">
    <input type="password" name="password" placeholder="Password">
    <button type="submit">Login</button>
</form>