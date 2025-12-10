<?php
/**
 * Login Page
 * User authentication with session management
 */

$pageTitle = 'Login';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

startSecureSession();

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(url('index.php'));
}

$errors = [];
$username = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username)) {
            $errors[] = 'Username or email is required';
        }
        if (empty($password)) {
            $errors[] = 'Password is required';
        }

        if (empty($errors)) {
            $result = loginUser($username, $password);

            if ($result['success']) {
                setFlash('success', 'Welcome back, ' . escape($result['user']['username']) . '!');

                // Redirect to intended page or homepage
                $redirect = $_GET['redirect'] ?? url('index.php');
                redirect($redirect);
            } else {
                $errors[] = $result['error'];
            }
        }
    }
}

require_once __DIR__ . '/templates/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1>Welcome Back</h1>
            <p>Sign in to your account to continue</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 1.25rem;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo escape($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <?php echo csrfField(); ?>

            <div class="form-group">
                <label for="username" class="form-label">Username or Email</label>
                <input type="text" id="username" name="username" class="form-control"
                       value="<?php echo escape($username); ?>"
                       placeholder="Enter your username or email" required autofocus>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control"
                       placeholder="Enter your password" required>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-lg btn-block">Sign In</button>
            </div>
        </form>

        <div class="auth-footer">
            Don't have an account? <a href="<?php echo url('register.php'); ?>">Create one</a>
        </div>
    </div>

    <!-- Demo Credentials -->
    <div class="card mt-2">
        <div class="card-body text-center">
            <p class="text-muted" style="font-size: 0.875rem; margin-bottom: 0.5rem;">Demo Admin Account:</p>
            <code style="font-size: 0.875rem;">admin / admin123</code>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
