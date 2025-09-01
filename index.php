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

$page_title = "Student Dashboard - OHBS";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
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
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            border-left: 5px solid #20B2AA;
        }

        .welcome-section h1 {
            color: #20B2AA;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .welcome-section p {
            color: #666;
            font-size: 16px;
        }



        /* Summary Section */
        .summary-section {
            background: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
            border: 1px solid #ddd;
        }

        .summary-section h2 {
            color: #20B2AA;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .summary-table td:first-child {
            font-weight: bold;
            color: #333;
            width: 30%;
        }

        .summary-table td:last-child {
            color: #666;
        }

        .status-active {
            color: #28a745;
            font-weight: bold;
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

        /* Responsive Design */
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
            }

            .main-content {
                padding: 20px 15px;
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
                <a href="room_availability_cards.php">View Hostels/Rooms</a>
                <a href="booking.php">Book Room</a>
                <a href="my_bookings.php">My Bookings</a>
                <a href="student_notifications_clean.php">Notifications</a>
                <a href="payment.php">Payments</a>
            </div>
            <div class="user-info">
                Welcome, <?php echo htmlspecialchars($student['full_name']); ?>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </nav>
    <div class="main-content">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <h1>Student Dashboard</h1>
            <p>University of Dodoma - College of Informatics and Virtual Education (CIVE)</p>
            <p>Manage your hostel accommodation needs efficiently</p>
        </div>



        <!-- Account Summary -->
        <div class="summary-section">
            <h2>Account Information</h2>
            <table class="summary-table">
                <tr>
                    <td>Student Name:</td>
                    <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                </tr>
                <tr>
                    <td>Email Address:</td>
                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                </tr>
                <tr>
                    <td>Phone Number:</td>
                    <td><?php echo htmlspecialchars($student['phone']); ?></td>
                </tr>
                <tr>
                    <td>Account Status:</td>
                    <td class="status-active">Active</td>
                </tr>
            </table>
        </div>
    </div>

    <footer class="main-footer">
        <p>&copy; <?php echo date('Y'); ?> Online Hostel Booking System (OHBS)</p>
        <p>University of Dodoma - College of Informatics and Virtual Education</p>
        <p>Providing quality student accommodation services</p>
    </footer>

</body>
</html>
