<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Result - OHBS</title>
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
            max-width: 800px;
            margin: 0 auto;
            padding: 30px 20px;
            width: 100%;
        }
        
        /* Result Container */
        .result-container {
            background: white;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-left: 5px solid #20B2AA;
        }
        
        .result-container.error {
            border-left-color: #dc3545;
        }
        
        .result-container.success {
            border-left-color: #28a745;
        }

        /* Icons */
        .result-icon {
            font-size: 60px;
            margin-bottom: 20px;
        }

        .success-icon {
            color: #28a745;
        }

        .error-icon {
            color: #dc3545;
        }

        /* Typography */
        .result-title {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
        }

        .result-message {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        /* Details Section */
        .booking-details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
            text-align: left;
            border-left: 3px solid #20B2AA;
        }

        .booking-details h3 {
            color: #20B2AA;
            margin-bottom: 15px;
            font-size: 18px;
            text-align: center;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: #333;
        }

        .detail-value {
            color: #666;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 16px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: #20B2AA;
            color: white;
        }

        .btn-primary:hover {
            background: #1a9b94;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
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
            
            .result-container {
                padding: 30px 20px;
            }

            .result-icon {
                font-size: 50px;
            }

            .result-title {
                font-size: 24px;
            }

            .result-message {
                font-size: 14px;
            }

            .action-buttons {
                flex-direction: column;
                align-items: center;
            }

            .btn {
                width: 100%;
                max-width: 250px;
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
                <a href="index.php">Dashboard</a>
            </div>
            <div class="user-info">
                Welcome, Student
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <div class="result-container <?php echo isset($success_message) ? 'success' : 'error'; ?>">
            <?php if (isset($success_message)): ?>
                <div class="result-icon success-icon">✓</div>
                <h1 class="result-title">Booking Successful!</h1>
                <p class="result-message"><?php echo htmlspecialchars($success_message); ?></p>
                
                <?php if (isset($booking_id) && isset($room_details)): ?>
                    <div class="booking-details">
                        <h3>Booking Details</h3>
                        <div class="detail-row">
                            <span class="detail-label">Booking ID:</span>
                            <span class="detail-value">#<?php echo $booking_id; ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Student:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($student_info['full_name']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Room:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($room_details['room_number']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Hostel:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($room_details['hostel_name']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Check-in:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($check_in); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Check-out:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($check_out); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Status:</span>
                            <span class="detail-value">Pending Approval</span>
                        </div>
                        <?php if (isset($room_info)): ?>
                            <div class="detail-row">
                                <span class="detail-label">Room Info:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($room_info); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="result-icon error-icon">✗</div>
                <h1 class="result-title">Booking Failed</h1>
                <p class="result-message"><?php echo htmlspecialchars($error_message ?? 'An error occurred while processing your booking.'); ?></p>
            <?php endif; ?>

            <div class="action-buttons">
                <?php if (isset($success_message)): ?>
                    <a href="payment.php" class="btn btn-primary"> Make Payment</a>
                    <a href="my_bookings.php" class="btn btn-secondary"> View My Bookings</a>
                    <a href="room_availability_cards.php" class="btn btn-secondary"> View Rooms</a>
                <?php else: ?>
                    <?php if (isset($error_type) && $error_type === 'existing_booking'): ?>
                        <a href="clear_bookings.php" class="btn btn-primary">Clear Existing Bookings</a>
                        <a href="my_bookings.php" class="btn btn-secondary">View My Bookings</a>
                    <?php endif; ?>
                    <a href="booking.php" class="btn btn-secondary">Try Again</a>
                    <a href="room_availability_cards.php" class="btn btn-success">View Available Rooms</a>
                <?php endif; ?>
                <a href="index.php" class="btn btn-secondary"> Dashboard</a>
            </div>
        </div>
    </div>

    <footer class="main-footer">
        <p>&copy; <?php echo date('Y'); ?> Online Hostel Booking System (OHBS)</p>
        <p>University of Dodoma - College of Informatics and Virtual Education</p>
        <p>Providing quality student accommodation services</p>
    </footer>
</body>
</html>
*