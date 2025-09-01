<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'OHBS - Online Hostel Booking System'; ?></title>
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
        
        /* Header Styles */
        .main-header {
            background-color: #20B2AA;
            color: white;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(32, 178, 170, 0.3);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            display: flex;
            align-items: center;
            font-size: 24px;
            font-weight: bold;
            text-decoration: none;
            color: white;
        }
        
        .logo:hover {
            color: #e0f7fa;
        }
        
        .logo-icon {
            font-size: 28px;
            margin-right: 10px;
        }
        
        .nav-menu {
            display: flex;
            list-style: none;
            gap: 20px;
            align-items: center;
        }
        
        .nav-menu li {
            position: relative;
        }
        
        .nav-menu a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .nav-menu a:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }
        
        .nav-menu a.active {
            background-color: rgba(255, 255, 255, 0.3);
            font-weight: bold;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }
        
        .user-name {
            font-weight: 500;
        }
        
        .logout-btn {
            background-color: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 6px 12px;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        
        .logout-btn:hover {
            background-color: rgba(255, 255, 255, 0.3);
            transform: translateY(-1px);
        }
        
        /* Mobile Menu */
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .header-container {
                padding: 0 15px;
            }
            
            .logo {
                font-size: 20px;
            }
            
            .nav-menu {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background-color: #20B2AA;
                flex-direction: column;
                padding: 20px;
                box-shadow: 0 5px 15px rgba(32, 178, 170, 0.3);
            }
            
            .nav-menu.active {
                display: flex;
            }
            
            .mobile-menu-toggle {
                display: block;
            }
            
            .user-info {
                gap: 10px;
            }
            
            .user-name {
                display: none;
            }
        }
        
        /* Main Content Area */
        .main-content {
            flex: 1;
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
            width: 100%;
        }
        
        /* Page Title */
        .page-title {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .page-title h1 {
            color: #20B2AA;
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .page-title p {
            color: #666;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <a href="<?php echo isset($_SESSION['role']) && $_SESSION['role'] == 'admin' ? 'admin_dashboard.php' : 'index.php'; ?>" class="logo">
                <span class="logo-icon">OHBS</span>
            </a>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <nav>
                    <ul class="nav-menu" id="navMenu">
                        <?php if ($_SESSION['role'] == 'student'): ?>
                            <li><a href="index.php" <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'class="active"' : ''; ?>>Dashboard</a></li>
                            <li><a href="booking.php" <?php echo basename($_SERVER['PHP_SELF']) == 'booking.php' ? 'class="active"' : ''; ?>>Book Room</a></li>
                            <li><a href="my_bookings.php" <?php echo basename($_SERVER['PHP_SELF']) == 'my_bookings.php' ? 'class="active"' : ''; ?>>My Bookings</a></li>
                            <li><a href="payment_simple.php" <?php echo basename($_SERVER['PHP_SELF']) == 'payment_simple.php' ? 'class="active"' : ''; ?>>Payments</a></li>
                            <li><a href="room_availability_cards.php" <?php echo basename($_SERVER['PHP_SELF']) == 'room_availability_cards.php' ? 'class="active"' : ''; ?>>Availability</a></li>
                            <li><a href="profile.php" <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'class="active"' : ''; ?>>Profile</a></li>
                        <?php elseif ($_SESSION['role'] == 'admin'): ?>
                            <li><a href="admin_dashboard.php" <?php echo basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'class="active"' : ''; ?>>Dashboard</a></li>
                            <li><a href="manage_user_dashboard.php" <?php echo basename($_SERVER['PHP_SELF']) == 'manage_user_dashboard.php' ? 'class="active"' : ''; ?>>Users</a></li>
                            <li><a href="manage_bookings.php" <?php echo basename($_SERVER['PHP_SELF']) == 'manage_bookings.php' ? 'class="active"' : ''; ?>>Bookings</a></li>
                            <li><a href="manage_payments.php" <?php echo basename($_SERVER['PHP_SELF']) == 'manage_payments.php' ? 'class="active"' : ''; ?>>Payments</a></li>
                            <li><a href="manage_hostels.php" <?php echo basename($_SERVER['PHP_SELF']) == 'manage_hostels.php' ? 'class="active"' : ''; ?>>Hostels</a></li>
                            <li><a href="manage_rooms.php" <?php echo basename($_SERVER['PHP_SELF']) == 'manage_rooms.php' ? 'class="active"' : ''; ?>>Rooms</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
                
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)); ?>
                    </div>
                    <span class="user-name">
                        <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                    </span>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
                
                <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
                    ≡
                </button>
            <?php else: ?>
                <nav>
                    <ul class="nav-menu">
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </header>

    <script>
        function toggleMobileMenu() {
            const navMenu = document.getElementById('navMenu');
            navMenu.classList.toggle('active');
        }
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const navMenu = document.getElementById('navMenu');
            const toggleBtn = document.querySelector('.mobile-menu-toggle');
            
            if (!navMenu.contains(event.target) && !toggleBtn.contains(event.target)) {
                navMenu.classList.remove('active');
            }
        });
    </script>
