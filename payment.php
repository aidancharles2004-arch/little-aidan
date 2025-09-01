<?php
include 'connect.php';
session_start();

// 1️⃣ Check if student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION['user_id'];

// 2️⃣ Get student info
$student_query = $conn->prepare("SELECT username, full_name FROM Users WHERE user_id = ?");
$student_query->bind_param("i", $student_id);
$student_query->execute();
$student_info = $student_query->get_result()->fetch_assoc();
$student_name = $student_info['full_name'] ?? 'Student';

$message = '';
$phone_number_for_display = ''; 
$today = date('Y-m-d');

// 3️⃣ Get student's latest booking and its status
$booking_stmt = $conn->prepare("SELECT booking_id, status FROM Bookings WHERE user_id=? ORDER BY booking_id DESC LIMIT 1");
$booking_stmt->bind_param("i", $student_id);
$booking_stmt->execute();
$booking_res = $booking_stmt->get_result();
$booking_data = ($booking_res->num_rows > 0) ? $booking_res->fetch_assoc() : null;
$booking_id = $booking_data['booking_id'] ?? null;
$booking_status = $booking_data['status'] ?? null;

// 4️⃣ Check existing payment
$existing_payment = null;
if ($booking_id) {
    $payment_check = $conn->prepare("SELECT * FROM payments WHERE user_id=? AND booking_id=? AND status IN ('pending','completed','paid')");
    $payment_check->bind_param("ii", $student_id, $booking_id);
    $payment_check->execute();
    $existing_payment_res = $payment_check->get_result();
    $existing_payment = ($existing_payment_res->num_rows > 0) ? $existing_payment_res->fetch_assoc() : null;
}

// 5️⃣ Handle payment submission only if booking confirmed and no existing payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $booking_status == 'confirmed' && !$existing_payment) {
    $amount = $_POST['amount'] ?? '';
    $payment_method = $_POST['payment_method'] ?? '';

    if ($amount == 10000 && in_array($payment_method, ['M-Pesa', 'Tigo Pesa'])) {
        $vodacom_number = '0753819164'; 
        $tigo_number = '0778601309'; 

        if ($payment_method === 'M-Pesa') {
            $phone_number_for_display = $vodacom_number;
        } elseif ($payment_method === 'Tigo Pesa') {
            $phone_number_for_display = $tigo_number;
        } else {
            $message = "Invalid payment method selected. Please try again.";
            $phone_number_for_display = '';
        }

        if (!empty($phone_number_for_display)) {
            $stmt = $conn->prepare("INSERT INTO payments 
                (user_id, booking_id, amount, payment_method, status, phone, payment_date) 
                VALUES (?, ?, ?, ?, 'pending', ?, ?)");
            $stmt->bind_param("iidsss", $student_id, $booking_id, $amount, $payment_method, $phone_number_for_display, $today);

            if ($stmt->execute()) {
                $message = "Payment initialized successfully! Send the amount to the phone number below.";
                $existing_payment = [
                    'payment_method' => $payment_method,
                    'phone' => $phone_number_for_display,
                    'status' => 'pending',
                    'amount' => $amount,
                    'payment_date' => $today
                ];
            } else {
                $message = "Error: " . $stmt->error;
            }
        }
    } else {
        $message = "Payment failed. Amount must be 10,000 TSh and method must be M-Pesa or Tigo Pesa.";
    }
}

