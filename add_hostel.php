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
    $name = trim($_POST['name']);
    $total_rooms = intval($_POST['total_rooms']);
    $description = trim($_POST['description']);
    
    if (!empty($name) && $total_rooms > 0) {
        $stmt = $conn->prepare("INSERT INTO Hostels (name, total_rooms, description) VALUES (?, ?, ?)");
        $stmt->bind_param("sis", $name, $total_rooms, $description);
        
        if ($stmt->execute()) {
            $success = "Hostel added successfully!";
            $name = $total_rooms = $description = ''; // Clear form
        } else {
            $error = "Error adding hostel: " . $stmt->error;
        }
    } else {
        $error = "Please fill all required fields correctly.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Hostel - OHBS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            background: #007bff;
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
        
        input, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        input:focus, textarea:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
        }
        
        textarea {
            height: 100px;
            resize: vertical;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Add New Hostel</h1>
            <p>Create a new hostel in the system</p>
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
                    <label>Hostel Name <span class="required">*</span></label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($name ?? ''); ?>" 
                           placeholder="e.g., Block 1, Block 2, etc." required>
                </div>
                
                <div class="form-group">
                    <label>Total Rooms <span class="required">*</span></label>
                    <input type="number" name="total_rooms" value="<?php echo $total_rooms ?? ''; ?>" 
                           min="1" max="1000" placeholder="e.g., 100" required>
                    <small style="color: #666;">Number of rooms this hostel can accommodate</small>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" placeholder="Optional description about the hostel..."><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-success">Add Hostel</button>
                    <a href="manage_hostels.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>

        <div class="actions">
            <a href="manage_hostels.php" class="btn btn-primary">View All Hostels</a>
            <a href="add_room.php" class="btn btn-primary">Add Room</a>
            <a href="admin_dashboard.php" class="btn btn-secondary">Dashboard</a>
        </div>
    </div>
</body>
</html>
