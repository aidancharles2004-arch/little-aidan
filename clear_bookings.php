<?php
include 'connect.php';
session_start();

// Check if student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION['user_id'];

// Get student info
$student_query = $conn->prepare("SELECT full_name, gender FROM Users WHERE user_id = ?");
$student_query->bind_param("i", $student_id);
$student_query->execute();
$student_info = $student_query->get_result()->fetch_assoc();

$message = '';
$message_type = '';

// Handle clear all bookings
if (isset($_POST['clear_all_bookings'])) {
    // Delete all bookings for this student
    $delete_query = $conn->prepare("DELETE FROM Bookings WHERE user_id = ?");
    $delete_query->bind_param("i", $student_id);
    
    if ($delete_query->execute()) {
        $affected_rows = $delete_query->affected_rows;
        $message = "Successfully cleared {$affected_rows} booking(s). You can now make a new booking.";
        $message_type = 'success';
    } else {
        $message = "Error clearing bookings. Please try again.";
        $message_type = 'error';
    }
}

// Get current bookings count
$count_query = $conn->prepare("SELECT COUNT(*) as booking_count FROM Bookings WHERE user_id = ? AND status IN ('pending', 'confirmed')");
$count_query->bind_param("i", $student_id);
$count_query->execute();
$count_result = $count_query->get_result()->fetch_assoc();
$active_bookings = $count_result['booking_count'];

