<?php
include 'connect.php';
session_start();

// Check if student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit;
}

// Get student info
$student_query = $conn->prepare("SELECT gender, full_name FROM Users WHERE user_id = ?");
$student_query->bind_param("i", $_SESSION['user_id']);
$student_query->execute();
$student_info = $student_query->get_result()->fetch_assoc();
$student_gender = strtolower($student_info['gender'] ?? 'male');
$student_name = $student_info['full_name'] ?? 'Student';

// Gender-based hostel rules
$gender_hostels = [
    'male' => [
        'Block1' => 'all',
        'Block2' => 'A',
        'Block3' => 'all',
        'Block4' => 'all',
        'Block5' => 'A'
    ],
    'female' => [
        'Block2' => 'B',
        'Block5' => 'B',
        'Block6' => 'all'
    ]
];

// Current month (YYYY-MM)
$current_month = date('Y-m');

// Fetch rooms based on gender and calculate available slots
$rooms = [];
foreach ($gender_hostels[$student_gender] as $hostel => $side) {
    $sql = "SELECT r.room_id, r.room_number, r.capacity,
                   (SELECT COUNT(*) 
                    FROM Bookings b 
                    WHERE b.room_id = r.room_id 
                      AND b.status IN ('pending','confirmed')
                      AND DATE_FORMAT(b.booking_date, '%Y-%m') = '$current_month') AS occupied_count,
                   h.name AS hostel_name
            FROM Rooms r
            JOIN Hostels h ON r.hostel_id = h.hostel_id
            WHERE h.name='$hostel'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Filter by side if needed
            if ($side != 'all') {
                if (stripos($row['room_number'], "_$side") === false) continue;
            }
            $occupied = intval($row['occupied_count']);
            $available = intval($row['capacity']) - $occupied;
            $rooms[$hostel][] = [
                'room_number' => $row['room_number'],
                'capacity' => $row['capacity'],
                'occupied' => $occupied,
                'available' => $available,
                'status' => ($available > 0) ? 'Available' : 'Full'
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Room Availability - OHBS</title>
<style>
* { margin:0; 
    padding:0; 
    box-sizing:border-box; 
    font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
body { background-color: 
    whitesmoke; 
    color:#333; 
    line-height:1.6; 
    min-height:100vh; 
    display:flex; flex-direction:column; 
}

.main-header { background-color:#20B2AA; 
    color:white; 
    padding:20px 0; 
    text-align:center; 
    box-shadow:0 2px 10px rgba(32,178,170,0.3);
}
.main-header h1 { font-size:28px; 
    margin-bottom:5px; }
.main-header p { font-size:16px; opacity:0.9; }

.main-nav { background-color: #1a9b94; padding:10px 0; }
.nav-container { max-width:1200px; margin:0 auto; padding:0 20px; display:flex; justify-content:space-between; align-items:center; }
.nav-links { display:flex; gap:20px; align-items:center; }
.nav-links a { color:white; text-decoration:none; padding:8px 15px; border-radius:5px; font-weight:500; transition:0.3s; }
.nav-links a:hover { background-color: rgba(255,255,255,0.2);}
.nav-links a.active { background-color: rgba(255,255,255,0.3);}
.user-info { color:white; font-weight:500; }
.logout-btn { background-color: rgba(255,255,255,0.2); border:1px solid rgba(255,255,255,0.3); color:white; padding:8px 15px; border-radius:5px; text-decoration:none; margin-left:15px; transition:all 0.3s ease;}
.logout-btn:hover { background-color: rgba(255,255,255,0.3); }

.main-content { flex:1; max-width:1100px; margin:20px auto; padding:30px 20px; width:100%; }

.container { background:white; padding:25px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,0.1); border-left:5px solid #20B2AA; margin-bottom:30px; }

h2 { color:#20B2AA; margin-bottom:20px; }

.table-responsive { overflow-x:auto; }

table { width:100%; border-collapse: collapse; margin-top:15px; font-size:16px;}
th, td { padding:12px; border:1px solid #ddd; text-align:center; }
th { background:#f8f9fa; font-weight:bold; color:#20B2AA; }
tr:nth-child(even) { background:#f9f9f9; }
tr:hover { background:#e8f4fd; }

.main-footer { background-color:#20B2AA; color:white; text-align:center; padding:20px 0; margin-top:auto; }
.main-footer p { margin:5px 0; }

.price-note { margin-bottom:15px; font-weight:bold; color:#20B2AA; }

@media (max-width:768px){
    .main-header h1 { font-size:24px; }
    .main-header p { font-size:14px; }
    .nav-container { flex-direction:column; gap:15px; text-align:center; }
    .nav-links { flex-wrap:wrap; justify-content:center; gap:10px; }
    table { font-size:14px; }
    th, td { padding:8px; }
}
</style>
</head>
<body>

<header class="main-header">
    <h1>ONLINE HOSTEL BOOKING SYSTEM (OHBS)</h1>
    <p>Welcome, <?php echo htmlspecialchars($student_name); ?></p>
</header>

<nav class="main-nav">
    <div class="nav-container">
        <div class="nav-links">
            <a href="room_availability_cards.php" class="active">View Rooms</a>
            <a href="booking.php">Book Room</a>
            <a href="my_bookings.php">My Bookings</a>
            <a href="student_notifications_clean.php">Notifications</a>
            <a href="payment.php">Payments</a>
            <a href="index.php">Dashboard</a>
        </div>
        <div class="user-info">
            <?php echo htmlspecialchars($student_name); ?>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
</nav>

<div class="main-content">
    <?php foreach ($rooms as $hostel_name => $hostel_rooms): ?>
    <div class="container">
        <h2><?php echo htmlspecialchars($hostel_name); ?> Hostel Rooms</h2>
        <p class="price-note">Each room costs 10,000 per month</p>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Room Number</th>
                        <th>Capacity</th>
                        <th>Occupied</th>
                        <th>Available</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($hostel_rooms as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['room_number']); ?></td>
                        <td><?php echo $row['capacity']; ?></td>
                        <td><?php echo $row['occupied']; ?></td>
                        <td><?php echo $row['available']; ?></td>
                        <td><?php echo $row['status']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($hostel_rooms)) echo '<tr><td colspan="5">No rooms available for your gender yet.</td></tr>'; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<footer class="main-footer">
    <p>&copy; <?php echo date('Y'); ?> Online Hostel Booking System (OHBS)</p>
    <p>University of Dodoma - College of Informatics and Virtual Education</p>
    <p>Providing quality student accommodation services</p>
</footer>

</body>
</html>
