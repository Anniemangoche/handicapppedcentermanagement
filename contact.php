<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Magdalene Home</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            color: var(--white);
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .donate-btn:hover {
            background-color: rgb(124, 128, 125);
        }

        main {
            margin-top: 80px;
            padding: 20px 5%;
        }

        section {
            margin-bottom: 40px;
        }

        h2 {
            color: var(--dark-color);
            text-align: center;
            margin-bottom: 20px;
        }

        .contact-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 30px;
            flex-wrap: wrap;
        }

        .contact-info {
            flex: 1;
            text-align: center;
            background-color: var(--white);
            padding: 30px;
            border-radius: 10px;
            box-shadow: var(--shadow);
        }

        .contact-info p {
            margin-bottom: 10px;
        }

        .contact-form {
            flex: 1;
            background-color: var(--white);
            padding: 30px;
            border-radius: 10px;
            box-shadow: var(--shadow);
        }

        .contact-form h3 {
            margin-bottom: 20px;
            text-align: center;
        }

        .contact-form input,
        .contact-form textarea {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: inherit;
            transition: var(--transition);
        }

        .contact-form input:focus,
        .contact-form textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(146, 108, 84, 0.2);
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

        footer {
            background-color: var(--dark-color);
            color: var(--white);
            text-align: center;
            padding: 20px;
            margin-top: 50px;
        }

        @media (max-width: 768px) {
            .contact-container {
                flex-direction: column;
                align-items: center;
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
            <a href="services.php">Our Services</a>
            <a href="about.php">About Us</a>
            <a href="contact.php">Contact</a>
            <a href="auth/login.php">Login</a>
        </nav>
    </header>

    <main>
        <section id="contact">
            <h2>Contact Us</h2>
            <div class="contact-container">
                <div class="contact-info">
                    <h3>Get in Touch</h3>
                    <p><i class="fas fa-map-marker-alt"></i> Rumphi, Malawi</p>
                    <p><i class="fas fa-phone"></i> +265 999746398</p>
                    <p><i class="fas fa-envelope"></i> magdalenehome@gmail.com</p>
                    <p><i class="fas fa-clock"></i> Open 24 hours</p>
                    
                    <p style="margin-top: 20px;">We welcome your inquiries and feedback. Your suggestions are vital to our mission of providing exceptional care and support to children with special needs.</p>
                    <p>If you prefer visit us in person. We look forward to hearing from you!</p>
                </div>
                <div class="contact-form">
                    <h3>Send us a Message</h3>
                    <form id="contactForm">
                        <input type="text" name="name" placeholder="Your Name" required>
                        <input type="email" name="email" placeholder="Your Email" required>
                        <input type="text" name="subject" placeholder="Subject" required>
                        <textarea placeholder="Your Message" name="message" required></textarea>
                        <button type="submit">Send Message</button>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <p>© 2025 Magdalene Home for Special Needs. All Rights Reserved.</p>
        <p>A sanctuary of hope and love for children with special needs</p>
    </footer>
</body>
</html>
