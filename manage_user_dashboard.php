<?php
session_start();
include 'connect.php';

// Check admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

// Fetch all users
$result = $conn->query("SELECT * FROM Users ORDER BY user_id ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Users - OHBS</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: whitesmoke;
        color: #333;
        display: flex;
        min-height: 100vh;
    }

    .sidebar {
        width: 220px;
        background: #20B2AA;
        color: #fff;
        padding: 20px;
        flex-shrink: 0;
    }
    .sidebar h4 { color: #fff; margin-bottom: 20px; }
    .sidebar a {
        display: block;
        color: #fff;
        text-decoration: none;
        padding: 10px;
        margin-bottom: 5px;
        border-radius: 5px;
        transition: 0.3s;
    }
    .sidebar a:hover { background: rgba(255,255,255,0.2); }
    .sidebar a.text-danger { background: rgba(255,0,0,0.5); }

    .main-content {
        flex-grow: 1;
        padding: 30px;
    }

    h2 {
        margin-bottom: 20px;
        color: #333;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
        background: #fff;
    }
    th, td {
        padding: 12px;
        text-align: left;
        border: 1px solid #ddd;
    }
    th { background: #20B2AA; color: #fff; }
    tr:nth-child(even) { background: #f2f2f2; }
    tr:hover { background: #e0f7f4; }

    .table-action-button {
        padding: 6px 12px;
        border-radius: 5px;
        text-decoration: none;
        color: #fff;
        margin-right: 5px;
        font-size: 14px;
        display: inline-block;
    }
    .edit-button { background: #20B2AA; }
    .delete-button { background: #c0392b; }
    .disabled-button {
        background: #bdc3c7;
        color: #7f8c8d;
        cursor: not-allowed;
        opacity: 0.6;
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
    }
    .modal-content {
        background-color: #fff;
        margin: 10% auto;
        padding: 30px;
        border-radius: 10px;
        width: 90%;
        max-width: 400px;
        text-align: center;
    }
    .modal-buttons button {
        padding: 10px 20px;
        border-radius: 5px;
        margin: 10px;
        border: none;
        font-weight: bold;
        cursor: pointer;
    }
    .modal-delete-button { background: #c0392b; color: #fff; }
    .modal-cancel-button { background: #95a5a6; color: #fff; }

</style>
</head>
<body>

<div class="sidebar">
    <h4>Admin Panel</h4>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="manage_user_dashboard.php">Manage Users</a>
    <a href="manage_hostels.php">Manage Hostels</a>
    <a href="manage_rooms.php">Manage Rooms</a>
    <a href="manage_bookings.php">Manage Bookings</a>
    <a href="manage_payments.php">Manage Payments</a>
    <a href="admin_notifications.php">Notifications</a>
    <a href="logout.php" class="text-danger">Logout</a>
</div>

<div class="main-content">
    <h2>Manage Users</h2>

    <table>
        <thead>
            <tr>
                <th>User ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Course</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()){ ?>
            <tr>
                <td><?= $row['user_id'] ?></td>
                <td><?= $row['full_name'] ?></td>
                <td><?= $row['email'] ?></td>
                <td><?= $row['phone'] ?></td>
                <td><?= $row['course'] ?></td>
                <td><?= ucfirst($row['role']) ?></td>
                <td>
                    <a href="edit_user.php?id=<?= $row['user_id'] ?>" class="table-action-button edit-button">Edit</a>
                    <?php if ($row['user_id'] != $_SESSION['user_id']): ?>
                        <a href="#" class="table-action-button delete-button" onclick="showDeleteModal(<?= $row['user_id'] ?>, '<?= htmlspecialchars($row['full_name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($row['username'], ENT_QUOTES) ?>')">Delete</a>
                    <?php else: ?>
                        <span class="table-action-button disabled-button" title="You cannot delete yourself">Delete</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <h3>Delete User?</h3>
        <p><strong>Name:</strong> <span id="userName"></span></p>
        <p><strong>Username:</strong> <span id="userUsername"></span></p>
        <div class="modal-buttons">
            <button onclick="confirmDelete()" class="modal-delete-button">Yes, Delete</button>
            <button onclick="closeDeleteModal()" class="modal-cancel-button">Cancel</button>
        </div>
    </div>
</div>

<script>
let userIdToDelete = null;

function showDeleteModal(userId, fullName, username) {
    userIdToDelete = userId;
    document.getElementById('userName').textContent = fullName;
    document.getElementById('userUsername').textContent = username;
    document.getElementById('deleteModal').style.display = 'block';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
    userIdToDelete = null;
}

function confirmDelete() {
    if (userIdToDelete) {
        window.location.href = 'delete_user.php?id=' + userIdToDelete;
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    var modal = document.getElementById('deleteModal');
    if (event.target == modal) {
        closeDeleteModal();
    }
}

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeDeleteModal();
    }
});
</script>

</body>
</html>
