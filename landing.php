<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OHBS - Online Hostel Booking System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            overflow-x: hidden;
            /* Hii hapa imebadilishwa */
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* Header */
        .main-header {
            background: linear-gradient(135deg, #20B2AA 0%, #1a9b94 100%);
            color: white;
            padding: 15px 0;
            position: fixed;
            width: 100%;
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
            font-size: 24px;
            font-weight: bold;
        }
        
        .nav-menu {
            display: flex;
            gap: 30px;
            align-items: center;
        }
        
        .nav-menu a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .nav-menu a:hover {
            color: #e0f7f5;
        }
        
        .login-btn {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .login-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
        }
        
        
        .hero-section {
            background: linear-gradient(135deg, #20B2AA 0%, #1a9b94 50%, #17a2b8 100%);
            color: white;
            padding: 120px 0 80px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: #199793ff;
            background-size: cover;
        }
        
        .hero-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            position: relative;
            z-index: 2;
        }
        
        .hero-title {
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .hero-subtitle {
            font-size: 24px;
            margin-bottom: 15px;
            opacity: 0.9;
        }
        
        .hero-description {
            font-size: 18px;
            margin-bottom: 40px;
            opacity: 0.8;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .cta-btn {
            padding: 15px 30px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: bold;
            font-size: 16px;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .cta-primary {
            background: white;
            color: #20B2AA;
            border: 2px solid white;
        }
        
        .cta-primary:hover {
            background: transparent;
            color: white;
            border-color: white;
        }
        
        .cta-secondary {
            background: transparent;
            color: white;
            border: 2px solid white;
        }
        
        .cta-secondary:hover {
            background: white;
            color: #20B2AA;
        }
        
      
        .features-section {
            padding: 80px 0;
            background: whitesmoke;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .section-title {
            text-align: center;
            font-size: 36px;
            color: #20B2AA;
            margin-bottom: 20px;
            font-weight: bold;
        }
        
        .section-subtitle {
            text-align: center;
            font-size: 18px;
            color: #666;
            margin-bottom: 60px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
        }
        
        .feature-card {
            background: white;
            padding: 40px 30px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(32, 178, 170, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-top: 5px solid #20B2AA;
        }
        
        
        
        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #20B2AA, #1a9b94);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 36px;
            color: white;
        }
        
        .feature-title {
            font-size: 24px;
            color: #20B2AA;
            margin-bottom: 15px;
            font-weight: bold;
        }
        
        .feature-description {
            color: #666;
            line-height: 1.6;
        }
        
       
        .about-section {
            padding: 80px 0;
            background: white;
        }
        
        .about-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }
        
        .about-text h2 {
            font-size: 36px;
            color: #20B2AA;
            margin-bottom: 20px;
            font-weight: bold;
        }
        
        .about-text p {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.8;
        }
        
        .about-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 50px;
            margin-top: 30px;
        }
        
        .stat-item {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 4px solid #20B2AA;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #20B2AA;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
        }
        
        .about-image {
            background: linear-gradient(135deg, #20B2AA, #1a9b94);
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            color: white;
        }
        
        .about-image h3 {
            font-size: 28px;
            margin-bottom: 20px;
        }
        
        .about-image p {
            font-size: 16px;
            opacity: 0.9;
        }
        
      
        .how-it-works {
            padding: 80px 0;
            background: #f8f9fa;
        }
        
        .steps-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-top: 60px;
        }
        
        .step-item {
            text-align: center;
            position: relative;
        }
        
        .step-number {
            width: 60px;
            height: 60px;
            background: #20B2AA;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            margin: 0 auto 20px;
        }
        
        .step-title {
            font-size: 20px;
            color: #20B2AA;
            margin-bottom: 15px;
            font-weight: bold;
        }
        
        .step-description {
            color: #666;
            line-height: 1.6;
        }
        
       
        .main-footer {
            background: whitesmoke;
            color: black;
            padding: 60px 0 20px;
          
            margin-top: auto; 
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 180px;
            margin-bottom: 40px;
        }
        
        .footer-section h3 {
            font-size: 20px;
            margin-bottom: 20px;
            font-weight: bold;
            color: #20B2AA;
        }
        
        .footer-section p,
        .footer-section li {
            margin-bottom: 10px;
            opacity: 0.9;
        }
        
        .footer-section ul {
            list-style: none;
        }
        
        .footer-section a {
            color: black;
            text-decoration: none;
            transition: opacity 0.3s ease;
        }
        
        .footer-section a:hover {
            opacity: 0.7;
        }
        
        .footer-bottom {
           
            padding-top: 20px;
            text-align: center;
         width:100%;
            background: #4d9b97ff;
            color:white;
          position:fixed;
          bottom:0px;
            
        }
        
        
        @media (max-width: 768px) {
            .nav-menu {
                display: none;
            }
            
            .hero-title {
                font-size: 36px;
            }
            
            .hero-subtitle {
                font-size: 20px;
            }
            
            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .about-content {
                grid-template-columns: 1fr;
                gap: 40px;
            }
            
            .about-stats {
                grid-template-columns: 1fr;
            }
            
            .section-title {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <div class="logo">OHBS</div>
            <nav class="nav-menu">
                <a href="#home">Home</a>
                <a href="#features">Features</a>
                <a href="#about">About</a>
                <a href="#contact">Contact</a>
                <a href="login.php" class="login-btn">Login</a>
            </nav>
        </div>
    </header>

    <section id="home" class="hero-section">
        <div class="hero-container">
            <h1 class="hero-title">Online Hostel Booking System</h1>
            <h2 class="hero-subtitle">University of Dodoma</h2>
            <p class="hero-description">
                Streamline your accommodation booking process with our modern, efficient, and user-friendly hostel booking system. 
                Designed specifically for University of Dodoma students.
            </p>
            <div class="cta-buttons">
                <a href="login.php" class="cta-btn cta-primary">Get Started</a>
                <a href="register.php" class="cta-btn cta-secondary">Register Now</a>
            </div>
        </div>
    </section>

    <section id="features" class="features-section">
        <div class="container">
            <h2 class="section-title">Why Choose OHBS?</h2>
            <p class="section-subtitle">
                Our system provides everything you need for a seamless hostel booking experience
            </p>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🏠</div>
                    <h3 class="feature-title">Easy Room Selection</h3>
                    <p class="feature-description">
                        Browse available rooms by hostel block, view capacity, and check real-time availability 
                        with our intuitive interface.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">⚡</div>
                    <h3 class="feature-title">Instant Booking</h3>
                    <p class="feature-description">
                        Book your preferred room instantly with our streamlined booking process. 
                        Get immediate confirmation and status updates.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">👥</div>
                    <h3 class="feature-title">Gender-Based Allocation</h3>
                    <p class="feature-description">
                        Automatic filtering ensures you only see rooms appropriate for your gender, 
                        maintaining proper accommodation standards.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">📱</div>
                    <h3 class="feature-title">Mobile Friendly</h3>
                    <p class="feature-description">
                        Access the system from any device - desktop, tablet, or mobile phone. 
                        Responsive design ensures optimal experience everywhere.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🔔</div>
                    <h3 class="feature-title">Real-time Notifications</h3>
                    <p class="feature-description">
                        Stay updated with booking confirmations, status changes, and important 
                        announcements through our notification system.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🔒</div>
                    <h3 class="feature-title">Secure & Reliable</h3>
                    <p class="feature-description">
                        Your personal information and booking data are protected with industry-standard 
                        security measures and reliable system architecture.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section id="about" class="about-section">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h2>About OHBS</h2>
                    <p>
                        The Online Hostel Booking System (OHBS) is a comprehensive digital solution 
                        developed specifically for the University of Dodoma to modernize and streamline 
                        the student accommodation booking process.
                    </p>
                    <p>
                        Our system eliminates the traditional paper-based booking process, reducing 
                        waiting times, minimizing errors, and providing students with 24/7 access 
                        to accommodation services.
                    </p>
                    <p>
                        Built with modern web technologies and designed with user experience in mind, 
                        OHBS represents the future of student accommodation management.
                    </p>
                    
                    <div class="about-stats">
                        <div class="stat-item">
                            <div class="stat-number">6</div>
                            <div class="stat-label">Hostel Blocks</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">1200</div>
                            <div class="stat-label">Available Rooms</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">4800</div>
                            <div class="stat-label">Student Capacity</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">24/7</div>
                            <div class="stat-label">System Access</div>
                        </div>
                    </div>
                </div>
                
                <div class="about-image">
                    <h3>University of Dodoma</h3>
                    <p>
                        College of Informatics and Virtual Education
                    </p>
                    <br>
                    <p>
                        Providing quality education and modern student accommodation 
                        services to support academic excellence and student welfare.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="how-it-works">
        <div class="container">
            <h2 class="section-title">How It Works</h2>
            <p class="section-subtitle">
                Simple steps to book your accommodation
            </p>
            
            <div class="steps-container">
                <div class="step-item">
                    <div class="step-number">1</div>
                    <h3 class="step-title">Register & Login</h3>
                    <p class="step-description">
                        Create your account using your student credentials and login to access the system.
                    </p>
                </div>
                
                <div class="step-item">
                    <div class="step-number">2</div>
                    <h3 class="step-title">Browse Rooms</h3>
                    <p class="step-description">
                        View available rooms filtered by your gender and explore different hostel blocks.
                    </p>
                </div>
                
                <div class="step-item">
                    <div class="step-number">3</div>
                    <h3 class="step-title">Select & Book</h3>
                    <p class="step-description">
                        Choose your preferred room and submit your booking request with a single click.
                    </p>
                </div>
                
                <div class="step-item">
                    <div class="step-number">4</div>
                    <h3 class="step-title">Get Confirmation</h3>
                    <p class="step-description">
                        Receive instant confirmation and track your booking status through notifications.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>OHBS</h3>
                    <p>Online Hostel Booking System</p>
                    <p>University of Dodoma</p>
                    <p>College of Informatics and Virtual Education</p>
                </div>
                
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="login.php">Student Login</a></li>
                        <li><a href="register.php">Register</a></li>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#about">About Us</a></li>
                    </ul>
                </div>
                
                
                
                <div class="footer-section">
                    <h3>Contact Info</h3>
                    <p>University of Dodoma</p>
                    <p>P.O. Box 259, Dodoma</p>
                    <p>Tanzania</p>
                    <p>Email: info@udom.ac.tz</p>
                </div>
            </div>
            

        </div>
    </footer>
            <div class="footer-bottom">
                <p>&copy; 2024 Online Hostel Booking System (OHBS). All rights reserved.</p>
                <p>University of Dodoma - College of Informatics and Virtual Education</p>
            </div>
   
</body>
</html>