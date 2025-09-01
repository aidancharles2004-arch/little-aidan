<?php
include 'connect.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

$success = '';
$error = '';

// Handle payment confirmation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_payment'])) {
    $payment_id = intval($_POST['payment_id']);
    
    // Update payment status to completed
    $stmt = $conn->prepare("UPDATE Payments SET status = 'completed' WHERE payment_id = ?");
    $stmt->bind_param("i", $payment_id);
    
    if ($stmt->execute()) {
        $success = "Payment confirmed successfully!";
    } else {
        $error = "Error confirming payment: " . $stmt->error;
    }
}

// Handle payment cancellation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_payment'])) {
    $payment_id = intval($_POST['payment_id']);
    
    // Update payment status to cancelled
    $stmt = $conn->prepare("UPDATE Payments SET status = 'cancelled' WHERE payment_id = ?");
    $stmt->bind_param("i", $payment_id);
    
    if ($stmt->execute()) {
        $success = "Payment cancelled successfully!";
    } else {
        $error = "Error cancelling payment: " . $stmt->error;
    }
}

// Get all pending payments
$pending_payments = $conn->query("
    SELECT p.*, b.booking_id, u.full_name, u.username, r.room_number, h.name as hostel_name
    FROM Payments p
    JOIN Bookings b ON p.booking_id = b.booking_id
    JOIN Users u ON b.user_id = u.user_id
    JOIN Rooms r ON b.room_id = r.room_id
    JOIN Hostels h ON r.hostel_id = h.hostel_id
    WHERE p.status = 'pending'
    ORDER BY p.payment_date DESC
");

// Get all completed payments
$completed_payments = $conn->query("
    SELECT p.*, b.booking_id, u.full_name, u.username, r.room_number, h.name as hostel_name
    FROM Payments p
    JOIN Bookings b ON p.booking_id = b.booking_id
    JOIN Users u ON b.user_id = u.user_id
    JOIN Rooms r ON b.room_id = r.room_id
    JOIN Hostels h ON r.hostel_id = h.hostel_id
    WHERE p.status = 'completed'
    ORDER BY p.payment_date DESC
    LIMIT 20
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Confirm Payments - OHBS Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        
        .header {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        
        .nav-links {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .nav-links a {
            display: inline-block;
            padding: 10px 20px;
            margin: 5px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
        }
        
        .nav-links a:hover {
            background: #0056b3;
            transform: translateY(-2px);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background: #f8f9fa;
            font-weight: bold;
        }
        
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 2px;
            font-weight: bold;
            font-size: 12px;
        }
        
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        
        .btn:hover {
            opacity: 0.8;
            transform: translateY(-1px);
        }
        
        .status-pending { color: #ffc107; font-weight: bold; }
        .status-completed { color: #28a745; font-weight: bold; }
        .status-cancelled { color: #dc3545; font-weight: bold; }
        
        .control-number {
            background: #e9ecef;
            padding: 4px 8px;
            border-radius: 3px;
            font-family: monospace;
            font-weight: bold;
            color: #495057;
            border: 1px solid #007bff;
        }
        
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
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
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💳 Payment Confirmation Center</h1>
            <p>Manage and confirm student payments</p>
        </div>

        <div class="nav-links">
            <a href="admin_dashboard.php">🏠 Admin Dashboard</a>
            <a href="manage_bookings.php">📑 Manage Bookings</a>
            <a href="manage_payments.php">💰 All Payments</a>
        </div>

        <?php if ($success): ?>
            <div class="message success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number" style="color: #ffc107;"><?php echo $pending_payments->num_rows; ?></div>
                <div class="stat-label">Pending Payments</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #28a745;"><?php echo $completed_payments->num_rows; ?></div>
                <div class="stat-label">Completed Today</div>
            </div>
        </div>

        <!-- Pending Payments -->
        <div class="card">
            <h3>⏳ Pending Payments</h3>
            
            <?php if ($pending_payments->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Payment ID</th>
                            <th>Student</th>
                            <th>Booking</th>
                            <th>Control Number</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($payment = $pending_payments->fetch_assoc()): ?>
                            <?php 
                            // Generate control number for display
                            $control_number = str_pad($payment['booking_id'], 3, '0', STR_PAD_LEFT) . str_pad($payment['payment_id'], 3, '0', STR_PAD_LEFT);
                            ?>
                            <tr>
                                <td><?php echo $payment['payment_id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($payment['full_name']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($payment['username']); ?></small>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($payment['hostel_name']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($payment['room_number']); ?></small>
                                </td>
                                <td>
                                    <div class="control-number"><?php echo $control_number; ?></div>
                                </td>
                                <td><strong>KSh <?php echo number_format($payment['amount']); ?></strong></td>
                                <td><?php echo date('M j, Y H:i', strtotime($payment['payment_date'])); ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="payment_id" value="<?php echo $payment['payment_id']; ?>">
                                        <button type="submit" name="confirm_payment" class="btn btn-success"
                                                onclick="return confirm('Confirm this payment?')">
                                            ✅ Confirm
                                        </button>
                                    </form>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="payment_id" value="<?php echo $payment['payment_id']; ?>">
                                        <button type="submit" name="cancel_payment" class="btn btn-danger"
                                                onclick="return confirm('Cancel this payment?')">
                                            ❌ Cancel
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align: center; color: #666; padding: 40px;">
                    No pending payments found. All payments have been processed.
                </p>
            <?php endif; ?>
        </div>

        <!-- Recent Completed Payments -->
        <div class="card">
            <h3>✅ Recently Completed Payments</h3>
            
            <?php if ($completed_payments->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Payment ID</th>
                            <th>Student</th>
                            <th>Booking</th>
                            <th>Amount</th>
                            <th>Completed Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($payment = $completed_payments->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $payment['payment_id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($payment['full_name']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($payment['username']); ?></small>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($payment['hostel_name']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($payment['room_number']); ?></small>
                                </td>
                                <td><strong>KSh <?php echo number_format($payment['amount']); ?></strong></td>
                                <td><?php echo date('M j, Y H:i', strtotime($payment['payment_date'])); ?></td>
                                <td>
                                    <span class="status-completed">✅ Completed</span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align: center; color: #666; padding: 40px;">
                    No completed payments found.
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
