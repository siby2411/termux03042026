<?php
// Minimal test to reproduce the exact index.php logic
session_start();

// Simulate the EXACT index.php code
$module = 'dashboard';
if (isset($_GET['module'])) {
    if (is_array($_GET['module'])) {
        if (count($_GET['module']) > 0 && is_string($_GET['module'][0])) {
            $module = $_GET['module'][0];
        } else {
            $module = 'dashboard';
        }
    } else {
        $module = $_GET['module'];
    }
}
$module = (string)$module;

require_once 'config.php';

// Check if check_login function exists and call it
if (function_exists('check_login')) {
    check_login();
}

// THE PROBLEMATIC LINE THAT WAS IN YOUR ORIGINAL CODE
// Commenting this out should fix the issue
// $module = $_GET['module'] ?? 'dashboard';

echo "<h1>Test Result</h1>";
echo "Module value: ";
var_dump($module);
echo "<br>Type: " . gettype($module);

// Test the error line
echo "<h2>Testing error line</h2>";
try {
    $test_output = htmlspecialchars($module);
    echo "htmlspecialchars succeeded: " . $test_output;
} catch (TypeError $e) {
    echo "ERROR: " . $e->getMessage();
}
