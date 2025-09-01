<?php
include 'connect.php';
session_start();

// Check if student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit;
}

// Fetch student info
$stmt = $conn->prepare("SELECT * FROM Users WHERE user_id=?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Room Booking - OHBS</title>
<!-- Font Awesome CDN haihitajiki tena bila icons kwenye nav, lakini inaweza kuachwa kwa matumizi mengine -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<style>
  /* Reset & Base */
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: whitesmoke;
    color: #333;
    line-height: 1.6;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
  }

  /* Header */
  .main-header {
    background-color: #20B2AA;
    color: white;
    padding: 20px 0;
    text-align: center;
    box-shadow: 0 2px 10px rgba(32, 178, 170, 0.3);
  }

  .main-header h1 {
    font-size: 28px;
    font-weight: bold;
    margin-bottom: 5px;
  }

  .main-header p {
    font-size: 16px;
    opacity: 0.9;
  }

  /* Navigation */
  .main-nav {
    background-color: #1a9b94;
    padding: 10px 0;
  }

  .nav-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    justify-content: space-between; /* Hii inasukuma nav-links kushoto na user-info kulia */
    align-items: center;
  }

  .nav-links {
    display: flex; /* Hii inafanya links zikae mlalo */
    gap: 20px; /* Nafasi kati ya links */
    align-items: center;
    margin-right: 30px; /* **MAREKEBISHO HAPA: Nafasi kati ya nav-links na user-info** */
  }

  .nav-links a {
    color: white;
    text-decoration: none;
    padding: 8px 15px;
    border-radius: 5px;
    font-weight: 500;
    transition: background-color 0.3s ease;
  }

  .nav-links a:hover {
    background-color: rgba(255, 255, 255, 0.2);
  }

  .nav-links a.active {
    background-color: rgba(255, 255, 255, 0.3);
  }

  .user-info {
    color: white;
    font-weight: 500;
    display: flex; /* Kufanya Welcome text na Logout button zikae mlalo */
    align-items: center;
  }

  .logout-btn {
    background-color: rgba(255, 255, 255, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: white;
    padding: 8px 15px;
    border-radius: 5px;
    text-decoration: none;
    margin-left: 15px; /* Nafasi kati ya welcome text na logout button */
    transition: all 0.3s ease;
  }

  .logout-btn:hover {
    background-color: rgba(255, 255, 255, 0.3);
  }

  /* Main Content */
  .main-content {
    flex: 1;
    max-width: 1000px;
    margin: 0 auto;
    padding: 30px 20px;
    width: 100%;
  }

  /* Welcome Section */
  .welcome-section {
    background: white;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 30px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    text-align: center;
    border-left: 5px solid #20B2AA;
  }

  .welcome-section h1 {
    color: #20B2AA;
    font-size: 24px;
    margin-bottom: 10px;
  }

  .welcome-section p {
    color: #666;
    font-size: 16px;
  }

  /* Main */
  .main {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    border-left: 5px solid #20B2AA;
  }

  /* Booking Form */
  .booking h2 {
    text-align: center;
    font-size: 22px;
    margin-bottom: 30px;
    color: #20B2AA;
    position: relative;
  }
  .booking h2::after {
    content: '';
    width: 60px; height: 3px;
    background: #20B2AA;
    display: block;
    margin: 8px auto 0;
    border-radius: 2px;
  }
  .form-group { margin-bottom: 20px; }
  .form-group label { display: block; margin-bottom: 5px; font-weight: 500; color: #333; }
  .form-group input, .form-group select {
    width: 100%; padding: 12px 15px;
    border-radius: 5px; border: 1px solid #ddd;
    font-size: 14px; transition: all 0.2s ease;
  }
  .form-group input:focus, .form-group select:focus {
    border-color: #20B2AA;
    outline: none;
  }

  /* Buttons */
  .booking-actions { display: flex; justify-content: center; gap: 15px; margin-top: 25px; }
  .booking-submit-button, .booking-reset-button {
    padding: 12px 25px;
    border: none;
    border-radius: 5px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
  }
  .booking-submit-button {
    background: #20B2AA; color: white;
  }
  .booking-submit-button:hover { background: #1a9b94; }
  .booking-submit-button:disabled {
    background: #ccc;
    cursor: not-allowed;
  }
  .booking-reset-button {
    background: #6c757d; color: white;
  }
  .booking-reset-button:hover { background: #5a6268; }

  /* Info card */
  .info-card {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-top: 25px;
    font-size: 14px;
    color: #666;
    border-left: 3px solid #20B2AA;
  }

  .info-card h4 {
    color: #20B2AA;
    margin-bottom: 10px;
  }

  /* Footer */
  .main-footer {
    background-color: #20B2AA;
    color: white;
    text-align: center;
    padding: 20px 0;
    margin-top: auto;
  }

  .main-footer p {
    margin: 5px 0;
  }

  /* Responsive */
  @media (max-width: 768px) {
    .main-header h1 {
      font-size: 24px;
    }

    .nav-container {
      flex-direction: column;
      gap: 15px;
      text-align: center;
    }

    .nav-links {
      flex-wrap: wrap;
      justify-content: center;
      gap: 10px;
    }

    .main-content {
      padding: 20px 15px;
    }
  }
</style>
</head>
<body>

<header class="main-header">
  <h1>ONLINE HOSTEL BOOKING SYSTEM (OHBS)</h1>
  <p>University of Dodoma - Student Accommodation Portal</p>
</header>

<nav class="main-nav">
  <div class="nav-container">
    <div class="nav-links">
      <a href="room_availability_cards.php">View Rooms</a>
      <a href="booking.php" class="active">Book Room</a>
      <a href="my_bookings.php">My Bookings</a>
      <a href="payment.php">Payment</a>
      <a href="clear_bookings.php">Clear Bookings</a>
      <a href="index.php">Dashboard</a>
    </div>
    <div class="user-info">
      Welcome, <?php echo htmlspecialchars($student['full_name']); ?>
      <a href="logout.php" class="logout-btn">Logout</a>
    </div>
  </div>
</nav>

<div class="main-content">
  <div class="welcome-section">
    <h1>Book Your Room</h1>
    <p>Select your preferred accommodation from available options</p>
  </div>

  <div class="main">
    <div class="booking">
      <h2>Room Booking Form</h2>
      <form method="POST" action="process_booking.php" id="bookingForm">
        <div class="form-group">
          <label for="gender">Your Gender (From Profile)</label>
          <select name="gender" id="gender" required readonly style="background: #f8f9fa; cursor: not-allowed;">
            <?php if (isset($student['gender'])): ?>
              <option value="<?php echo $student['gender']; ?>" selected>
                <?php echo $student['gender'] == 'male' ? 'Male' : 'Female'; ?>
              </option>
            <?php else: ?>
              <option value="">Gender not set</option>
            <?php endif; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="hostel">Select Hostel Block</label>
          <select name="hostel" id="hostel" required disabled>
            <option value="">Choose gender first...</option>
          </select>
        </div>
        <div class="form-group">
          <label for="side">Select Side</label>
          <select name="side" id="side" required disabled>
            <option value="">Choose hostel first...</option>
          </select>
        </div>
        <div class="form-group">
          <label for="floor">Select Floor</label>
          <select name="floor" id="floor" required disabled>
            <option value="">Choose side first...</option>
          </select>
        </div>
        <div class="form-group">
          <label for="room">Select Room</label>
          <select name="room" id="room" required disabled>
            <option value="">Choose floor first...</option>
          </select>
        </div>
        <div class="form-group">
          <label for="check_in_date">Check-in Date</label>
          <input type="date" name="check_in_date" id="check_in_date" required min="<?php echo date('Y-m-d'); ?>">
        </div>
        <div class="form-group">
          <label for="check_out_date">Check-out Date</label>
          <input type="date" name="check_out_date" id="check_out_date" required min="<?php echo date('Y-m-d'); ?>">
        </div>
        <div class="booking-actions">
          <button type="submit" class="booking-submit-button" id="submitBtn" disabled>Book My Room</button>
          <button type="reset" class="booking-reset-button" id="resetBtn">Reset Form</button>
        </div>
      </form>
      <div class="info-card">
        <h4>Booking Instructions</h4>
        <p>1. Your gender is pre-filled from your profile.</p>
        <p>2. Choose your preferred hostel block.</p>
        <p>3. Select side, floor, and room.</p>
        <p>4. Pick your check-in and check-out dates.</p>
        <p>5. Submit your booking for approval.</p>
      </div>
    </div>
  </div>
</div>

<footer class="main-footer">
  <p>&copy; <?php echo date('Y'); ?> Online Hostel Booking System (OHBS)</p>
  <p>University of Dodoma - College of Informatics and Virtual Education</p>
  <p>Providing quality student accommodation services</p>
</footer>

<script>
// Gender-based hostel allocation
const genderHostels = {
    'male': {
        'Block1': ['A', 'B'],
        'Block2': ['A'],
        'Block3': ['A', 'B'],
        'Block4': ['A', 'B'],
        'Block5': ['A']
    },
    'female': {
        'Block6': ['A', 'B'],
        'Block5': ['B'],
        'Block2': ['B']
    }
};

// Initialize form
document.addEventListener('DOMContentLoaded', function() {
    const genderSelect = document.getElementById('gender');
    const hostelSelect = document.getElementById('hostel');
    const sideSelect = document.getElementById('side');
    const floorSelect = document.getElementById('floor');
    const roomSelect = document.getElementById('room');
    const checkInDate = document.getElementById('check_in_date');
    const checkOutDate = document.getElementById('check_out_date');
    const submitBtn = document.getElementById('submitBtn');

    // Load hostels based on gender
    function loadHostels() {
        const gender = genderSelect.value;
        hostelSelect.innerHTML = '<option value="">Choose hostel...</option>';
        sideSelect.innerHTML = '<option value="">Choose hostel first...</option>';
        floorSelect.innerHTML = '<option value="">Choose side first...</option>';
        roomSelect.innerHTML = '<option value="">Choose floor first...</option>';

        if (gender && genderHostels[gender]) {
            hostelSelect.disabled = false;
            Object.keys(genderHostels[gender]).forEach(hostel => {
                const option = document.createElement('option');
                option.value = hostel;
                option.textContent = hostel;
                hostelSelect.appendChild(option);
            });
        } else {
            hostelSelect.disabled = true;
        }
        updateSubmitButton();
    }

    // Load sides based on hostel
    function loadSides() {
        const gender = genderSelect.value;
        const hostel = hostelSelect.value;
        sideSelect.innerHTML = '<option value="">Choose side...</option>';
        floorSelect.innerHTML = '<option value="">Choose side first...</option>';
        roomSelect.innerHTML = '<option value="">Choose floor first...</option>';

        if (gender && hostel && genderHostels[gender][hostel]) {
            sideSelect.disabled = false;
            genderHostels[gender][hostel].forEach(side => {
                const option = document.createElement('option');
                option.value = side;
                option.textContent = `Side ${side}`;
                sideSelect.appendChild(option);
            });
        } else {
            sideSelect.disabled = true;
        }
        updateSubmitButton();
    }

    // Load floors based on side
    function loadFloors() {
        const side = sideSelect.value;
        floorSelect.innerHTML = '<option value="">Choose floor...</option>';
        roomSelect.innerHTML = '<option value="">Choose floor first...</option>';

        if (side) {
            floorSelect.disabled = false;
            for (let i = 0; i <= 3; i++) {
                const option = document.createElement('option');
                option.value = i;
                option.textContent = i === 0 ? 'Ground Floor' : `Floor ${i}`;
                floorSelect.appendChild(option);
            }
        } else {
            floorSelect.disabled = true;
        }
        updateSubmitButton();
    }

    // Load rooms based on floor
    function loadRooms() {
        const hostel = hostelSelect.value;
        const side = sideSelect.value;
        const floor = floorSelect.value;
        roomSelect.innerHTML = '<option value="">Choose room...</option>';

        if (hostel && side && floor !== '') {
            roomSelect.disabled = false;
            const floorName = floor === '0' ? 'Ground' : `Floor${floor}`;
            
            // Generate 12 rooms per floor
            for (let i = 1; i <= 12; i++) {
                const roomNumber = `${hostel}_${side}_${floorName}_Room${i}`;
                const option = document.createElement('option');
                option.value = roomNumber;
                option.textContent = `Room ${i}`;
                roomSelect.appendChild(option);
            }
        } else {
            roomSelect.disabled = true;
        }
        updateSubmitButton();
    }

    // Update submit button state
    function updateSubmitButton() {
        const allFieldsFilled = genderSelect.value && hostelSelect.value &&
                               sideSelect.value && floorSelect.value !== '' &&
                               roomSelect.value && checkInDate.value && checkOutDate.value;
        submitBtn.disabled = !allFieldsFilled;
    }

    // Event listeners
    genderSelect.addEventListener('change', loadHostels);
    hostelSelect.addEventListener('change', loadSides);
    sideSelect.addEventListener('change', loadFloors);
    floorSelect.addEventListener('change', loadRooms);
    roomSelect.addEventListener('change', updateSubmitButton);
    checkInDate.addEventListener('change', updateSubmitButton);
    checkOutDate.addEventListener('change', updateSubmitButton);


    // Initialize on page load
    loadHostels();
    updateSubmitButton(); // Call on load to set initial state

    // Date validation (remains the same as it correctly shows alerts)
    checkInDate.addEventListener('change', function() {
        checkOutDate.min = this.value;
        if (checkOutDate.value && checkOutDate.value <= this.value) {
            checkOutDate.value = '';
        }
        updateSubmitButton(); // Update button state after check-in date changes
    });

    // Form submission
    document.getElementById('bookingForm').addEventListener('submit', function(e) {
        if (checkOutDate.value <= checkInDate.value) {
            e.preventDefault();
            alert('Check-out date must be after check-in date.');
            return;
        }

        // Show loading state
        submitBtn.textContent = 'Booking...';
        submitBtn.disabled = true;
    });
});
</script>
</body>
</html>
