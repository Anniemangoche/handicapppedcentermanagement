<?php
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

// Determine active section
$active_section = isset($_GET['section']) ? $_GET['section'] : 'dashboard';

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard | Magdalene Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            margin: 0;
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

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

        /* Topbar */
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

        .toggle-sidebar {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--primary-color);
        }

        .dashboard-content {
            background-color: var(--card-bg);
            padding: 30px;
            border-radius: 8px;
            box-shadow: var(--shadow);
        }

        .welcome-section h2 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .welcome-section p {
            color: var(--text-color);
        }

        /* Footer */
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
            <h2>Staff Dashboard</h2>
            <ul>
                <li><a href="volunteer_staff.php" class="<?php echo $active_section == 'dashboard' ? 'active' : ''; ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="viewtask.php"><i class="fas fa-tasks"></i> Tasks</a></li>
                <li><a href="staff_profile.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="staff_messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Topbar -->
            <header class="topbar">
                <button class="toggle-sidebar"><i class="fas fa-bars"></i></button>
                <h1>Staff Dashboard</h1>
                <div class="user-actions">
                    <a href="staff_profile.php" title="Profile"><i class="fas fa-user"></i></a>
                    <a href="logout.php" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
                </div>
            </header>

            <div class="dashboard-content">
                <!-- Welcome Section -->
                <div class="welcome-section">
                    <h2>Welcome back, <?php echo htmlspecialchars($user_name); ?>!</h2>
                    <p>We're glad to see you again. Here's an overview of your activities.</p>
                </div>
            </div>
        </main>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="copyright">
                © <?php echo date('Y'); ?> Magdalene Management System. All rights reserved.
            </div>
            <div class="social-links">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
            </div>
        </div>
    </footer>

    <script>
        // Toggle sidebar on mobile
        document.querySelector('.toggle-sidebar').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
    </script>
</body>
</html>