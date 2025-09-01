<?php
include 'connect.php';

echo "<h2>Setting up Default Hostels</h2>";

// Array of hostels to create
$hostels = [
    ['name' => 'Block1', 'location' => 'UDOM Campus - Block 1', 'total_rooms' => 100],
    ['name' => 'Block2', 'location' => 'UDOM Campus - Block 2', 'total_rooms' => 100],
    ['name' => 'Block3', 'location' => 'UDOM Campus - Block 3', 'total_rooms' => 100],
    ['name' => 'Block4', 'location' => 'UDOM Campus - Block 4', 'total_rooms' => 100],
    ['name' => 'Block5', 'location' => 'UDOM Campus - Block 5', 'total_rooms' => 100],
    ['name' => 'Block6', 'location' => 'UDOM Campus - Block 6', 'total_rooms' => 100],
];

foreach ($hostels as $hostel) {
    // Check if hostel already exists
    $check = $conn->prepare("SELECT hostel_id FROM Hostels WHERE name = ?");
    $check->bind_param("s", $hostel['name']);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows == 0) {
        // Create hostel
        $insert = $conn->prepare("INSERT INTO Hostels (name, location, total_rooms) VALUES (?, ?, ?)");
        $insert->bind_param("ssi", $hostel['name'], $hostel['location'], $hostel['total_rooms']);
        
        if ($insert->execute()) {
            echo "<p>✅ Created hostel: " . $hostel['name'] . "</p>";
        } else {
            echo "<p>❌ Error creating hostel " . $hostel['name'] . ": " . $insert->error . "</p>";
        }
    } else {
        echo "<p>ℹ️ Hostel " . $hostel['name'] . " already exists</p>";
    }
}

echo "<h3>Current Hostels:</h3>";
$hostels_query = $conn->query("SELECT * FROM Hostels ORDER BY name");
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Name</th><th>Location</th><th>Total Rooms</th></tr>";
while ($row = $hostels_query->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['hostel_id'] . "</td>";
    echo "<td>" . $row['name'] . "</td>";
    echo "<td>" . $row['location'] . "</td>";
    echo "<td>" . $row['total_rooms'] . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<p><a href='booking.php'>Go to Booking Page</a></p>";
?>
