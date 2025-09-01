<?php
session_start();
include 'connect.php';

// Check if admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

// Handle booking operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_status'])) {
        $booking_id = intval($_POST['booking_id']);
        $status = $_POST['status'];
        
        if (in_array($status, ['pending', 'confirmed', 'cancelled'])) {
            $stmt = $conn->prepare("UPDATE Bookings SET status = ? WHERE booking_id = ?");
            $stmt->bind_param("si", $status, $booking_id);
            if ($stmt->execute()) {
                $success = "Booking status updated successfully!";
            } else {
                $error = "Error updating booking: " . $stmt->error;
            }
        }
    }
    
    if (isset($_POST['delete_booking'])) {
        $booking_id = intval($_POST['booking_id']);
        $stmt = $conn->prepare("DELETE FROM Bookings WHERE booking_id = ?");
        $stmt->bind_param("i", $booking_id);
        if ($stmt->execute()) {
            $success = "Booking deleted successfully!";
        } else {
            $error = "Error deleting booking: " . $stmt->error;
        }
    }
}

// Get all bookings with user and room details
$bookings = $conn->query("
    SELECT b.*, u.username, u.full_name, r.room_number, h.name as hostel_name
    FROM Bookings b
    JOIN Users u ON b.user_id = u.user_id
    JOIN Rooms r ON b.room_id = r.room_id
    JOIN Hostels h ON r.hostel_id = h.hostel_id
    ORDER BY b.booking_date DESC
");

// Get booking statistics
$stats = [
    'total' => $conn->query("SELECT COUNT(*) as count FROM Bookings")->fetch_assoc()['count'],
    'pending' => $conn->query("SELECT COUNT(*) as count FROM Bookings WHERE status = 'pending'")->fetch_assoc()['count'],
    'confirmed' => $conn->query("SELECT COUNT(*) as count FROM Bookings WHERE status = 'confirmed'")->fetch_assoc()['count'],
    'cancelled' => $conn->query("SELECT COUNT(*) as count FROM Bookings WHERE status = 'cancelled'")->fetch_assoc()['count']
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Bookings - OHBS</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: whitesmoke;
    margin: 0;
    color: #333;
}

header, footer {
    background: #20B2AA;
    color: white;
    padding: 20px;
    text-align: center;
}

.container {
    max-width: 1400px;
    margin: 20px auto;
}

.card {
    background: #fff;
    padding: 20px;
    border-radius: 5px;
    margin-bottom: 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    text-align: center;
}

.stat-number {
    font-size: 32px;
    font-weight: bold;
    margin-bottom: 5px;
}

.stat-label { color: #666; font-size: 14px; }

.btn {
    padding: 8px 15px;
    border: none;
    border-radius: 3px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    margin: 2px;
    font-size: 12px;
    font-weight: bold;
}

.btn-primary { background: #20B2AA; color: white; }
.btn-success { background: #28a745; color: white; }
.btn-warning { background: #ffc107; color: black; }
.btn-danger { background: #c0392b; color: white; }
.btn-secondary { background: #6c757d; color: white; }

.btn:hover { opacity: 0.8; }

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    font-size: 14px;
    background: #fff;
}

th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
th { background: #20B2AA; color: #fff; }
tr:nth-child(even) { background: #f2f2f2; }
tr:hover { background: #e0f7f4; }

.status-pending { color: #ffc107; font-weight: bold; }
.status-confirmed { color: #28a745; font-weight: bold; }
.status-cancelled { color: #c0392b; font-weight: bold; }

.message { padding: 10px; border-radius: 5px; margin-bottom: 15px; }
.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

select { padding: 5px; border: 1px solid #ddd; border-radius: 3px; }

.footer-buttons { text-align: center; margin-top: 20px; }
</style>
</head>
<body>

<header>
    <h1>Online Hostel Booking System</h1>
    <p>Admin Panel - Manage Bookings</p>
</header>

<div class="container">

    <?php if (isset($success)): ?>
        <div class="message success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="message error"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Booking Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number" style="color: #20B2AA;"><?php echo $stats['total']; ?></div>
            <div class="stat-label">Total Bookings</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color: #ffc107;"><?php echo $stats['pending']; ?></div>
            <div class="stat-label">Pending</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color: #28a745;"><?php echo $stats['confirmed']; ?></div>
            <div class="stat-label">Confirmed</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color: #c0392b;"><?php echo $stats['cancelled']; ?></div>
            <div class="stat-label">Cancelled</div>
        </div>
    </div>

    <!-- Bookings List -->
    <div class="card">
        <h3>All Bookings</h3>
        <?php if ($bookings->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student</th>
                        <th>Hostel</th>
                        <th>Room</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($booking = $bookings->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $booking['booking_id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($booking['full_name']); ?></strong><br>
                                <small><?php echo htmlspecialchars($booking['username']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($booking['hostel_name']); ?></td>
                            <td><?php echo htmlspecialchars($booking['room_number']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($booking['booking_date'])); ?></td>
                            <td>
                                <span class="status-<?php echo $booking['status']; ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                    <select name="status" onchange="this.form.submit()">
                                        <option value="pending" <?php echo $booking['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="confirmed" <?php echo $booking['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="cancelled" <?php echo $booking['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                    <input type="hidden" name="update_status" value="1">
                                </form>
                                
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                    <button type="submit" name="delete_booking" class="btn btn-danger" 
                                            onclick="return confirm('Are you sure you want to delete this booking?')">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No bookings found.</p>
        <?php endif; ?>
    </div>

    <div class="footer-buttons">
        <a href="manage_hostels.php" class="btn btn-primary">Manage Hostels</a>
        <a href="manage_rooms.php" class="btn btn-primary">Manage Rooms</a>
        <a href="admin_dashboard.php" class="btn btn-secondary">Dashboard</a>
    </div>

</div>

<footer>
    <p>&copy; <?php echo date("Y"); ?> Online Hostel Booking System | All Rights Reserved</p>
</footer>

</body>
</html>
