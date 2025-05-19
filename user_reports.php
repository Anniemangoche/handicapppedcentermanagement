<?php
// Start session
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "magdalene_management";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize user_name
$user_name = "User";

// Fetch user details
if (isset($_SESSION['email'])) {
    $login_email = $_SESSION['email'];
    $sql = "SELECT fname, lname FROM staff_records WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $login_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_name = $row['fname'] . " " . $row['lname'];
    }
    $stmt->close();
}

// Fetch reports
$reports = [];
$message = "";

if (isset($login_email)) {
    $query = "SELECT r.report_id, r.report_text, r.report_date, a.activity_name 
              FROM task_reports r
              JOIN activity_schedules a ON r.activity_id = a.activity_id
              WHERE r.staff_name = ?
              ORDER BY r.report_date DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $user_name);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $reports[] = $row;
            }
        } else {
            $message = "No reports found.";
        }
    } else {
        $message = "Error retrieving reports: " . $stmt->error;
    }
    $stmt->close();
} else {
    $message = "User not logged in.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports | Magdalene Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #926c54;
            --secondary-color: #7a5b47;
            --accent-color: #e74c3c;
            --background-color: #ffffff;
            --text-color: #333;
            --card-bg: #ffffff;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --border-color: #e0e0e0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
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
        }

        .sidebar h2 {
            padding: 20px;
            font-size: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 20px;
            color: white;
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
        }

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

        .topbar .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .topbar .user-info span {
            font-size: 1rem;
            color: var(--primary-color);
            font-weight: 500;
        }

        .topbar .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #e9e9e9;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: var(--primary-color);
            font-weight: 600;
        }

        .dashboard-content {
            background-color: var(--card-bg);
            padding: 30px;
            border-radius: 8px;
            box-shadow: var(--shadow);
        }

        .reports-container {
            margin-top: 30px;
        }

        .report {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: var(--shadow);
        }

        .report h3 {
            margin: 0 0 10px 0;
            color: var(--primary-color);
        }

        .report p {
            margin: 5px 0;
            color: #555;
        }

        .report-date {
            font-style: italic;
            color: #777;
            font-size: 0.9rem;
        }

        .message {
            text-align: center;
            color: green;
            font-weight: bold;
            margin: 15px 0;
        }

        .error {
            text-align: center;
            color: red;
            font-weight: bold;
            margin: 15px 0;
        }

        .dashboard-footer {
            text-align: center;
            padding: 15px;
            background-color: var(--primary-color);
            color: white;
            font-size: 0.9rem;
            margin-top: auto;
            border-radius: 8px 8px 0 0;
            box-shadow: var(--shadow);
        }

        @media (max-width: 768px) {
            .sidebar {
                position: absolute;
                left: -250px;
                transition: left 0.3s;
            }

            .sidebar.active {
                left: 0;
            }

            .main-content {
                margin-left: 0;
            }

            .topbar {
                flex-wrap: wrap;
                gap: 10px;
            }

            .topbar h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>Magdalene Management</h2>
            </div>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="viewtask.php"><i class="fas fa-tasks"></i> Tasks</a></li>
                <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="reports.php" class="active"><i class="fas fa-chart-bar"></i> Reports</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="topbar">
                <h1>Reports</h1>
                <div class="user-info">
                    <span><?php echo htmlspecialchars($user_name); ?></span>
                    <div class="user-avatar">
                        <?php echo substr(htmlspecialchars($user_name), 0, 1); ?>
                    </div>
                </div>
            </header>

            <div class="dashboard-content">
                <!-- Display messages -->
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="message"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>

                <!-- Reports Section -->
                <div id="reports" class="reports-container">
                    <h2>Your Activity Reports</h2>
                    <?php if (!empty($message)): ?>
                        <p class="message"><?php echo $message; ?></p>
                    <?php else: ?>
                        <?php foreach ($reports as $report): ?>
                            <div class="report">
                                <h3><?php echo htmlspecialchars($report['activity_name']); ?></h3>
                                <p class="report-date"><?php echo date('F j, Y, g:i a', strtotime($report['report_date'])); ?></p>
                                <p><?php echo nl2br(htmlspecialchars($report['report_text'])); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Footer -->
    <div class="dashboard-footer">
        <p>© 2025 Magdalene Management System. All rights reserved.</p>
    </div>
</body>
</html>