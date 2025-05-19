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

// Function to count staff by role
function countStaffByRole($conn, $role) {
    $query = "SELECT COUNT(*) as count FROM staff_records WHERE role = ? AND archived = 0";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $role);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['count'];
}

// Count all active staff
$total_staff_query = "SELECT COUNT(*) as total FROM staff_records WHERE archived = 0";
$total_staff_result = $conn->query($total_staff_query);
$total_staff = $total_staff_result->fetch_assoc()['total'];

// Count staff by each role
$cookers = countStaffByRole($conn, 'cooker');
$volunteers = countStaffByRole($conn, 'volunteer');
$donors = countStaffByRole($conn, 'Donor');
$caregivers = countStaffByRole($conn, 'caregiver');
$teachers = countStaffByRole($conn, 'teacher');

// Count archived staff
$archived_query = "SELECT COUNT(*) as archived FROM staff_records WHERE archived = 1";
$archived_result = $conn->query($archived_query);
$archived_staff = $archived_result->fetch_assoc()['archived'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management Report</title>
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
            --success-color: #28a745;
            --error-color: #dc3545;
        }

        body { font-family: Arial, sans-serif; line-height: 1.6; background-color: #f4f4f4; }
        .dashboard { display: flex; min-height: 100vh; }
        .sidebar {
            width: 250px;
            background-color: var(--primary-color);
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100%;
        }
        .sidebar h2 { padding: 20px; font-size: 1.5rem; border-bottom: 1px solid rgba(204, 218, 223, 0.2); margin-bottom: 20px; }
        .sidebar ul { list-style: none; }
        .sidebar ul li { margin: 10px 0; }
        .sidebar ul li a { display: flex; align-items: center; padding: 12px 20px; color: white; text-decoration: none; }
        .sidebar ul li a:hover, .sidebar ul li a.active { background-color: var(--secondary-color); border-radius: 4px; margin: 0 10px; }
        .sidebar ul li a i { margin-right: 12px; width: 20px; text-align: center; }
        .main-content { margin-left: 250px; flex: 1; padding: 30px; }
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
        .topbar h1 { font-size: 1.8rem; color: var(--primary-color); }
        .topbar .user-actions { display: flex; gap: 15px; }
        .topbar .user-actions a { color: var(--primary-color); text-decoration: none; font-size: 1.5rem; }
        .topbar .user-actions a:hover { color: var(--secondary-color); }
        #report-content { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: var(--shadow); }
        h1 { color: var(--primary-color); text-align: center; }
        h2 { color: var(--secondary-color); border-bottom: 2px solid var(--primary-color); padding-bottom: 10px; }
        .logo { text-align: center; margin-bottom: 20px; }
        .logo img { max-width: 150px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: var(--primary-color); color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            text-align: center;
        }
        .stat-card h3 {
            margin-top: 0;
            color: var(--primary-color);
        }
        .stat-value {
            font-size: 2.5rem;
            font-weight: bold;
            margin: 10px 0;
            color: var(--secondary-color);
        }
        .role-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 20px;
        }
        .role-item {
            display: flex;
            justify-content: space-between;
            padding: 15px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .role-name {
            font-weight: bold;
        }
        .role-count {
            color: var(--secondary-color);
            font-weight: bold;
        }
        .buttons { text-align: center; margin-bottom: 20px; }
        .buttons button { padding: 12px 24px; background-color: var(--primary-color); color: white; border: none; border-radius: 4px; cursor: pointer; margin: 0 10px; }
        .buttons button:hover { background-color: var(--secondary-color); }
        .notification { position: fixed; top: 20px; right: 20px; padding: 15px 20px; border-radius: 4px; color: white; z-index: 1001; animation: fadeOut 5s forwards; }
        .notification.success { background-color: var(--success-color); }
        .notification.error { background-color: var(--error-color); }
        @keyframes fadeOut { 0% { opacity: 1; } 80% { opacity: 1; } 100% { opacity: 0; display: none; } }
        @media print {
            .sidebar, .topbar, .buttons, .notification { display: none; }
            .main-content { margin-left: 0; padding: 0; }
            #report-content { max-width: 100%; box-shadow: none; border-radius: 0; padding: 10px; }
            body { background: white; }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <aside class="sidebar">
            <h2>Director Panel</h2>
            <ul>
                <li><a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="child_retrive.php"><i class="fas fa-child"></i> Child Records</a></li>
                <li><a href="auth/addstaff_retrive.php"><i class="fas fa-users"></i> Staff Management</a></li>
                <li><a href="admin_don.php"><i class="fas fa-donate"></i> Donations</a></li>
                <li><a href="eventsadd.php"><i class="fas fa-calendar-alt"></i> Events</a></li>
                <li><a href="tasks.php"><i class="fas fa-tasks"></i> Tasks</a></li>
                <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                <li><a href="inventory.php"><i class="fas fa-boxes"></i> Inventory</a></li>
                <li><a href="staff_report.php" class="active"><i class="fas fa-chart-bar"></i> Reports</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <h1>User Management Report</h1>
                <div class="user-actions">
                    <a href="profile.php" title="Profile"><i class="fas fa-user"></i></a>
                    <a href="logout.php" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
                </div>
            </header>

            <?php if (isset($_SESSION['notification'])): ?>
                <div class="notification <?php echo strpos($_SESSION['notification'], 'Error') === false ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($_SESSION['notification']); ?>
                </div>
                <?php unset($_SESSION['notification']); ?>
            <?php endif; ?>

            <div class="buttons">
                <button onclick="printReport()">Print Report</button>
                <button onclick="window.location.href='reports.php'">Back to Reports</button>
            </div>

            <div id="report-content">
                <div class="logo">
                    <img src="images/logo.png" alt="Magdalene Logo">
                </div>
                <h1>User Management Report</h1>
                <p style="text-align: center; color: #555;">Generated on <?php echo date('F j, Y'); ?></p>

                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Total Active Staff</h3>
                        <div class="stat-value"><?php echo $total_staff; ?></div>
                        <p>Currently working staff members</p>
                    </div>
                    
                    <div class="stat-card">
                        <h3>Archived Staff</h3>
                        <div class="stat-value"><?php echo $archived_staff; ?></div>
                        <p>Inactive or former staff members</p>
                    </div>
                </div>
                
                <div class="role-stats">
                    <h2>Staff Distribution by Role</h2>
                    <div class="role-grid">
                        <div class="role-item">
                            <span class="role-name">Cookers</span>
                            <span class="role-count"><?php echo $cookers; ?></span>
                        </div>
                        
                        <div class="role-item">
                            <span class="role-name">Volunteers</span>
                            <span class="role-count"><?php echo $volunteers; ?></span>
                        </div>
                        
                        <div class="role-item">
                            <span class="role-name">Donors</span>
                            <span class="role-count"><?php echo $donors; ?></span>
                        </div>
                        
                        <div class="role-item">
                            <span class="role-name">Caregivers</span>
                            <span class="role-count"><?php echo $caregivers; ?></span>
                        </div>
                        
                        <div class="role-item">
                            <span class="role-name">Teachers</span>
                            <span class="role-count"><?php echo $teachers; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function printReport() {
            window.print();
        }
    </script>
</body>
</html>
<?php
$conn->close();
?>