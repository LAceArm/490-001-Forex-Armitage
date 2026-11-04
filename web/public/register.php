<?php
require_once __DIR__ . '/../broker/broker_client.php'; // Loads send_to_broker()

$error = ''; // Error message; empty means no error

// 8+ characters, 1 number, an uppercase and a lowercase letter, and a symbol
$regex = "/(?=.*[0-9])(?=.*[A-Z])(?=.*[a-z])(?=.*[^A-Za-z0-9]).{8,}/";

// Runs when form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        // Run password checks
        if ($password !== $confirm) {
                $error = 'Passwords do not match';
        } else if (!preg_match($regex, $password)) {
                $error = "Password needs at least 8 characters, 1 number, an uppercase and a lowercase letter, and a symbol";
        } else {
                // Bothers DB to create account
                $r = send_to_broker([
                        'type'     => 'register',
                        'username' => $username,
                        'email'    => $email,
                        'password' => $password,
                ]);

                // Registration succeeded; redirect to login.php
                if (!empty($r['success'])) {
                        header('Location: login.php');
                        exit;
                }

                // Registration failed; error message loaded
                $error = "Registration failed (username or email may be taken)";
        }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                                onclick="location.href='./login.php'" 
                                type="button"
                        >
                                Back to Login
                        </button>
                </div>
        </div>
</body>
</html>
