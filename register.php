<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if ($name === '' || $email === '' || $password === '' || $confirm === '') {
        echo "VALIDATION FAILED<br>";
        exit;
    }

    if ($password !== $confirm) {
        echo "PASSWORD MISMATCH<br>";
        exit;
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
        $ok = $stmt->execute([$name, $email, $passwordHash]);

        if ($ok) {
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;

            // এখানে কোনো echo নেই, direct redirect
            header('Location: index.php');
            exit;
        } else {
            echo "INSERT FAILED<br>";
            exit;
        }
    } catch (PDOException $e) {
        echo "DB ERROR: " . $e->getMessage();
        exit;
    }
}
?>

<form method="post" action="">
    <input type="text" name="name" placeholder="Name">
    <input type="email" name="email" placeholder="Email">
    <input type="password" name="password" placeholder="Password">
    <input type="password" name="confirm_password" placeholder="Confirm Password">
    <button type="submit">Submit</button>
</form>