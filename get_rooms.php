<?php
include 'connect.php';
session_start();

// Check if student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    http_response_code(403);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $gender = $_POST['gender'] ?? '';
    $hostel = $_POST['hostel'] ?? '';
    $side = $_POST['side'] ?? '';
    $floor = $_POST['floor'] ?? '';

    if (empty($gender) || empty($hostel) || empty($side) || empty($floor)) {
        echo json_encode([]);
        exit;
    }

    // Validate gender-hostel-side combination
    $valid_combination = false;

    if ($gender === 'female') {
        // Female: Block6 (both sides), Block5 (Side B), Block2 (Side B)
        if ($hostel === 'Block6' && ($side === 'A' || $side === 'B')) {
            $valid_combination = true;
        } elseif (($hostel === 'Block5' || $hostel === 'Block2') && $side === 'B') {
            $valid_combination = true;
        }
    } elseif ($gender === 'male') {
        // Male: Block1 (both sides), Block2 (Side A), Block3 (both sides), Block4 (both sides), Block5 (Side A)
        if (($hostel === 'Block1' || $hostel === 'Block3' || $hostel === 'Block4') && ($side === 'A' || $side === 'B')) {
            $valid_combination = true;
        } elseif (($hostel === 'Block2' || $hostel === 'Block5') && $side === 'A') {
            $valid_combination = true;
        }
    }

    if (!$valid_combination) {
        echo json_encode([]);
        exit;
    }
    
    // Create room pattern for search
    $room_pattern = $hostel . "_" . $side . "_" . $floor . "_%";
    
    // Get existing rooms from database
    $stmt = $conn->prepare("
        SELECT room_id, room_number
        FROM Rooms
        WHERE room_number LIKE ?
        ORDER BY room_number
    ");
    $stmt->bind_param("s", $room_pattern);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $rooms = [];
    while ($row = $result->fetch_assoc()) {
        // Get current occupancy for this room
        $occupancy_stmt = $conn->prepare("
            SELECT COUNT(*) as current_occupants
            FROM Bookings
            WHERE room_id = ? AND status IN ('pending', 'confirmed')
        ");
        $occupancy_stmt->bind_param("i", $row['room_id']);
        $occupancy_stmt->execute();
        $occupancy_result = $occupancy_stmt->get_result();
        $occupancy_data = $occupancy_result->fetch_assoc();
        $current_occupants = $occupancy_data['current_occupants'];

        $available_spaces = 4 - $current_occupants;
        $status = ($available_spaces > 0) ? "Available ({$available_spaces}/4)" : "Full (4/4)";

        $rooms[] = [
            'room_id' => $row['room_id'],
            'room_number' => $row['room_number'] . " - " . $status,
            'available' => $available_spaces > 0
        ];
    }
    
    // If no rooms found, generate some default rooms
    if (empty($rooms)) {
        $totalRooms = ($side === "A") ? 50 : (($side === "B") ? 50 : 25); // Total 100 rooms per hostel

        for ($i = 1; $i <= $totalRooms; $i++) {
            $room_name = $hostel . "_" . $side . "_" . $floor . "_Room" . $i;
            $rooms[] = [
                'room_id' => $room_name, // Use room name as ID for new rooms
                'room_number' => "Room " . $i . " - Available (4/4)",
                'available' => true
            ];
        }
    }
    
    echo json_encode($rooms);
} else {
    echo json_encode([]);
}
?>
