<?php
include 'connect.php';
session_start();

// Check if admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

// Get user_id from URL
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Fetch user data
    $stmt = $conn->prepare("SELECT * FROM Users WHERE user_id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        echo "User not found!";
        exit;
    }
    $user = $result->fetch_assoc();
}

// Update user data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username  = $_POST['username'];
    $full_name = $_POST['full_name'];
    $email     = $_POST['email'];
    $phone     = $_POST['phone'];
    $course    = $_POST['course'];

    // Check if any value changed
    if (
        $username  == $user['username'] &&
        $full_name == $user['full_name'] &&
        $email     == $user['email'] &&
        $phone     == $user['phone'] &&
        $course    == $user['course']
    ) {
        $success = "No changes detected.";
    } else {
        $stmt2 = $conn->prepare("UPDATE Users SET username=?, full_name=?, email=?, phone=?, course=? WHERE user_id=?");
        $stmt2->bind_param("sssssi", $username, $full_name, $email, $phone, $course, $user_id);
        if ($stmt2->execute()) {
            $success = "User updated successfully!";
            $user['username']  = $username;
            $user['full_name'] = $full_name;
            $user['email']     = $email;
            $user['phone']     = $phone;
            $user['course']    = $course;
        } else {
            $error = "Error: " . $stmt2->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit User - OHBS</title>
<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f5f5f5;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

.header, .footer {
    background: #20B2AA;
    color: white;
    text-align: center;
    padding: 12px 20px;
}

.header h1 {
    margin-bottom: 5px;
    font-size: 24px;
}

.header p {
    font-size: 14px;
    margin: 0;
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
    padding: 20px;
}

.container {
    width: 90%;
    max-width: 450px;
    background: white;
    padding: 25px 20px;
    border-radius: 10px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}

h2 { 
    text-align: center; 
    color: #343a40; 
    margin-bottom: 15px;
}

form { display: flex; flex-direction: column; }
label { margin: 10px 0 5px; font-weight: bold; }
input[type="text"], input[type="email"] {
    padding: 10px; border-radius: 5px; border: 1px solid #ced4da;
}
input[type="submit"] {
    margin-top: 20px; padding: 10px;
    background-color: #20B2AA; color: white; border: none; border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
}
input[type="submit"]:hover { background-color: #179f9c; }

.message {
    text-align: center; margin-bottom: 15px; font-weight: bold;
    padding: 10px; border-radius: 5px;
}
.success { color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb; }
.error { color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; }

.back {
    display: block;
    text-align: center;
    margin-top: 15px;
    text-decoration: none;
    color: #20B2AA;
    font-weight: bold;
    font-size: 14px;
}
</style>
</head>
<body>

<div class="header">
    <h1>Online Hostel Booking System</h1>
    <p>Admin Panel - Edit User Details</p>
</div>

<div class="container-wrapper">
    <div class="container">
        <h2>Edit User</h2>

        <?php
        if (isset($success)) { echo "<p class='message success'>$success</p>"; }
        if (isset($error)) { echo "<p class='message error'>$error</p>"; }
        ?>

        <form method="POST">
            <label>Username</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>

            <label>Full Name</label>
            <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>

            <label>Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">

            <label>Phone</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">

            <label>Course</label>
            <input type="text" name="course" value="<?php echo htmlspecialchars($user['course']); ?>">

            <input type="submit" value="Update">
        </form>

        <a class="back" href="admin_dashboard.php">Back to Dashboard</a>
    </div>
</div>

<div class="footer">
    <p>&copy; <?= date('Y') ?> OHBS - Online Hostel Booking System</p>
    <p>Manage your hostel users efficiently and securely.</p>
</div>

</body>
</html>
