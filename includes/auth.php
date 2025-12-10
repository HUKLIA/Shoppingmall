<?php
/**
 * Authentication Functions
 * Handles user registration, login, and session management
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

// =====================================================
// SESSION MANAGEMENT
// =====================================================

/**
 * Start secure session
 */
function startSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // Set secure session parameters
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);

        session_start();
    }
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Require login - redirect to login page if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        setFlash('warning', 'Please log in to continue.');
        redirect(url('login.php?redirect=' . urlencode(currentUrl())));
    }
}

/**
 * Require admin - redirect if not admin
 */
function requireAdmin() {
    if (!isAdmin()) {
        setFlash('error', 'Access denied. Admin privileges required.');
        redirect(url('index.php'));
    }
}

/**
 * Get current user data
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }

    $db = getDB();
    $stmt = $db->prepare("SELECT id, username, email, full_name, phone, address, role, created_at
                          FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// =====================================================
// REGISTRATION
// =====================================================

/**
 * Register new user
 */
function registerUser($data) {
    $db = getDB();

    // Validate required fields
    $errors = [];

    if (empty($data['username'])) {
        $errors[] = 'Username is required';
    } elseif (strlen($data['username']) < 3) {
        $errors[] = 'Username must be at least 3 characters';
    }

    if (empty($data['email'])) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }

    if (empty($data['password'])) {
        $errors[] = 'Password is required';
    } elseif (strlen($data['password']) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    }

    if ($data['password'] !== ($data['confirm_password'] ?? '')) {
        $errors[] = 'Passwords do not match';
    }

    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    // Check if username exists
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$data['username']]);
    if ($stmt->fetch()) {
        return ['success' => false, 'errors' => ['Username already exists']];
    }

    // Check if email exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$data['email']]);
    if ($stmt->fetch()) {
        return ['success' => false, 'errors' => ['Email already registered']];
    }

    // Hash password securely
    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT, ['cost' => HASH_COST]);

    // Insert user
    try {
        $stmt = $db->prepare("INSERT INTO users (username, email, password, full_name, phone, address)
                              VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['username'],
            $data['email'],
            $hashedPassword,
            $data['full_name'] ?? null,
            $data['phone'] ?? null,
            $data['address'] ?? null
        ]);

        return ['success' => true, 'user_id' => $db->lastInsertId()];

    } catch (PDOException $e) {
        error_log("Registration failed: " . $e->getMessage());
        return ['success' => false, 'errors' => ['Registration failed. Please try again.']];
    }
}

// =====================================================
// LOGIN / LOGOUT
// =====================================================

/**
 * Login user
 */
function loginUser($username, $password) {
    $db = getDB();

    // Find user by username or email
    $stmt = $db->prepare("SELECT id, username, email, password, role FROM users
                          WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if (!$user) {
        return ['success' => false, 'error' => 'Invalid username or password'];
    }

    // Verify password
    if (!password_verify($password, $user['password'])) {
        return ['success' => false, 'error' => 'Invalid username or password'];
    }

    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);

    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['login_time'] = time();

    return ['success' => true, 'user' => $user];
}

/**
 * Logout user
 */
function logoutUser() {
    // Clear all session variables
    $_SESSION = [];

    // Delete session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Destroy session
    session_destroy();

    return true;
}

// =====================================================
// PROFILE MANAGEMENT
// =====================================================

/**
 * Update user profile
 */
function updateProfile($userId, $data) {
    $db = getDB();

    $errors = [];

    // Validate email
    if (empty($data['email'])) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }

    // Check if email is taken by another user
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$data['email'], $userId]);
    if ($stmt->fetch()) {
        $errors[] = 'Email is already in use';
    }

    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    try {
        $stmt = $db->prepare("UPDATE users SET email = ?, full_name = ?, phone = ?, address = ?, updated_at = NOW()
                              WHERE id = ?");
        $stmt->execute([
            $data['email'],
            $data['full_name'] ?? null,
            $data['phone'] ?? null,
            $data['address'] ?? null,
            $userId
        ]);

        // Update session email
        $_SESSION['user_email'] = $data['email'];

        return ['success' => true];

    } catch (PDOException $e) {
        error_log("Profile update failed: " . $e->getMessage());
        return ['success' => false, 'errors' => ['Update failed. Please try again.']];
    }
}

/**
 * Change password
 */
function changePassword($userId, $currentPassword, $newPassword, $confirmPassword) {
    $db = getDB();

    $errors = [];

    if (empty($currentPassword)) {
        $errors[] = 'Current password is required';
    }

    if (empty($newPassword)) {
        $errors[] = 'New password is required';
    } elseif (strlen($newPassword) < 6) {
        $errors[] = 'New password must be at least 6 characters';
    }

    if ($newPassword !== $confirmPassword) {
        $errors[] = 'New passwords do not match';
    }

    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    // Get current password hash
    $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($currentPassword, $user['password'])) {
        return ['success' => false, 'errors' => ['Current password is incorrect']];
    }

    // Update password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT, ['cost' => HASH_COST]);

    try {
        $stmt = $db->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$hashedPassword, $userId]);

        return ['success' => true];

    } catch (PDOException $e) {
        error_log("Password change failed: " . $e->getMessage());
        return ['success' => false, 'errors' => ['Password change failed. Please try again.']];
    }
}
