<?php
// Start session if not already started
session_start();

// Database connection parameters
$servername = "localhost";
$username = "root"; // Default MySQL username (change if needed)
$password = ""; // Default MySQL password (change if needed)
$dbname = "magdalene_management";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize user_name variable
$user_name = "User";

// Fetch user details using email if logged in
if (isset($_SESSION['email'])) {
    $login_email = $_SESSION['email']; // Email stored in session during login

    // Prepare and execute query to get user details
    $sql = "SELECT fname, lname FROM staff_records WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $login_email); // s for string
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_name = $row['fname'] . " " . $row['lname']; // Combine fname and lname
    }

    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="css/user.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #926c54; /* Main brown color */
            --secondary-color: #7a5b47; /* Darker shade for hover */
            --accent-color: #5c6e58; /* Green accent color */
            --background-color: #f4f4f9; /* Light background */
            --text-color: #333;
            --card-bg: #ffffff;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --border-color: #e0e0e0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            margin: 0;
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Dashboard Styles */
        .dashboard {
            display: flex;
            flex: 1;
        }

        .sidebar {
            width: 250px;
            background-color: var(--primary-color);
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100%;
            transition: transform 0.3s ease;
        }

        .sidebar h2 {
            padding: 20px;
            font-size: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 20px;
            color: white;
            text-align: center;
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar ul li {
            margin: 10px 0;
        }

        .sidebar ul li a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            transition: background-color 0.2s ease;
        }

        .sidebar ul li a i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }

        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            background-color: var(--secondary-color);
            border-radius: 4px;
            margin: 0 10px;
        }

        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 30px;
            padding-bottom: 80px; /* Space for footer */
            background-color: var(--background-color);
            display: flex;
            flex-direction: column;
        }

        /* Updated topbar to match second code */
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: var(--card-bg);
            padding: 20px;
            border-radius: 8px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }

        .topbar h1 {
            font-size: 1.8rem;
            color: var(--primary-color);
        }

        .topbar .user-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .topbar .user-actions a {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 1.5rem;
            transition: color 0.2s ease;
        }

        .topbar .user-actions a:hover {
            color: var(--secondary-color);
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .profile-icon {
            font-size: 2rem;
            color: var(--primary-color);
            cursor: pointer;
        }

        .toggle-sidebar {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--primary-color);
        }

        .welcome-message {
            font-size: 18px;
            color: var(--text-color);
            text-align: center;
            background-color: var(--card-bg);
            padding: 30px;
            border-radius: 8px;
            box-shadow: var(--shadow);
            max-width: 1000px;
            margin: 30px auto;
            flex: 1;
        }

        /* Updated Footer Styles to match second code */
        .footer {
            background-color: var(--primary-color);
            color: white;
            padding: 20px;
            text-align: center;
            margin-top: auto;
            width: 100%;
            position: relative;
        }

        .footer .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer .copyright {
            font-size: 0.9rem;
        }

        .footer .social-links {
            display: flex;
            gap: 15px;
        }

        .footer .social-links a {
            color: white;
            font-size: 1.2rem;
            transition: color 0.2s ease;
        }

        .footer .social-links a:hover {
            color: #f0f0f0;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                z-index: 1000;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .toggle-sidebar {
                display: block;
            }

            .topbar {
                flex-wrap: wrap;
                gap: 10px;
            }

            .topbar h1 {
                font-size: 1.5rem;
            }

            .footer .container {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>

</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h2>Donor Dashboard</h2>
            <ul>
                <li><a href="user_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="user_messages.php"><i class="fas fa-envelope"></i>message</a></li>
                <li><a href="donor_donation.php"><i class="fas fa-hand-holding-heart"></i> Donate</a></li>

            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Updated topbar to match second code -->
            <header class="topbar">
                <button class="toggle-sidebar"><i class="fas fa-bars"></i></button>
                <h1>Welcome, <?php echo htmlspecialchars($user_name); ?></h1>
                <div class="user-actions">
                    <div class="user-profile">
                        <a href="donor_profile.php" title="Profile">
                            <i class="fas fa-user-circle profile-icon"></i>
                        </a>
                        <a href="logout.php" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
                    </div>
                </div>
            </header>

            <!-- Welcome Message -->
            <div class="welcome-message">
                Hello <?php echo htmlspecialchars($user_name); ?>, we're delighted to have you here! Explore your dashboard to manage your profile and donations.
            </div>
        </main>
    </div>

    <!-- Updated footer to match second code -->
    <footer class="footer">
        <div class="container">
            <div class="copyright">
                &copy; <?php echo date('Y'); ?> Magdalene Management System. All rights reserved.
            </div>
        </div>
    </footer>
        
    <script>
        // Add toggle sidebar functionality
        const toggleSidebar = document.querySelector('.toggle-sidebar');
        const sidebar = document.querySelector('.sidebar');
        
        toggleSidebar.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });
    </script>
</body>
</html>