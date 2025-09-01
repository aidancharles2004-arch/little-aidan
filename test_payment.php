<?php
// Simple test for payment system
include 'db_connect.php';

echo "<h2>Payment System Test</h2>";

// Test database connection
echo "<h3>1. Database Connection Test</h3>";
if ($conn) {
    echo "✓ Database connection successful<br>";
    echo "Database: " . $conn->get_server_info() . "<br>";
} else {
    echo "✗ Database connection failed<br>";
    exit;
}

// Test if payments table exists
echo "<h3>2. Payments Table Test</h3>";
$table_check = $conn->query("SHOW TABLES LIKE 'payments'");
if ($table_check && $table_check->num_rows > 0) {
    echo "✓ Payments table exists<br>";
    
    // Show table structure
    $structure = $conn->query("DESCRIBE payments");
    if ($structure) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Key</th></tr>";
        while ($row = $structure->fetch_assoc()) {
            echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Key']}</td></tr>";
        }
        echo "</table>";
    }
} else {
    echo "✗ Payments table does not exist<br>";
    echo "Creating payments table...<br>";
    
    $create_table = "CREATE TABLE payments (
        payment_id INT AUTO_INCREMENT PRIMARY KEY,
        booking_id INT,
        full_name VARCHAR(255),
        amount DECIMAL(10,2),
        method VARCHAR(50),
        control_number VARCHAR(100) UNIQUE,
        status VARCHAR(20) DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($create_table)) {
        echo "✓ Payments table created successfully<br>";
    } else {
        echo "✗ Error creating payments table: " . $conn->error . "<br>";
    }
}

// Test generate control number
echo "<h3>3. Control Number Generation Test</h3>";
$test_name = "John Doe";
$random_digits = rand(1000,9999);
$control_number = strtoupper(substr($test_name,0,3)).time().$random_digits;
echo "Generated control number: <strong>$control_number</strong><br>";

// Test insert payment
echo "<h3>4. Payment Insert Test</h3>";
$test_booking_id = 999; // Test booking ID
$test_amount = 150000;
$test_method = "mobile_money";

$stmt = $conn->prepare("INSERT INTO payments (booking_id, full_name, amount, method, control_number, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
if ($stmt) {
    $stmt->bind_param("isdss", $test_booking_id, $test_name, $test_amount, $test_method, $control_number);
    
    if ($stmt->execute()) {
        echo "✓ Test payment inserted successfully<br>";
        $payment_id = $conn->insert_id;
        echo "Payment ID: $payment_id<br>";
        
        // Test update payment status
        echo "<h3>5. Payment Update Test</h3>";
        $update_stmt = $conn->prepare("UPDATE payments SET status='Paid' WHERE control_number=?");
        $update_stmt->bind_param("s", $control_number);
        
        if ($update_stmt->execute()) {
            echo "✓ Payment status updated successfully<br>";
        } else {
            echo "✗ Error updating payment: " . $update_stmt->error . "<br>";
        }
        
        // Clean up test data
        $delete_stmt = $conn->prepare("DELETE FROM payments WHERE payment_id=?");
        $delete_stmt->bind_param("i", $payment_id);
        $delete_stmt->execute();
        echo "Test data cleaned up<br>";
        
    } else {
        echo "✗ Error inserting test payment: " . $stmt->error . "<br>";
    }
} else {
    echo "✗ Error preparing statement: " . $conn->error . "<br>";
}

// Test API endpoints
echo "<h3>6. API Endpoints Test</h3>";
if (file_exists('generate_control.php')) {
    echo "✓ generate_control.php exists<br>";
} else {
    echo "✗ generate_control.php missing<br>";
}

if (file_exists('confirm_payment_new.php')) {
    echo "✓ confirm_payment_new.php exists<br>";
} else {
    echo "✗ confirm_payment_new.php missing<br>";
}

echo "<h3>7. Session Test</h3>";
session_start();
if (isset($_SESSION['user_id'])) {
    echo "✓ User session active: User ID " . $_SESSION['user_id'] . "<br>";
    echo "Role: " . ($_SESSION['role'] ?? 'Not set') . "<br>";
} else {
    echo "✗ No active user session<br>";
    echo "You need to login first to test payment system<br>";
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment System Test</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            background-color: #f5f5f5;
        }
        h2, h3 { color: #333; }
        table { 
            border-collapse: collapse; 
            margin: 10px 0; 
            background: white;
        }
        th, td { 
            padding: 8px; 
            text-align: left; 
            border: 1px solid #ddd;
        }
        th { background-color: #f2f2f2; }
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
        <a href="payment.php">Payment Page</a>
        <a href="payment.php?debug=1">Payment Debug</a>
        <a href="create_payments_table.php">Create Table</a>
        <a href="login.php">Login</a>
        <a href="index.php">Dashboard</a>
    </div>
</body>
</html>
