<?php
/**
 * Header Template
 * Included at the top of every page for consistent navigation
 */

require_once __DIR__ . '/../includes/auth.php';
startSecureSession();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? escape($pageTitle) . ' | ' : ''; ?><?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="top-bar-content">
                <span>Free shipping on orders over $100</span>
                <div class="top-bar-links">
                    <?php if (isLoggedIn()): ?>
                        <span>Welcome, <?php echo escape($_SESSION['username']); ?></span>
                        <?php if (isAdmin()): ?>
                            <a href="<?php echo url('admin/'); ?>">Admin Panel</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="<?php echo url('login.php'); ?>">Login</a>
                        <a href="<?php echo url('register.php'); ?>">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <!-- Logo -->
                <a href="<?php echo url('index.php'); ?>" class="logo">
                    <h1><?php echo SITE_NAME; ?></h1>
                </a>

                <!-- Search Bar -->
                <form action="<?php echo url('products.php'); ?>" method="GET" class="search-form">
                    <input type="text" name="search" placeholder="Search products..."
                           value="<?php echo isset($_GET['search']) ? escape($_GET['search']) : ''; ?>">
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>

                <!-- Header Actions -->
                <div class="header-actions">
                    <?php if (isLoggedIn()): ?>
                        <a href="<?php echo url('profile.php'); ?>" class="header-icon" title="My Account">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </a>
                    <?php endif; ?>
                    <a href="<?php echo url('cart.php'); ?>" class="header-icon cart-icon" title="Shopping Cart">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                        <?php $cartCount = getCartCount(); ?>
                        <?php if ($cartCount > 0): ?>
                            <span class="cart-count"><?php echo $cartCount; ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="main-nav">
        <div class="container">
            <ul class="nav-menu">
                <li><a href="<?php echo url('index.php'); ?>" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Home</a></li>
                <li><a href="<?php echo url('products.php'); ?>" class="<?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">All Products</a></li>
                <?php
                $categories = getCategories();
                foreach (array_slice($categories, 0, 5) as $cat):
                ?>
                    <li><a href="<?php echo url('products.php?category=' . $cat['id']); ?>"><?php echo escape($cat['name']); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </nav>
