<?php
include 'connect.php';

echo "<h2>🧪 Registration Test</h2>";

// Test form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    echo "<h3>📝 Form Data Received:</h3>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    // Get form data
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $role = 'student';
    
    echo "<h3>🔍 Validation Results:</h3>";
    
    $errors = [];
    
    // Validate
    if (strlen($username) < 4) $errors[] = "Username too short";
    if (strlen($password) < 4) $errors[] = "Password too short";
    if (strlen($full_name) < 3) $errors[] = "Full name too short";
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email";
    if (!empty($phone) && !preg_match('/^[0-9]{10}$/', $phone)) $errors[] = "Invalid phone";
    if (empty($course)) $errors[] = "Course required";
    if (!in_array($gender, ['male', 'female'])) $errors[] = "Invalid gender";
    
    if (empty($errors)) {
        echo "<p style='color: green;'>✅ All validation passed!</p>";
        
        // Check if username exists
        $check_stmt = $conn->prepare("SELECT username FROM Users WHERE username = ?");
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            echo "<p style='color: red;'>❌ Username already exists!</p>";
        } else {
            echo "<p style='color: green;'>✅ Username available!</p>";
            
            // Try to insert
            $insert_stmt = $conn->prepare("INSERT INTO Users (username, password, full_name, email, phone, course, gender, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("ssssssss", $username, $password, $full_name, $email, $phone, $course, $gender, $role);
            
            if ($insert_stmt->execute()) {
                $user_id = $conn->insert_id;
                echo "<p style='color: green;'>🎉 Registration successful!</p>";
                echo "<p><strong>User ID:</strong> $user_id</p>";
                echo "<p><a href='login.php'>Login now</a></p>";
            } else {
                echo "<p style='color: red;'>❌ Database error: " . $insert_stmt->error . "</p>";
            }
        }
    } else {
        echo "<p style='color: red;'>❌ Validation errors:</p>";
        foreach ($errors as $error) {
            echo "<p style='color: red;'>- $error</p>";
        }
    }
    
    echo "<hr>";
}

// Show current users
echo "<h3>👥 Current Users in Database:</h3>";
$users_result = $conn->query("SELECT user_id, username, full_name, gender, role FROM Users ORDER BY user_id DESC LIMIT 10");

if ($users_result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Username</th><th>Full Name</th><th>Gender</th><th>Role</th></tr>";
    while ($user = $users_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $user['user_id'] . "</td>";
        echo "<td>" . $user['username'] . "</td>";
        echo "<td>" . $user['full_name'] . "</td>";
        echo "<td>" . $user['gender'] . "</td>";
        echo "<td>" . $user['role'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No users found</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registration Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; background: #f8f9fa; }
        h2, h3 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 5px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; }
        input[type=submit] { background: #28a745; color: white; border: none; font-weight: bold; cursor: pointer; }
        input[type=submit]:hover { background: #218838; }
        table { background: white; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; }
        pre { background: #e9ecef; padding: 15px; border-radius: 5px; overflow-x: auto; }
        p { background: white; padding: 10px; border-radius: 5px; margin: 5px 0; }
        hr { margin: 20px 0; border: none; border-top: 2px solid #ddd; }
    </style>
</head>
<body>

<h3>📝 Test Registration Form:</h3>
<form method="POST">
    <div class="form-group">
        <label>Username *</label>
        <input type="text" name="username" required>
    </div>
    
    <div class="form-group">
        <label>Password *</label>
        <input type="password" name="password" required>
    </div>
    
    <div class="form-group">
        <label>Full Name *</label>
        <input type="text" name="full_name" required>
    </div>
    
    <div class="form-group">
        <label>Gender *</label>
        <select name="gender" required>
            <option value="">Select Gender</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
        </select>
    </div>
    
    <div class="form-group">
        <label>Course *</label>
        <input type="text" name="course" required>
    </div>
    
    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email">
    </div>
    
    <div class="form-group">
        <label>Phone</label>
        <input type="text" name="phone">
    </div>
    
    <input type="submit" value="Test Registration">
</form>

<p><a href="register.php">← Back to Main Registration</a></p>
<p><a href="login.php">Go to Login</a></p>

</body>
</html>
