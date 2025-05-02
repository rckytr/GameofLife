<?php
session_start();

// Only allow admins
if (!isset($_SESSION['admin'])) {
    header('Location: admin_login.php');
    exit();
}

// Database connection
$host = 'localhost';
$db = 'mpham27';
$user = 'mpham27';
$pass = 'mpham27';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}

// Handle suspend/unsuspend user
if (isset($_GET['suspend_id'])) {
    $userId = (int)$_GET['suspend_id'];
    $stmt = $pdo->prepare("UPDATE users SET suspended = 1 WHERE id = ?");
    $stmt->execute([$userId]);
    header('Location: admin_dashboard.php');
    exit();
}

if (isset($_GET['unsuspend_id'])) {
    $userId = (int)$_GET['unsuspend_id'];
    $stmt = $pdo->prepare("UPDATE users SET suspended = 0 WHERE id = ?");
    $stmt->execute([$userId]);
    header('Location: admin_dashboard.php');
    exit();
}

// Fetch stats
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalGames = $pdo->query("SELECT COUNT(*) FROM game_sessions")->fetchColumn();

$topUsersStmt = $pdo->query("
    SELECT u.username, COUNT(g.session_id) as games_played
    FROM users u
    LEFT JOIN game_sessions g ON u.id = g.user_id
    GROUP BY u.id
    ORDER BY games_played DESC
    LIMIT 5
");
$topUsers = $topUsersStmt->fetchAll(PDO::FETCH_ASSOC);

$allUsersStmt = $pdo->query("SELECT id, username, suspended FROM users");
$allUsers = $allUsersStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    body { font-family: Arial; padding: 20px; background: #f0f0f0; }
    h1 { color: #333; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
    th { background: #eee; }
    .btn { padding: 5px 10px; text-decoration: none; border-radius: 4px; }
    .suspend { background: #ff4d4d; color: white; }
    .unsuspend { background: #4CAF50; color: white; }
</style>
</head>
<body>

<h1>Admin Dashboard</h1>
<p><a href="admin_login.php">Logout</a></p>

<h2>Quick Stats</h2>
<ul>
    <li><strong>Total Users:</strong> <?php echo $totalUsers; ?></li>
    <li><strong>Total Games Played:</strong> <?php echo $totalGames; ?></li>
</ul>

<h2>Top Users (by Games Played)</h2>
<canvas id="topUsersChart" width="400" height="200"></canvas>

<script>
const ctx = document.getElementById('topUsersChart').getContext('2d');
const chart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: [<?php foreach ($topUsers as $u) { echo "'" . $u['username'] . "',"; } ?>],
        datasets: [{
            label: 'Games Played',
            data: [<?php foreach ($topUsers as $u) { echo $u['games_played'] . ","; } ?>],
            backgroundColor: 'rgba(54, 162, 235, 0.7)'
        }]
    }
});
</script>

<h2>Manage Users</h2>
<table>
    <tr>
        <th>ID</th>
        <th>Username</th>
        <th>Suspended</th>
        <th>Action</th>
    </tr>
    <?php foreach ($allUsers as $user): ?>
    <tr>
        <td><?php echo $user['id']; ?></td>
        <td><?php echo htmlspecialchars($user['username']); ?></td>
        <td><?php echo $user['suspended'] ? 'Yes' : 'No'; ?></td>
        <td>
            <?php if ($user['suspended']): ?>
                <a class="btn unsuspend" href="?unsuspend_id=<?php echo $user['id']; ?>">Unsuspend</a>
            <?php else: ?>
                <a class="btn suspend" href="?suspend_id=<?php echo $user['id']; ?>">Suspend</a>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
