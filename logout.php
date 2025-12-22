<?php
/**
 * Logout Page
 * Handles user logout and session destruction
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

startSecureSession();

// Logout user
logoutUser();

// Set flash message and redirect
setFlash('success', 'You have been logged out successfully.');
redirect(url('index.php'));
