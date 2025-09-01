<?php
header('Content-Type: application/json');

// Include database connection
include "db_connect.php";

// Get POST data
$full_name = $_POST['full_name'] ?? '';
$amount = $_POST['amount'] ?? '';
$method = $_POST['method'] ?? '';
$booking_id = $_POST['booking_id'] ?? '';

// Validate input
if(empty($full_name) || empty($amount) || empty($method)){
    echo json_encode(['success'=>false,'message'=>'All fields are required']);
    exit;
}

// Check database connection
if (!$conn) {
    echo json_encode(['success'=>false,'message'=>'Database connection failed']);
    exit;
}

// Create payments table if it doesn't exist
$create_table_sql = "CREATE TABLE IF NOT EXISTS payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT,
    full_name VARCHAR(255),
    amount DECIMAL(10,2),
    method VARCHAR(50),
    control_number VARCHAR(100),
    status VARCHAR(20) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn->query($create_table_sql)) {
    echo json_encode(['success'=>false,'message'=>'Error creating table: ' . $conn->error]);
    exit;
}

// Generate unique control number
$random_digits = rand(1000,9999);
$control_number = strtoupper(substr($full_name,0,3)).time().$random_digits;

$status = "Pending";

// Insert payment record
$stmt = $conn->prepare("INSERT INTO payments (booking_id, full_name, amount, method, control_number, status) VALUES (?, ?, ?, ?, ?, ?)");

if (!$stmt) {
    echo json_encode(['success'=>false,'message'=>'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param("isdsss", $booking_id, $full_name, $amount, $method, $control_number, $status);

if($stmt->execute()){
    echo json_encode([
        'success'=>true,
        'control_number'=>$control_number,
        'amount'=>$amount,
        'status'=>$status
    ]);
}else{
    echo json_encode(['success'=>false,'message'=>'Database error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
