<?php
include 'connect.php';
session_start();

// Check if student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit;
}

// Fetch student info
$stmt = $conn->prepare("SELECT * FROM Users WHERE user_id=?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

// Handle Edit Profile
$edit_message = "";
if (isset($_POST['edit_profile'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $course = $_POST['course'];

    $stmt2 = $conn->prepare("UPDATE Users SET full_name=?, email=?, phone=?, course=? WHERE user_id=?");
    $stmt2->bind_param("ssssi", $full_name, $email, $phone, $course, $_SESSION['user_id']);
    if ($stmt2->execute()) {
        $edit_message = "Profile updated successfully!";
        $student['full_name'] = $full_name;
        $student['email'] = $email;
        $student['phone'] = $phone;
        $student['course'] = $course;
    } else {
        $edit_message = "Error updating profile: ".$stmt2->error;
    }
}

// Handle Change Password
$pass_message = "";
if (isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if (password_verify($current, $student['password'])) {
        if ($new === $confirm) {
            $new_hash = password_hash($new, PASSWORD_BCRYPT);
            $stmt3 = $conn->prepare("UPDATE Users SET password=? WHERE user_id=?");
            $stmt3->bind_param("si", $new_hash, $_SESSION['user_id']);
            if ($stmt3->execute()) {
                $pass_message = "Password changed successfully!";
            } else {
                $pass_message = "Error updating password: ".$stmt3->error;
            }
        } else {
            $pass_message = "New passwords do not match!";
        }
    } else {
        $pass_message = "Current password is incorrect!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Profile</title>
<style>
/* General */
body {
    margin:0;
    padding:0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: whitesmoke;
    color:#333;
    min-height: 100vh;
}

/* Header */
header {
    text-align:center;
    padding:20px;
    background: #054946;
    color: white;
    border-radius:0 0 12px 12px;
}
header h1 {margin:0; font-size:28px;}
header p {margin:5px 0 0; opacity:0.9;}

/* Navbar */
.topnav {
    display:flex; justify-content:center; flex-wrap:wrap; margin:20px 0;
}
.topnav a {
    margin:5px; padding:10px 18px;
    text-decoration:none; color:#054946; font-weight:bold;
    background:#fff; border:2px solid #054946;
    border-radius:8px;
    transition:0.3s;
}
.topnav a:hover {background:#054946; color:white;}

/* Containers */
.container {
    max-width:800px;
    margin:20px auto;
    background: #fff;
    padding:25px;
    border-radius:12px;
    box-shadow:0 4px 10px rgba(0,0,0,0.1);
}

/* Section titles */
h2 {color:#054946; margin-top:0; border-bottom:2px solid #054946; padding-bottom:5px;}

/* Tables */
table {width:100%; border-collapse:collapse; margin-top:15px;}
th, td {padding:10px; text-align:left; border-bottom:1px solid #ddd;}
tr:nth-child(even) {background:#f8f9fa;}

/* Forms */
form {margin-top:15px;}
form input {padding:10px; margin-bottom:10px; width:100%; border-radius:6px; border:1px solid #ccc;}
form label {font-weight:bold;}

/* Buttons */
.save-btn, .logout {
    display:inline-block; margin-top:15px; padding:10px 22px;
    border-radius:6px; font-weight:bold; text-decoration:none; cursor:pointer;
    transition:0.3s;
}
.save-btn {
    background:#054946; color:#fff; border:none;
}
.save-btn:hover {background:#06655f;}
.logout {
    background:#b02a37; color:#fff; border:none;
}
.logout:hover {background:#922530;}

/* Messages */
.message {padding:10px; margin:10px 0; border-radius:6px; background:#d4edda; color:#155724;}

/* Responsive */
@media(max-width:768px){
    .topnav a{padding:8px 12px; font-size:14px;}
}
</style>
</head>
<body>

<header>
    <h1>University of Dodoma (UDOM)</h1>
    <p>College of Informatics and Virtual Education (CIVE)</p>
</header>

<div class="topnav">
    <a href="index.php">Dashboard</a>
    <a href="view_rooms.php">View Rooms</a>
    <a href="my_bookings.php">My Bookings</a>
    <a href="payments.php">Payments</a>
    <a href="notifications.php">Notifications</a>
    <a href="#view-profile">Profile</a>
    <a href="logout.php">Logout</a>
</div>

<div class="container" id="view-profile">
    <h2>My Profile</h2>
    <table>
        <tr><td><strong>Username:</strong></td><td><?php echo $student['username']; ?></td></tr>
        <tr><td><strong>Full Name:</strong></td><td><?php echo $student['full_name']; ?></td></tr>
        <tr><td><strong>Email:</strong></td><td><?php echo $student['email']; ?></td></tr>
        <tr><td><strong>Phone:</strong></td><td><?php echo $student['phone']; ?></td></tr>
        <tr><td><strong>Course:</strong></td><td><?php echo $student['course']; ?></td></tr>
        <tr><td><strong>Member Since:</strong></td><td><?php echo date("d-M-Y", strtotime($student['created_at'])); ?></td></tr>
    </table>
</div>

<div class="container" id="edit-profile">
    <h2>Edit Profile</h2>
    <?php if($edit_message) echo "<div class='message'>{$edit_message}</div>"; ?>
    <form method="POST">
        <label>Full Name</label>
        <input type="text" name="full_name" value="<?php echo $student['full_name']; ?>" required>
        <label>Email</label>
        <input type="email" name="email" value="<?php echo $student['email']; ?>" required>
        <label>Phone</label>
        <input type="text" name="phone" value="<?php echo $student['phone']; ?>" required>
        <label>Course</label>
        <input type="text" name="course" value="<?php echo $student['course']; ?>" required>
        <button type="submit" name="edit_profile" class="save-btn">Save Changes</button>
    </form>
</div>

<div class="container" id="change-password">
    <h2>Change Password</h2>
    <?php if($pass_message) echo "<div class='message'>{$pass_message}</div>"; ?>
    <form method="POST">
        <label>Current Password</label>
        <input type="password" name="current_password" required>
        <label>New Password</label>
        <input type="password" name="new_password" required>
        <label>Confirm New Password</label>
        <input type="password" name="confirm_password" required>
        <button type="submit" name="change_password" class="save-btn">Change Password</button>
    </form>
</div>

<a class="logout" href="logout.php">Logout</a>

</body>
</html>
