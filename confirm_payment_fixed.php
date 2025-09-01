<?php
header('Content-Type: application/json');
include 'connect.php';

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);
$control_number = $data['control_number'] ?? '';

if(empty($control_number)){
    echo json_encode(['success'=>false,'message'=>'Control number missing']);
    exit;
}

try {
    // Method 1: Try control_number column first (if exists)
    $stmt1 = $conn->prepare("SELECT * FROM Payments WHERE control_number = ? AND status = 'pending'");
    $stmt1->bind_param("s", $control_number);
    $stmt1->execute();
    $result1 = $stmt1->get_result();
    
    $payment = null;
    
    if($result1->num_rows > 0) {
        $payment = $result1->fetch_assoc();
    } else {
        // Method 2: Search in payment_method field (format: "Bank|CONTROLNUMBER")
        $stmt2 = $conn->prepare("SELECT * FROM Payments WHERE payment_method LIKE ? AND status = 'pending'");
        $search_pattern = '%' . $control_number . '%';
        $stmt2->bind_param("s", $search_pattern);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        
        if($result2->num_rows > 0) {
            $payment = $result2->fetch_assoc();
        }
    }
    
    if(!$payment) {
        // Method 3: Direct search by exact control number
        $stmt3 = $conn->prepare("SELECT * FROM Payments WHERE payment_method = ? OR payment_method = ? AND status = 'pending'");
        $bank_format = "Bank|" . $control_number;
        $mobile_format = "Mobile|" . $control_number;
        $stmt3->bind_param("ss", $bank_format, $mobile_format);
        $stmt3->execute();
        $result3 = $stmt3->get_result();
        
        if($result3->num_rows > 0) {
            $payment = $result3->fetch_assoc();
        }
    }
    
    if(!$payment) {
        echo json_encode(['success'=>false,'message'=>'Control number not found: ' . $control_number]);
        exit;
    }
    
    // Update payment status to completed
    $update_stmt = $conn->prepare("UPDATE Payments SET status = 'completed' WHERE payment_id = ?");
    $update_stmt->bind_param("i", $payment['payment_id']);
    
    if($update_stmt->execute() && $update_stmt->affected_rows > 0) {
        echo json_encode(['success'=>true,'message'=>'Payment confirmed successfully']);
    } else {
        echo json_encode(['success'=>false,'message'=>'Failed to update payment status']);
    }
    
} catch(Exception $e) {
    echo json_encode(['success'=>false,'message'=>'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>
