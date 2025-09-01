<?php
include 'db_connect.php';

echo "<h2>Payment Table Constraints Fix</h2>";

// Check current constraints
echo "<h3>Current Foreign Key Constraints:</h3>";
$constraints_query = "SELECT 
    CONSTRAINT_NAME, 
    TABLE_NAME, 
    COLUMN_NAME, 
    REFERENCED_TABLE_NAME, 
    REFERENCED_COLUMN_NAME 
FROM information_schema.KEY_COLUMN_USAGE 
WHERE TABLE_SCHEMA = 'ohbs_db' 
AND TABLE_NAME = 'payments' 
AND REFERENCED_TABLE_NAME IS NOT NULL";

$constraints_result = $conn->query($constraints_query);
if ($constraints_result && $constraints_result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Constraint Name</th><th>Column</th><th>References</th></tr>";
    while ($row = $constraints_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['CONSTRAINT_NAME']}</td>";
        echo "<td>{$row['COLUMN_NAME']}</td>";
        echo "<td>{$row['REFERENCED_TABLE_NAME']}.{$row['REFERENCED_COLUMN_NAME']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No foreign key constraints found.<br>";
}

// Option 1: Make booking_id nullable
echo "<h3>Option 1: Make booking_id Nullable</h3>";
$make_nullable = "ALTER TABLE payments MODIFY COLUMN booking_id INT NULL";
if ($conn->query($make_nullable)) {
    echo "✓ booking_id column is now nullable<br>";
} else {
    echo "✗ Error making booking_id nullable: " . $conn->error . "<br>";
}

// Option 2: Drop foreign key constraint (if exists)
echo "<h3>Option 2: Remove Foreign Key Constraint</h3>";
$drop_fk = "ALTER TABLE payments DROP FOREIGN KEY payments_ibfk_1";
if ($conn->query($drop_fk)) {
    echo "✓ Foreign key constraint removed<br>";
} else {
    echo "Note: " . $conn->error . "<br>";
}

// Test payment insertion
echo "<h3>Test Payment Insertion:</h3>";
$test_user_id = 1; // Assuming user ID 1 exists
$test_amount = 150000;
$test_method = "mobile_money";
$test_control = "TEST" . time() . rand(1000, 9999);

// Test without booking_id
$test_stmt = $conn->prepare("INSERT INTO payments (user_id, amount, payment_method, status, control_number) VALUES (?, ?, ?, 'pending', ?)");
if ($test_stmt) {
    $test_stmt->bind_param("idss", $test_user_id, $test_amount, $test_method, $test_control);
    
    if ($test_stmt->execute()) {
        $test_payment_id = $conn->insert_id;
        echo "✓ Test payment inserted successfully without booking_id<br>";
        echo "Payment ID: $test_payment_id<br>";
        echo "Control Number: $test_control<br>";
        
        // Clean up test payment
        $delete_test = $conn->prepare("DELETE FROM payments WHERE payment_id = ?");
        $delete_test->bind_param("i", $test_payment_id);
        $delete_test->execute();
        echo "✓ Test payment cleaned up<br>";
    } else {
        echo "✗ Error inserting test payment: " . $test_stmt->error . "<br>";
    }
} else {
    echo "✗ Error preparing test statement: " . $conn->error . "<br>";
}

// Show updated table structure
echo "<h3>Updated Payments Table Structure:</h3>";
$structure = $conn->query("DESCRIBE payments");
if ($structure) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $structure->fetch_assoc()) {
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

// Alternative: Create a simple payments table without constraints
echo "<h3>Alternative: Create Simple Payments Table</h3>";
$create_simple_payments = "CREATE TABLE IF NOT EXISTS simple_payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    amount DECIMAL(10,2),
    payment_method VARCHAR(50),
    status VARCHAR(20) DEFAULT 'pending',
    control_number VARCHAR(100),
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($create_simple_payments)) {
    echo "✓ Simple payments table created (no foreign keys)<br>";
    
    // Test insertion in simple table
    $simple_test = $conn->prepare("INSERT INTO simple_payments (user_id, amount, payment_method, status, control_number) VALUES (?, ?, ?, 'pending', ?)");
    $simple_test->bind_param("idss", $test_user_id, $test_amount, $test_method, $test_control . "_SIMPLE");
    
    if ($simple_test->execute()) {
        $simple_payment_id = $conn->insert_id;
        echo "✓ Test payment in simple_payments table successful<br>";
        echo "Payment ID: $simple_payment_id<br>";
        
        // Clean up
        $delete_simple = $conn->prepare("DELETE FROM simple_payments WHERE payment_id = ?");
        $delete_simple->bind_param("i", $simple_payment_id);
        $delete_simple->execute();
        echo "✓ Simple test payment cleaned up<br>";
    } else {
        echo "✗ Error in simple payments table: " . $simple_test->error . "<br>";
    }
} else {
    echo "✗ Error creating simple payments table: " . $conn->error . "<br>";
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Constraints Fix</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            background-color: #f5f5f5;
        }
        table { 
            border-collapse: collapse; 
            margin: 10px 0; 
            background: white;
            width: 100%;
        }
        th, td { 
            padding: 8px; 
            text-align: left; 
            border: 1px solid #ddd;
        }
        th { background-color: #f2f2f2; }
        h2, h3 { color: #333; }
        .success { color: green; }
        .error { color: red; }
        .links {
            margin: 20px 0;
            padding: 15px;
            background: white;
            border-radius: 5px;
        }
        .links a {
            display: inline-block;
            margin: 5px 10px 5px 0;
            padding: 8px 15px;
            background: #20B2AA;
            color: white;
            text-decoration: none;
            border-radius: 3px;
        }
        .links a:hover {
            background: #1a9b94;
        }
    </style>
</head>
<body>
    <div class="links">
        <h3>Quick Links:</h3>
        <a href="payment_fixed.php">Payment Fixed</a>
        <a href="payment_no_booking.php">Payment No Booking</a>
        <a href="payment_simple.php">Payment Simple</a>
        <a href="check_payments_table.php">Check Table</a>
    </div>
    
    <div style="background: white; padding: 15px; border-radius: 5px; margin-top: 20px;">
        <h3>Instructions:</h3>
        <p>1. Run this script to fix foreign key constraints</p>
        <p>2. Try <strong>payment_fixed.php</strong> - should work now</p>
        <p>3. If still issues, use <strong>payment_no_booking.php</strong></p>
        <p>4. Alternative: Use <strong>simple_payments</strong> table</p>
    </div>
</body>
</html>
