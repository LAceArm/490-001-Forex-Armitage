<?php
session_start();

require_once __DIR__ . '/../lib/broker_client.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$r = send_to_broker([
		'type'     => 'login',
		'username' => $_POST['username'] ?? '',
		'password' => $_POST['password'] ?? '',
	]);

	if (!empty($r['success'])) {
		$_SESSION['user_id']  = $r['user_id'];
		$_SESSION['username'] = $r['username'];
		header('Location: home.php');
		exit;
	}

	$error = 'Invalid username or password';
}
?>

<!DOCTYPE html>
<html>
<head>
        <title>Login</title>
        <link rel="stylesheet" href="assets/style.css">
</head>
<body>
        <div class="login-box">
                <h1>Login</h1>
                <p class="welcome">Welcome back! Please sign in to continue.</p>
                <?php if ($error): ?>
                        <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form method="POST">
                        <label for="username">Username</label>
                        <input
                                type="text"
                                id="username"
                                name="username"
                                placeholder="Enter your username"
                                value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                                required
                        >
                        <label for="password">Password</label>
                        <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Enter your password"
                                required
                        >
                        <button class="login-button" type="submit">
                                Login
                        </button>
                </form>
                <div class="register-section">
                        <p>Don't have an account?</p>
                        <button
                                class="register-button"
                                onclick="location.href='./register.php'"
                                type="button"
                        >
                                Create an account
                        </button>
                </div>
        </div>
</body>
</html>
