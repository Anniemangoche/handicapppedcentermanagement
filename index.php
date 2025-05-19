<?php
// Database connection
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "magdalene_management";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Modified query to include sum of donations from pay table
$query = "SELECT e.*, COALESCE(SUM(p.fee), 0) as current_funds 
          FROM events e 
          LEFT JOIN pay p ON e.name = p.event_name AND p.status = 'success' 
          GROUP BY e.id 
          ORDER BY e.created_at DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Magdalene Home - Hope for the Special Needs</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #926c54;
            --secondary-color: #7d5b46;
            --accent-color: #e3a073;
            --light-color: #f8f1e9;
            --dark-color: #2c3e50;
            --text-color: #333;
            --white: #ffffff;
            --gray-light: #f5f5f5;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: var(--text-color);
            line-height: 1.6;
            background-color: var(--light-color);
            overflow-x: hidden;
        }

        /* Header Styles */
        header {
            background-color: var(--white);
            box-shadow: var(--shadow);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            transition: var(--transition);
            padding: 15px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header.scrolled {
            padding: 10px 5%;
        }

        .logo {
            padding: 10;
            width: 100px;
            height: 100px;
        }

        .logo a {
            text-decoration: none;
            color: inherit;
        }

        nav {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .nav-item {
            position: relative;
        }

        .nav-item > a, nav > a {
            text-decoration: none;
            color: var(--dark-color);
            font-weight: 500;
            transition: var(--transition);
            padding: 8px 12px;
            border-radius: 4px;
        }

        .nav-item > a:hover, nav > a:hover {
            color: var(--primary-color);
            background-color: rgba(146, 108, 84, 0.1);
        }

        /* Dropdown Styles */
        .dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            background-color: var(--white);
            box-shadow: var(--shadow);
            border-radius: 6px;
            padding: 10px 0;
            min-width: 180px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: var(--transition);
            z-index: 100;
        }

        .nav-item:hover .dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown a {
            display: block;
            padding: 8px 20px;
            color: var(--dark-color);
            text-decoration: none;
            transition: var(--transition);
        }

        .dropdown a:hover {
            background-color: var(--gray-light);
            color: var(--primary-color);
        }

        /* Dropdown2 Styles */
        .dropdown2 {
            position: relative;
            display: inline-block;
        }

        .dropdown2 a {
            text-decoration: none;
            background-color: var(--primary-color);
            color: var(--white);
            padding: 10px 20px;
            border-radius: 6px;
            display: block;
            cursor: pointer;
            transition: var(--transition);
            font-weight: 500;
        }

        .dropdown2 a:hover {
            background-color: var(--secondary-color);
        }

        .dropdown2-menu {
            display: none;
            position: absolute;
            background-color: var(--white);
            box-shadow: var(--shadow);
            border-radius: 6px;
            min-width: 160px;
            z-index: 100;
            right: 0;
            margin-top: 5px;
        }

        .dropdown2-menu a {
            display: block;
            padding: 10px 15px;
            text-decoration: none;
            color: var(--dark-color);
            transition: var(--transition);
        }

        .dropdown2-menu a:hover {
            background-color: var(--gray-light);
            color: var(--primary-color);
        }

        /* Main Content Styles */
        main {
            margin-top: 80px;
        }

        section {
            padding: 80px 5%;
            position: relative;
        }

        section:nth-child(even) {
            background-color: var(--white);
        }

        h1, h2, h3 {
            color: var(--dark-color);
            margin-bottom: 20px;
            font-weight: 600;
        }

        h1 {
            font-size: 2.5rem;
        }

        h2 {
            font-size: 2rem;
            text-align: center;
            position: relative;
            padding-bottom: 15px;
        }

        h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background-color: var(--accent-color);
        }

        p {
            margin-bottom: 15px;
        }

        /* Hero Slider Styles */
        .hero-container {
            padding: 0;
            margin-top: -80px;
        }

        .slider {
            position: relative;
            width: 100%;
            height: 90vh;
            min-height: 600px;
            overflow: hidden;
        }

        .slide {
            position: absolute;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            opacity: 0;
            transition: opacity 1s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .slide::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .slide.active {
            opacity: 1;
        }

        .slide-content {
            position: relative;
            z-index: 1;
            color: var(--white);
            text-align: center;
            max-width: 800px;
            padding: 0 20px;
        }

        .slide-content h1 {
            font-size: 3rem;
            margin-bottom: 20px;
            color: var(--white);
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .slide-content p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }

        .cta-button {
            display: inline-block;
            background-color: var(--accent-color);
            color: var(--white);
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .cta-button:hover {
            background-color: var(--primary-color);
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
        }

        .slider-controls {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            z-index: 10;
        }

        .slider-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: var(--transition);
        }

        .slider-dot.active {
            background-color: var(--white);
            transform: scale(1.2);
        }

        /* Pop-up Styles */
        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: var(--white);
            padding: 30px;
            border-radius: 10px;
            box-shadow: var(--shadow);
            z-index: 1000;
            max-width: 500px;
            width: 90%;
            text-align: center;
            animation: fadeIn 0.5s ease;
        }

        .popup-content p {
            margin-bottom: 20px;
            font-size: 1.1rem;
        }

        .popup-content a {
            color: var(--primary-color);
            text-decoration: underline;
            transition: var(--transition);
        }

        .popup-content a:hover {
            color: var(--secondary-color);
        }

        .popup-close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 1.5rem;
            color: var(--dark-color);
            cursor: pointer;
            transition: var(--transition);
        }

        .popup-close:hover {
            color: var(--primary-color);
        }

        .popup-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        /* Features/Services Styles */
        .features-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .feature-card {
            background-color: var(--white);
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .feature-card i {
            margin-bottom: 20px;
            color: var(--primary-color);
        }

        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
        }

        /* About Section Styles */
        .about-section {
            display: flex;
            align-items: center;
            gap: 50px;
        }

        .about-content {
            flex: 1;
        }

        .about-image {
            flex: 1;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .about-image img {
            width: 100%;
            height: auto;
            display: block;
            transition: var(--transition);
        }

        .about-image:hover img {
            transform: scale(1.05);
        }

        /* Donation Events Styles */
        .page-title {
            text-align: center;
            color: var(--dark-color);
            margin-bottom: 40px;
            font-size: 2.5rem;
            position: relative;
        }

        .page-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background-color: var(--accent-color);
        }

        .donations-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .donation {
            background-color: var(--white);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .donation:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .donation img {
            width: 100%;
            height: 150px;
            object-fit: contain;
            border-bottom: 1px solid var(--gray-light);
            padding: 10px;
            background-color: var(--white);
        }

        .donation-content {
            padding: 20px;
        }

        .donation h2 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            text-align: left;
            padding-bottom: 0;
        }

        .donation h2::after {
            display: none;
        }

        .donation p {
            margin-bottom: 10px;
            color: var(--text-color);
        }

        .donation .event-date {
            color: var(--primary-color);
            font-weight: 500;
        }

        .donation .event-amount, .donation .event-current-funds {
            background-color: var(--light-color);
            padding: 5px 10px;
            border-radius: 4px;
            display: inline-block;
            font-weight: 500;
            margin-right: 10px;
        }

        .donate-btn {
            display: block;
            text-align: center;
            background-color: var(--primary-color);
            color: var(--white);
            text-decoration: none;
            padding: 12px;
            margin: 20px 20px 0;
            border-radius: 6px;
            font-weight: 500;
            transition: var(--transition);
        }

        .donate-btn:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }

        .no-donations {
            text-align: center;
            padding: 40px;
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: var(--shadow);
            grid-column: 1 / -1;
            color: var(--text-color);
            font-size: 1.2rem;
        }

        /* Contact Section Styles */
        .contact-container {
            display: flex;
            flex-direction: column;
            gap: 30px;
            margin-top: 40px;
        }

        /* Map and Form Container */
        .map-form-container {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }

        #find-us, .contact-form {
            flex: 1;
            background-color: var(--white);
            padding: 30px;
            border-radius: 10px;
            box-shadow: var(--shadow);
        }

        #find-us iframe {
            width: 100%;
            height: 300px;
            border: 0;
            border-radius: 10px;
            box-shadow: var(--shadow);
        }

        .contact-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 10px;
        }

        .contact-form input,
        .contact-form textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: inherit;
            transition: var(--transition);
            margin-bottom: 10px;
        }

        .contact-form input:focus,
        .contact-form textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(146, 108, 84, 0.2);
        }

        .contact-form textarea {
            min-height: 150px;
            resize: vertical;
        }

        .contact-form button {
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
            padding: 12px 25px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            width: 100%;
        }

        .contact-form button:hover {
            background-color: var(--secondary-color);
        }

        @media (max-width: 576px) {
            .map-form-container {
            }
        }

        @media (max-width: 992px) {
            .map-form-container {
            }
        }

        @media (max-width: 1200px) {
            .map-form-container {
            }
        }

        #popup {
            display: none;
            background: var(--primary-color);
            color: var(--white);
            padding: 15px;
            margin-top: 15px;
            border-radius: 6px;
            text-align: center;
            animation: fadeIn 0.5s;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Footer Styles */
        footer {
            background-color: var(--dark-color);
            color: var(--white);
            text-align: center;
            padding: 30px 20px;
            margin-top: 50px;
        }

        .footer-content {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            max-width: 1200px;
            margin: 0 auto 30px;
        }

        .footer-section {
            width: 30%;
            min-width: 250px;
            margin-bottom: 20px;
        }

        .footer-section h4 {
            color: var(--accent-color);
            margin-bottom: 15px;
            font-size: 1.2rem;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section ul li {
            margin-bottom: 8px;
        }

        .footer-section a {
            color: var(--light-color);
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-section a:hover {
            color: var(--accent-color);
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 15px;
        }

        .social-links a {
            color: var(--white);
            background-color: rgba(255, 255, 255, 0.1);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .social-links a:hover {
            background-color: var(--accent-color);
            transform: translateY(-3px);
        }

        .copyright {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 20px;
            font-size: 0.9rem;
        }

        /* Responsive Styles */
        @media (max-width: 992px) {
            .about-section {
                flex-direction: column;
            }

            .about-content, .about-image {
                flex: none;
                width: 100%;
            }

            .about-image {
                margin-top: 30px;
            }

            .slide-content h1 {
                font-size: 2.5rem;
            }

            .popup {
                width: 95%;
                padding: 20px;
            }

            .footer-section {
                width: 45%;
            }
        }

        @media (max-width: 768px) {
            header {
                flex-direction: column;
                padding: 15px;
            }

            nav {
                margin-top: 15px;
                flex-wrap: wrap;
                justify-content: center;
                gap: 15px;
            }

            .slider {
                height: 80vh;
                min-height: 500px;
            }

            .slide-content h1 {
                font-size: 2rem;
            }

            .slide-content p {
                font-size: 1rem;
            }

            .donations-container {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            }

            section {
                padding: 60px 5%;
            }

            .footer-section {
                width: 100%;
                text-align: center;
            }
        }

        @media (max-width: 576px) {
            .slider {
                height: 70vh;
                min-height: 400px;
            }

            .slide-content h1 {
                font-size: 1.8rem;
            }

            .cta-button {
                padding: 10px 20px;
            }

            .donations-container {
                grid-template-columns: 1fr;
            }

            .contact-container {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .popup {
                padding: 15px;
            }

            .popup-content p {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <header id="header">
        <img src="images/logo.png" alt="magdalene" class="logo">
        <nav>
            <div class="nav-item">
                <a href="about.php">About Us</a>
            </div>
            <div class="nav-item">
                <a href="services.php">Services</a>
            </div>
            <div class="nav-item">
                <a href="#">Get Involved</a>
                <div class="dropdown">
                    <a href="don.php">Donations</a>
                    <a href="auth/signup.php">Volunteer</a>
                    <a href="#events">Events</a>
                </div>
            </div>
            <a href="#contact">Contact</a>
            <a href="auth/login.php">Login</a>
            <a href="auth/signup.php">Sign Up</a>
        </nav>
    </header>

    <main>
        <!-- Hero Section with Slider -->
        <section class="hero-container">
            <div class="slider" id="hero-slider">
                <div class="slide active" style="background-image: url('images/donate.jpg')">
                    <div class="slide-content">
                        <h1>Providing Care for Special Needs</h1>
                        <p>Creating a nurturing environment where every child is valued, loved, and given the opportunity to thrive.</p>
                        <a href="auth/signup.php" class="cta-button">Join Our Community</a>
                    </div>
                </div>
                <div class="slide" style="background-image: url('images/donate1.jpg')">
                    <div class="slide-content">
                        <h1>Every Child Deserves Love</h1>
                        <p>At Magdalene Home, we believe in embracing differences and celebrating abilities.</p>
                        <a href="don.php" class="cta-button">Support Our Cause</a>
                    </div>
                </div>
                <div class="slide" style="background-image: url('images/image1.jpg')">
                    <div class="slide-content">
                        <h1>Making a Difference Together</h1>
                        <p>Join our mission to provide care, education, and opportunities for children with special needs.</p>
                        <a href="auth/signup.php" class="cta-button">Become a Volunteer</a>
                    </div>
                </div>
                <div class="slider-controls">
                    <span class="slider-dot active" data-slide="0"></span>
                    <span class="slider-dot" data-slide="1"></span>
                    <span class="slider-dot" data-slide="2"></span>
                </div>
            </div>
            <div class="popup-overlay" id="popupOverlay"></div>
            <div class="popup" id="donationPopup">
                <span class="popup-close" id="popupClose">×</span>
                <div class="popup-content">
                    <p>To make material donations or learn more about child registration to our care center, please <a href="contact.php">contact us</a>.</p>
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section class="features" id="services">
            <h2>Our Services</h2>
            <div class="features-container">
                <div class="feature-card">
                    <i class="fas fa-hand-holding-heart fa-3x"></i>
                    <h3>Personalized Care</h3>
                    <p>Customized plans tailored to the individual needs of each child, ensuring they receive the attention they deserve.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-gamepad fa-3x"></i>
                    <h3>Therapeutic Activities</h3>
                    <p>Engaging events and recreational programs to foster a sense of belonging, joy, and personal growth.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-graduation-cap fa-3x"></i>
                    <h3>Special Education</h3>
                    <p>Individualized learning programs designed to help each child reach their full potential.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-home fa-3x"></i>
                    <h3>Residential Care</h3>
                    <p>A safe, loving home environment with 24/7 support from trained caregivers.</p>
                </div>
            </div>
        </section>

        <!-- About Us Section -->
        <section class="about-section" id="about">
            <div class="about-content">
                <h2>About Magdalene Home</h2>
                <p>Magdalene Home is a sanctuary of hope and love for children with special needs. Founded in 1991 and run by sisters of the Holy Rosary in Rumphi District, we have been committed to providing a nurturing environment where every child can reach their full potential.</p>
                <p>Our dedicated team of caregivers, therapists, and volunteers work tirelessly to ensure that each child receives personalized attention, compassionate care, and opportunities for growth and development.</p>
                <p>We believe that every child, regardless of their abilities, deserves love, respect, and the chance to live a fulfilling life. At Magdalene Home, we celebrate differences and focus on abilities rather than disabilities.</p>
                <div style="margin-top: 30px;">
                    <a href="#contact" class="cta-button" style="display: inline-block;">Learn More About Us</a>
                </div>
            </div>
            <div class="about-image">
                <img src="images/image1.jpg" alt="Children at Magdalene Home">
            </div>
        </section>

        <!-- Donation Events Section -->
        <section class="donation-events" id="events">
            <h1 class="page-title">Upcoming Donation Events</h1>
            <div class="donations-container">
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<div class='donation'>";
                        echo "<img src='" . htmlspecialchars($row['image_path']) . "' alt='Donation Image'>";
                        echo "<div class='donation-content'>";
                        echo "<h2>" . htmlspecialchars($row['name']) . "</h2>";
                        echo "<p class='event-date'><i class='fas fa-calendar-alt'></i> " . htmlspecialchars($row['date']) . " at " . htmlspecialchars($row['time']) . "</p>";
                        echo "<p class='event-amount'><i class='fas fa-hand-holding-usd'></i> Goal: " . htmlspecialchars($row['amount']) . "</p>";
                        echo "<p class='event-current-funds'><i class='fas fa-money-bill-wave'></i> Collected: " . number_format($row['current_funds'], 2) . "</p>";
                        echo "<p>" . htmlspecialchars($row['description']) . "</p>";
                        echo "<a href='donateforevent.php?donation_id=" . $row['id'] . "&event_name=" . urlencode($row['name']) . "' class='donate-btn'><i class='fas fa-heart'></i> Donate Now</a>";
                        echo "</div></div>";
                    }
                } else {
                    echo "<div class='no-donations'>No donation events available at this time. Please check back later or contact us to learn how you can help.</div>";
                }
                ?>
            </div>
        </section>

        <!-- Contact Section -->
        <section class="contact-section" id="contact">
            <h2>Contact Us</h2>
            <div class="contact-container">
                <div class="map-form-container">
                    <div class="content" id="find-us">
                        <h2>Find Us on the Map</h2>
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d5514.0084377417525!2d33.86964721348876!3d-11.02463243296584!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x1902de6e549995e1%3A0xdac3226ee311bc13!2sSt.%20Patrick's%20Seminary%20Chapel!5e0!3m2!1sen!2smw!4v1744895452341!5m2!1sen!2smw" 
                            width="400" height="300" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                    <div class="contact-form">
                        <h3>Send us a Message</h3>
                        <form id="contactForm">
                            <input type="text" name="name" placeholder="Your Name" required>
                            <input type="email" name="email" placeholder="Your Email" required>
                            <input type="text" name="subject" placeholder="Subject">
                            <textarea placeholder="Your Message" name="message" required></textarea>
                            <button type="submit">Send Message</button>
                        </form>
                        <div id="popup">
                            Message sent successfully! We'll get back to you soon.
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h4>Contact Us</h4>
                <p><i class="fas fa-map-marker-alt"></i> Rumphi, Malawi</p>
                <p><i class="fas fa-phone"></i> +265 999746398</p>
                <p><i class="fas fa-envelope"></i> magdalenehome@gmail.com</p>
                <p><i class="fas fa-clock"></i> Open 24 hours</p>
            </div>
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="services.php">Our Services</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="don.php">Donate</a></li>
                </ul>
            </div>
           
        </div>
        <div class="copyright">
            <p>© 2025 Magdalene Home for Special Needs. All Rights Reserved.</p>
            <p>A sanctuary of hope and love for children with special needs</p>
        </div>
    </footer>

    <script>
        // Slider functionality
        document.addEventListener('DOMContentLoaded', function() {
            let currentSlide = 0;
            const slides = document.querySelectorAll('.slide');
            const dots = document.querySelectorAll('.slider-dot');
            const totalSlides = slides.length;
            
            function showSlide(n) {
                slides.forEach(slide => slide.classList.remove('active'));
                dots.forEach(dot => dot.classList.remove('active'));
                
                currentSlide = (n + totalSlides) % totalSlides;
                
                slides[currentSlide].classList.add('active');
                dots[currentSlide].classList.add('active');
            }
            
            // Automatic slideshow
            const slideInterval = setInterval(() => {
                showSlide(currentSlide + 1);
            }, 5000);
            
            // Click on dots to change slide
            dots.forEach((dot, index) => {
                dot.addEventListener('click', () => {
                    clearInterval(slideInterval);
                    showSlide(index);
                });
            });
            
            // Header scroll effect
            window.addEventListener('scroll', function() {
                const header = document.getElementById('header');
                if (window.scrollY > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            });
            
            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    if (this.getAttribute('href') !== '#') {
                        e.preventDefault();
                        
                        const targetId = this.getAttribute('href');
                        if (targetId === '#') return;
                        
                        const targetElement = document.querySelector(targetId);
                        if (targetElement) {
                            window.scrollTo({
                                top: targetElement.offsetTop - 80,
                                behavior: 'smooth'
                            });
                            
                            // Update URL without page jump
                            if (history.pushState) {
                                history.pushState(null, null, targetId);
                            } else {
                                location.hash = targetId;
                            }
                        }
                    }
                });
            });
            
            // Dropdown functionality
            const signupBtn = document.getElementById("signupBtn");
            const dropdown2Menu = document.getElementById("dropdown2Menu");

            if (signupBtn) {
                signupBtn.addEventListener("click", function(event) {
                    event.preventDefault();
                    dropdown2Menu.style.display = dropdown2Menu.style.display === "block" ? "none" : "block";
                });

                document.addEventListener("click", function(event) {
                    if (!signupBtn.contains(event.target) && !dropdown2Menu.contains(event.target)) {
                        dropdown2Menu.style.display = "none";
                    }
                });
            }
            
            // Contact form submission
            document.getElementById('contactForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                fetch('contact_process.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    if (data.includes("Message sent successfully")) {
                        document.getElementById('popup').style.display = 'block';
                        document.getElementById('contactForm').reset();
                        setTimeout(() => {
                            document.getElementById('popup').style.display = 'none';
                        }, 5000);
                    } else {
                        alert("Something went wrong: " + data);
                    }
                })
                .catch(error => {
                    alert("An error occurred: " + error);
                });
            });
            
            // Pop-up functionality
            const popup = document.getElementById('donationPopup');
            const popupOverlay = document.getElementById('popupOverlay');
            const closePopup = document.getElementById('popupClose');

            function showPopup() {
                popup.style.display = 'block';
                popupOverlay.style.display = 'block';
            }

            function hidePopup() {
                popup.style.display = 'none';
                popupOverlay.style.display = 'none';
            }

            // Show pop-up every 5 minutes (300000 ms)
            setInterval(showPopup, 300000);

            // Show pop-up immediately on page load
            setTimeout(showPopup, 1000);

            // Close pop-up on click
            closePopup.addEventListener('click', hidePopup);

            // Close pop-up when clicking outside
            popupOverlay.addEventListener('click', hidePopup);
            
            // Animation on scroll
            const animateOnScroll = function() {
                const elements = document.querySelectorAll('.feature-card, .donation, .about-image, .about-content');
                
                elements.forEach(element => {
                    const elementPosition = element.getBoundingClientRect().top;
                    const screenPosition = window.innerHeight / 1.2;
                    
                    if (elementPosition < screenPosition) {
                        element.style.opacity = '1';
                        element.style.transform = 'translateY(0)';
                    }
                });
            };
            
            // Set initial state for animation
            document.querySelectorAll('.feature-card, .donation, .about-image, .about-content').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            });
            
            window.addEventListener('scroll', animateOnScroll);
            animateOnScroll();
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>