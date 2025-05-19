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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Magdalene Home for Special Needs</title>
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
            width: 90px;
            height: 90px;
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

        /* Hero Section for About Page */
        .about-hero {
            height: 60vh;
            min-height: 400px;
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('images/donate.jpg');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: var(--white);
            margin-top: -80px;
        }

        .about-hero-content h1 {
            font-size: 3rem;
            color: var(--white);
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            margin-bottom: 20px;
        }

        .about-hero-content p {
            font-size: 1.2rem;
            max-width: 800px;
            margin: 0 auto 30px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
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

        /* Mission/Vision Section */
        .mission-vision {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .mission-card, .vision-card {
            background-color: var(--light-color);
            padding: 30px;
            border-radius: 10px;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .mission-card:hover, .vision-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .mission-card h3, .vision-card h3 {
            color: var(--primary-color);
            font-size: 1.5rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Team Section */
        .team-section {
            background-color: var(--light-color);
        }

        .team-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .team-member {
            background-color: var(--white);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            text-align: center;
        }

        .team-member:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .team-member img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-bottom: 1px solid var(--gray-light);
        }

        .team-member-info {
            padding: 20px;
        }

        .team-member-info h3 {
            font-size: 1.3rem;
            margin-bottom: 5px;
        }

        .team-member-info p.position {
            color: var(--primary-color);
            font-weight: 500;
            margin-bottom: 15px;
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 15px;
        }

        .social-links a {
            color: var(--primary-color);
            font-size: 1.2rem;
            transition: var(--transition);
        }

        .social-links a:hover {
            color: var(--secondary-color);
            transform: translateY(-3px);
        }

        /* History Timeline */
        .timeline {
            position: relative;
            max-width: 1200px;
            margin: 40px auto 0;
        }

        .timeline::after {
            content: '';
            position: absolute;
            width: 6px;
            background-color: var(--primary-color);
            top: 0;
            bottom: 0;
            left: 50%;
            margin-left: -3px;
            border-radius: 3px;
        }

        .timeline-item {
            padding: 10px 40px;
            position: relative;
            width: 50%;
            box-sizing: border-box;
        }

        .timeline-item::after {
            content: '';
            position: absolute;
            width: 25px;
            height: 25px;
            right: -12px;
            background-color: var(--white);
            border: 4px solid var(--primary-color);
            top: 15px;
            border-radius: 50%;
            z-index: 1;
        }

        .left {
            left: 0;
        }

        .right {
            left: 50%;
        }

        .left::before {
            content: " ";
            height: 0;
            position: absolute;
            top: 22px;
            width: 0;
            z-index: 1;
            right: 30px;
            border: medium solid var(--primary-color);
            border-width: 10px 0 10px 10px;
            border-color: transparent transparent transparent var(--primary-color);
        }

        .right::before {
            content: " ";
            height: 0;
            position: absolute;
            top: 22px;
            width: 0;
            z-index: 1;
            left: 30px;
            border: medium solid var(--primary-color);
            border-width: 10px 10px 10px 0;
            border-color: transparent var(--primary-color) transparent transparent;
        }

        .right::after {
            left: -12px;
        }

        .timeline-content {
            padding: 20px 30px;
            background-color: var(--white);
            position: relative;
            border-radius: 6px;
            box-shadow: var(--shadow);
        }

        .timeline-content h3 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .timeline-content p {
            margin-bottom: 0;
        }

        /* Stats Section */
        .stats-section {
            background: linear-gradient(rgba(146, 108, 84, 0.9), rgba(146, 108, 84, 0.9)), url('images/image2.jpg');
            background-size: cover;
            background-position: center;
            color: var(--white);
            text-align: center;
            padding: 60px 5%;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .stat-item {
            padding: 30px 20px;
        }

        .stat-item i {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--accent-color);
        }

        .stat-item .number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .stat-item .label {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* CTA Section */
        .cta-section {
            text-align: center;
            padding: 60px 5%;
            background-color: var(--primary-color);
            color: var(--white);
        }

        .cta-section h2 {
            color: var(--white);
        }

        .cta-section h2::after {
            background-color: var(--accent-color);
        }

        .cta-section p {
            max-width: 700px;
            margin: 0 auto 30px;
            font-size: 1.1rem;
        }

        .cta-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
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

        .cta-button.outline {
            background-color: transparent;
            border: 2px solid var(--white);
            color: var(--white);
        }

        .cta-button:hover {
            background-color: var(--white);
            color: var(--primary-color);
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
        }

        .cta-button.outline:hover {
            background-color: var(--white);
            color: var(--primary-color);
            border-color: var(--white);
        }

        /* Footer Styles */
        footer {
            background-color: var(--dark-color);
            color: var(--white);
            text-align: center;
            padding: 20px;
            margin-top: 50px;
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

            .timeline::after {
                left: 31px;
            }

            .timeline-item {
                width: 100%;
                padding-left: 70px;
                padding-right: 25px;
            }

            .timeline-item::after {
                left: 18px;
            }

            .left::before, .right::before {
                border-width: 10px 10px 10px 0;
                border-color: transparent var(--primary-color) transparent transparent;
                left: 50px;
                right: auto;
            }

            .right {
                left: 0%;
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

            .about-hero {
                height: 50vh;
                min-height: 350px;
            }

            .about-hero-content h1 {
                font-size: 2.2rem;
            }

            .about-hero-content p {
                font-size: 1rem;
            }

            section {
                padding: 60px 5%;
            }
        }

        @media (max-width: 576px) {
            .about-hero {
                height: 40vh;
                min-height: 300px;
            }

            .about-hero-content h1 {
                font-size: 1.8rem;
            }

            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }

            .cta-button {
                width: 100%;
                max-width: 250px;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header id="header">
    <img src="images/logo.png" alt="magdalene" class="logo">
        <nav>
       
        <div class="nav-item">
                <a href="index.php">Home</a>
            </div>
        
              
            <div class="nav-item">
                <a href="services.php">Services</a>
               
            </div>
            <div class="nav-item">
                <a href="#">Get Involved</a>
                <div class="dropdown">
                    <a href="don.php">Donations</a>
                    <a href="auth/signup.php">Volunteer</a>
                    
                </div>
            </div>
            <a href="contact.php">Contact</a>
            <a href="auth/login.php">Login</a>
            <div class="dropdown2">
                <a id="signupBtn">Sign Up</a>
                <div class="dropdown2-menu" id="dropdown2Menu">
                    <a href="auth/signup.php">Volunteer</a>
                    <a href="auth/signup.php">Donor</a>
                </div>
            </div>
            <a href="don.php" class="cta-button" style="padding: 8px 15px; font-size: 0.9rem;">Donate Now</a>
        </nav>
    </header>

    <main>
        <!-- Hero Section -->
        <section class="about-hero">
            <div class="about-hero-content">
                <h1>Our Story, Mission & Vision</h1>
                <p>For over 30 years, Magdalene Home has been a beacon of hope for children with special needs in Malawi, providing love, care, and opportunities for growth.</p>
                <a href="#history" class="cta-button">Explore Our History</a>
            </div>
        </section>

        <!-- About Section -->
        <section class="about-section">
            <div class="about-content">
                <h2>Who We Are</h2>
                <p>Magdalene Home is a sanctuary of hope and love for children with special needs. Founded in 1991 and run by sisters of the Holy Rosary in Rumphi District, we have been committed to providing a nurturing environment where every child can reach their full potential.</p>
                <p>Our organization was born out of a deep recognition of the challenges faced by children with disabilities in Malawi. Many of these children were marginalized, with limited access to education, healthcare, or even basic care. Magdalene Home was established to change this reality.</p>
                <p>Today, we serve over 50 children with various special needs, including physical disabilities, intellectual disabilities, and autism spectrum disorders. Our holistic approach addresses not just medical needs, but also emotional, educational, and social development.</p>
            </div>
            <div class="about-image">
                <img src="images/image1.jpg" alt="Children at Magdalene Home">
            </div>
        </section>

        <!-- Mission/Vision Section -->
        <section style="background-color: var(--light-color);">
            <h2>Our Mission & Vision</h2>
            <div class="mission-vision">
                <div class="mission-card">
                    <h3><i class="fas fa-bullseye"></i> Our Mission</h3>
                    <p>To provide exceptional care, education, and support to children with special needs, empowering them to lead fulfilling lives while advocating for their rights and inclusion in society.</p>
                    <p>We strive to create a loving, safe environment where each child is valued for their unique abilities and given opportunities to develop to their fullest potential.</p>
                </div>
                <div class="vision-card">
                    <h3><i class="fas fa-eye"></i> Our Vision</h3>
                    <p>A world where children with special needs are fully integrated into society, with equal access to opportunities, care, and respect.</p>
                    <p>We envision communities that embrace diversity, where every child regardless of ability can thrive and contribute meaningfully to their families and society.</p>
                </div>
            </div>
        </section>

        <!-- Core Values Section -->
        <section>
            <h2>Our Core Values</h2>
            <div class="features-container" style="margin-top: 40px;">
                <div class="feature-card">
                    <i class="fas fa-heart fa-3x"></i>
                    <h3>Compassion</h3>
                    <p>We approach every child and family with deep empathy, understanding their unique challenges and celebrating their strengths.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-hand-holding-heart fa-3x"></i>
                    <h3>Dignity</h3>
                    <p>We believe every child deserves to be treated with respect and valued for who they are, regardless of ability.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-users fa-3x"></i>
                    <h3>Inclusion</h3>
                    <p>We work to break down barriers and create opportunities for full participation in all aspects of community life.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-lightbulb fa-3x"></i>
                    <h3>Innovation</h3>
                    <p>We continuously seek new and better ways to serve our children, adapting to their evolving needs.</p>
                </div>
            </div>
        </section>
        

        <!-- Stats Section -->
        <section class="stats-section">
            <div class="stats-container">
                <div class="stat-item">
                    <i class="fas fa-child"></i>
                    <div class="number">50+</div>
                    <div class="label">Children Helped Annually</div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-users"></i>
                    <div class="number">15</div>
                    <div class="label">Dedicated Staff Members</div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-home"></i>
                    <div class="number">30</div>
                    <div class="label">Years of Service</div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-hand-holding-heart"></i>
                    <div class="number">20+</div>
                    <div class="label">Volunteers Each Year</div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
            <h2>Join Us in Making a Difference</h2>
            <p>Whether through donations, volunteering, or simply spreading awareness, your support helps us continue our vital work with children who need it most.</p>
            <div class="cta-buttons">
                <a href="don.php" class="cta-button">Donate Now</a>
                <a href="auth/signup.php"class="cta-button outline">Become a Volunteer</a>
            </div>
        </section>
    </main>

    <footer>
        <p>© 2025 Magdalene Home for Special Needs. All Rights Reserved.</p>
        <p style="margin-top: 10px; font-size: 0.9rem;">A sanctuary of hope and love for children with special needs</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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

            signupBtn.addEventListener("click", function(event) {
                event.preventDefault();
                dropdown2Menu.style.display = dropdown2Menu.style.display === "block" ? "none" : "block";
            });

            document.addEventListener("click", function(event) {
                if (!signupBtn.contains(event.target) && !dropdown2Menu.contains(event.target)) {
                    dropdown2Menu.style.display = "none";
                }
            });
            
            // Animation on scroll
            const animateOnScroll = function() {
                const elements = document.querySelectorAll('.feature-card, .team-member, .timeline-item, .mission-card, .vision-card');
                
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
            document.querySelectorAll('.feature-card, .team-member, .timeline-item, .mission-card, .vision-card').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            });
            
            window.addEventListener('scroll', animateOnScroll);
            animateOnScroll(); // Run once on load
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>