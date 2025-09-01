<?php
echo "<h1>🎉 XAMPP Server is Working!</h1>";
echo "<p><strong>Current Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Server:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";

// Test database connection
include 'connect.php';
if ($conn) {
    echo "<p style='color: green;'>✅ <strong>Database Connection:</strong> SUCCESS</p>";
    
    // Test if Users table exists
    $result = $conn->query("SHOW TABLES LIKE 'Users'");
    if ($result->num_rows > 0) {
        echo "<p style='color: green;'>✅ <strong>Users Table:</strong> EXISTS</p>";
        
        // Count users
        $count_result = $conn->query("SELECT COUNT(*) as count FROM Users");
        $count = $count_result->fetch_assoc()['count'];
        echo "<p style='color: blue;'>👥 <strong>Total Users:</strong> $count</p>";
    } else {
        echo "<p style='color: red;'>❌ <strong>Users Table:</strong> NOT FOUND</p>";
    }
} else {
    echo "<p style='color: red;'>❌ <strong>Database Connection:</strong> FAILED</p>";
}

echo "<hr>";
echo "<h2>🔗 Quick Links:</h2>";
echo "<p><a href='register.php' style='color: blue;'>📝 Registration Page</a></p>";
echo "<p><a href='login.php' style='color: blue;'>🔑 Login Page</a></p>";
echo "<p><a href='index.php' style='color: blue;'>🏠 Home Page</a></p>";

echo "<hr>";
echo "<h2>📁 File Check:</h2>";
$files_to_check = ['register.php', 'login.php', 'connect.php', 'index.php'];
foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✅ $file - EXISTS</p>";
    } else {
        echo "<p style='color: red;'>❌ $file - NOT FOUND</p>";
    }
}

echo "<hr>";
echo "<h2>🌐 URL Examples:</h2>";
echo "<p><code>http://localhost/OHBS/test_server.php</code></p>";
echo "<p><code>http://localhost/OHBS/register.php</code></p>";
echo "<p><code>http://localhost/OHBS/login.php</code></p>";
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 20px auto;
    padding: 20px;
    background: #f5f5f5;
}

h1, h2 {
    color: #333;
}

p {
    margin: 10px 0;
    padding: 8px;
    background: white;
    border-radius: 5px;
}

code {
    background: #e9ecef;
    padding: 4px 8px;
    border-radius: 3px;
    font-family: monospace;
}

a {
    text-decoration: none;
    font-weight: bold;
}

a:hover {
    text-decoration: underline;
}

hr {
    margin: 20px 0;
    border: none;
    border-top: 2px solid #ddd;
}
</style>
