<?php
/**
 * Registration Page
 * New user account creation
 */

$pageTitle = 'Create Account';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

startSecureSession();

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(url('index.php'));
}

$errors = [];
$formData = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $formData = [
            'username' => trim($_POST['username'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
            'full_name' => trim($_POST['full_name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? '')
        ];

        $result = registerUser($formData);

        if ($result['success']) {
            // Auto-login after registration
            loginUser($formData['username'], $formData['password']);

            setFlash('success', 'Account created successfully! Welcome to ' . SITE_NAME . '.');
            redirect(url('index.php'));
        } else {
            $errors = $result['errors'];
        }
    }
}

require_once __DIR__ . '/templates/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1>Create Account</h1>
            <p>Join us and start shopping today</p>
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
                <label for="username" class="form-label">Username *</label>
                <input type="text" id="username" name="username" class="form-control"
                       value="<?php echo escape($formData['username'] ?? ''); ?>"
                       placeholder="Choose a username" required autofocus>
                <div class="form-text">At least 3 characters, letters and numbers only.</div>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email Address *</label>
                <input type="email" id="email" name="email" class="form-control"
                       value="<?php echo escape($formData['email'] ?? ''); ?>"
                       placeholder="your@email.com" required>
            </div>

            <div class="form-group">
                <label for="full_name" class="form-label">Full Name</label>
                <input type="text" id="full_name" name="full_name" class="form-control"
                       value="<?php echo escape($formData['full_name'] ?? ''); ?>"
                       placeholder="Your full name (optional)">
            </div>

            <div class="form-group">
                <label for="phone" class="form-label">Phone Number</label>
                <input type="tel" id="phone" name="phone" class="form-control"
                       value="<?php echo escape($formData['phone'] ?? ''); ?>"
                       placeholder="Your phone number (optional)">
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password *</label>
                <input type="password" id="password" name="password" class="form-control"
                       placeholder="Create a password" required>
                <div class="form-text">At least 6 characters.</div>
            </div>

            <div class="form-group">
                <label for="confirm_password" class="form-label">Confirm Password *</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                       placeholder="Repeat your password" required>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-lg btn-block">Create Account</button>
            </div>
        </form>

        <div class="auth-footer">
            Already have an account? <a href="<?php echo url('login.php'); ?>">Sign in</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
