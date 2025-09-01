<?php
include 'connect.php';

// Map ya block name na hostel_id sahihi
$hostel_map = [
    'Block1' => 1,
    'Block2' => 2,
    'Block3' => 3,
    'Block4' => 4,
    'Block5' => 5,
    'Block6' => 6
];

// Pata zote rooms
$sql = "SELECT room_id, room_number FROM Rooms";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $room_id = $row['room_id'];
        $room_number = $row['room_number'];

        // Pata block kutoka room_number (mfano: Block2_A_1_Room10 => Block2)
        if (preg_match('/^(Block\d)/', $room_number, $matches)) {
            $block_name = $matches[1];
            
            if (isset($hostel_map[$block_name])) {
                $correct_hostel_id = $hostel_map[$block_name];

                // Update room kwa hostel_id sahihi
                $update_sql = "UPDATE Rooms SET hostel_id = $correct_hostel_id WHERE room_id = $room_id";
                if($conn->query($update_sql)) {
                    echo "Room ID $room_id updated to hostel_id $correct_hostel_id<br>";
                } else {
                    echo "Error updating Room ID $room_id: " . $conn->error . "<br>";
                }
            }
        }
    }
} else {
    echo "No rooms found in the database.";
}
?>
