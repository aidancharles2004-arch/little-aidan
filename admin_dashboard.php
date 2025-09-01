<?php
session_start();
include 'connect.php';

// check kama admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

$admin_id = $_SESSION['user_id'];

// fetch admin full name
$admin_name = $conn->query("SELECT full_name FROM Users WHERE user_id = $admin_id")->fetch_assoc()['full_name'];

// fetch stats
$totalUsers = $conn->query("SELECT COUNT(*) as total FROM Users")->fetch_assoc()['total'];
$totalHostels = $conn->query("SELECT COUNT(*) as total FROM Hostels")->fetch_assoc()['total'];
$totalRooms = $conn->query("SELECT COUNT(*) as total FROM Rooms")->fetch_assoc()['total'];
$totalBookings = $conn->query("SELECT COUNT(*) as total FROM Bookings")->fetch_assoc()['total'];
$totalPayments = $conn->query("SELECT COUNT(*) as total FROM Payments")->fetch_assoc()['total'];
$pendingBookings = $conn->query("SELECT COUNT(*) as total FROM Bookings WHERE status='pending'")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard - OHBS</title>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: whitesmoke;
      display: flex;
    }
    /* Sidebar */
    .sidebar {
      width: 220px;
      background: #20B2AA;
      color: #fff;
      min-height: 100vh;
      padding: 20px;
    }
    .sidebar h4 {
      margin-bottom: 25px;
      font-size: 20px;
      text-align: center;
    }
    .sidebar a {
      display: block;
      padding: 10px 15px;
      margin: 5px 0;
      color: #fff;
      text-decoration: none;
      border-radius: 5px;
      transition: background 0.3s;
    }
    .sidebar a:hover {
      background: rgba(255,255,255,0.2);
    }
    .sidebar a.text-danger {
      background: rgba(255,0,0,0.5);
    }
    /* Main Content */
    .main {
      flex: 1;
      display: flex;
      flex-direction: column;
    }
    header, footer {
      background: #20B2AA;
      color: #fff;
      text-align: center;
      padding: 15px;
    }
    .content {
      flex: 1;
      padding: 30px;
    }
    h2 {
      margin-bottom: 20px;
      color: #333;
    }
    /* Table style */
    table {
      width: 100%;
      border-collapse: collapse;
      background: #fff;
    }
    th, td {
      padding: 12px;
      border: 1px solid #ddd;
      text-align: left;
    }
    th {
      background: #20B2AA;
      color: #fff;
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <h4>Admin Panel</h4>
    <a href="admin_dashboard.php"> Dashboard</a>
    <a href="manage_user_dashboard.php"> Manage Users</a>
    <a href="manage_hostels.php"> Manage Hostels</a>
    <a href="manage_rooms.php"> Manage Rooms</a>
    <a href="manage_bookings.php"> Manage Bookings</a>
    <a href="manage_payments.php"> Manage Payments</a>
    <a href="admin_notifications.php"> Notifications</a>
    <a href="logout.php" class="text-danger"> Logout</a>
  </div>

  <!-- Main Content -->
  <div class="main">
    <header>
      <h2>Welcome, <?php echo htmlspecialchars($admin_name); ?></h2>
    </header>

    <div class="content">
      <h3>System Statistics</h3>
      <table>
        <tr>
          <th>Item</th>
          <th>Total</th>
        </tr>
        <tr>
          <td>Users</td>
          <td><?php echo $totalUsers; ?></td>
        </tr>
        <tr>
          <td>Hostels</td>
          <td><?php echo $totalHostels; ?></td>
        </tr>
        <tr>
          <td>Rooms</td>
          <td><?php echo $totalRooms; ?></td>
        </tr>
        <tr>
          <td>Bookings</td>
          <td><?php echo $totalBookings; ?></td>
        </tr>
        <tr>
          <td>Pending Bookings</td>
          <td><?php echo $pendingBookings; ?></td>
        </tr>
        <tr>
          <td>Payments</td>
          <td><?php echo $totalPayments; ?></td>
        </tr>
      </table>
    </div>

    <footer>
      <p>&copy; <?php echo date("Y"); ?> Online Hostel Booking System | All Rights Reserved</p>
    </footer>
  </div>
</body>
</html>
