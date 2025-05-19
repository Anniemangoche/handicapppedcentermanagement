<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$database = "magdalene_management";

$conn = new mysqli($servername, $dbusername, $dbpassword, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get child_id from URL
$child_id = isset($_GET['child_id']) ? intval($_GET['child_id']) : 0;
if ($child_id <= 0) {
    echo "<script>alert('Invalid child ID'); window.location.href='child_retrive.php';</script>";
    exit;
}

// Fetch child record
$query = "SELECT * FROM child_records WHERE child_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $child_id);
$stmt->execute();
$result = $stmt->get_result();
$child = $result->fetch_assoc();

if (!$child) {
    echo "<script>alert('Child record not found'); window.location.href='child_retrive.php';</script>";
    exit;
}

// Fetch staff details for assigned staff email
$staff_query = "SELECT fname, lname, role FROM staff_records WHERE email = ?";
$stmt = $conn->prepare($staff_query);
$stmt->bind_param("s", $child['staff_email']);
$stmt->execute();
$staff_result = $stmt->get_result();
$staff = $staff_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Child Details</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
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

        .button-row {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }

        .button-row a button, .button-row button {
            padding: 10px 15px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .button-row a button:hover, .button-row button:hover {
            background-color: var(--secondary-color);
        }

        .details-container {
            background-color: var(--card-bg);
            padding: 20px;
            border-radius: 8px;
            box-shadow: var(--shadow);
            max-width: 800px;
        }

        .details-container h2 {
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .details-container .detail-item {
            margin-bottom: 15px;
        }

        .details-container .detail-item label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
            color: var(--primary-color);
        }

        .details-container .detail-item p {
            background-color: #f9f9f9;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid var(--border-color);
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        @media print {
            .sidebar, .topbar .user-actions, .toggle-sidebar, .button-row {
                display: none;
            }

            .main-content {
                margin-left: 0;
                padding: 0;
            }

            .topbar {
                box-shadow: none;
                background: none;
                padding: 10mm;
            }

            .details-container {
                box-shadow: none;
                border-radius: 0;
                padding: 10mm;
                max-width: 100%;
                font-size: 10pt;
                line-height: 1.4;
            }

            .details-container h1, .details-container h2 {
                font-size: 14pt;
                text-align: center;
                margin-bottom: 10mm;
            }

            .details-container .detail-item label {
                font-size: 10pt;
                font-weight: bold;
            }

            .details-container .detail-item p {
                font-size: 10pt;
                background: none;
                border: none;
                padding: 0;
                margin-bottom: 5mm;
                white-space: normal;
                word-wrap: break-word;
                max-width: 180mm;
            }

            @page {
                size: A4;
                margin: 10mm;
            }
        }
    </style>
    <script>
        function printPage() {
            window.print();
        }
    </script>
</head>
<body>
    <div class="dashboard">
        <aside class="sidebar">
            <h2>Director Panel</h2>
            <ul>
                <li><a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="child_retrive.php" class="active"><i class="fas fa-child"></i> Child Records</a></li>
                <li><a href="addstaff_retrive.php"><i class="fas fa-users"></i> Staff Management</a></li>
                <li><a href="admin_don.php"><i class="fas fa-donate"></i> Donations</a></li>
                <li><a href="eventsadd.php"><i class="fas fa-calendar-alt"></i> Events</a></li>
                <li><a href="tasks.php"><i class="fas fa-tasks"></i> Tasks</a></li>
                <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                <li><a href="inventory.php"><i class="fas fa-boxes"></i> Inventory</a></li>
                <li><a href="#"><i class="fas fa-chart-bar"></i> Reports</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <button class="toggle-sidebar"><i class="fas fa-bars"></i></button>
                <h1>Child Details</h1>
                <div class="user-actions">
                    <a href="profile.php" title="Profile"><i class="fas fa-user"></i></a>
                    <a href="logout.php" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
                </div>
            </header>

            <div class="button-row">
                <a href="child_retrive.php"><button>Back to Records</button></a>
                <button onclick="printPage()">Print</button>
            </div>

            <div class="details-container">
                <h2><?php echo htmlspecialchars($child['fname'] . ' ' . $child['lname']); ?></h2>
                <div class="detail-item">
                    <label>First Name</label>
                    <p><?php echo htmlspecialchars($child['fname']); ?></p>
                </div>
                <div class="detail-item">
                    <label>Last Name</label>
                    <p><?php echo htmlspecialchars($child['lname']); ?></p>
                </div>
                <div class="detail-item">
                    <label>Date of Birth</label>
                    <p><?php echo htmlspecialchars($child['dateofbirth']); ?></p>
                </div>
                <div class="detail-item">
                    <label>Gender</label>
                    <p><?php echo htmlspecialchars($child['gender']); ?></p>
                </div>
                <div class="detail-item">
                    <label>Medical Information</label>
                    <p><?php echo htmlspecialchars($child['medical_info']); ?></p>
                </div>
                <div class="detail-item">
                    <label>Education Information</label>
                    <p><?php echo htmlspecialchars($child['education_info']); ?></p>
                </div>
                <div class="detail-item">
                    <label>Assigned Staff</label>
                    <p>
                        <?php 
                        if ($staff) {
                            echo htmlspecialchars($child['staff_email'] . ' (' . $staff['fname'] . ' ' . $staff['lname'] . ' - ' . $staff['role'] . ')');
                        } else {
                            echo htmlspecialchars($child['staff_email']);
                        }
                        ?>
                    </p>
                </div>
                <div class="detail-item">
                    <label>Relative's Phone Number</label>
                    <p><?php echo htmlspecialchars($child['relatives_phonenumber']); ?></p>
                </div>
                <div class="detail-item">
                    <label>Background Information</label>
                    <p><?php echo htmlspecialchars($child['child_backgroundinfo']); ?></p>
                </div>
                <div class="detail-item">
                    <label>Relative's Address</label>
                    <p><?php echo htmlspecialchars($child['relatives_address']); ?></p>
                </div>
                <div class="detail-item">
                    <label>Status</label>
                    <p><?php echo $child['archived'] ? 'Archived' : 'Active'; ?></p>
                </div>
            </div>
        </main>
    </div>
</body>
</html>