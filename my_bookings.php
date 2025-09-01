<?php
include 'connect.php';
session_start();

// Check if student is logged in
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

// Fetch student's bookings with payment information
$bookings_stmt = $conn->prepare("
    SELECT b.*, r.room_number, r.price_per_month, h.name as hostel_name, h.location,
           p.payment_id, p.status as payment_status, p.transaction_reference, p.amount as payment_amount, p.phone as payment_phone
    FROM Bookings b
    JOIN Rooms r ON b.room_id = r.room_id
    JOIN Hostels h ON r.hostel_id = h.hostel_id
    LEFT JOIN Payments p ON b.booking_id = p.booking_id
    WHERE b.user_id = ?
    ORDER BY b.booking_date DESC
");
$bookings_stmt->bind_param("i", $_SESSION['user_id']);
$bookings_stmt->execute();
$bookings_result = $bookings_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - OHBS</title>
    <!-- Font Awesome CDN haihitajiki tena bila icons kwenye nav, lakini inaweza kuachwa kwa matumizi mengine -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* Header */
        .main-header {
            background-color: #20B2AA;
            color: white;
            padding: 20px 0;
            text-align: center;
            box-shadow: 0 2px 10px rgba(32, 178, 170, 0.3);
        }
        
        .main-header h1 {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .main-header p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        /* Navigation */
        .main-nav {
            background-color: #1a9b94;
            padding: 10px 0;
        }
        
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between; /* Hii inasukuma nav-links kushoto na user-info kulia */
            align-items: center;
        }
        
        .nav-links {
            display: flex; /* Hii inafanya links zikae mlalo */
            gap: 20px; /* Nafasi kati ya links */
            align-items: center;
            margin-right: 30px; /* **MAREKEBISHO HAPA: Nafasi kati ya nav-links na user-info** */
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }
        
        .nav-links a:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        .nav-links a.active {
            background-color: rgba(255, 255, 255, 0.3);
        }
        
        .user-info {
            color: white;
            font-weight: 500;
            display: flex; /* Kufanya Welcome text na Logout button zikae mlalo */
            align-items: center;
        }
        
        .logout-btn {
            background-color: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            margin-left: 15px; /* Nafasi kati ya welcome text na logout button */
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background-color: rgba(255, 255, 255, 0.3);
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            max-width: 1000px;
            margin: 0 auto;
            padding: 30px 20px;
            width: 100%;
        }
        
        /* Welcome Section */
        .welcome-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            border-left: 5px solid #20B2AA;
        }
        
        .welcome-section h1 {
            color: #20B2AA;
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .welcome-section p {
            color: #666;
            font-size: 16px;
        }
        
        /* Bookings Section */
        .bookings-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-left: 5px solid #20B2AA;
        }
        
        .bookings-section h2 {
            color: #20B2AA;
            margin-bottom: 20px;
            font-size: 22px;
        }
        
        /* Table */
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #f8f9fa;
            font-weight: bold;
            color: #20B2AA;
        }

        tr:nth-child(even) {
            background: #f9f9f9;
        }

        tr:hover {
            background: #e8f4fd;
        }

        /* Status badges */
        .status {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status.pending {
            background: #fff3cd;
            color: #856404;
        }

        .status.confirmed {
            background: #d4edda;
            color: #155724;
        }

        .status.cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        /* Payment status */
        .payment-status {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .payment-status.completed {
            background: #d4edda;
            color: #155724;
        }

        .payment-status.pending {
            background: #fff3cd;
            color: #856404;
        }

        .btn-payment {
            background: #20B2AA;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 12px;
        }

        .btn-payment:hover {
            background: #1a9b94;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #666;
        }

        .empty-state h3 {
            margin-bottom: 10px;
            color: #20B2AA;
        }

        .empty-state p {
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #20B2AA;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .btn:hover {
            background: #1a9b94;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
            margin: 0;
        }

        /* Footer */
        .main-footer {
            background-color: #20B2AA;
            color: white;
            text-align: center;
            padding: 20px 0;
            margin-top: auto;
        }

        .main-footer p {
            margin: 5px 0;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-header h1 {
                font-size: 24px;
            }
            
            .main-header p {
                font-size: 14px;
            }
            
            .nav-container {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
                gap: 10px;
                margin-right: 0; /* Kuondoa margin-right kwenye responsive view */
            }
            
            .main-content {
                padding: 20px 15px;
            }

            table {
                font-size: 14px;
            }

            th, td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <header class="main-header">
        <h1>ONLINE HOSTEL BOOKING SYSTEM (OHBS)</h1>
        <p>University of Dodoma - Student Accommodation Portal</p>
    </header>

    <nav class="main-nav">
        <div class="nav-container">
            <div class="nav-links">
                <a href="room_availability_cards.php">View Rooms</a>
                <a href="booking.php">Book Room</a>
                <a href="my_bookings.php" class="active">My Bookings</a>
                <a href="student_notifications_clean.php">Notifications</a>
                <a href="payment.php">Payments</a>
                <a href="index.php">Dashboard</a>
            </div>
            <div class="user-info">
                Welcome, <?php echo htmlspecialchars($student['full_name']); ?>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <div class="welcome-section">
            <h1>My Room Bookings</h1>
            <p>View and manage your accommodation bookings</p>
        </div>

        <div class="bookings-section">
            <h2>Booking History</h2>
            
            <?php if ($bookings_result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Room</th>
                                <th>Hostel</th>
                                <th>Booking Date</th>
                                <th>Status</th>
                                <th>Payment</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($booking = $bookings_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($booking['booking_id']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['room_number']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['hostel_name']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></td>
                                    <td>
                                        <span class="status <?php echo strtolower($booking['status']); ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($booking['payment_status'] == 'completed'): ?>
                                            <span class="payment-status completed">✓ Paid</span>
                                        <?php elseif ($booking['payment_status'] == 'pending'): ?>
                                            <span class="payment-status pending"> Pending</span>
                                            <br><small>Send to: <?php echo htmlspecialchars($booking['payment_phone'] ?? '+255123456789'); ?></small>
                                            <br><small>Ref: <?php echo htmlspecialchars($booking['transaction_reference']); ?></small>
                                        <?php else: ?>
                                            <a href="payment.php?booking_id=<?php echo $booking['booking_id']; ?>&amount=<?php echo $booking['price_per_month']; ?>" class="btn btn-sm btn-payment">
                                                Make Payment
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <h3>No Bookings Found</h3>
                    <p>You haven't made any room bookings yet.</p>
                    <a href="booking.php" class="btn">Book Your First Room</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="main-footer">
        <p>&copy; <?php echo date('Y'); ?> Online Hostel Booking System (OHBS)</p>
        <p>University of Dodoma - College of Informatics and Virtual Education</p>
        <p>Providing quality student accommodation services</p>
    </footer>
</body>
</html>
