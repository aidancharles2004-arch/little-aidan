<?php
session_start();
include 'connect.php';

// Check if admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

// Handle payment actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['confirm_payment'])) {
        $payment_id = intval($_POST['payment_id']);
        $transaction_ref = 'PMT-' . date('Ymd-His') . '-' . $payment_id;

        $stmt = $conn->prepare("UPDATE Payments SET status = 'paid', transaction_reference = ? WHERE payment_id = ?");
        $stmt->bind_param("si", $transaction_ref, $payment_id);
        if ($stmt->execute()) {
            $booking_update = $conn->prepare("
                UPDATE Bookings b
                JOIN Payments p ON b.booking_id = p.booking_id
                SET b.status = 'confirmed'
                WHERE p.payment_id = ? AND b.status = 'pending'
            ");
            $booking_update->bind_param("i", $payment_id);
            $booking_update->execute();
            $success = "Payment confirmed successfully! Booking status updated.";
        } else {
            $error = "Error confirming payment: " . $stmt->error;
        }
    }

    if (isset($_POST['reject_payment'])) {
        $payment_id = intval($_POST['payment_id']);
        $rejection_reason = $_POST['rejection_reason'] ?? 'Payment rejected by admin';

        $stmt = $conn->prepare("UPDATE Payments SET status = 'failed', transaction_reference = ? WHERE payment_id = ?");
        $stmt->bind_param("si", $rejection_reason, $payment_id);
        if ($stmt->execute()) {
            $success = "Payment rejected successfully!";
        } else {
            $error = "Error rejecting payment: " . $stmt->error;
        }
    }
}

