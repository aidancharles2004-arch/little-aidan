<?php
include 'connect.php';

echo "<h2>🔧 Adding Gender Field to Users Table</h2>";

// Check if gender column already exists
$check_column = $conn->query("SHOW COLUMNS FROM Users LIKE 'gender'");

if ($check_column->num_rows > 0) {
    echo "<p>✅ Gender column already exists in Users table</p>";
} else {
    echo "<p>⏳ Adding gender column to Users table...</p>";
    
    // Add gender column to Users table
    $add_column_sql = "ALTER TABLE Users ADD COLUMN gender ENUM('male', 'female') NOT NULL DEFAULT 'male' AFTER course";
    
    if ($conn->query($add_column_sql) === TRUE) {
        echo "<p>✅ Gender column added successfully!</p>";
        
        // Show updated table structure
        echo "<h3>📋 Updated Users Table Structure:</h3>";
        $structure = $conn->query("DESCRIBE Users");
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #f0f0f0;'><th style='padding: 8px;'>Field</th><th style='padding: 8px;'>Type</th><th style='padding: 8px;'>Null</th><th style='padding: 8px;'>Key</th><th style='padding: 8px;'>Default</th></tr>";
        while ($row = $structure->fetch_assoc()) {
            $highlight = ($row['Field'] == 'gender') ? "style='background: #e8f5e8;'" : "";
            echo "<tr $highlight>";
            echo "<td style='padding: 8px;'>" . $row['Field'] . "</td>";
            echo "<td style='padding: 8px;'>" . $row['Type'] . "</td>";
            echo "<td style='padding: 8px;'>" . $row['Null'] . "</td>";
            echo "<td style='padding: 8px;'>" . $row['Key'] . "</td>";
            echo "<td style='padding: 8px;'>" . $row['Default'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } else {
        echo "<p>❌ Error adding gender column: " . $conn->error . "</p>";
    }
}

// Check current users and their gender status
echo "<h3>👥 Current Users Status:</h3>";
$users_query = $conn->query("SELECT user_id, username, full_name, gender, role FROM Users");

if ($users_query->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #f0f0f0;'><th style='padding: 8px;'>ID</th><th style='padding: 8px;'>Username</th><th style='padding: 8px;'>Full Name</th><th style='padding: 8px;'>Gender</th><th style='padding: 8px;'>Role</th></tr>";
    
    while ($user = $users_query->fetch_assoc()) {
        echo "<tr>";
        echo "<td style='padding: 8px;'>" . $user['user_id'] . "</td>";
        echo "<td style='padding: 8px;'>" . $user['username'] . "</td>";
        echo "<td style='padding: 8px;'>" . $user['full_name'] . "</td>";
        echo "<td style='padding: 8px;'>" . $user['gender'] . "</td>";
        echo "<td style='padding: 8px;'>" . $user['role'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No users found in database</p>";
}

echo "<h3>📝 Next Steps:</h3>";
echo "<ol>";
echo "<li>✅ Gender field has been added to Users table</li>";
echo "<li>🔄 Update registration form to include gender selection</li>";
echo "<li>🔄 Update profile editing to allow gender changes</li>";
echo "<li>🔄 Modify booking process to use user's gender from database</li>";
echo "<li>🔄 Update existing users to set their correct gender</li>";
echo "</ol>";

echo "<h3>🔗 Quick Actions:</h3>";
echo "<p><a href='update_user_genders.php' style='background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>Update Existing Users' Gender</a></p>";
echo "<p><a href='register.php' style='background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>Test Registration Form</a></p>";
echo "<p><a href='manage_user_dashboard.php' style='background: #6c757d; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>View Users Dashboard</a></p>";

?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 20px auto;
    padding: 20px;
    background: #f8f9fa;
}

h2, h3 {
    color: #333;
    border-bottom: 2px solid #007bff;
    padding-bottom: 5px;
}

table {
    width: 100%;
    background: white;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

th {
    background: #007bff;
    color: white;
}

p {
    background: white;
    padding: 10px;
    border-radius: 5px;
    margin: 10px 0;
}

ol, ul {
    background: white;
    padding: 20px;
    border-radius: 5px;
}
</style>
