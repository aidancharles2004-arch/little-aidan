<?php
include 'connect.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

$message = '';

// Check if user_id is provided in the URL
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Prevent admin from deleting themselves
    if ($user_id == $_SESSION['user_id']) {
        $message = "You cannot delete yourself!";
    } else {
        // Delete user directly
        $stmt = $conn->prepare("DELETE FROM Users WHERE user_id=?");
        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            $message = "User deleted successfully!";
        } else {
            $message = "Error deleting user: " . $stmt->error;
        }
    }
} else {
    $message = "No user specified!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete User - OHBS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .header, .footer {
            background: #20B2AA;
            color: white;
            text-align: center;
            padding: 12px 20px;
            width: 100%;
        }

        .header h1 {
            margin-bottom: 5px;
            font-size: 24px;
        }

        .header p {
            font-size: 14px;
            margin-top: 0;
        }

        .footer p {
            font-size: 13px;
            margin: 5px 0;
        }

        .container-wrapper {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            width: 90%;
            max-width: 450px;
            background: white;
            padding: 18px 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
        }

        h2 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 22px;
        }

        .message {
            font-size: 14px;
            margin: 15px 0;
            padding: 12px;
            border-radius: 8px;
            font-weight: bold;
        }

        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px 0;
            background: #20B2AA;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Online Hostel Booking System</h1>
        <p>Admin Panel - Manage Users and Bookings Efficiently</p>
    </div>

    <!-- Centered Delete User Container -->
    <div class="container-wrapper">
        <div class="container">
            <h2>Delete User</h2>

            <!-- Show result message -->
            <div class="message <?php echo strpos($message, 'successfully') !== false ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>

            <a href="manage_user_dashboard.php" class="btn">Back to Users</a>
        </div>
    </div>

    <div class="footer">
        <p>&copy; <?= date('Y') ?> OHBS - Online Hostel Booking System</p>
        <p>All rights reserved. Manage your hostel users securely.</p>
    </div>
</body>
</html>