// Get all bookings for display (this data is not currently displayed but is fetched)
$bookings_query = $conn->prepare("
    SELECT b.booking_id, b.room_id, b.booking_date, b.status, b.check_in_date, b.check_out_date
    FROM Bookings b
    WHERE b.user_id = ?
    ORDER BY b.booking_date DESC
");
$bookings_query->bind_param("i", $student_id);
$bookings_query->execute();
$bookings_result = $bookings_query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clear Bookings - OHBS</title>
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
            max-width: 800px;
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
        
        /* Messages */
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 5px solid #28a745;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 5px solid #dc3545;
        }
        
        /* Status Section */
        .status-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-left: 5px solid #20B2AA;
        }
        
        .status-section h2 {
            color: #20B2AA;
            margin-bottom: 20px;
            font-size: 22px;
        }
        
        .status-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .status-number {
            font-size: 48px;
            font-weight: bold;
            color: #20B2AA;
            text-align: center;
            margin-bottom: 10px;
        }
        
        .status-label {
            text-align: center;
            color: #666;
            font-size: 16px;
        }
        
        /* Clear Section */
        .clear-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-left: 5px solid #dc3545;
            margin-bottom: 30px;
        }
        
        .clear-section h2 {
            color: #dc3545;
            margin-bottom: 20px;
            font-size: 22px;
        }
        
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .warning-box h4 {
            color: #856404;
            margin-bottom: 10px;
        }
        
        .warning-box p {
            color: #856404;
            margin-bottom: 5px;
        }
        
        .clear-btn {
            background-color: #dc3545;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            transition: background-color 0.3s;
            width: 100%;
        }
        
        .clear-btn:hover {
            background-color: #c82333;
        }
        
        .clear-btn:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }
        
        /* Action Buttons */
        .action-buttons {
            text-align: center;
            margin-top: 30px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 25px;
            margin: 10px;
            background: #20B2AA;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            background: #1a9b94;
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
        
        /* Custom Modal Styling */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: none; /* Default hidden */
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-content {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            text-align: center;
            max-width: 400px;
            width: 90%;
        }
        .modal-content h3 {
            margin-top: 0;
            color: #333;
            font-size: 20px;
            margin-bottom: 20px;
        }
        .modal-content p {
            margin-bottom: 25px;
            color: #555;
            line-height: 1.6;
        }
        .modal-actions {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        .modal-actions .btn {
            padding: 10px 25px;
            font-size: 16px;
            min-width: 100px;
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
                Welcome, <?php echo htmlspecialchars($student_info['full_name']); ?>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <div class="welcome-section">
            <h1>Clear Bookings</h1>
            <p>Manage your existing bookings to make new ones</p>
        </div>

        <?php if ($message): ?>
            <div class="<?php echo $message_type; ?>-message"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="status-section">
            <h2>Current Booking Status</h2>
            <div class="status-info">
                <div class="status-number"><?php echo $active_bookings; ?></div>
                <div class="status-label">Active Booking(s)</div>
            </div>
            
            <?php if ($active_bookings > 0): ?>
                <p style="color: #dc3545; text-align: center; font-weight: bold;">
                    You have active bookings. Only one booking per student is allowed.
                </p>
            <?php else: ?>
                <p style="color: #28a745; text-align: center; font-weight: bold;">
                    You have no active bookings. You can make a new booking now!
                </p>
            <?php endif; ?>
        </div>

        <?php if ($active_bookings > 0): ?>
            <div class="clear-section">
                <h2>Clear All Bookings</h2>
                
                <div class="warning-box">
                    <h4>Warning: This action cannot be undone!</h4>
                    <p>• This will permanently delete ALL your bookings</p>
                    <p>• You will lose any confirmed room reservations</p>
                    <p>• After clearing, you can make a new booking</p>
                </div>
                
                <form id="clearBookingsForm" method="POST">
                    <button type="button" name="clear_all_bookings_button" id="clearAllBookingsButton" class="clear-btn">
                        Clear All My Bookings
                    </button>
                    <input type="hidden" name="clear_all_bookings" value="1">
                </form>
            </div>
        <?php endif; ?>

        <div class="action-buttons">
            <?php if ($active_bookings == 0): ?>
                <a href="booking.php" class="btn">Make New Booking</a>
            <?php endif; ?>
            <a href="room_availability_cards.php" class="btn">View Available Rooms</a>
            <a href="my_bookings.php" class="btn">View My Bookings</a>
        </div>
    </div>

    <footer class="main-footer">
        <p>&copy; <?php echo date('Y'); ?> Online Hostel Booking System (OHBS)</p>
        <p>University of Dodoma - College of Informatics and Virtual Education</p>
        <p>Providing quality student accommodation services</p>
    </footer>

    <!-- Custom Confirmation Modal for Clear Bookings -->
    <div id="clearBookingsConfirmationModal" class="modal-overlay">
        <div class="modal-content">
            <h3>Confirm Deletion</h3>
            <p id="clearModalMessage">Are you absolutely sure you want to delete ALL your bookings? This action cannot be undone!</p>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="hideClearBookingsModal()">No</button>
                <button type="button" class="btn btn-danger" id="confirmClearBtn">Yes</button>
            </div>
        </div>
    </div>

</body>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const clearAllBookingsButton = document.getElementById('clearAllBookingsButton');
    const clearBookingsForm = document.getElementById('clearBookingsForm');
    const clearBookingsConfirmationModal = document.getElementById('clearBookingsConfirmationModal');
    const confirmClearBtn = document.getElementById('confirmClearBtn');
    const clearModalMessage = document.getElementById('clearModalMessage');

    // Function to show the custom modal
    function showClearBookingsModal() {
        clearBookingsConfirmationModal.style.display = 'flex';
    }

    // Function to hide the custom modal
    window.hideClearBookingsModal = function() { // Made global for onclick in HTML
        clearBookingsConfirmationModal.style.display = 'none';
    };

    // Event listener for the "Clear All My Bookings" button
    if (clearAllBookingsButton) {
        clearAllBookingsButton.addEventListener('click', function() {
            showClearBookingsModal();
        });
    }

    // Event listener for the "Yes" button in the modal
    if (confirmClearBtn) {
        confirmClearBtn.addEventListener('click', function() {
            hideClearBookingsModal(); // Hide modal first
            clearBookingsForm.submit(); // Submit the form
        });
    }
});
</script>
</html>
