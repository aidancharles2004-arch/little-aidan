<?php
include 'db_connect.php';

// Create payments table if it doesn't exist
$create_table_sql = "CREATE TABLE IF NOT EXISTS payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT,
    full_name VARCHAR(255),
    amount DECIMAL(10,2),
    method VARCHAR(50),
    control_number VARCHAR(100) UNIQUE,
    status VARCHAR(20) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_booking_id (booking_id),
    INDEX idx_control_number (control_number),
    INDEX idx_status (status)
)";

if ($conn->query($create_table_sql) === TRUE) {
    echo "Payments table created successfully or already exists.<br>";
} else {
    echo "Error creating payments table: " . $conn->error . "<br>";
}

// Check if table exists and show structure
$check_table = $conn->query("DESCRIBE payments");
if ($check_table) {
    echo "<h3>Payments table structure:</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $check_table->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Error checking table structure: " . $conn->error;
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Database Setup</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Payment System Database Setup</h2>
    <p><a href="payment.php">Go to Payment Page</a></p>
</body>
</html>