// 6️⃣ Get recent payments (limit 5)
$existing_payments_list = []; 
if ($booking_id) {
    $payments_query = $conn->prepare("SELECT payment_id, amount, payment_method, status, phone, payment_date 
        FROM payments WHERE user_id = ? ORDER BY payment_id DESC LIMIT 5");
    $payments_query->bind_param("i", $student_id);
    $payments_query->execute();
    $payments_result = $payments_query->get_result();

    while ($payment = $payments_result->fetch_assoc()) {
        $existing_payments_list[] = $payment;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment - OHBS</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;}
body { background-color: whitesmoke; color:#333; line-height:1.6; min-height:100vh; display:flex; flex-direction:column; }

.main-header { background-color:#20B2AA; color:white; padding:20px 0; text-align:center; box-shadow:0 2px 10px rgba(32,178,170,0.3);}
.main-header h1 { font-size:28px; margin-bottom:5px; }
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

.main-content { flex:1; max-width:800px; margin:20px auto; padding:30px 20px; width:100%; }
.container { background:white; padding:25px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,0.1); border-left:5px solid #20B2AA; }

h2 { color:#20B2AA; margin-bottom:20px; }
.message { padding: 15px; margin-bottom: 20px; border-radius: 5px; }
.success { background: #d4edda; color: #155724; }
.error { background: #f8d7da; color: #721c24; }
.phone-box { background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; border-radius:8px; text-align: center; font-size: 18px; font-weight: bold; margin-bottom:15px;}
form label { font-weight: bold; display: block; margin: 10px 0 5px; }
form input, form select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; }
form button { background: #20B2AA; color: white; border: none; padding: 12px; width: 100%; margin-top: 15px; border-radius: 5px; font-weight: bold; cursor: pointer; }
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { padding: 10px; border: 1px solid #ddd; text-align:center; }
th { background: #f1f1f1; }

.main-footer { background-color:#20B2AA; color:white; text-align:center; padding:20px 0; margin-top:auto; }
.main-footer p { margin:5px 0; }

@media (max-width:768px){
    .main-content{padding:20px 15px;}
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
            <a href="room_availability_cards.php">View Rooms</a>
            <a href="booking.php">Book Room</a>
            <a href="my_bookings.php">My Bookings</a>
            <a href="student_notifications_clean.php">Notifications</a>
            <a href="payment_no_booking.php" class="active">Payments</a>
            <a href="index.php">Dashboard</a>
        </div>
        <div class="user-info">
            <?php echo htmlspecialchars($student_name); ?>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
</nav>

<div class="main-content">
    <div class="container">
        <h2>Payment Portal</h2>

        <?php if ($message): ?>
            <div class="message <?php echo (strpos($message, 'successfully') !== false) ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($existing_payment): ?>
            <div class="phone-box">
                Your payment is <?php echo ucfirst($existing_payment['status']); ?> via <?php echo htmlspecialchars($existing_payment['payment_method']); ?><br>
                Paid Amount: <?php echo number_format($existing_payment['amount']); ?> TSh<br>
                Payment Date: <?php echo htmlspecialchars($existing_payment['payment_date']); ?>
                <?php if ($existing_payment['status'] == 'pending' && !empty($existing_payment['phone'])): ?>
                    <br><br>
                    Please send the amount to: <strong><?php echo htmlspecialchars($existing_payment['phone']); ?></strong><br>
                    The name to be displayed is: <strong>UNIVERSITY OF DODOMA-CIVE</strong>
                <?php endif; ?>
            </div>
        <?php elseif ($booking_status == 'confirmed'): ?>
            <form method="POST">
                <label>Student Name:</label>
                <input type="text" value="<?php echo htmlspecialchars($student_name); ?>" readonly>

                <label>Amount (TSh):</label>
                <input type="number" name="amount" value="10000" readonly>

                <label>Payment Method:</label>
                <select name="payment_method" required>
                    <option value="">-- Select --</option>
                    <option value="M-Pesa">M-Pesa</option>
                    <option value="Tigo Pesa">Tigo Pesa</option>
                </select>

                <button type="submit">Pay Now</button>
            </form>
        <?php elseif ($booking_status != 'confirmed'): ?>
            <div class="message error">
                Your booking is not yet confirmed by admin. You cannot make payment now.
            </div>
        <?php endif; ?>

        <?php if (!empty($existing_payments_list)): ?>
            <h2>Your Recent Payments</h2>
            <table>
                <tr>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th>Phone</th>
                    <th>Date</th>
                </tr>
                <?php foreach ($existing_payments_list as $pay): ?>
                <tr>
                    <td><?php echo number_format($pay['amount']); ?> TSh</td>
                    <td><?php echo htmlspecialchars($pay['payment_method']); ?></td>
                    <td><?php echo ucfirst(htmlspecialchars($pay['status'])); ?></td>
                    <td><?php echo htmlspecialchars($pay['phone']); ?></td>
                    <td><?php echo htmlspecialchars($pay['payment_date']); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
</div>

<footer class="main-footer">
    <p>&copy; <?php echo date("Y"); ?> Online Hostel Booking System (OHBS)</p>
    <p>University of Dodoma - College of Informatics and Virtual Education</p>
    <p>Providing quality student accommodation services</p>
</footer>

</body>
</html>