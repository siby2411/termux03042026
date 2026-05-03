<?php
echo "<h1>Module Parameter Test</h1>";

// Simulate what happens in index.php
$module = 'dashboard';
if (isset($_GET['module'])) {
    $raw = $_GET['module'];
    if (is_array($raw)) {
        echo "<p style='color:red'>Module is an ARRAY!</p>";
        echo "<pre>Array contents: ";
        print_r($raw);
        echo "</pre>";
        $module = (is_array($raw) && count($raw) > 0) ? (string)$raw[0] : 'dashboard';
    } else {
        $module = (string)$raw;
    }
}

echo "<p>Final module value: <strong>" . htmlspecialchars($module) . "</strong></p>";
echo "<p>Module type: " . gettype($module) . "</p>";

// Test file existence
$page_file = "pages/$module.php";
echo "<p>Looking for: $page_file</p>";
echo "<p>Exists: " . (file_exists($page_file) ? 'YES' : 'NO') . "</p>";

echo "<hr>";
echo "<h2>Available pages:</h2>";
$pages = glob('pages/*.php');
foreach ($pages as $page) {
    $name = basename($page, '.php');
    echo "<a href='test_module.php?module=$name'>$name</a><br>";
}
