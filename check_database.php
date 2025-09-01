<?php
include 'db_connect.php';

echo "<h2>Database Structure Check</h2>";

// Check Users table structure
echo "<h3>Users Table Structure:</h3>";
$users_structure = $conn->query("DESCRIBE Users");
if ($users_structure) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $users_structure->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Error: " . $conn->error;
}

// Check if Users table has data
echo "<h3>Users Table Data:</h3>";
$users_data = $conn->query("SELECT user_id, username, full_name, email, role FROM Users LIMIT 10");
if ($users_data && $users_data->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Username</th><th>Full Name</th><th>Email</th><th>Role</th></tr>";
    while ($row = $users_data->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['user_id']}</td>";
        echo "<td>{$row['username']}</td>";
        echo "<td>{$row['full_name']}</td>";
        echo "<td>{$row['email']}</td>";
        echo "<td>{$row['role']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No users found or error: " . $conn->error;
}

// Check Payments table structure
echo "<h3>Payments Table Structure:</h3>";
$payments_check = $conn->query("SHOW TABLES LIKE 'payments'");
if ($payments_check && $payments_check->num_rows > 0) {
    $payments_structure = $conn->query("DESCRIBE payments");
    if ($payments_structure) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        while ($row = $payments_structure->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['Field']}</td>";
            echo "<td>{$row['Type']}</td>";
            echo "<td>{$row['Null']}</td>";
            echo "<td>{$row['Key']}</td>";
            echo "<td>{$row['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "Payments table does not exist.";
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Database Check</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        h2, h3 { color: #333; }
    </style>
</head>
<body>
    <p><a href="register.php">Register Page</a> | <a href="payment_simple.php">Payment Page</a></p>
</body>
</html>
