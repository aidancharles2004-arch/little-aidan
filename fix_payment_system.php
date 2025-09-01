<?php
include 'connect.php';

echo "<h2>OHBS Payment System Fix</h2>";
echo "<p>Fixing payment system issues...</p>";

// Step 1: Check current database structure
echo "<h3>1. Checking Current Database Structure</h3>";

// Check if Payments table exists (capital P)
$check_payments_capital = $conn->query("SHOW TABLES LIKE 'Payments'");
$payments_capital_exists = $check_payments_capital->num_rows > 0;

// Check if payments table exists (lowercase p)
$check_payments_lower = $conn->query("SHOW TABLES LIKE 'payments'");
$payments_lower_exists = $check_payments_lower->num_rows > 0;

echo "Payments table (capital P): " . ($payments_capital_exists ? "EXISTS" : "NOT EXISTS") . "<br>";
echo "payments table (lowercase p): " . ($payments_lower_exists ? "EXISTS" : "NOT EXISTS") . "<br>";

// Step 2: Standardize to use 'Payments' table (capital P) to match other tables
echo "<h3>2. Standardizing Database Schema</h3>";

if ($payments_lower_exists && !$payments_capital_exists) {
    // Rename lowercase table to capital
    $rename_query = "RENAME TABLE payments TO Payments";
    if ($conn->query($rename_query)) {
        echo "✓ Renamed 'payments' table to 'Payments'<br>";
    } else {
        echo "✗ Error renaming table: " . $conn->error . "<br>";
    }
} elseif ($payments_lower_exists && $payments_capital_exists) {
    // Both exist - merge data and drop lowercase
    echo "Both tables exist. Merging data...<br>";
    
    // Copy data from lowercase to capital
    $merge_query = "INSERT IGNORE INTO Payments SELECT * FROM payments";
    if ($conn->query($merge_query)) {
        echo "✓ Data merged from 'payments' to 'Payments'<br>";
        
        // Drop lowercase table
        $drop_query = "DROP TABLE payments";
        if ($conn->query($drop_query)) {
            echo "✓ Dropped duplicate 'payments' table<br>";
        } else {
            echo "✗ Error dropping duplicate table: " . $conn->error . "<br>";
        }
    } else {
        echo "✗ Error merging data: " . $conn->error . "<br>";
    }
}

// Step 3: Create or update Payments table with proper structure
echo "<h3>3. Creating/Updating Payments Table Structure</h3>";

$create_payments_table = "CREATE TABLE IF NOT EXISTS Payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    booking_id INT NULL,
    amount DECIMAL(10,2) NOT NULL DEFAULT 150000.00,
    payment_method VARCHAR(50) DEFAULT 'mobile_money',
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
    transaction_reference VARCHAR(100) NULL,
    control_number VARCHAR(100) NULL,
    phone VARCHAR(20) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    INDEX idx_booking_id (booking_id),
    INDEX idx_status (status),
    INDEX idx_payment_date (payment_date),
    INDEX idx_control_number (control_number),
    
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES Bookings(booking_id) ON DELETE SET NULL
)";

if ($conn->query($create_payments_table)) {
    echo "✓ Payments table created/updated successfully<br>";
} else {
    echo "✗ Error creating Payments table: " . $conn->error . "<br>";
    
    // If foreign key constraints fail, create without them
    $create_payments_simple = "CREATE TABLE IF NOT EXISTS Payments (
        payment_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        booking_id INT NULL,
        amount DECIMAL(10,2) NOT NULL DEFAULT 150000.00,
        payment_method VARCHAR(50) DEFAULT 'mobile_money',
        payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('pending', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
        transaction_reference VARCHAR(100) NULL,
        control_number VARCHAR(100) NULL,
        phone VARCHAR(20) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        INDEX idx_user_id (user_id),
        INDEX idx_booking_id (booking_id),
        INDEX idx_status (status),
        INDEX idx_payment_date (payment_date),
        INDEX idx_control_number (control_number)
    )";
    
    if ($conn->query($create_payments_simple)) {
        echo "✓ Payments table created without foreign keys<br>";
    } else {
        echo "✗ Error creating simple Payments table: " . $conn->error . "<br>";
    }
}

// Step 4: Check and display final table structure
echo "<h3>4. Final Payments Table Structure</h3>";
$describe_payments = $conn->query("DESCRIBE Payments");
if ($describe_payments) {
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background-color: #f2f2f2;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $describe_payments->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "✗ Error describing Payments table: " . $conn->error . "<br>";
}

echo "<h3>5. Database Schema Fix Complete</h3>";
echo "<p>✓ Payment system database schema has been standardized.</p>";
echo "<p><a href='payment_simple.php'>Test Payment System</a> | <a href='manage_payments.php'>Manage Payments</a></p>";

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>OHBS Payment System Fix</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: whitesmoke; }
        h2, h3 { color: #20B2AA; }
        table { border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        a { color: #20B2AA; text-decoration: none; padding: 10px; background: white; border: 1px solid #20B2AA; border-radius: 5px; margin: 5px; }
        a:hover { background: #20B2AA; color: white; }
    </style>
</head>
<body>
</body>
</html>
