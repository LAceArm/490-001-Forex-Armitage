<?php
session_start(); // Start session

require_once __DIR__ . '/../broker/broker_client.php'; // Loads send_to_broker()

$error = ''; // Error message; empty means no error

// Runs when form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Bothers DB to check username and password
	$r = send_to_broker([
		'type'     => 'login',
		'username' => $_POST['username'] ?? '',
		'password' => $_POST['password'] ?? '',
	]);

	// Login succeeded; remembers user and redirect to home.php
	if (!empty($r['success'])) {
		$_SESSION['user_id']  = $r['user_id'];
		$_SESSION['username'] = $r['username'];
		$_SESSION['session_key'] = $r['session_key'];
		header('Location: home.php');
		exit;
	}

	// Login failed; error message loaded
	$error = 'Invalid username or password';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login</title>
        <link rel="stylesheet" href="assets/style.css">
</head>
<body>
        <div class="login-box">
                <h1>Login</h1>
                <p class="welcome">Sign in or create an account to continue.</p>
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
