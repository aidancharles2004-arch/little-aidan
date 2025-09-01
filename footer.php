    <footer class="main-footer">
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>OHBS</h3>
                    <p>Online Hostel Booking System</p>
                    <p>Making hostel booking simple and efficient for students.</p>
                </div>
                
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <?php if ($_SESSION['role'] == 'student'): ?>
                                <li><a href="index.php">Dashboard</a></li>
                                <li><a href="booking.php">Book Room</a></li>
                                <li><a href="my_bookings.php">My Bookings</a></li>
                                <li><a href="payment_simple.php">Payments</a></li>
                            <?php else: ?>
                                <li><a href="admin_dashboard.php">Admin Dashboard</a></li>
                                <li><a href="manage_bookings.php">Manage Bookings</a></li>
                                <li><a href="manage_payments.php">Manage Payments</a></li>
                                <li><a href="manage_user_dashboard.php">Manage Users</a></li>
                            <?php endif; ?>
                        <?php else: ?>
                            <li><a href="login.php">Login</a></li>
                            <li><a href="register.php">Register</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Support</h4>
                    <ul class="footer-links">
                        <li><a href="#help">Help Center</a></li>
                        <li><a href="#contact">Contact Us</a></li>
                        <li><a href="#faq">FAQ</a></li>
                        <li><a href="#terms">Terms of Service</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Contact Info</h4>
                    <div class="contact-info">
                        <p>Email: support@ohbs.com</p>
                        <p>Phone: +255 123 456 789</p>
                        <p>Address: University Campus</p>
                        <p>Hours: 24/7 Support</p>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <p>&copy; <?php echo date('Y'); ?> OHBS - Online Hostel Booking System. All rights reserved.</p>
                    <div class="footer-social">
                        <a href="#facebook" title="Facebook">FB</a>
                        <a href="#twitter" title="Twitter">TW</a>
                        <a href="#instagram" title="Instagram">IG</a>
                        <a href="#linkedin" title="LinkedIn">LI</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <style>
        /* Footer Styles */
        .main-footer {
            background-color: #20B2AA;
            color: white;
            margin-top: auto;
            box-shadow: 0 -2px 10px rgba(32, 178, 170, 0.3);
        }
        
        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            padding: 40px 0 30px;
        }
        
        .footer-section h3 {
            font-size: 24px;
            margin-bottom: 15px;
            color: white;
        }
        
        .footer-section h4 {
            font-size: 18px;
            margin-bottom: 15px;
            color: #e0f7fa;
        }
        
        .footer-section p {
            margin-bottom: 10px;
            line-height: 1.6;
            color: #e0f7fa;
        }
        
        .footer-links {
            list-style: none;
            padding: 0;
        }
        
        .footer-links li {
            margin-bottom: 8px;
        }
        
        .footer-links a {
            color: #e0f7fa;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .footer-links a:hover {
            color: white;
            transform: translateX(5px);
        }
        
        .contact-info p {
            margin-bottom: 8px;
            color: #e0f7fa;
        }
        
        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            padding: 20px 0;
        }
        
        .footer-bottom-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .footer-bottom p {
            margin: 0;
            color: #e0f7fa;
        }
        
        .footer-social {
            display: flex;
            gap: 15px;
        }
        
        .footer-social a {
            color: #e0f7fa;
            text-decoration: none;
            font-size: 20px;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .footer-social a:hover {
            color: white;
            transform: translateY(-3px);
        }
        
        /* Responsive Footer */
        @media (max-width: 768px) {
            .footer-content {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 25px;
                padding: 30px 0 20px;
            }
            
            .footer-section h3 {
                font-size: 20px;
            }
            
            .footer-section h4 {
                font-size: 16px;
            }
            
            .footer-bottom-content {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }
            
            .footer-social {
                justify-content: center;
            }
        }
        
        @media (max-width: 480px) {
            .footer-content {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .footer-container {
                padding: 0 15px;
            }
        }
    </style>
</body>
</html>
