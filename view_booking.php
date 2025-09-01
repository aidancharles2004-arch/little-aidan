<?php
include 'connect.php';
session_start();

// Check if student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit;
}

$booking = null;
$error = '';

if (isset($_GET['id'])) {
    $booking_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];
    
    // Fetch booking details
    $stmt = $conn->prepare("
        SELECT b.*, r.room_number, r.capacity, r.price_per_month, 
               h.name as hostel_name, h.location,
               u.full_name, u.email, u.phone
        FROM Bookings b 
        JOIN Rooms r ON b.room_id = r.room_id 
        JOIN Hostels h ON r.hostel_id = h.hostel_id 
        JOIN Users u ON b.user_id = u.user_id
        WHERE b.booking_id = ? AND b.user_id = ?
    ");
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $booking = $result->fetch_assoc();
    } else {
        $error = "Booking not found or you don't have permission to view it.";
    }
} else {
    $error = "No booking ID specified.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Booking - OHBS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: #f0f0f0;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: rgba(30, 30, 60, 0.9);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.5);
        }

        h1 {
            color: #ffcc00;
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #ffcc00;
            padding-bottom: 10px;
        }

        .booking-card {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
        }

        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .booking-id {
            font-size: 24px;
            font-weight: bold;
            color: #ffcc00;
        }

        .status {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pending {
            background: #f39c12;
            color: white;
        }

        .status-confirmed {
            background: #27ae60;
            color: white;
        }

        .status-cancelled {
            background: #e74c3c;
            color: white;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .detail-item {
            background: rgba(255,255,255,0.05);
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #ff416c;
        }

        .detail-label {
            font-size: 12px;
            color: #bdc3c7;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .detail-value {
            font-size: 16px;
            font-weight: bold;
            color: #ecf0f1;
        }

        .actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 30px;
        }

        .btn {
            display: inline-block;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
            text-align: center;
        }

        .btn-primary {
            background: #ff416c;
            color: white;
        }

        .btn-primary:hover {
            background: #ff4b2b;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 65, 108, 0.4);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #545b62;
            transform: translateY(-2px);
        }

        .btn-danger {
            background: #e74c3c;
            color: white;
        }

        .btn-danger:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }

        .error {
            background: #e74c3c;
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .container {
                margin: 10px;
                padding: 20px;
            }

            .booking-header {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }

            .details-grid {
                grid-template-columns: 1fr;
            }

            .actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h1>👁️ Booking Details</h1>

    <?php if ($error): ?>
        <div class="error">
            <h3>❌ Error</h3>
            <p><?php echo htmlspecialchars($error); ?></p>
        </div>
    <?php elseif ($booking): ?>
        <div class="booking-card">
            <div class="booking-header">
                <div class="booking-id">Booking #<?php echo $booking['booking_id']; ?></div>
                <div class="status status-<?php echo $booking['status']; ?>">
                    <?php echo ucfirst($booking['status']); ?>
                </div>
            </div>

            <div class="details-grid">
                <div class="detail-item">
                    <div class="detail-label">Student Name</div>
                    <div class="detail-value"><?php echo htmlspecialchars($booking['full_name']); ?></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Email</div>
                    <div class="detail-value"><?php echo htmlspecialchars($booking['email']); ?></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Phone</div>
                    <div class="detail-value"><?php echo htmlspecialchars($booking['phone']); ?></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Room Number</div>
                    <div class="detail-value"><?php echo htmlspecialchars($booking['room_number']); ?></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Hostel</div>
                    <div class="detail-value"><?php echo htmlspecialchars($booking['hostel_name']); ?></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Location</div>
                    <div class="detail-value"><?php echo htmlspecialchars($booking['location']); ?></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Room Capacity</div>
                    <div class="detail-value"><?php echo $booking['capacity']; ?> students</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Price per Month</div>
                    <div class="detail-value">TSH <?php echo number_format($booking['price_per_month'], 2); ?></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Booking Date</div>
                    <div class="detail-value"><?php echo date('d-M-Y', strtotime($booking['booking_date'])); ?></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Check-in Date</div>
                    <div class="detail-value">
                        <?php echo $booking['check_in_date'] ? date('d-M-Y', strtotime($booking['check_in_date'])) : 'Not set'; ?>
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Check-out Date</div>
                    <div class="detail-value">
                        <?php echo $booking['check_out_date'] ? date('d-M-Y', strtotime($booking['check_out_date'])) : 'Not set'; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="actions">
        <a href="my_bookings.php" class="btn btn-primary">📑 Back to My Bookings</a>
        
        <?php if ($booking && $booking['status'] == 'pending'): ?>
            <a href="cancel_booking.php?id=<?php echo $booking['booking_id']; ?>" 
               class="btn btn-danger" 
               onclick="return confirm('Are you sure you want to cancel this booking?')">
                ❌ Cancel Booking
            </a>
        <?php endif; ?>
        
        <a href="booking.php" class="btn btn-secondary">📋 New Booking</a>
        <a href="index.php" class="btn btn-secondary">🏠 Dashboard</a>
    </div>
</div>

</body>
</html>
