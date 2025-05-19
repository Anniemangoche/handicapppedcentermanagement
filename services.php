<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Services - Magdalene Home for Special Needs</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
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

        header {
            background-color: var(--white);
            box-shadow: var(--shadow);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            padding: 15px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        nav a {
            text-decoration: none;
            color: var(--dark-color);
            font-weight: 500;
            padding: 8px 12px;
            border-radius: 4px;
            transition: var(--transition);
        }

        nav a:hover {
            background-color: rgba(146, 108, 84, 0.1);
        }

        .donate-btn {
            background-color: var(--primary-color);
            color: var(--white) !important;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .donate-btn:hover {
            background-color: var(--accent-color);
        }

        main {
            margin-top: 80px;
            padding: 40px 5%;
        }

        section {
            margin-bottom: 60px;
        }

        .page-title {
            text-align: center;
            margin-bottom: 50px;
            position: relative;
        }

        .page-title h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .page-title p {
            font-size: 1.1rem;
            max-width: 800px;
            margin: 0 auto;
            color: var(--secondary-color);
        }

        .page-title::after {
            content: "";
            display: block;
            width: 80px;
            height: 3px;
            background-color: var(--accent-color);
            margin: 20px auto;
        }

        h2 {
            font-family: 'Playfair Display', serif;
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 30px;
            font-size: 2rem;
        }

        h3 {
            color: var(--secondary-color);
            margin: 15px 0;
            font-weight: 600;
        }

        .intro-text {
            max-width: 900px;
            margin: 0 auto 40px;
            text-align: center;
            font-size: 1.1rem;
        }

        .features-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }

        .feature-card {
            background-color: var(--white);
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: var(--shadow);
            transition: var(--transition);
            overflow: hidden;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .feature-card i {
            margin-bottom: 20px;
            color: var(--primary-color);
            background-color: rgba(146, 108, 84, 0.1);
            width: 80px;
            height: 80px;
            line-height: 80px;
            border-radius: 50%;
            display: inline-block;
        }

        .service-process {
            background-color: var(--white);
            padding: 50px 20px;
            border-radius: 15px;
            box-shadow: var(--shadow);
            margin-top: 50px;
        }

        .process-steps {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            margin-top: 30px;
        }

        .step {
            width: 250px;
            text-align: center;
            position: relative;
        }

        .step:not(:last-child)::after {
            content: "→";
            position: absolute;
            right: -25px;
            top: 30px;
            font-size: 24px;
            color: var(--accent-color);
        }

        .step-number {
            width: 60px;
            height: 60px;
            background-color: var(--primary-color);
            color: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 600;
            margin: 0 auto 15px;
        }

        .cta-section {
            text-align: center;
            margin: 60px 0;
        }

        .cta-button {
            display: inline-block;
            background-color: var(--accent-color);
            color: var(--white);
            padding: 12px 30px;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            margin-top: 20px;
        }

        .cta-button:hover {
            background-color: var(--primary-color);
            transform: scale(1.05);
        }

        .faq-section {
            margin-top: 60px;
        }

        .faq-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .faq-item {
            background-color: var(--white);
            border-radius: 8px;
            margin-bottom: 15px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .faq-question {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
        }

        .faq-answer {
            border-top: 1px solid #eee;
            padding-top: 15px;
            margin-top: 5px;
        }

        .vocational-examples {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .vocational-item {
            background-color: var(--white);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: var(--shadow);
        }
        
        .vocational-item i {
            color: var(--accent-color);
            font-size: 2rem;
            margin-bottom: 15px;
        }

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

        @media (max-width: 992px) {
            .process-steps {
                flex-direction: column;
                align-items: center;
            }
            
            .step:not(:last-child)::after {
                content: "↓";
                position: absolute;
                bottom: -25px;
                right: 50%;
                top: auto;
            }
            
            .footer-section {
                width: 45%;
            }
        }

        @media (max-width: 768px) {
            header {
                flex-direction: column;
                padding: 15px 3%;
            }
            
            nav {
                margin-top: 15px;
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .features-container {
                grid-template-columns: 1fr;
            }
            
            .footer-section {
                width: 100%;
                text-align: center;
            }
            
            .page-title h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <header>
    <img src="images/logo.png" alt="magdalene" class="logo">
        <nav>
        <a href="index.php">Home</a>
            <a href="don.php" class="donate-btn">Donate</a>
            <a href="about.php">About Us</a>
            <a href="contact.php">Contact</a>
            <a href="auth/login.php">Login</a>
        </nav>
    </header>

    <main>
        <section class="page-title">
            <h1>Our Comprehensive Services</h1>
            <p>Dedicated support and specialized care for children with special needs in a nurturing environment</p>
        </section>

        <section id="services-intro">
            <p class="intro-text">At Magdalene Home, we believe every child deserves the opportunity to thrive. Our team of experienced professionals provides specialized care and support tailored to each child's unique needs, abilities, and aspirations. We focus on holistic development through a range of therapeutic, educational, and recreational services.</p>
        </section>

        <section id="core-services">
            <h2>Core Services</h2>
            <div class="features-container">
                <div class="feature-card">
                    <i class="fas fa-hand-holding-heart fa-3x"></i>
                    <h3>Personalized Care Plans</h3>
                    <p>Our multi-disciplinary team develops individualized care plans that address each child's specific needs, strengths, and goals. These plans are regularly reviewed and adjusted to ensure optimal progress and well-being.</p>
                    <p>Services include assessment, goal setting, progress monitoring, and family involvement in the care process.</p>
                </div>
                
                <div class="feature-card">
                    <i class="fas fa-graduation-cap fa-3x"></i>
                    <h3>Special Education & Vocational Training</h3>
                    <p>Our specialized educational programs are designed to meet the diverse learning needs of each child, with low student-to-teacher ratios and evidence-based teaching methods tailored to different learning styles.</p>
                    <p>Children who are able participate in vocational training programs where they learn practical skills such as making jewelry, crafts, and other handmade items like bangles, beadwork, and decorative items. These activities develop fine motor skills, creativity, and provide potential future income opportunities.</p>
                </div>
                
                <div class="feature-card">
                    <i class="fas fa-home fa-3x"></i>
                    <h3>Residential Care</h3>
                    <p>Our safe, loving home environment provides 24/7 support from trained caregivers who understand the unique needs of children with special needs, ensuring comfort, security, and nurturing care.</p>
                    <p>Features include accessible accommodations, structured routines, personal care assistance, and a family-like atmosphere focused on dignity and respect.</p>
                </div>
            </div>
        </section>

        <section id="vocational-training">
            <h2>Vocational Training & Handwork</h2>
            <p class="intro-text">We believe in developing practical skills that can empower our children and provide them with potential future livelihood options. Our vocational training program teaches various handcrafting skills to those who are able to participate.</p>
            
            <div class="vocational-examples">
                <div class="vocational-item">
                    <i class="fas fa-gem"></i>
                    <h3>Jewelry Making</h3>
                    <p>Creation of necklaces, bracelets, earrings and bangles using various materials and techniques.</p>
                </div>
                
                <div class="vocational-item">
                    <i class="fas fa-paint-brush"></i>
                    <h3>Arts & Crafts</h3>
                    <p>Development of creativity through painting, drawing, and creating decorative items.</p>
                </div>
                
                <div class="vocational-item">
                    <i class="fas fa-tshirt"></i>
                    <h3>Textile Work</h3>
                    <p>Basic sewing, embroidery, and fabric decoration techniques.</p>
                </div>
                
                <div class="vocational-item">
                    <i class="fas fa-seedling"></i>
                    <h3>Gardening</h3>
                    <p>Growing plants, vegetables and learning about sustainable practices.</p>
                </div>
            </div>
        </section>

        <section id="additional-services">
            <h2>Additional Support Services</h2>
            <div class="features-container">
                <div class="feature-card">
                    <i class="fas fa-utensils fa-3x"></i>
                    <h3>Nutritional Support</h3>
                    <p>Provision of nutritious meal plans catering to special dietary requirements, sensory issues, and medical needs, ensuring proper nutrition for optimal development and health.</p>
                </div>
                
                <div class="feature-card">
                    <i class="fas fa-heartbeat fa-3x"></i>
                    <h3>Health Services</h3>
                    <p>On-site healthcare including nursing care, medication management, and coordination with specialists to address medical needs promptly and effectively.</p>
                </div>
                
                <div class="feature-card">
                    <i class="fas fa-running fa-3x"></i>
                    <h3>Physical & Occupational Therapy</h3>
                    <p>Everyday work to develop motor skills, independence in daily activities, and adaptive strategies to enhance quality of life.</p>
                </div>
            </div>
        </section>

        <section class="service-process">
            <h2>Our Service Approach</h2>
            <div class="process-steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <h3>Initial Assessment</h3>
                    <p>Comprehensive evaluation of each child's needs, abilities, and aspirations</p>
                </div>
                
                <div class="step">
                    <div class="step-number">2</div>
                    <h3>Personalized Planning</h3>
                    <p>Development of individual care and education plans with family input</p>
                </div>
                
                <div class="step">
                    <div class="step-number">3</div>
                    <h3>Implementation</h3>
                    <p>Skilled execution of services by our dedicated professional team</p>
                </div>
                
                <div class="step">
                    <div class="step-number">4</div>
                    <h3>Regular Review</h3>
                    <p>Ongoing assessment and adjustment of plans to ensure optimal progress</p>
                </div>
            </div>
        </section>

        <section class="faq-section">
            <h2>Frequently Asked Questions</h2>
            <div class="faq-container">
                <div class="faq-item">
                    <div class="faq-question">
                        How do you determine the specific services each child needs?
                    </div>
                    <div class="faq-answer">
                        We conduct a comprehensive assessment involving our multidisciplinary team, review of medical and educational records, observation, and family consultation. This helps us understand each child's unique needs, strengths, and challenges to develop an appropriate service plan.
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        Can families be involved in their child's care and education?
                    </div>
                    <div class="faq-answer">
                        Absolutely! We believe family involvement is crucial for a child's progress. We regularly communicate with families, invite them to participate in care planning meetings, offer parent education workshops, and encourage visits and participation in special events.
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        What vocational skills do you teach?
                    </div>
                    <div class="faq-answer">
                        Our vocational program includes jewelry making (bangles, necklaces, beadwork), arts and crafts, basic textile work, gardening, and other practical skills. We tailor these activities to each child's abilities and interests, focusing on developing both creativity and marketable skills.
                    </div>
                </div>
                
               
            </div>
        </section>

        <section class="cta-section">
            <h2>Ready to Learn More?</h2>
            <p>We invite you to schedule a visit to Magdalene Home or speak with our admissions team to learn how we can support your child's unique journey.</p>
            <a href="contact.php" class="cta-button">Contact Us Today</a>
        </section>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h4>Contact Us</h4>
                <p>Rumphi,  Malawi</p>
                <p>Phone: (265) 999 56 66 17</p>
                <p>Email: magdalenehome@gmail.com</p>
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
</body>
</html>