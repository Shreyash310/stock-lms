<?php
/**
 * Stock Market Learning Platform - Configuration
 * 
 * Central configuration file for database credentials,
 * application constants, and environment settings.
 */

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'stock_lms');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application Constants
define('APP_NAME', 'StockVerse');
define('APP_TAGLINE', 'Master the Markets');
define('APP_VERSION', '1.0.0');

// Base URL - adjust if not in root
define('BASE_URL', '/stock-lms');

// Paths
define('ROOT_PATH', __DIR__);
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('ASSETS_PATH', BASE_URL . '/assets');
