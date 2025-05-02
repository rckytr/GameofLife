<?php
session_start();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['username'] === 'admin' && $_POST['password'] === 'admin123') {  // Change later!
        $_SESSION['admin'] = 'admin';
        header('Location: admin_dashboard.php');
        exit();
    } else {
        $error = "Invalid credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f0f0; padding: 50px; }
        form { background: #fff; padding: 20px; border-radius: 5px; max-width: 300px; margin: auto; }
        input, button { width: 100%; padding: 10px; margin: 5px 0; }
        .error { color: red; }
    </style>
</head>
<body>

<form method="POST">
    <h2>Admin Login</h2>
    <input name="username" placeholder="Admin Username" required><br>
    <input name="password" type="password" placeholder="Password" required><br>
    <button type="submit">Login</button><br>
    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
</form>

</body>
</html>

