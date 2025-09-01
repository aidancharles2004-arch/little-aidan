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

$user_id = $_SESSION['user_id'];

// Get student's booking notifications
$notifications = [];

// Booking status notifications
$booking_notifications = $conn->query("
    SELECT b.booking_id, b.status, b.booking_date, r.room_number, h.name as hostel_name,
           'booking' as type
    FROM Bookings b
    JOIN Rooms r ON b.room_id = r.room_id
    JOIN Hostels h ON r.hostel_id = h.hostel_id
    WHERE b.user_id = $user_id
    ORDER BY b.booking_date DESC
    LIMIT 20
");

while ($notification = $booking_notifications->fetch_assoc()) {
    $notifications[] = $notification;
}

// Get summary statistics
$stats = [
    'total_bookings' => $conn->query("SELECT COUNT(*) as count FROM Bookings WHERE user_id = $user_id")->fetch_assoc()['count'],
    'confirmed_bookings' => $conn->query("SELECT COUNT(*) as count FROM Bookings WHERE user_id = $user_id AND status = 'confirmed'")->fetch_assoc()['count'],
    'pending_bookings' => $conn->query("SELECT COUNT(*) as count FROM Bookings WHERE user_id = $user_id AND status = 'pending'")->fetch_assoc()['count'],
    'cancelled_bookings' => $conn->query("SELECT COUNT(*) as count FROM Bookings WHERE user_id = $user_id AND status = 'cancelled'")->fetch_assoc()['count']
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - OHBS</title>
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
            justify-content: space-between;
            align-items: center;
        }
        
        .nav-links {
            display: flex;
            gap: 20px;
            align-items: center;
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
        }
        
        .logout-btn {
            background-color: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            margin-left: 15px;
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
        
        /* Stats Section */
        .stats-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-left: 5px solid #20B2AA;
        }
        
        .stats-section h2 {
            color: #20B2AA;
            margin-bottom: 20px;
            font-size: 22px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .stat-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border-left: 3px solid #20B2AA;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #20B2AA;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
        }
        
        /* Notifications Section */
        .notifications-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-left: 5px solid #20B2AA;
        }
        
        .notifications-section h2 {
            color: #20B2AA;
            margin-bottom: 20px;
            font-size: 22px;
        }
        
        .notification-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .notification-item:last-child {
            border-bottom: none;
        }
        
        .notification-content h4 {
            color: #333;
            margin-bottom: 5px;
        }
        
        .notification-content p {
            color: #666;
            font-size: 14px;
        }
        
        .notification-date {
            color: #999;
            font-size: 12px;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-confirmed {
            background: #d4edda;
            color: #155724;
        }
        
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .no-notifications {
            text-align: center;
            padding: 40px;
            color: #666;
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
            
            .nav-container {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
                gap: 10px;
            }
            
            .main-content {
                padding: 20px 15px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
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
                <a href="my_bookings.php">My Bookings</a>
                <a href="student_notifications_clean.php" class="active">Notifications</a>
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
            <h1>Notifications</h1>
            <p>Stay updated with your booking status and important announcements</p>
        </div>

        <div class="stats-section">
            <h2>Booking Summary</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_bookings']; ?></div>
                    <div class="stat-label">Total Bookings</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['confirmed_bookings']; ?></div>
                    <div class="stat-label">Confirmed</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['pending_bookings']; ?></div>
                    <div class="stat-label">Pending</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['cancelled_bookings']; ?></div>
                    <div class="stat-label">Cancelled</div>
                </div>
            </div>
        </div>

        <div class="notifications-section">
            <h2>Recent Notifications</h2>
            
            <?php if (empty($notifications)): ?>
                <div class="no-notifications">
                    <h3>No Notifications</h3>
                    <p>You don't have any notifications at the moment.</p>
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-item">
                        <div class="notification-content">
                            <h4>Booking Update - <?php echo htmlspecialchars($notification['room_number']); ?></h4>
                            <p>Your booking for <?php echo htmlspecialchars($notification['hostel_name']); ?> has been <?php echo $notification['status']; ?></p>
                            <div class="notification-date"><?php echo date('M d, Y', strtotime($notification['booking_date'])); ?></div>
                        </div>
                        <div>
                            <span class="status-badge status-<?php echo $notification['status']; ?>">
                                <?php echo ucfirst($notification['status']); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
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
