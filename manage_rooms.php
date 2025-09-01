<?php
session_start();
include 'connect.php';

// Enable error reporting for mysqli
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

// Initialize messages
$success = '';
$error = '';

// Handle room operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Add room
    if (isset($_POST['add_room'])) {
        $hostel_id = intval($_POST['hostel_id']);
        $room_number = trim($_POST['room_number']);
        $capacity = intval($_POST['capacity']);
        $price_per_month = floatval($_POST['price_per_month']);
        $status = trim($_POST['status']);
        
        if ($hostel_id > 0 && !empty($room_number) && $capacity > 0 && !empty($status)) {
            $stmt = $conn->prepare("INSERT INTO Rooms (hostel_id, room_number, capacity, price_per_month, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("isids", $hostel_id, $room_number, $capacity, $price_per_month, $status);
            if ($stmt->execute()) {
                $success = "Room added successfully!";
            } else {
                $error = "Error adding room: " . $stmt->error;
            }
        } else {
            $error = "Please fill all fields correctly.";
        }
    }
    
    // Delete selected rooms
    if (isset($_POST['delete_selected_rooms']) && isset($_POST['selected_rooms']) && is_array($_POST['selected_rooms'])) {
        $room_ids_to_delete = $_POST['selected_rooms'];
        
        // Create a comma-separated list of placeholders for the IN clause
        $placeholders = implode(',', array_fill(0, count($room_ids_to_delete), '?'));
        $stmt = $conn->prepare("DELETE FROM Rooms WHERE room_id IN ($placeholders)");
        
        // Bind parameters dynamically
        $types = str_repeat('i', count($room_ids_to_delete)); // 'i' for integer
        $stmt->bind_param($types, ...$room_ids_to_delete);

        if ($stmt->execute()) {
            $success = count($room_ids_to_delete) . " room(s) deleted successfully!";
        } else {
            $error = "Error deleting room(s): " . $stmt->error;
        }
    }
}

// Get all hostels for dropdown
$hostels_result = $conn->query("SELECT * FROM Hostels ORDER BY name");
$hostels = [];
while ($row = $hostels_result->fetch_assoc()) {
    $hostels[] = $row;
}


// Get all rooms with hostel names (updated every page load)
// Sorting rooms by hostel name, then floor, then numeric part of room number
$rooms_result = $conn->query("
    SELECT 
        r.*, 
        h.name as hostel_name,
        CASE
            WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(r.room_number, '_', 3), '_', -1) = 'Ground' THEN 0
            WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(r.room_number, '_', 3), '_', -1) LIKE 'Floor%' THEN CAST(REPLACE(SUBSTRING_INDEX(SUBSTRING_INDEX(r.room_number, '_', 3), '_', -1), 'Floor', '') AS UNSIGNED)
            ELSE 99 -- Fallback for unknown floor formats, placing them last
        END AS floor_order_key
    FROM 
        Rooms r 
    JOIN 
        Hostels h ON r.hostel_id = h.hostel_id 
    ORDER BY 
        h.name,             -- Sort by hostel name first
        floor_order_key,    -- Then by floor order key (Ground=0, Floor1=1, etc.)
        CAST(REPLACE(SUBSTRING_INDEX(r.room_number, '_', -1), 'Room', '') AS UNSIGNED) -- Then by the numeric part of the room number
