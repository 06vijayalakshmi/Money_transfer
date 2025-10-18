<?php
require_once 'config.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        redirect('dashboard.php');
    } else {
        $error = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Money Transfer</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">
    <div class="container">
        <div class="login-card">
            <div class="logo">
                <i class="fas fa-money-bill-wave"></i>
                <h1>MoneyTransfer</h1>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            
            <div class="demo-accounts">
                <h3>Demo Accounts:</h3>
                <p><strong>Username:</strong> john <strong>Password:</strong> password</p>
                <p><strong>Username:</strong> jane <strong>Password:</strong> password</p>
                <p><strong>Username:</strong> mike <strong>Password:</strong> password</p>
            </div>
        </div>
    </div>
    <!-- Include JavaScript -->
<script src="js/script.js"></script>

<!-- Font Awesome for icons -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>