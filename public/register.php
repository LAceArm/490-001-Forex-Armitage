<?php
session_start();
require_once __DIR__ . '/../broker/broker_client.php';

$error = '';
$regex="/(?=(.*[0-9]))((?=.*[A-Za-z0-9])(?=.*[A-Z])(?=.*[a-z]))^.{8,}$/";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';
	$compliant_password=preg_match($regex,$password);

        if ($password !== $confirm) {
                $error = 'Passwords do not match';
	}
	else if (!$compliant_password){ 
		$error="Password needs at least one special character and one number";
	}
	else {
                $r = send_to_broker([
                        'type'     => 'register',
                        'username' => $username,
                        'email'    => $email,
                        'password' => $password,
                ]);
                if (!empty($r['success'])) {
                        header('Location: login.php');
                        exit;
                }
                $error = 'Registration failed (username or email may be taken)';
        }
}
?>
<!DOCTYPE html>
<html>
<head>
        <title>Register</title>
        <link rel="stylesheet" href="assets/style.css">
</head>
<body>
        <div class="login-box">
                <h1>Register</h1>
                <p class="welcome">Create your account.</p>
                <?php if ($error): ?>
                        <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form method="POST">
                        <label for="username">Username</label>
                        <input
                                type="text"
                                id="username"
                                name="username"
                                placeholder="Choose a username"
                                value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                                required
                        >
                        <label for="email">Email</label>
                        <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="Enter your email"
                                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                required
                        >
                        <label for="password">Password</label>
                        <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Choose a password"
                                required
                        >
                        <label for="confirm_password">Confirm Password</label>
                        <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                placeholder="Confirm your password"
                                required
                        >
                        <button class="login-button" type="submit">
                                Register
                        </button>
                </form>
                <div class="register-section">
                        <p>Already have an account?</p>
                        <button
                                 class="register-button"
                                 onclick="window.location.href='login.php'"
                                 type="button"
                                >
                                  Back to Login
                                </button>
                </div>
        </div>
</body>
</html>
