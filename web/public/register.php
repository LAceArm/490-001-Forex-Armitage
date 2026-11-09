
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
    <title>Register | Forex Armitage</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light d-flex justify-content-center align-items-center min-vh-100">

    <div class="card shadow-sm border-0 rounded-4 p-3 mx-3 my-4 w-100" style="max-width: 440px;">

        <div class="card-body">

            <h1 class="text-center fw-bold fs-3 mb-2">Register</h1>

            <p class="text-center text-secondary fs-6 mb-4">
                Create your account.
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
                        placeholder="Choose a username"
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold mb-2">
                        Email
                    </label>

                    <input
                        type="email"
                        class="form-control"
                        id="email"
                        name="email"
                        placeholder="Enter your email"
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
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
                        placeholder="Choose a password"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label for="confirm_password" class="form-label fw-semibold mb-2">
                        Confirm Password
                    </label>

                    <input
                        type="password"
                        class="form-control"
                        id="confirm_password"
                        name="confirm_password"
                        placeholder="Confirm your password"
                        required
                    >
                </div>

                <button class="btn btn-primary w-100 py-2 fw-semibold mt-2" type="submit">
                    Register
                </button>

            </form>

            <div class="text-center border-top mt-3 pt-3">

                <p class="text-secondary mb-2">
                    Already have an account?
                </p>

                <button
                    class="btn btn-outline-secondary w-100 py-2 fw-semibold"
                    onclick="location.href='./login.php'"
                    type="button"
                >
                    Back to Login
                </button>

            </div>

        </div>

    </div>

</body>
</html>