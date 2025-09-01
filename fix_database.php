<?php
include 'db_connect.php';

echo "<h2>Database Fix Script</h2>";

// Check current Users table structure
echo "<h3>Current Users Table Structure:</h3>";
$users_structure = $conn->query("DESCRIBE Users");
$columns = [];
if ($users_structure) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $users_structure->fetch_assoc()) {
        $columns[] = $row['Field'];
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
    echo "Error checking Users table: " . $conn->error;
}

// Check if full_name column exists
echo "<h3>Column Check Results:</h3>";
if (in_array('full_name', $columns)) {
    echo "✓ full_name column exists<br>";
} else {
    echo "✗ full_name column missing - Adding it...<br>";
    $add_column = "ALTER TABLE Users ADD COLUMN full_name VARCHAR(255) AFTER username";
    if ($conn->query($add_column)) {
        echo "✓ full_name column added successfully<br>";
    } else {
        echo "✗ Error adding full_name column: " . $conn->error . "<br>";
    }
}

// Check other required columns
$required_columns = ['username', 'password', 'email', 'phone', 'course', 'gender', 'role'];
foreach ($required_columns as $col) {
    if (in_array($col, $columns)) {
        echo "✓ $col column exists<br>";
    } else {
        echo "✗ $col column missing<br>";
        
        // Add missing columns
        switch ($col) {
            case 'phone':
                $add_col = "ALTER TABLE Users ADD COLUMN phone VARCHAR(20)";
                break;
            case 'course':
                $add_col = "ALTER TABLE Users ADD COLUMN course VARCHAR(255)";
                break;
            case 'gender':
                $add_col = "ALTER TABLE Users ADD COLUMN gender ENUM('male', 'female')";
                break;
            case 'role':
                $add_col = "ALTER TABLE Users ADD COLUMN role VARCHAR(20) DEFAULT 'student'";
                break;
            default:
                $add_col = "ALTER TABLE Users ADD COLUMN $col VARCHAR(255)";
        }
        
        if ($conn->query($add_col)) {
            echo "✓ $col column added successfully<br>";
        } else {
            echo "✗ Error adding $col column: " . $conn->error . "<br>";
        }
    }
}

// Update existing users to have full_name if missing
echo "<h3>Data Fix:</h3>";
$update_fullname = "UPDATE Users SET full_name = username WHERE full_name IS NULL OR full_name = ''";
if ($conn->query($update_fullname)) {
    $affected = $conn->affected_rows;
    echo "✓ Updated $affected users with full_name from username<br>";
} else {
    echo "✗ Error updating full_name: " . $conn->error . "<br>";
}

// Show current users
echo "<h3>Current Users in Database:</h3>";
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
    echo "No users found in database.<br>";
}

// Test registration
echo "<h3>Test User Creation:</h3>";
$test_username = "testuser" . rand(100, 999);
$test_stmt = $conn->prepare("INSERT INTO Users (username, password, full_name, email, phone, course, gender, role) VALUES (?, ?, ?, ?, ?, ?, ?, 'student')");
if ($test_stmt) {
    $test_stmt->bind_param("sssssss", $test_username, "password123", "Test User", "test@example.com", "0123456789", "Computer Science", "male");
    
    if ($test_stmt->execute()) {
        echo "✓ Test user created successfully: $test_username<br>";
        
        // Clean up test user
        $delete_test = $conn->prepare("DELETE FROM Users WHERE username = ?");
        $delete_test->bind_param("s", $test_username);
        $delete_test->execute();
        echo "✓ Test user cleaned up<br>";
    } else {
        echo "✗ Error creating test user: " . $test_stmt->error . "<br>";
    }
} else {
    echo "✗ Error preparing test statement: " . $conn->error . "<br>";
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Database Fix</title>
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
        <a href="register.php">Register</a>
        <a href="login.php">Login</a>
        <a href="payment_simple.php">Payment</a>
        <a href="check_database.php">Check Database</a>
    </div>
</body>
</html>
