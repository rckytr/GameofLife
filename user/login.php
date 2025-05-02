<?php
session_start();

// Database connection settings
$host = 'localhost';
$db = 'mpham27'; // Replace with your DB name
$user = 'mpham27';   // Replace with your DB username
$pass = 'mpham27';       // Replace with your DB password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$error = '';
$success = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Username and Password cannot be empty.';
    } else {
        if ($action === 'signup') {
            // Check if the username already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error = 'Username already exists.';
            } else {
                // Hash password and insert user into database
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                $stmt->execute([$username, $hashedPassword]);
                $success = 'Sign Up successful! You can now log in.';
            }
        } elseif ($action === 'login') {
            // Verify login credentials
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$user || !password_verify($password, $user['password'])) {
                $error = 'Invalid username or password.';
            } else {
                // Start session and redirect
                $_SESSION['user'] = $username;
                header('Location: auth.php');
                exit();
            }
        }
    }
}

// Handle Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: auth.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>PHP Login & Sign Up (MySQL)</title>
<style>
    body { font-family: Arial, sans-serif; background: #f0f0f0; padding: 50px; }
    form { background: #fff; padding: 20px; border-radius: 5px; max-width: 300px; margin: auto; }
    input { width: 100%; padding: 10px; margin: 5px 0; }
    button { width: 100%; padding: 10px; }
    .msg { color: green; }
    .error { color: red; }
</style>
</head>
<body>

<?php if (isset($_SESSION['user'])): ?>
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user']); ?>!</h2>
    <p><a href="?logout=1">Logout</a></p>
<?php else: ?>

    <?php if ($error): ?><p class="error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
    <?php if ($success): ?><p class="msg"><?php echo htmlspecialchars($success); ?></p><?php endif; ?>

    <form method="POST">
        <h2>Login / Sign Up</h2>
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>

        <button type="submit" name="action" value="login">Login</button>
        <button type="submit" name="action" value="signup">Sign Up</button>
    </form>


<?php endif; ?>

<a href="admin/admin_login.php" class="btn btn-primary">ADMIN</a>

</body>
</html>
