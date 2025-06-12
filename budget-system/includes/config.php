<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'budget_planner');

// Site configuration
define('SITE_URL', 'http://localhost/budget-system');
define('SITE_NAME', 'Budget Planner');

// Start session
session_start();

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>