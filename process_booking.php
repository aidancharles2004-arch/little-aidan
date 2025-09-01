<?php
include 'connect.php';
session_start();

// Check if student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit;
}

// Get student info including gender
$student_id = $_SESSION['user_id'];
$student_query = $conn->prepare("SELECT gender, full_name FROM Users WHERE user_id = ?");
$student_query->bind_param("i", $student_id);
$student_query->execute();
$student_result = $student_query->get_result();

if ($student_result->num_rows === 0) {
    $error_message = "Student not found in database.";
    $error_type = "student_not_found";
    include 'booking_result.php';
    exit;
}

$student_info = $student_result->fetch_assoc();
$gender = $student_info['gender']; // Gender from database only
$student_name = $student_info['full_name'];

// Get POST data
$hostel = $_POST['hostel'] ?? null;
$side = $_POST['side'] ?? null;
$floor = $_POST['floor'] ?? null; // Floor can be 0 (Ground Floor)
$room_selection = $_POST['room'] ?? null;
$check_in = $_POST['check_in_date'] ?? null;
$check_out = $_POST['check_out_date'] ?? null;

// Validate input
// Hapa ndipo marekebisho muhimu yamefanyika kwa ajili ya '$floor'
if (empty($hostel) || empty($side) || !isset($floor) || empty($room_selection) || empty($check_in) || empty($check_out)) {
    $error_message = "Please fill all fields.";
    $error_type = "missing_fields";
    include 'booking_result.php';
    exit;
}

// Validate gender-hostel-side combination
$valid_combination = false;

if ($gender === 'female') {
    if ($hostel === 'Block6' && ($side === 'A' || $side === 'B')) {
        $valid_combination = true;
    } elseif (($hostel === 'Block5' || $hostel === 'Block2') && $side === 'B') {
        $valid_combination = true;
    }
} elseif ($gender === 'male') {
    if (($hostel === 'Block1' || $hostel === 'Block3' || $hostel === 'Block4') && ($side === 'A' || $side === 'B')) {
        $valid_combination = true;
    } elseif (($hostel === 'Block2' || $hostel === 'Block5') && $side === 'A') {
        $valid_combination = true;
    }
}

if (!$valid_combination) {
    $error_message = "Invalid hostel selection for your gender! Please select appropriate hostel.";
    $error_type = "gender_error";
    include 'booking_result.php';
    exit;
}

// Check if student already has an active booking
$existing_booking_check = $conn->prepare("
    SELECT booking_id FROM Bookings
    WHERE user_id = ? AND status IN ('pending', 'confirmed')
");
$existing_booking_check->bind_param("i", $student_id);
$existing_booking_check->execute();
$existing_result = $existing_booking_check->get_result();

if ($existing_result->num_rows > 0) {
    $error_message = "You already have an active booking! Only one booking per student is allowed.";
    $error_type = "existing_booking";
    include 'booking_result.php';
    exit;
}

// Check if room exists and get its details
$room_check = $conn->prepare("SELECT room_id, capacity FROM Rooms WHERE room_number = ?");
$room_check->bind_param("s", $room_selection);
$room_check->execute();
$room_result = $room_check->get_result();

if ($room_result->num_rows > 0) {
    $room_data = $room_result->fetch_assoc();
    $room_id = $room_data['room_id'];
    $capacity = $room_data['capacity'];
} else {
    // Ikiwa chumba hakipatikani, toa ujumbe wa kosa na usiendelee
    $error_message = "Selected room (" . htmlspecialchars($room_selection) . ") does not exist in the database. Please ensure the room is available and try again, or contact the administrator.";
    $error_type = "room_not_found";
    include 'booking_result.php';
    exit;
}

// Check room capacity
$capacity_check = $conn->prepare("
    SELECT COUNT(*) as current_occupants
    FROM Bookings
    WHERE room_id = ? AND status IN ('pending', 'confirmed')
");
$capacity_check->bind_param("i", $room_id);
$capacity_check->execute();
$capacity_result = $capacity_check->get_result();
$current_occupants = ($capacity_result->num_rows > 0) ? $capacity_result->fetch_assoc()['current_occupants'] : 0;

if ($current_occupants >= $capacity) {
    $error_message = "Room is full! Current occupants: {$current_occupants}/{$capacity}. Please choose another room.";
    $error_type = "room_full";
    include 'booking_result.php';
    exit;
}

// Insert new booking
$stmt = $conn->prepare("
    INSERT INTO Bookings (user_id, room_id, booking_date, check_in_date, check_out_date, status)
    VALUES (?, ?, CURDATE(), ?, ?, 'pending')
");
$stmt->bind_param("iiss", $student_id, $room_id, $check_in, $check_out);

if ($stmt->execute()) {
    $booking_id = $conn->insert_id;
    $success_message = "Booking submitted successfully!";

    // Get room details for display
    $room_details_query = $conn->prepare("
        SELECT r.room_number, h.name as hostel_name, r.price_per_month
        FROM Rooms r
        LEFT JOIN Hostels h ON r.hostel_id = h.hostel_id
        WHERE r.room_id = ?
    ");
    $room_details_query->bind_param("i", $room_id);
    $room_details_query->execute();
    $room_details_result = $room_details_query->get_result();

    if ($room_details_result->num_rows > 0) {
        $room_details = $room_details_result->fetch_assoc();
    } else {
        // Fallback should ideally not be reached if room exists
        $room_details = [
            'room_number' => $room_selection,
            'hostel_name' => ucfirst($hostel) . ' - Side ' . $side,
            'price_per_month' => 10000.00 // Assuming a default if not found, but it should be found now.
        ];
    }

    include 'booking_result.php';
} else {
    $error_message = "Error submitting booking: " . $stmt->error;
    $error_type = "database_error";
    include 'booking_result.php';
}
?>
