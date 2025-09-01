<?php
include 'connect.php';
session_start();

// Check if student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit;
}

$message = '';
$message_type = '';

if (isset($_GET['id'])) {
    $booking_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];
    
    // Verify booking belongs to this user and is cancellable
    $check_stmt = $conn->prepare("SELECT * FROM Bookings WHERE booking_id = ? AND user_id = ? AND status = 'pending'");
    $check_stmt->bind_param("ii", $booking_id, $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Cancel the booking
        $cancel_stmt = $conn->prepare("UPDATE Bookings SET status = 'cancelled' WHERE booking_id = ? AND user_id = ?");
        $cancel_stmt->bind_param("ii", $booking_id, $user_id);
        
        if ($cancel_stmt->execute()) {
            $message = "✅ Booking cancelled successfully!";
            $message_type = 'success';
        } else {
            $message = "❌ Error cancelling booking: " . $cancel_stmt->error;
            $message_type = 'error';
        }
    } else {
        $message = "❌ Booking not found or cannot be cancelled!";
        $message_type = 'error';
    }
} else {
    $message = "❌ No booking specified!";
    $message_type = 'error';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancel Booking - OHBS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            text-align: center;
        }

        h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 28px;
        }

        .message {
            font-size: 18px;
            margin: 20px 0;
            padding: 20px;
            border-radius: 10px;
            font-weight: bold;
        }

        .success { 
            background: #d4edda; 
            color: #155724; 
            border: 2px solid #c3e6cb;
        }

        .error { 
            background: #f8d7da; 
            color: #721c24; 
            border: 2px solid #f5c6cb;
        }

        .btn {
            display: inline-block;
            padding: 12px 25px;
            margin: 10px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .btn:hover { 
            background: #0056b3; 
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,123,255,0.3);
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-secondary:hover {
            background: #545b62;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>❌ Cancel Booking</h2>
        
        <div class="message <?php echo $message_type === 'success' ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
        
        <a href="my_bookings.php" class="btn">📑 Back to My Bookings</a>
        <a href="booking.php" class="btn btn-secondary">📋 Make New Booking</a>
        <a href="index.php" class="btn btn-secondary">🏠 Dashboard</a>
    </div>
</body>
</html>
