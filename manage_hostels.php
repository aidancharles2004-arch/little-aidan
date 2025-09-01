<?php
session_start();
include 'connect.php';

// Check if admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

// Handle hostel operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_hostel'])) {
        $name = trim($_POST['name']);
        $total_rooms = intval($_POST['total_rooms']);
        
        if (!empty($name) && $total_rooms > 0) {
            $stmt = $conn->prepare("INSERT INTO Hostels (name, total_rooms) VALUES (?, ?)");
            $stmt->bind_param("si", $name, $total_rooms);
            if ($stmt->execute()) {
                $success = "Hostel added successfully!";
            } else {
                $error = "Error adding hostel: " . $stmt->error;
            }
        } else {
            $error = "Please fill all fields correctly.";
        }
    }
    
    if (isset($_POST['delete_hostel'])) {
        $hostel_id = intval($_POST['hostel_id']);
        $stmt = $conn->prepare("DELETE FROM Hostels WHERE hostel_id = ?");
        $stmt->bind_param("i", $hostel_id);
        if ($stmt->execute()) {
            $success = "Hostel deleted successfully!";
        } else {
            $error = "Error deleting hostel: " . $stmt->error;
        }
    }
}

// Get all hostels
$hostels = $conn->query("SELECT * FROM Hostels ORDER BY name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Hostels - OHBS</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: whitesmoke;
        margin: 0;
        color: #333;
    }

    header, footer {
        background: #20B2AA;
        color: white;
        padding: 20px;
        text-align: center;
    }

    .container {
        max-width: 1000px;
        margin: 20px auto;
    }

    .card {
        background: #fff;
        padding: 20px;
        border-radius: 5px;
        margin-bottom: 20px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }

    input, select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
    }

    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        margin: 5px;
        color: white;
        font-weight: bold;
    }

    .btn-primary { background: #20B2AA; }
    .btn-danger { background: #c0392b; }

    .btn:hover { opacity: 0.8; }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        background: #fff;
    }

    th, td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    th { background: #20B2AA; color: #fff; }

    tr:nth-child(even) { background: #f2f2f2; }
    tr:hover { background: #e0f7f4; }

    .message {
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 15px;
    }

    .success { background: #d4edda; color: #155724; }
    .error { background: #f8d7da; color: #721c24; }

    .footer-buttons {
        text-align: center;
        margin-top: 20px;
    }
</style>
</head>
<body>

<header>
    <h1>Online Hostel Booking System</h1>
    <p>Admin Panel - Manage Hostels</p>
</header>

<div class="container">

    <?php if (isset($success)): ?>
        <div class="message success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="message error"><?php echo $error; ?></div>
    <?php endif; ?>

      <div class="footer-buttons">
        <a href="manage_rooms.php" class="btn btn-primary">Manage Rooms</a>
        <a href="admin_dashboard.php" class="btn btn-primary">Dashboard</a>
    </div>
    <!-- Add Hostel Form -->
    <div class="card">
        <h3>Add New Hostel</h3>
        <form method="POST">
            <div class="form-group">
                <label>Hostel Name</label>
                <input type="text" name="name" required>
            </div>
            
            <div class="form-group">
                <label>Total Rooms</label>
                <input type="number" name="total_rooms" min="1" required>
            </div>
            
            <button type="submit" name="add_hostel" class="btn btn-primary">Add Hostel</button>
        </form>
    </div>

    <!-- Hostels List -->
    <div class="card">
        <h3>Existing Hostels</h3>
        
        <?php if ($hostels->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Total Rooms</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($hostel = $hostels->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $hostel['hostel_id']; ?></td>
                            <td><?php echo htmlspecialchars($hostel['name']); ?></td>
                            <td><?php echo $hostel['total_rooms']; ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="hostel_id" value="<?php echo $hostel['hostel_id']; ?>">
                                    <button type="submit" name="delete_hostel" class="btn btn-danger" 
                                            onclick="return confirm('Are you sure you want to delete this hostel?')">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No hostels found. Add some hostels to get started.</p>
        <?php endif; ?>
    </div>

  
</div>

<footer>
    <p>&copy; <?php echo date("Y"); ?> Online Hostel Booking System | All Rights Reserved</p>
</footer>

</body>
</html>
