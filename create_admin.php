<?php
include 'connect.php';

// Admin data
$username = 'admin';
$password = password_hash('admin123', PASSWORD_BCRYPT); // hash password
$full_name = 'Super Admin';
$email = 'admin@example.com';
$phone = '123456789';
$course = 'CNSE';
$role = 'admin';

// Check if admin already exists
$stmt = $conn->prepare("SELECT * FROM Users WHERE username=?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Insert admin
    $stmt2 = $conn->prepare("INSERT INTO Users (username, password, full_name, email, phone, course, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt2->bind_param("sssssss", $username, $password, $full_name, $email, $phone, $course, $role);
    if ($stmt2->execute()) {
        echo "Admin account created successfully!";
    } else {
        echo "Error: " . $stmt2->error;
    }
} else {
    echo "Admin account already exists!";
}
?>
