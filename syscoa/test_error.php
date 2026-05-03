<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test different scenarios
echo "<h1>Error Isolation Test</h1>";

// Test 1: Direct htmlspecialchars with array
echo "<h2>Test 1: Direct htmlspecialchars with array</h2>";
try {
    $test = ['dashboard', 'parametres'];
    $result = htmlspecialchars($test);
    echo "Result: " . $result;
} catch (TypeError $e) {
    echo "ERROR: " . $e->getMessage();
}

// Test 2: What happens in the actual code
echo "<h2>Test 2: Simulate index.php logic</h2>";
$_GET['module'] = ['dashboard']; // Simulate array parameter

$module = 'dashboard';
if (isset($_GET['module'])) {
    $raw = $_GET['module'];
    echo "Raw type: " . gettype($raw) . "<br>";
    echo "Raw value: ";
    var_dump($raw);
    echo "<br>";
    
    if (is_array($raw)) {
        echo "Is array! Count: " . count($raw) . "<br>";
        if (count($raw) > 0) {
            $module = $raw[0];
            echo "Using first element: $module<br>";
        }
    } else {
        $module = $raw;
    }
}

echo "Final module: $module (type: " . gettype($module) . ")<br>";

// Test htmlspecialchars
echo "htmlspecialchars test: " . htmlspecialchars($module) . "<br>";
