<?php
include 'db_connect.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $course = $_POST['course'] ?? '';
    $gender = $_POST['gender'] ?? '';
    
    if (!empty($username) && !empty($password) && !empty($full_name) && !empty($email)) {
        // Check if Users table exists and has correct structure
        $table_check = $conn->query("SHOW TABLES LIKE 'Users'");
        if ($table_check->num_rows == 0) {
            // Create Users table
            $create_table = "CREATE TABLE Users (
                user_id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE,
                password VARCHAR(255),
                full_name VARCHAR(255),
                email VARCHAR(255),
                phone VARCHAR(20),
                course VARCHAR(255),
                gender ENUM('male', 'female'),
                role VARCHAR(20) DEFAULT 'student',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            
            if (!$conn->query($create_table)) {
                $message = "Error creating Users table: " . $conn->error;
            }
        }
        
        if (empty($message)) {
            // Insert user
            $stmt = $conn->prepare("INSERT INTO Users (username, password, full_name, email, phone, course, gender, role) VALUES (?, ?, ?, ?, ?, ?, ?, 'student')");
            
            if ($stmt) {
                $stmt->bind_param("sssssss", $username, $password, $full_name, $email, $phone, $course, $gender);
                
                if ($stmt->execute()) {
                    $message = "Registration successful! User ID: " . $conn->insert_id;
                } else {
                    $message = "Error: " . $stmt->error;
                }
            } else {
                $message = "Error preparing statement: " . $conn->error;
            }
        }
    } else {
        $message = "Please fill all required fields.";
    }
}

// Get current users
$users = [];
$users_query = $conn->query("SELECT user_id, username, full_name, email, role FROM Users ORDER BY user_id DESC LIMIT 10");
if ($users_query) {
    while ($row = $users_query->fetch_assoc()) {
        $users[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Registration - OHBS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: whitesmoke;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-left: 5px solid #20B2AA;
        }
        
        .section h2 {
            color: #20B2AA;
            margin-bottom: 20px;
            font-size: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #20B2AA;
        }
        
        .btn {
            background: #20B2AA;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background: #1a9b94;
        }
        
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            border-left: 5px solid #28a745;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            border-left: 5px solid #dc3545;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        
        .links {
            text-align: center;
            margin: 20px 0;
        }
        
        .links a {
            display: inline-block;
            margin: 5px 10px;
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
    <div class="container">
        <div class="links">
            <a href="register.php">Main Register</a>
            <a href="login.php">Login</a>
            <a href="fix_database.php">Fix Database</a>
            <a href="payment_simple.php">Payment</a>
        </div>

        <div class="section">
            <h2>Test Registration Form</h2>
            
            <?php if ($message): ?>
                <div class="message <?php echo strpos($message, 'successful') !== false ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="full_name">Full Name:</label>
                    <input type="text" id="full_name" name="full_name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone:</label>
                    <input type="text" id="phone" name="phone">
                </div>
                
                <div class="form-group">
                    <label for="course">Course:</label>
                    <input type="text" id="course" name="course">
                </div>
                
                <div class="form-group">
                    <label for="gender">Gender:</label>
                    <select id="gender" name="gender">
                        <option value="">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
                
                <button type="submit" class="btn">Register User</button>
            </form>
        </div>

        <div class="section">
            <h2>Current Users in Database</h2>
            
            <?php if (empty($users)): ?>
                <p>No users found in database.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['user_id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['role']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
