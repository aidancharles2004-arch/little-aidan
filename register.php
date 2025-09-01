<?php
include 'connect.php'; 
session_start();

$page_title = "Register - OHBS";
$username = $password_input = $full_name = $email = $phone = $course = $gender = "";
$username_err = $password_err = $full_name_err = $email_err = $phone_err = $course_err = $gender_err = '';
$success = '';
$duplicate = false;
$duplicate_field = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password_input = trim($_POST['password']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $course = trim($_POST['course']);
    $gender = trim($_POST['gender']);

    
    if (empty($username)) $username_err = "Username is required";
    if (empty($password_input)) {
        $password_err = "Password is required";
    } else if(strlen($password_input) < 4) {
        $password_err = "Password must be at least 4 characters long";
    } else if(!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password_input)) {
        $password_err = "Password must contain at least 1 special character";
    }
    if (empty($full_name)) $full_name_err = "Full name is required";
    if (empty($email)) $email_err = "Email is required";
    else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $email_err = "Invalid email format";

    if (empty($phone)) {
        $phone_err = "Phone number is required";
    } else if (!ctype_digit($phone)) {
        $phone_err = "Phone number must be digits only";
    } else if (strlen($phone) !== 10) {
        $phone_err = "Phone number must be exactly 10 digits";
    }

    if (empty($course)) $course_err = "Course is required";
    if (empty($gender)) $gender_err = "Gender is required";


    if (empty($username_err) && empty($password_err) && empty($full_name_err) && empty($email_err) &&
        empty($phone_err) && empty($course_err) && empty($gender_err)) {



        $stmt_check = $conn->prepare("SELECT user_id FROM Users WHERE email=? OR phone=?");
        $stmt_check->bind_param("ss", $email, $phone);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        if($result_check->num_rows > 0){
            $duplicate = true;
            $duplicate_field = "Email or Phone already in use!";
        } else {
            
            $stmt = $conn->prepare("INSERT INTO Users (username, password, full_name, email, phone, course, gender, role) VALUES (?, ?, ?, ?, ?, ?, ?, 'student')");
            $stmt->bind_param("sssssss", $username, $password_input, $full_name, $email, $phone, $course, $gender);
            if ($stmt->execute()) {
                $_SESSION['user_id'] = $conn->insert_id;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = 'student';
                $_SESSION['full_name'] = $full_name;
                $_SESSION['phone'] = $phone;
                $success = "Registration successful!";
            } else {
                $success = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
        $stmt_check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $page_title; ?></title>
<style>
body { 
    font-family:'Segoe UI',sans-serif; 
    background:whitesmoke; 
    margin:0; 
    padding:0; 
    display: flex; 
    flex-direction: column; 
    min-height: 100vh;
}

header { 
    background:#20B2AA; 
    color:white; 
    text-align:center; 
    padding:20px 0; 
    font-size:30px; 
    font-weight:bold;
}

footer { 
    background:#1a9b94; 
    color:white; 
    text-align:center; 
    padding:15px 0; 
    margin-top:auto; 
}

.main-content { 
    display:flex; 
    justify-content:center; 
    align-items:center; 
    flex:1; 
    flex-direction: column;  /* panga vertical */
    padding:50px;
}

.register-container { 
    background:#fff; 
    padding:30px; 
    border-radius:12px; 
    border:2px solid #20B2AA; 
    width:400px; 
    box-shadow:0 8px 20px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column; /* ndani ipangike wima */
}

.register-container h2 { 
    text-align:center; 
    color:#20B2AA; 
    margin-bottom:20px;
}

.form-group {
    margin-bottom:15px;
    display: flex;
    flex-direction: column; /* label na input zikae wima */
}

.form-group label {
    margin-bottom:5px; 
    font-weight:600;
}

.form-group input, .form-group select {
    width:100%; 
    padding:10px; 
    border:2px solid #ddd; 
    border-radius:8px;
}

.error-message {
    color:red; 
    font-size:13px; 
    margin-top:3px;
}

.error-input {border-color:red;}

.register-btn {
    width:100%; 
    padding:12px; 
    background:#20B2AA; 
    color:white; 
    border:none; 
    border-radius:8px; 
    font-weight:bold; 
    cursor:pointer;
}

.register-btn:hover {background:#1a9b94;}

.login-link {
    text-align:center; 
    margin-top:15px;
}

.modal { 
    display:none; 
    position:fixed; 
    z-index:1000; 
    left:0; top:0; 
    width:100%; 
    height:100%; 
    background:rgba(0,0,0,0.5); 
    justify-content:center; 
    align-items:center;
}

.modal-content { 
    background:white; 
    padding:20px 30px; 
    border-radius:10px; 
    text-align:center; 
    max-width:400px; 
    box-shadow:0 5px 15px rgba(0,0,0,0.3);
}

.close-btn { 
    background:#20B2AA; 
    color:white; 
    border:none; 
    padding:10px 20px; 
    border-radius:8px; 
    cursor:pointer; 
    font-weight:bold;
}

.close-btn:hover {background:#1a9b94;}

#successModal .modal-content { 
    background-color: #d4edda; 
    border:1px solid #c3e6cb; 
    color:#155724;
}
#successModal h3 {color:#155724;}
#successModal .close-btn {background-color:#155724;}
#successModal .close-btn:hover {background-color:#0d361c;}

#duplicateModal .modal-content { 
    background-color: #f8d7da; 
    border:1px solid #f5c6cb; 
    color:#721c24;
}
#duplicateModal h3 {color:#721c24;}
#duplicateModal .close-btn {background-color:#721c24;}
#duplicateModal .close-btn:hover {background-color:#4c0000;}
</style>

</head>
<body>

<header>
    Online Hostel Booking System (OHBS)
</header>

<div class="main-content">
<div class="register-container">
    <h2>Register Account</h2>
    <form method="POST">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" placeholder="enter your username"  value="<?php echo isset($username)?$username:''; ?>" class="<?php echo !empty($username_err)?'error-input':''; ?>" >
            <div class="error-message"><?php echo $username_err; ?></div>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="enter your password" id="password" class="<?php echo !empty($password_err)?'error-input':''; ?>">
            <div class="error-message" id="password_error"><?php echo $password_err; ?></div>
            <small>Password min 4 chars & 1 special char</small>
        </div>
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="full_name"   placeholder="enter your full name" value="<?php echo isset($full_name)?$full_name:''; ?>" class="<?php echo !empty($full_name_err)?'error-input':''; ?>">
            <div class="error-message"><?php echo $full_name_err; ?></div>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" placeholder="enter your valid email" value="<?php echo isset($email)?$email:''; ?>" class="<?php echo !empty($email_err)?'error-input':''; ?>">
            <div class="error-message"><?php echo $email_err; ?></div>
        </div>
        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" placeholder="enter your phone-numbber" value="<?php echo isset($phone)?$phone:''; ?>" class="<?php echo !empty($phone_err)?'error-input':''; ?>">
            <div class="error-message"><?php echo $phone_err; ?></div>
        </div>
        <div class="form-group">
            <label>Course/Program</label>
            <input type="text" name="course" placeholder="enter your course or program" value="<?php echo isset($course)?$course:''; ?>" class="<?php echo !empty($course_err)?'error-input':''; ?>">
            <div class="error-message"><?php echo $course_err; ?></div>
        </div>
        <div class="form-group">
            <label>Gender</label>
            <select name="gender" class="<?php echo !empty($gender_err)?'error-input':''; ?>">
                <option value="">--Select--</option>
                <option value="Male" <?php if(isset($gender)&&$gender=="Male") echo "selected"; ?>>Male</option>
                <option value="Female" <?php if(isset($gender)&&$gender=="Female") echo "selected"; ?>>Female</option>
            </select>
            <div class="error-message"><?php echo $gender_err; ?></div>
        </div>
        <button type="submit" class="register-btn">Create Account</button>
    </form>
    <div class="login-link">
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</div>
</div>

<footer>
    &copy; <?php echo date("Y"); ?> Online Hostel Booking System (OHBS). All rights reserved.
</footer>

<!-- Modals -->
<div class="modal" id="successModal">
  <div class="modal-content">
    <h3>Registration Successful</h3>
    <p>Your account has been created. You can now login.</p>
    <button class="close-btn" onclick="goLogin()">OK</button>
  </div>
</div>

<div class="modal" id="duplicateModal">
  <div class="modal-content">
    <h3>Account Already Exists</h3>
    <p></p>
    <button class="close-btn" onclick="goLogin()">OK</button>
  </div>
</div>

<script>
function goLogin(){ window.location.href="login.php"; }

document.getElementById('password').addEventListener('input', function() {
    let pass = this.value;
    let errorField = document.getElementById('password_error');
    if(pass.length < 4) errorField.textContent = "Password min 4 chars";
    else if(!/[!@#$%^&*(),.?":{}|<>]/.test(pass)) errorField.textContent = "Password must have 1 special char";
    else { errorField.textContent="Password looks good!"; errorField.style.color="green"; }
});




<?php if ($success === "Registration successful!"): ?>
document.getElementById("successModal").style.display = "flex";
<?php elseif ($duplicate): ?>
document.getElementById("duplicateModal").style.display = "flex";
document.getElementById("duplicateModal").querySelector("p").textContent = "<?php echo $duplicate_field; ?>";
<?php endif; ?>
</script>

</body>
</html>
