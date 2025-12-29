<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/config/db.php';
session_start();

// Fake login (for testing)
if (empty($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // Your user ID
    $_SESSION['user_name'] = 'Test User';
}

$recipe_id = 2; // Your valid recipe ID

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2 style='color: green;'>POST RECEIVED!</h2>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    $rating = (int)($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    
    echo "<p>Rating: $rating</p>";
    echo "<p>Comment: $comment</p>";
    echo "<p>Recipe ID: $recipe_id</p>";
    echo "<p>User ID: " . $_SESSION['user_id'] . "</p>";
    
    if ($rating > 0) {
        try {
            $stmt = $pdo->prepare('INSERT INTO reviews (recipe_id, user_id, rating, comment) VALUES (?, ?, ?, ?)');
            $ok = $stmt->execute([$recipe_id, $_SESSION['user_id'], $rating, $comment]);
            
            if ($ok) {
                echo "<h3 style='color: blue;'>DATABASE INSERT SUCCESS!</h3>";
                
                // Check if insert was successful
                $stmt = $pdo->query('SELECT * FROM reviews ORDER BY id DESC LIMIT 1');
                $lastReview = $stmt->fetch();
                echo "<pre>";
                print_r($lastReview);
                echo "</pre>";
            } else {
                echo "<h3 style='color: red;'>INSERT FAILED</h3>";
            }
        } catch (PDOException $e) {
            echo "<h3 style='color: red;'>ERROR: " . $e->getMessage() . "</h3>";
        }
    }
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Review Test</title>
</head>
<body>
    <h1>Simple Review Test</h1>
    
    <form method="post" action="">
        <div>
            <label>Rating:</label><br>
            <select name="rating" required>
                <option value="">-- Select --</option>
                <option value="5">5 Stars</option>
                <option value="4">4 Stars</option>
                <option value="3">3 Stars</option>
                <option value="2">2 Stars</option>
                <option value="1">1 Star</option>
            </select>
        </div>
        <br>
        <div>
            <label>Comment:</label><br>
            <textarea name="comment" rows="3"></textarea>
        </div>
        <br>
        <button type="submit">SUBMIT TEST</button>
    </form>
</body>
</html>