<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/config/db.php';
session_start();

// Admin check
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if user is admin
$stmt = $pdo->prepare('SELECT role FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['role'] !== 'admin') {
    echo "❌ You do not have permission to access this page.";
    exit;
}

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $targetUserId = $_POST['user_id'] ?? '';

    if (!$targetUserId) {
        echo "Invalid user ID";
        exit;
    }

    try {
        if ($action === 'ban') {
            $stmt = $pdo->prepare('UPDATE users SET status = "banned" WHERE id = ?');
            $stmt->execute([$targetUserId]);
        } elseif ($action === 'unban') {
            $stmt = $pdo->prepare('UPDATE users SET status = "active" WHERE id = ?');
            $stmt->execute([$targetUserId]);
        } elseif ($action === 'make_admin') {
            $stmt = $pdo->prepare('UPDATE users SET role = "admin" WHERE id = ?');
            $stmt->execute([$targetUserId]);
        } elseif ($action === 'remove_admin') {
            // Can't remove yourself as admin
            if ($targetUserId != $_SESSION['user_id']) {
                $stmt = $pdo->prepare('UPDATE users SET role = "user" WHERE id = ?');
                $stmt->execute([$targetUserId]);
            }
        } elseif ($action === 'delete') {
            // Can't delete yourself
            if ($targetUserId != $_SESSION['user_id']) {
                // Delete user's recipes, reviews, likes first (foreign keys)
                $stmt = $pdo->prepare('DELETE FROM recipes WHERE user_id = ?');
                $stmt->execute([$targetUserId]);
                
                $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
                $stmt->execute([$targetUserId]);
            }
        }

        header('Location: admin-users.php');
        exit;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Get filters
$roleFilter = $_GET['role'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Build query
$query = 'SELECT id, name, email, role, status, created_at, points FROM users WHERE 1=1';
$params = [];

if ($roleFilter) {
    $query .= ' AND role = ?';
    $params[] = $roleFilter;
}



if ($search) {
    $query .= ' AND (name LIKE ? OR email LIKE ?)';
    $searchTerm = '%' . $search . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

// Get total count for pagination
$countQuery = str_replace('SELECT id, name, email, role, status, created_at, points FROM users', 'SELECT COUNT(*) as count FROM users', $query);
$stmt = $pdo->prepare($countQuery);
$stmt->execute($params);
$totalUsers = $stmt->fetch()['count'];
$totalPages = ceil($totalUsers / $perPage);

// Get statistics
try {
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM users');
    $allUsers = $stmt->fetch()['count'];

    $stmt = $pdo->query('SELECT COUNT(*) as count FROM users WHERE status = "active"');
    $activeUsers = $stmt->fetch()['count'];

    $stmt = $pdo->query('SELECT COUNT(*) as count FROM users WHERE status = "banned"');
    $bannedUsers = $stmt->fetch()['count'];

    $stmt = $pdo->query('SELECT COUNT(*) as count FROM users WHERE role = "admin"');
    $adminCount = $stmt->fetch()['count'];
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Get paginated users
$query .= ' ORDER BY created_at DESC LIMIT ? OFFSET ?';
$params[] = $perPage;
$params[] = $offset;

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<main>
    <section class="container" style="max-width: 1200px; margin: 40px auto;">
        <h1>👥 Manage Users</h1>

        <!-- Statistics -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 32px;">
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px;">
                <div style="font-size: 1.8rem; font-weight: bold;"><?php echo $allUsers; ?></div>
                <div style="font-size: 0.9rem; opacity: 0.9;">Total Users</div>
            </div>
            <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 20px; border-radius: 8px;">
                <div style="font-size: 1.8rem; font-weight: bold;"><?php echo $activeUsers; ?></div>
                <div style="font-size: 0.9rem; opacity: 0.9;">Active Users</div>
            </div>
            <div style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; padding: 20px; border-radius: 8px;">
                <div style="font-size: 1.8rem; font-weight: bold;"><?php echo $bannedUsers; ?></div>
                <div style="font-size: 0.9rem; opacity: 0.9;">Banned Users</div>
            </div>
            <div style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%); color: white; padding: 20px; border-radius: 8px;">
                <div style="font-size: 1.8rem; font-weight: bold;"><?php echo $adminCount; ?></div>
                <div style="font-size: 0.9rem; opacity: 0.9;">Admin Count</div>
            </div>
        </div>

        <!-- Filters -->
        <div style="background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); padding: 20px; border-radius: 8px; margin-bottom: 24px;">
            <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
                <input type="text" name="search" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>" style="padding: 10px; border: 1px solid #ddd; border-radius: 4px;">

                <select name="role" style="padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="">All Roles</option>
                    <option value="user" <?php echo $roleFilter === 'user' ? 'selected' : ''; ?>>User</option>
                    <option value="admin" <?php echo $roleFilter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                </select>

                <select name="status" style="padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="banned" <?php echo $statusFilter === 'banned' ? 'selected' : ''; ?>>Banned</option>
                </select>

                <button type="submit" style="padding: 10px 20px; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;">
                    <i class="fas fa-search"></i> Filter
                </button>
                <a href="admin-users.php" style="padding: 10px 20px; background: #999; color: white; border: none; border-radius: 4px; text-align: center; text-decoration: none;">
                    <i class="fas fa-redo"></i> Reset
                </a>
            </form>
        </div>

        <!-- Users Table -->
        <div style="background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background: #f5f5f5; border-bottom: 2px solid #ddd;">
                    <tr>
                        <th style="padding: 16px; text-align: left; font-weight: 600;">ID</th>
                        <th style="padding: 16px; text-align: left; font-weight: 600;">Name</th>
                        <th style="padding: 16px; text-align: left; font-weight: 600;">Email</th>
                        <th style="padding: 16px; text-align: left; font-weight: 600;">Role</th>
                        <th style="padding: 16px; text-align: left; font-weight: 600;">Status</th>
                        <th style="padding: 16px; text-align: left; font-weight: 600;">Points</th>
                        <th style="padding: 16px; text-align: left; font-weight: 600;">Joined</th>
                        <th style="padding: 16px; text-align: left; font-weight: 600;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($users) > 0): ?>
                        <?php foreach ($users as $u): ?>
                            <tr style="border-bottom: 1px solid #eee; ">
                                <td style="padding: 16px;">#<?php echo $u['id']; ?></td>
                                <td style="padding: 16px;">
                                    <strong><?php echo htmlspecialchars($u['name']); ?></strong>
                                </td>
                                <td style="padding: 16px;"><?php echo htmlspecialchars($u['email']); ?></td>
                                <td style="padding: 16px;">
                                    <span style="display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; background: <?php echo $u['role'] === 'admin' ? '#ff9800' : '#2196f3'; ?>; color: white;">
                                        <?php echo ucfirst($u['role']); ?>
                                    </span>
                                </td>
                                <td style="padding: 16px;">
                                    <span style="display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; background: <?php echo $u['status'] === 'active' ? '#4CAF50' : '#f44336'; ?>; color: white;">
                                        <?php echo ucfirst($u['status']); ?>
                                    </span>
                                </td>
                                <td style="padding: 16px;">
                                    ⭐ <?php echo $u['points']; ?>
                                </td>
                                <td style="padding: 16px; font-size: 0.85rem; color: #666;">
                                    <?php echo date('M d, Y', strtotime($u['created_at'])); ?>
                                </td>
                                <td style="padding: 16px;">
                                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                        <!-- View Profile -->
                                        <a href="profile.php?user_id=<?php echo $u['id']; ?>" style="padding: 6px 12px; font-size: 0.8rem; background: #2196f3; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block;">
                                            👁️ View
                                        </a>

                                        <!-- Ban/Unban -->
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                            <input type="hidden" name="action" value="<?php echo $u['status'] === 'active' ? 'ban' : 'unban'; ?>">
                                            <button type="submit" style="padding: 6px 12px; font-size: 0.8rem; background: <?php echo $u['status'] === 'active' ? '#f44336' : '#4CAF50'; ?>; color: white; border: none; border-radius: 4px; cursor: pointer;"
                                                onclick="return confirm('<?php echo $u['status'] === 'active' ? 'Ban this user?' : 'Unban this user?'; ?>')">
                                                <?php echo $u['status'] === 'active' ? '🚫 Ban' : '✅ Unban'; ?>
                                            </button>
                                        </form>

                                        <!-- Make/Remove Admin -->
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                            <input type="hidden" name="action" value="<?php echo $u['role'] === 'admin' ? 'remove_admin' : 'make_admin'; ?>">
                                            <button type="submit" style="padding: 6px 12px; font-size: 0.8rem; background: <?php echo $u['role'] === 'admin' ? '#999' : '#ff9800'; ?>; color: white; border: none; border-radius: 4px; cursor: pointer;"
                                                onclick="return confirm('<?php echo $u['role'] === 'admin' ? 'Remove admin privileges?' : 'Make admin?'; ?>')"
                                                <?php echo $_SESSION['user_id'] == $u['id'] ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>
                                                <?php echo $u['role'] === 'admin' ? '👑 Remove' : '👑 Make Admin'; ?>
                                            </button>
                                        </form>

                                        <!-- Delete -->
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" style="padding: 6px 12px; font-size: 0.8rem; background: #666; color: white; border: none; border-radius: 4px; cursor: pointer;"
                                                onclick="return confirm('⚠️ This will delete the user and all their data. Are you sure?')"
                                                <?php echo $_SESSION['user_id'] == $u['id'] ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>
                                                🗑️ Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="padding: 32px; text-align: center; color: #999;">
                                No users found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div style="display: flex; justify-content: center; gap: 8px; margin-top: 24px; flex-wrap: wrap;">
                <?php if ($page > 1): ?>
                    <a href="?page=1<?php echo $roleFilter ? '&role=' . $roleFilter : ''; ?><?php echo $statusFilter ? '&status=' . $statusFilter : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" style="padding: 8px 12px; background: #2196F3; color: white; text-decoration: none; border-radius: 4px;">
                        First
                    </a>
                    <a href="?page=<?php echo $page - 1; ?><?php echo $roleFilter ? '&role=' . $roleFilter : ''; ?><?php echo $statusFilter ? '&status=' . $statusFilter : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" style="padding: 8px 12px; background: #2196F3; color: white; text-decoration: none; border-radius: 4px;">
                        Previous
                    </a>
                <?php endif; ?>

                <span style="padding: 8px 12px;">
                    Page <strong><?php echo $page; ?></strong> of <strong><?php echo $totalPages; ?></strong>
                </span>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo $roleFilter ? '&role=' . $roleFilter : ''; ?><?php echo $statusFilter ? '&status=' . $statusFilter : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" style="padding: 8px 12px; background: #2196F3; color: white; text-decoration: none; border-radius: 4px;">
                        Next
                    </a>
                    <a href="?page=<?php echo $totalPages; ?><?php echo $roleFilter ? '&role=' . $roleFilter : ''; ?><?php echo $statusFilter ? '&status=' . $statusFilter : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" style="padding: 8px 12px; background: #2196F3; color: white; text-decoration: none; border-radius: 4px;">
                        Last
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
