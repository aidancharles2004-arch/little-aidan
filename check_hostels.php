<?php
include 'connect.php';

echo "<h2>Checking Hostels Table Structure</h2>";

// Check if table exists
$result = $conn->query("SHOW TABLES LIKE 'Hostels'");
if ($result->num_rows > 0) {
    echo "<p>✅ Hostels table exists</p>";
    
    // Show table structure
    echo "<h3>Table Structure:</h3>";
    $structure = $conn->query("DESCRIBE Hostels");
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $structure->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} else {
    echo "<p>❌ Hostels table does not exist</p>";
}
?>
