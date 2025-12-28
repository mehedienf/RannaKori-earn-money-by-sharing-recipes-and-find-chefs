<?php
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 0);

require __DIR__ . '/../config/db.php';
session_start();

// Check if user is logged in
if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
    exit;
}

$chefId = (int)($input['chef_id'] ?? 0);
$action = $input['action'] ?? 'follow';
$userId = $_SESSION['user_id'];

// Validate chef_id
if ($chefId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid chef ID format']);
    exit;
}

if ($chefId === $userId) {
    echo json_encode(['success' => false, 'message' => 'Cannot follow yourself']);
    exit;
}

try {
    // Get or create chef entry for the user being followed
    $stmt = $pdo->prepare('SELECT id FROM chefs WHERE user_id = ?');
    $stmt->execute([$chefId]);
    $chef = $stmt->fetch();

    // If not a chef yet, create chef entry
    if (!$chef) {
        $stmt = $pdo->prepare('INSERT INTO chefs (user_id, followers) VALUES (?, 0)');
        $stmt->execute([$chefId]);
        $actualChefId = $pdo->lastInsertId();
    } else {
        $actualChefId = $chef['id'];
    }

    // Check current follow status
    $stmt = $pdo->prepare('SELECT id FROM chef_followers WHERE chef_id = ? AND user_id = ?');
    $stmt->execute([$actualChefId, $userId]);
    $isFollowing = $stmt->fetch();

    if ($isFollowing) {
        // Already following - unfollow
        $stmt = $pdo->prepare('DELETE FROM chef_followers WHERE chef_id = ? AND user_id = ?');
        $stmt->execute([$actualChefId, $userId]);

        // Decrease followers count
        $stmt = $pdo->prepare('UPDATE chefs SET followers = GREATEST(followers - 1, 0) WHERE user_id = ?');
        $stmt->execute([$chefId]);

        echo json_encode([
            'success' => true,
            'message' => 'Unfollowed',
            'action' => 'unfollow'
        ]);
    } else {
        // Not following - follow
        $stmt = $pdo->prepare('INSERT INTO chef_followers (chef_id, user_id) VALUES (?, ?)');
        $stmt->execute([$actualChefId, $userId]);

        // Increase followers count
        $stmt = $pdo->prepare('UPDATE chefs SET followers = followers + 1 WHERE user_id = ?');
        $stmt->execute([$chefId]);

        echo json_encode([
            'success' => true,
            'message' => 'Followed',
            'action' => 'follow'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>

