<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    include "db_connect.php";

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $control_number = $data['control_number'] ?? '';

    if(empty($control_number)){
        echo json_encode(['success'=>false,'message'=>'Control number missing']);
        exit;
    }
} catch (Exception $e) {
    echo json_encode(['success'=>false,'message'=>'System error: ' . $e->getMessage()]);
    exit;
}

// Update payment status
$stmt = $conn->prepare("UPDATE payments SET status='Paid' WHERE control_number=?");
$stmt->bind_param("s", $control_number);

if($stmt->execute() && $stmt->affected_rows>0){
    echo json_encode(['success'=>true]);
}else{
    echo json_encode(['success'=>false,'message'=>'Control number not found']);
}

$stmt->close();
$conn->close();
?>