");
$rooms = [];
while ($row = $rooms_result->fetch_assoc()) {
    $rooms[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Rooms - OHBS</title>
<style>
body { font-family: Arial, sans-serif; background: whitesmoke; margin: 0; color: #333; }
header, footer { background: #20B2AA; color: white; padding: 20px; text-align: center; }
.container { max-width: 1200px; margin: 20px auto; }
.card { background: #fff; padding: 20px; border-radius: 5px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
.form-group { margin-bottom: 15px; }
label { display: block; margin-bottom: 5px; font-weight: bold; }
input[type="text"], input[type="number"], select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
.btn { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; display: inline-block; margin: 5px; color: white; font-weight: bold; text-decoration: none; }
.btn-primary { background: #20B2AA; }
.btn-success { background: #28a745; }
.btn-danger { background: #c0392b; }
.btn-secondary { background: #6c757d; }
.btn:hover { opacity: 0.9; }
table { width: 100%; border-collapse: collapse; margin-top: 10px; background: #fff; }
th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
th { background: #20B2AA; color: #fff; }
tr:nth-child(even) { background: #f2f2f2; }
tr:hover { background: #e0f7f4; }
.message { padding: 10px; border-radius: 5px; margin-bottom: 15px; }
.success { background: #d4edda; color: #155724; }
.error { background: #f8d7da; color: #721c24; }
.footer-buttons { text-align: center; margin-top: 20px; }

/* Custom Modal Styling */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    display: none; /* Default hidden */
    justify-content: center;
    align-items: center;
    z-index: 1000;
}
.modal-content {
    background: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    text-align: center;
    max-width: 400px;
    width: 90%;
}
.modal-content h3 {
    margin-top: 0;
    color: #333;
    font-size: 20px;
    margin-bottom: 20px;
}
.modal-content p {
    margin-bottom: 25px;
    color: #555;
    line-height: 1.6;
}
.modal-actions {
    display: flex;
    justify-content: center;
    gap: 15px;
}
.modal-actions .btn {
    padding: 10px 25px;
    font-size: 16px;
    min-width: 100px;
}

/* Hostel Group Header Styling */
.hostel-group-header td {
    background-color: #e0f2f1; /* Light teal for hostel group headers */
    font-weight: bold;
    text-align: center;
    padding: 15px;
    border-bottom: 2px solid #20B2AA;
    border-top: 2px solid #20B2AA;
    color: #20B2AA;
    font-size: 1.2em;
}
</style>
</head>
<body>

<header>
    <h1>Online Hostel Booking System</h1>
    <p>Admin Panel - Manage Rooms</p>
</header>

<div class="container">

    <?php if ($success) echo "<div class='message success'>{$success}</div>"; ?>
    <?php if ($error) echo "<div class='message error'>{$error}</div>"; ?>

    <div class="footer-buttons">
        <a href="manage_hostels.php" class="btn btn-primary">Manage Hostels</a>
        <a href="manage_bookings.php" class="btn btn-primary">Manage Bookings</a>
        <a href="admin_dashboard.php" class="btn btn-secondary">Dashboard</a>
    </div>

    <!-- Add Room Form -->
    <div class="card">
        <h3>Add New Room</h3>
        <form method="POST">
            <div class="form-group">
                <label>Select Hostel</label>
                <select name="hostel_id" required>
                    <option value="">Choose Hostel</option>
                    <?php foreach ($hostels as $hostel): ?>
                        <option value="<?php echo $hostel['hostel_id']; ?>">
                            <?php echo htmlspecialchars($hostel['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Room Number</label>
                <input type="text" name="room_number" placeholder="e.g., Block1_A_Ground_Room1" required>
            </div>
            <div class="form-group">
                <label>Capacity</label>
                <input type="number" name="capacity" min="1" max="10" value="4" required>
            </div>
            <div class="form-group">
                <label>Price Per Month</label>
                <input type="number" name="price_per_month" min="0" value="10000" required>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" required>
                    <option value="available">Available</option>
                    <option value="occupied">Occupied</option>
                    <option value="maintenance">Maintenance</option>
                </select>
            </div>
            <button type="submit" name="add_room" class="btn btn-success">Add Room</button>
        </form>
    </div>

    <!-- Rooms List with Multi-Delete -->
    <div class="card">
        <h3>Existing Rooms</h3>
        <?php if (!empty($rooms)): ?>
            <form id="deleteRoomsForm" method="POST">
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAllRooms"></th>
                            <th>ID</th>
                            <th>Hostel</th>
                            <th>Room Number</th>
                            <th>Capacity</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Actions</th> 
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $current_hostel_name = '';
                        foreach ($rooms as $room): 
                            if ($room['hostel_name'] !== $current_hostel_name):
                        ?>
                                <tr class="hostel-group-header">
                                    <td colspan="8"><strong>Hostel: <?= htmlspecialchars($room['hostel_name']); ?></strong></td>
                                </tr>
                        <?php 
                                $current_hostel_name = $room['hostel_name'];
                            endif;
                        ?>
                            <tr>
                                <td><input type="checkbox" name="selected_rooms[]" value="<?php echo $room['room_id']; ?>" class="room-checkbox"></td>
                                <td><?php echo $room['room_id']; ?></td>
                                <td><?php echo htmlspecialchars($room['hostel_name']); ?></td>
                                <td><?php echo htmlspecialchars($room['room_number']); ?></td>
                                <td><?php echo $room['capacity']; ?></td>
                                <td><?php echo number_format($room['price_per_month'], 2); ?></td>
                                <td><?php echo htmlspecialchars($room['status']); ?></td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="showDeleteModalSingle(<?php echo $room['room_id']; ?>, '<?php echo htmlspecialchars($room['room_number']); ?>')">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div style="margin-top: 20px; text-align: right;">
                    <button type="button" id="deleteSelectedBtn" class="btn btn-danger" disabled>Delete Selected Rooms</button>
                </div>
                <input type="hidden" name="delete_selected_rooms" value="1">
            </form>
        <?php else: ?>
            <p>No rooms found. Add some rooms to get started.</p>
        <?php endif; ?>
    </div>

</div>

<footer>
    <p>&copy; <?php echo date("Y"); ?> Online Hostel Booking System | All Rights Reserved</p>
</footer>

<!-- Custom Confirmation Modal -->
<div id="deleteConfirmationModal" class="modal-overlay">
    <div class="modal-content">
        <h3>Confirm Deletion</h3>
        <p id="modalMessage">Are you sure you want to delete the selected room(s)? This action cannot be undone.</p>
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" onclick="hideDeleteModal()">No</button>
            <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Yes</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAllRooms');
    const roomCheckboxes = document.querySelectorAll('.room-checkbox');
    const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
    const deleteConfirmationModal = document.getElementById('deleteConfirmationModal');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const modalMessage = document.getElementById('modalMessage');
    const deleteRoomsForm = document.getElementById('deleteRoomsForm');

    // Function to update the state of the "Delete Selected Rooms" button
    function updateDeleteButtonState() {
        const anyCheckboxChecked = Array.from(roomCheckboxes).some(checkbox => checkbox.checked);
        deleteSelectedBtn.disabled = !anyCheckboxChecked;
    }

    // "Select All" functionality
    selectAllCheckbox.addEventListener('change', function() {
        roomCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateDeleteButtonState();
    });

    // Individual checkbox change listener
    roomCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (!this.checked) {
                selectAllCheckbox.checked = false;
            }
            updateDeleteButtonState();
        });
    });

    // Show custom modal for deleting selected rooms
    deleteSelectedBtn.addEventListener('click', function() {
        const checkedRoomsCount = Array.from(roomCheckboxes).filter(checkbox => checkbox.checked).length;
        if (checkedRoomsCount > 0) {
            modalMessage.textContent = `Are you sure you want to delete ${checkedRoomsCount} selected room(s)? This action cannot be undone.`;
            deleteConfirmationModal.style.display = 'flex';
        }
    });

    // Show custom modal for single room deletion (from individual row button)
    window.showDeleteModalSingle = function(roomId, roomNumber) {
        // Clear all other checkboxes
        roomCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        selectAllCheckbox.checked = false;

        // Check the specific room to be deleted
        const singleRoomCheckbox = document.querySelector(`.room-checkbox[value="${roomId}"]`);
        if (singleRoomCheckbox) {
            singleRoomCheckbox.checked = true;
        }

        modalMessage.textContent = `Are you sure you want to delete room ${roomNumber}? This action cannot be undone.`;
        deleteConfirmationModal.style.display = 'flex';
    };

    // Hide custom modal
    window.hideDeleteModal = function() {
        deleteConfirmationModal.style.display = 'none';
        // Optionally, reset checkboxes if cancelled
        // roomCheckboxes.forEach(checkbox => checkbox.checked = false);
        // selectAllCheckbox.checked = false;
        updateDeleteButtonState(); // Update button state after modal closes
    };

    // Confirm deletion and submit form
    confirmDeleteBtn.addEventListener('click', function() {
        deleteConfirmationModal.style.display = 'none';
        deleteRoomsForm.submit(); // Submit the form to perform deletion
    });

    // Initial state setup
    updateDeleteButtonState();
});
</script>
</body>
</html>
