<?php
session_start();
include 'connect.php';

$page_title = "Login - OHBS";
$error = ''; // Initialize error variable

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM Users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // HII NDIO SEHEMU ILIYOBADILISHWA: sasa inalinganisha password kama plain text
        if ($password === $user['password']) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name']; // Nimeongeza hii pia
            $_SESSION['phone'] = $user['phone']; // Nimeongeza hii pia

            if ($user['role'] == 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $error = "Password or username is incorrect!";
        }
    } else {
        $error = "Password or username is incorrect!";
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

        /* Main Header */
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

        /* Main Content */
        .main-content {
            flex: 1;
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
            width: 100%;
        }

        @media (max-width: 768px) {
            .main-header h1 {
                font-size: 24px;
            }

            .main-header p {
                font-size: 14px;
            }

            .main-content {
                padding: 30px 15px;
            }
        }
    </style>
</head>
<body>
    <header class="main-header">
        <h1>ONLINE HOSTEL BOOKING SYSTEM (OHBS)</h1>
        <p>University of Dodoma - Student Accommodation Portal</p>
    </header>

    <div class="main-content">
        <div class="login-container">
            <h2>Login to Your Account</h2>

            <style>
                .login-container {
                    background: white;
                    padding: 30px;
                    border-radius: 10px;
                    max-width: 450px;
                    margin: 0 auto;
                    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
                    border: 2px solid #20B2AA;
                }

                .login-container h2 {
                    text-align: center;
                    color: #20B2AA;
                    margin-bottom: 25px;
                    font-size: 24px;
                }

                .form-group {
                    margin-bottom: 20px;
                }

                .form-group label {
                    display: block;
                    margin-bottom: 8px;
                    font-weight: 600;
                    color: #333;
                    font-size: 14px;
                }

                .form-group input {
                    width: 100%;
                    padding: 12px;
                    border: 2px solid #ddd;
                    border-radius: 8px;
                    font-size: 16px;
                    transition: border-color 0.3s ease;
                    box-sizing: border-box;
                }

                .form-group input:focus {
                    outline: none;
                    border-color: #20B2AA;
                    box-shadow: 0 0 0 3px rgba(32, 178, 170, 0.1);
                }

                .login-btn {
                    width: 100%;
                    padding: 15px;
                    background: #20B2AA;
                    border: none;
                    color: white;
                    font-size: 16px;
                    font-weight: bold;
                    border-radius: 8px;
                    cursor: pointer;
                    margin-top: 10px;
                    transition: background-color 0.3s ease;
                }

                .login-btn:hover {
                    background: #1a9b94;
                }

                .error-message {
                    background: #f8d7da;
                    color: #721c24;
                    text-align: center;
                    margin-bottom: 20px;
                    padding: 12px;
                    border-radius: 8px;
                    border: 1px solid #f5c6cb;
                    font-weight: 500;
                }

                .success-message {
                    background: #d1ecf1;
                    color: #0c5460;
                    text-align: center;
                    margin-bottom: 20px;
                    padding: 12px;
                    border-radius: 8px;
                    border: 1px solid #bee5eb;
                    font-weight: 500;
                }

                .register-link {
                    text-align: center;
                    margin-top: 25px;
                    padding-top: 20px;
                    border-top: 1px solid #eee;
                }

                .register-link p {
                    margin: 0;
                    color: #666;
                    font-size: 14px;
                }

                .register-link a {
                    color: #20B2AA;
                    text-decoration: none;
                    font-weight: bold;
                }

                .register-link a:hover {
                    text-decoration: underline;
                }

                /* Responsive Design */
                @media (max-width: 768px) {
                    .login-container {
                        margin: 20px;
                        padding: 25px 20px;
                    }

                    .form-group input {
                        font-size: 16px; /* Prevents zoom on iOS */
                    }
                }
            </style>
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php
            $prefill_username = isset($_GET['username']) ? htmlspecialchars($_GET['username']) : '';
            if ($prefill_username):
            ?>
                <div class="success-message">
                    <strong>Account Found!</strong><br>
                    <small>Please login with your existing account</small>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username"
                           value="<?php echo $prefill_username; ?>"
                           placeholder="Enter your username" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password"
                           placeholder="Enter your password" required>
                </div>

                <button type="submit" class="login-btn">Login to Account</button>
            </form>

            <div class="register-link">
                <p>Don't have an account? <a href="register.php">Create new account</a></p>
            </div>
        </div>
    </div>

    <footer style="background-color: #20B2AA; color: white; text-align: center; padding: 20px 0; margin-top: auto;">
        <p>&copy; <?php echo date('Y'); ?> Online Hostel Booking System (OHBS). All rights reserved.</p>
        <p>University of Dodoma - College of Informatics and Virtual Education</p>
    </footer>

</body>
</html>