<?php
session_start();
include 'connect.php';

// Check if admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

$admin_id = $_SESSION['user_id'];

// Handle notification actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['mark_read'])) {
        $notification_id = intval($_POST['notification_id']);
        $stmt = $conn->prepare("UPDATE Notifications SET status = 'read' WHERE notification_id = ?");
        $stmt->bind_param("i", $notification_id);
        $stmt->execute();
    }
    
    if (isset($_POST['mark_all_read'])) {
        $stmt = $conn->prepare("UPDATE Notifications SET status = 'read' WHERE recipient_type IN ('admin', 'all') AND status = 'unread'");
        $stmt->execute();
        $success = "All notifications marked as read!";
    }
}

// Get admin notifications
$admin_notifications = $conn->query("
    SELECT n.*, u.full_name, u.username
    FROM Notifications n
    LEFT JOIN Users u ON n.user_id = u.user_id
    WHERE n.recipient_type IN ('admin', 'all')
    ORDER BY n.sent_at DESC
    LIMIT 50
");

// Get notification statistics
$stats = [
    'unread_count' => $conn->query("SELECT COUNT(*) as count FROM Notifications WHERE recipient_type IN ('admin', 'all') AND status = 'unread'")->fetch_assoc()['count'],
    'total_count' => $conn->query("SELECT COUNT(*) as count FROM Notifications WHERE recipient_type IN ('admin', 'all')")->fetch_assoc()['count'],
    'high_priority' => $conn->query("SELECT COUNT(*) as count FROM Notifications WHERE recipient_type IN ('admin', 'all') AND priority = 'high' AND status = 'unread'")->fetch_assoc()['count'],
    'pending_bookings' => $conn->query("SELECT COUNT(*) as count FROM Bookings WHERE status = 'pending'")->fetch_assoc()['count'],
    'pending_payments' => $conn->query("SELECT COUNT(*) as count FROM Payments WHERE status = 'pending'")->fetch_assoc()['count']
];

$recent_bookings = $conn->query("
    SELECT b.*, u.full_name, r.room_number, h.name as hostel_name
    FROM Bookings b
    JOIN Users u ON b.user_id = u.user_id
    JOIN Rooms r ON b.room_id = r.room_id
    JOIN Hostels h ON r.hostel_id = h.hostel_id
    WHERE b.status = 'pending'
    ORDER BY b.booking_date DESC
    LIMIT 10
");

$recent_payments = $conn->query("
    SELECT p.*, u.full_name, b.booking_id
    FROM Payments p
    JOIN Users u ON p.user_id = u.user_id
    LEFT JOIN Bookings b ON p.booking_id = b.booking_id
    WHERE p.status = 'pending'
    ORDER BY p.payment_date DESC
    LIMIT 10
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Notifications - OHBS</title>
<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f5f5f5;
    margin: 0;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}
.header, .footer {
    background: #20B2AA;
    color: white;
    text-align: center;
    padding: 15px 20px;
}
.header h1, .footer p {
    margin: 5px 0;
}
.container {
    flex: 1;
    max-width: 1200px;
    margin: 20px auto;
    width: 95%;
}
.nav-links {
    text-align: center;
    margin-bottom: 15px;
    background: white;
    padding: 10px;
    border-radius: 8px;
}
.nav-links a {
    margin: 0 15px;
    color: #20B2AA;
    text-decoration: none;
    font-weight: bold;
}
table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    margin-bottom: 20px;
}
th, td {
    padding: 8px;
    border: 1px solid #ddd;
    text-align: left;
}
th { background: #20B2AA; color: white; }
.notification-unread { background: #fff3cd; }
.notification-high { background: #f8d7da; }
.btn {
    padding: 5px 10px;
    border: none;
    cursor: pointer;
    font-size: 12px;
    border-radius: 4px;
}
.btn-primary { background: #20B2AA; color: white; }
.btn-success { background: #28a745; color: white; }
.btn-warning { background: #ffc107; color: black; }
.btn-danger { background: #dc3545; color: white; }
.message {
    padding: 10px;
    margin: 10px 0;
    font-weight: bold;
    border-radius: 5px;
}
.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
</style>
</head>
<body>
<div class="header">
    <h1>Online Hostel Booking System</h1>
    <p>Admin Notifications & Pending Actions</p>
</div>

<div class="container">
    <div class="nav-links">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="manage_bookings.php">Manage Bookings</a>
        <a href="manage_payments.php">Manage Payments</a>
        <a href="manage_hostels.php">Manage Hostels</a>
    </div>

    <?php if (isset($success)): ?>
        <div class="message success"><?php echo $success; ?></div>
    <?php endif; ?>

    <h2>Statistics Overview</h2>
    <table>
        <tr>
            <th>Unread Notifications</th>
            <th>Total Notifications</th>
            <th>Pending Bookings</th>
            <th>Pending Payments</th>
        </tr>
        <tr>
            <td><?php echo $stats['unread_count']; ?></td>
            <td><?php echo $stats['total_count']; ?></td>
            <td><?php echo $stats['pending_bookings']; ?></td>
            <td><?php echo $stats['pending_payments']; ?></td>
        </tr>
    </table>

    <h2>All Notifications</h2>
    <?php if ($admin_notifications->num_rows > 0): ?>
        <table>
            <tr>
                <th>Type</th>
                <th>Message</th>
                <th>Student</th>
                <th>Priority</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
            <?php while ($notification = $admin_notifications->fetch_assoc()): ?>
                <tr class="<?php echo $notification['status']=='unread'?'notification-unread':'';?> <?php echo $notification['priority']=='high'?'notification-high':'';?>">
                    <td><?php echo ucfirst($notification['type']); ?></td>
                    <td><?php echo htmlspecialchars($notification['message']); ?></td>
                    <td><?php echo $notification['full_name']?htmlspecialchars($notification['full_name'])." (".$notification['username'].")":'-'; ?></td>
                    <td><?php echo strtoupper($notification['priority']); ?></td>
                    <td><?php echo date('M j, Y H:i', strtotime($notification['sent_at'])); ?></td>
                    <td>
                        <?php if ($notification['status']=='unread'): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="notification_id" value="<?php echo $notification['notification_id']; ?>">
                                <button type="submit" name="mark_read" class="btn btn-primary">Mark Read</button>
                            </form>
                        <?php else: ?>
                            <span style="color:green;font-weight:bold;">Read</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No notifications found.</p>
    <?php endif; ?>

</div>

<div class="footer">
    &copy; <?php echo date('Y'); ?> OHBS - Online Hostel Booking System
</div>
</body>
</html>
