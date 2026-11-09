
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
    <title>Login | Forex Armitage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light d-flex justify-content-center align-items-center min-vh-100">

    <div class="card shadow-sm border-0 rounded-4 p-3 mx-3" style="width: 100%; max-width: 440px;">

        <div class="card-body">

            <h1 class="text-center fw-bold fs-3 mb-2">Login</h1>

            <p class="text-center text-secondary fs-6 mb-4">
                Sign in or create an account to continue.
            </p>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <div class="mb-3">
                    <label for="username" class="form-label fw-semibold mb-2">
                        Username
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="username"
                        name="username"
                        placeholder="Enter your username"
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold mb-2">
                        Password
                    </label>

                    <input
                        type="password"
                        class="form-control"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                    >
                </div>

                <button
                    class="btn btn-primary w-100 py-2 fw-semibold mt-2"
                    type="submit"
                >
                    Login
                </button>

            </form>

            <div class="text-center border-top mt-3 pt-3">

                <p class="text-secondary mb-2">
                    Don't have an account?
                </p>

                <button
                    class="btn btn-outline-secondary w-100 py-2 fw-semibold"
                    onclick="location.href='./register.php'"
                    type="button"
                >
                    Create an account
                </button>

            </div>

        </div>

    </div>

</body>
</html>