// Get all payments with user details (username & phone)
$payments = $conn->query("
    SELECT p.*, u.username, u.phone, b.booking_id
    FROM Payments p
    JOIN Users u ON p.user_id = u.user_id
    LEFT JOIN Bookings b ON p.booking_id = b.booking_id
    ORDER BY p.payment_date DESC
");

// Payment statistics
$stats = [
    'total_payments' => $conn->query("SELECT COUNT(*) as count FROM Payments")->fetch_assoc()['count'],
    'total_amount' => $conn->query("SELECT SUM(amount) as total FROM Payments")->fetch_assoc()['total'] ?? 0,
    'pending_payments' => $conn->query("SELECT COUNT(*) as count FROM Payments WHERE status='pending'")->fetch_assoc()['count'],
    'completed_payments' => $conn->query("SELECT COUNT(*) as count FROM Payments WHERE status='paid'")->fetch_assoc()['count']
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Payments - OHBS</title>
<style>
body { font-family: Arial, sans-serif; background: whitesmoke; margin:0; display:flex; flex-direction:column; min-height:100vh; }
.header { background:#20B2AA; color:white; padding:20px; text-align:center; }
.container { flex:1; max-width:1400px; margin:20px auto; width:95%; }
.card { background:white; padding:20px; border-radius:5px; box-shadow:0 2px 5px rgba(0,0,0,0.1); margin-bottom:20px; }
.stats-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:20px; margin-bottom:20px; }
.stat-card { background:white; padding:20px; border-radius:5px; box-shadow:0 2px 5px rgba(0,0,0,0.1); text-align:center; }
.stat-number { font-size:32px; font-weight:bold; margin-bottom:5px; }
.stat-label { color:#666; font-size:14px; }
.btn { padding:8px 15px; border:none; border-radius:3px; cursor:pointer; text-decoration:none; display:inline-block; margin:2px; font-size:12px; }
.btn-secondary { background:#6c757d; color:white; }
.btn-confirm { background:#28a745; color:white; }
.btn-reject { background:#dc3545; color:white; }
.btn:hover { opacity:0.8; }
table { width:100%; border-collapse:collapse; margin-top:10px; font-size:14px; }
th, td { padding:12px; text-align:left; border-bottom:1px solid #ddd; }
th { background:#f8f9fa; font-weight:bold; }
.status-pending { color:#ffc107; font-weight:bold; }
.status-paid { color:#28a745; font-weight:bold; }
.status-failed { color:#dc3545; font-weight:bold; }
.amount { font-weight:bold; color:#28a745; }
.footer { background:#20B2AA; color:white; text-align:center; padding:15px 20px; margin-top:auto; border-radius:10px 10px 0 0; }
input[type=text].rejection-reason-input { padding:3px; width:120px; border:1px solid #ccc; border-radius:3px; margin-top:5px; } 

/* Custom Modal Styling */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    display: none; 
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
    max-width: 450px;
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
    margin-top: 20px;
}
.modal-actions .btn {
    padding: 10px 25px;
    font-size: 16px;
    min-width: 100px;
}
#rejectionReasonContainer {
    margin-top: 20px;
    text-align: left;
    display: none; 
}
#rejectionReasonContainer label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    color: #333;
}
#rejectionReasonInput {
    width: calc(100% - 10px); 
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}
</style>
</head>
<body>

<div class="header"><h1>Hostel Payment Management (OHBS)</h1></div>

<div class="container">
    <?php if (!empty($success)): ?>
        <p style="background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 10px; border-radius: 5px;"><?= $success ?></p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <p style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 10px; border-radius: 5px;"><?= $error ?></p>
    <?php endif; ?>

    <div style="margin-bottom:20px; text-align:right;">
        <a href="admin_dashboard.php" class="btn btn-secondary">Go to Dashboard</a>
    </div>

    <div class="card">
        <h3>Payment Statistics Summary</h3>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number" style="color:#20B2AA;"><?= $stats['total_payments'] ?></div>
                <div class="stat-label">Total Payments</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color:#28a745;">Tsh <?= number_format($stats['total_amount']) ?></div>
                <div class="stat-label">Total Amount</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color:#ffc107;"><?= $stats['pending_payments'] ?></div>
                <div class="stat-label">Pending Payments</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color:#28a745;"><?= $stats['completed_payments'] ?></div>
                <div class="stat-label">Completed Payments</div>
            </div>
        </div>
    </div>

    <div class="card">
        <h3>List of All Payments</h3>
        <?php if($payments->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>Student</th>
                    <th>Phone Number</th>
                    <th>Booking ID</th>
                    <th>Amount</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Transaction Ref</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($p = $payments->fetch_assoc()): ?>
                <tr>
                    <td><?= $p['payment_id'] ?></td>
                    <td><?= htmlspecialchars($p['username']) ?></td>
                    <td><?= htmlspecialchars($p['phone']) ?></td>
                    <td><?= $p['booking_id'] ?? 'N/A' ?></td>
                    <td class="amount">Tsh <?= number_format($p['amount']) ?></td>
                    <td><?= ucfirst($p['payment_method']) ?></td>
                    <td><span class="status-<?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
                    <td><?= date('M j, Y', strtotime($p['payment_date'])) ?></td>
                    <td><?= htmlspecialchars($p['transaction_reference'] ?? 'N/A') ?></td>
                    <td>
                        <?php if($p['status'] == 'pending'): ?>
                            <button type="button" class="btn btn-confirm" 
                                    onclick="showPaymentModal(<?= $p['payment_id'] ?>, 'confirm', '<?= htmlspecialchars($p['username']) ?>')">✓ Confirm</button>
                            <button type="button" class="btn btn-reject" 
                                    onclick="showPaymentModal(<?= $p['payment_id'] ?>, 'reject', '<?= htmlspecialchars($p['username']) ?>')">✗ Reject</button>
                        <?php else: ?>
                            <span style="color:#666;">No action needed</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>No payments found.</p>
        <?php endif; ?>
    </div>
</div>

<div class="footer">&copy; <?= date('Y') ?> OHBS - Online Hostel Booking System</div>

<div id="paymentConfirmationModal" class="modal-overlay">
    <div class="modal-content">
        <h3 id="paymentModalTitle">Confirm Payment</h3>
        <p id="paymentModalMessage">Are you sure you want to confirm this payment?</p>
        
        <div id="rejectionReasonContainer">
            <label for="rejectionReasonInput">Reason for rejection:</label>
            <input type="text" id="rejectionReasonInput" placeholder="Enter reason (optional)">
        </div>

        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" onclick="hidePaymentModal()">No</button>
            <button type="button" class="btn btn-confirm" id="confirmActionBtn">Yes</button>
        </div>
    </div>
</div>

<form id="paymentActionForm" method="POST" style="display: none;">
    </form>

<script>
let currentPaymentId = null;
let currentAction = null; // 'confirm' or 'reject'

function showPaymentModal(paymentId, action, username) {
    currentPaymentId = paymentId;
    currentAction = action;

    const modalTitle = document.getElementById('paymentModalTitle');
    const modalMessage = document.getElementById('paymentModalMessage');
    const rejectionReasonContainer = document.getElementById('rejectionReasonContainer');
    const rejectionReasonInput = document.getElementById('rejectionReasonInput');
    const confirmActionBtn = document.getElementById('confirmActionBtn');

    if (action === 'confirm') {
        modalTitle.textContent = "Confirm Payment";
        modalMessage.textContent = `Are you sure you want to confirm payment for ${username} (ID: ${paymentId})?`;
        rejectionReasonContainer.style.display = 'none';
        rejectionReasonInput.value = ''; 
        confirmActionBtn.className = 'btn btn-confirm'; 
    } else if (action === 'reject') {
        modalTitle.textContent = "Reject Payment";
        modalMessage.textContent = `Are you sure you want to reject payment for ${username} (ID: ${paymentId})?`;
        rejectionReasonContainer.style.display = 'block'; 
        confirmActionBtn.className = 'btn btn-reject'; 
    }

    document.getElementById('paymentConfirmationModal').style.display = 'flex';
}

function hidePaymentModal() {
    document.getElementById('paymentConfirmationModal').style.display = 'none';
    currentPaymentId = null;
    currentAction = null;
    document.getElementById('rejectionReasonInput').value = ''; 
}

document.getElementById('confirmActionBtn').addEventListener('click', function() {
    if (currentPaymentId && currentAction) {
        
        const form = document.getElementById('paymentActionForm');
        
        while (form.firstChild) {
            form.removeChild(form.firstChild);
        }

        const paymentIdInput = document.createElement('input');
        paymentIdInput.type = 'hidden';
        paymentIdInput.name = 'payment_id';
        paymentIdInput.value = currentPaymentId;
        form.appendChild(paymentIdInput);

        if (currentAction === 'confirm') {
            const confirmInput = document.createElement('input');
            confirmInput.type = 'hidden';
            confirmInput.name = 'confirm_payment';
            confirmInput.value = '1';
            form.appendChild(confirmInput);
        } else if (currentAction === 'reject') {
            const rejectInput = document.createElement('input');
            rejectInput.type = 'hidden';
            rejectInput.name = 'reject_payment';
            rejectInput.value = '1';
            form.appendChild(rejectInput);

            const rejectionReasonInput = document.getElementById('rejectionReasonInput');
            const reasonInput = document.createElement('input');
            reasonInput.type = 'hidden';
            reasonInput.name = 'rejection_reason';
            reasonInput.value = rejectionReasonInput.value.trim(); 
            form.appendChild(reasonInput);
        }
        
        hidePaymentModal(); 
        form.submit(); 
    }
});
</script>

</body>
</html>