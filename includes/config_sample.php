<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'your_database_username');
define('DB_PASSWORD', 'your_database_password');
define('DB_NAME', 'your_database_name');

// Application configuration
// Ensure BASE_URL ends with a forward slash
define('BASE_URL', 'http://localhost/Timberguard/');

// Paystack configuration
define('PAYSTACK_SECRET_KEY', 'your_paystack_secret_key');
define('PAYSTACK_PUBLIC_KEY', 'your_paystack_public_key');

// Forest manager email
define('FOREST_MANAGER_EMAIL', 'your_email@example.com');

// Site name
define('SITE_NAME', 'TimberGuard');

// Session configuration
session_set_cookie_params([
    'lifetime' => 3600,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

// Error reporting (only in development)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>