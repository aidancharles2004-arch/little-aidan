<?php
// Simple test to see form data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    echo "<h2>Form Data Received:</h2>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    echo "<h3>Validation Results:</h3>";
    
    $gender = $_POST['gender'] ?? '';
    $hostel = $_POST['hostel'] ?? '';
    $side = $_POST['side'] ?? '';
    $floor = $_POST['floor'] ?? '';
    $room = $_POST['room'] ?? '';
    
    echo "<p><strong>Gender:</strong> " . htmlspecialchars($gender) . "</p>";
    echo "<p><strong>Hostel:</strong> " . htmlspecialchars($hostel) . "</p>";
    echo "<p><strong>Side:</strong> " . htmlspecialchars($side) . "</p>";
    echo "<p><strong>Floor:</strong> " . htmlspecialchars($floor) . "</p>";
    echo "<p><strong>Room:</strong> " . htmlspecialchars($room) . "</p>";
    
    // Check if all required fields are filled
    if (empty($gender) || empty($hostel) || empty($side) || empty($floor) || empty($room)) {
        echo "<p style='color: red;'>❌ Missing required fields!</p>";
        
        if (empty($gender)) echo "<p style='color: red;'>- Gender is missing</p>";
        if (empty($hostel)) echo "<p style='color: red;'>- Hostel is missing</p>";
        if (empty($side)) echo "<p style='color: red;'>- Side is missing</p>";
        if (empty($floor)) echo "<p style='color: red;'>- Floor is missing</p>";
        if (empty($room)) echo "<p style='color: red;'>- Room is missing</p>";
    } else {
        echo "<p style='color: green;'>✅ All fields filled correctly!</p>";
    }
    
    echo "<br><a href='booking.php'>← Back to Booking Form</a>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Test Booking Form</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 600px; margin: 0 auto; }
        h2 { color: #333; }
    </style>
</head>
<body>
    <div class="container">
        <h2>🧪 Booking Form Test</h2>
        <p>This page will help debug the booking form issues.</p>
        
        <h3>Steps to test:</h3>
        <ol>
            <li>Go to <a href="booking.php">booking.php</a></li>
            <li>Select gender first</li>
            <li>Select hostel (should enable based on gender)</li>
            <li>Select side (should enable based on hostel)</li>
            <li>Select floor (should enable after side)</li>
            <li>Select room (should load via AJAX)</li>
            <li>Fill dates</li>
            <li>Submit form</li>
        </ol>
        
        <h3>Expected behavior:</h3>
        <ul>
            <li>Each dropdown should enable the next one</li>
            <li>Gender should filter available hostels</li>
            <li>Hostel should filter available sides</li>
            <li>Side should enable floor selection</li>
            <li>Floor should trigger room loading</li>
        </ul>
        
        <p><strong>If you're having issues with floor selection:</strong></p>
        <ul>
            <li>Make sure you selected gender first</li>
            <li>Make sure you selected a valid hostel for your gender</li>
            <li>Make sure you selected a valid side for your hostel</li>
            <li>Check browser console for JavaScript errors (F12)</li>
        </ul>
        
        <br>
        <a href="booking.php" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
            🧪 Test Booking Form
        </a>
    </div>
</body>
</html>
