<?php
session_start();
include 'connect.php';

// Check if admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $hostel_id = intval($_POST['hostel_id']);
    $room_number = trim($_POST['room_number']);
    $capacity = intval($_POST['capacity']);
    
    if ($hostel_id > 0 && !empty($room_number) && $capacity > 0) {
        // Check if room number already exists
        $check_stmt = $conn->prepare("SELECT room_id FROM Rooms WHERE room_number = ?");
        $check_stmt->bind_param("s", $room_number);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = "Room number already exists! Please use a different room number.";
        } else {
            $stmt = $conn->prepare("INSERT INTO Rooms (hostel_id, room_number, capacity) VALUES (?, ?, ?)");
            $stmt->bind_param("isi", $hostel_id, $room_number, $capacity);
            
            if ($stmt->execute()) {
                $success = "Room added successfully!";
                $hostel_id = $room_number = $capacity = ''; // Clear form
            } else {
                $error = "Error adding room: " . $stmt->error;
            }
        }
    } else {
        $error = "Please fill all fields correctly.";
    }
}

// Get all hostels for dropdown
$hostels = $conn->query("SELECT * FROM Hostels ORDER BY name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Room - OHBS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            background: #28a745;
            color: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .card {
            background: white;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        
        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: #28a745;
            box-shadow: 0 0 0 2px rgba(40,167,69,0.25);
        }
        
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
            font-size: 16px;
            font-weight: bold;
        }
        
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .actions {
            text-align: center;
            margin-top: 30px;
        }
        
        .required {
            color: #dc3545;
        }
        
        .help-text {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        .example-box {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            padding: 15px;
            margin: 15px 0;
        }
        
        .example-box h4 {
            margin: 0 0 10px 0;
            color: #495057;
        }
        
        .example-box ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .example-box li {
            margin: 5px 0;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Add New Room</h1>
            <p>Create a new room in the selected hostel</p>
        </div>

        <div class="card">
            <?php if (isset($success)): ?>
                <div class="message success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="message error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Select Hostel <span class="required">*</span></label>
                    <select name="hostel_id" required>
                        <option value="">Choose a hostel</option>
                        <?php while ($hostel = $hostels->fetch_assoc()): ?>
                            <option value="<?php echo $hostel['hostel_id']; ?>" 
                                    <?php echo (isset($_POST['hostel_id']) && $_POST['hostel_id'] == $hostel['hostel_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($hostel['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Room Number <span class="required">*</span></label>
                    <input type="text" name="room_number" value="<?php echo htmlspecialchars($room_number ?? ''); ?>" 
                           placeholder="e.g., Block1_A_Ground_Room1" required>
                    <div class="help-text">Use format: BlockName_Side_Floor_RoomNumber</div>
                </div>
                
                <div class="form-group">
                    <label>Room Capacity <span class="required">*</span></label>
                    <input type="number" name="capacity" value="<?php echo $capacity ?? 4; ?>" 
                           min="1" max="10" required>
                    <div class="help-text">Number of students this room can accommodate (typically 4)</div>
                </div>
                
                <div class="example-box">
                    <h4>Room Number Examples:</h4>
                    <ul>
                        <li>Block1_A_Ground_Room1</li>
                        <li>Block1_A_First_Room5</li>
                        <li>Block2_B_Second_Room12</li>
                        <li>Block3_A_Third_Room8</li>
                    </ul>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-success">Add Room</button>
                    <a href="manage_rooms.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>

        <div class="actions">
            <a href="manage_rooms.php" class="btn btn-primary">View All Rooms</a>
            <a href="add_hostel.php" class="btn btn-primary">Add Hostel</a>
            <a href="admin_dashboard.php" class="btn btn-secondary">Dashboard</a>
        </div>
    </div>
</body>
</html>
