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

// Initialize variables and error array
$errors = [];
$start_date = '';
$end_date = '';
$where_clauses = [];
$params = [];
$types = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate dates
    $start_date = isset($_POST['start_date']) ? trim($_POST['start_date']) : '';
    $end_date = isset($_POST['end_date']) ? trim($_POST['end_date']) : '';

    // Only apply date filter if both dates are provided
    if (!empty($start_date) && !empty($end_date)) {
        // Validate date format and ensure they are valid
        $start_date_obj = DateTime::createFromFormat('Y-m-d', $start_date);
        $end_date_obj = DateTime::createFromFormat('Y-m-d', $end_date);

        if (!$start_date_obj || !$end_date_obj) {
            $errors[] = "Invalid date format.";
        } elseif ($end_date_obj < $start_date_obj) {
            $errors[] = "End date cannot be before start date.";
        } else {
            $where_clauses[] = "DATE(start_time) BETWEEN ? AND ?";
            $params[] = $start_date;
            $params[] = $end_date;
            $types .= "ss";
        }
    } elseif ($start_date || $end_date) {
        // If one date is provided but not the other
        $errors[] = "Both start and end dates are required.";
    }
}

// Function to count tasks by status
function countTasksByStatus($conn, $status, $where_clauses, $params, $types) {
    $query = "SELECT COUNT(*) as count FROM activity_schedules WHERE archived = 0";
    if (!empty($where_clauses)) {
        $query .= " AND " . implode(" AND ", $where_clauses);
    }
    $query .= " AND status = ?";
    $params[] = $status;
    $types .= "s";
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['count'];
}

// Count all tasks
$total_tasks_query = "SELECT COUNT(*) as total FROM activity_schedules";
$stmt = $conn->prepare($total_tasks_query);
$stmt->execute();
$total_tasks_result = $stmt->get_result();
$total_tasks = $total_tasks_result->fetch_assoc()['total'];

// Count active tasks
$active_tasks_query = "SELECT COUNT(*) as active FROM activity_schedules WHERE archived = 0";
if (!empty($where_clauses)) {
    $active_tasks_query .= " AND " . implode(" AND ", $where_clauses);
}
$stmt = $conn->prepare($active_tasks_query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$active_tasks_result = $stmt->get_result();
$active_tasks = $active_tasks_result->fetch_assoc()['active'];

// Count archived tasks
$archived_tasks_query = "SELECT COUNT(*) as archived FROM activity_schedules WHERE archived = 1";
$stmt = $conn->prepare($archived_tasks_query);
$stmt->execute();
$archived_tasks_result = $stmt->get_result();
$archived_tasks = $archived_tasks_result->fetch_assoc()['archived'];

// Count tasks by status
$pending_tasks = countTasksByStatus($conn, 'pending', $where_clauses, $params, $types);
$inprogress_tasks = countTasksByStatus($conn, 'inprogress', $where_clauses, $params, $types);
$complete_tasks = countTasksByStatus($conn, 'complete', $where_clauses, $params, $types);
$overdue_tasks = countTasksByStatus($conn, 'overdue', $where_clauses, $params, $types);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management Report</title>
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
        .status-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 20px;
        }
        .status-item {
            display: flex;
            justify-content: space-between;
            padding: 15px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .status-name {
            font-weight: bold;
        }
        .status-count {
            color: var(--secondary-color);
            font-weight: bold;
        }
        .buttons { text-align: center; margin-bottom: 20px; }
        .buttons button { padding: 12px 24px; background-color: var(--primary-color); color: white; border: none; border-radius: 4px; cursor: pointer; margin: 0 10px; }
        .buttons button:hover { background-color: var(--secondary-color); }
        .filter-form { max-width: 900px; margin: 0 auto 20px; background: white; padding: 20px; border-radius: 8px; box-shadow: var(--shadow); }
        .filter-form label { display: inline-block; margin-right: 10px; font-weight: bold; }
        .filter-form input[type="date"] { padding: 8px; border: 1px solid var(--border-color); border-radius: 4px; margin-right: 10px; }
        .filter-form button { padding: 8px 16px; background-color: var(--primary-color); color: white; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px; }
        .filter-form button:hover { background-color: var(--secondary-color); }
        .filter-form .reset-btn { background-color: #6c757d; }
        .filter-form .reset-btn:hover { background-color: #5a6268; }
        .error-messages { color: var(--error-color); margin-bottom: 10px; }
        .error-messages li { margin: 5px 0; }
        .notification { position: fixed; top: 20px; right: 20px; padding: 15px 20px; border-radius: 4px; color: white; z-index: 1001; animation: fadeOut 5s forwards; }
        .notification.success { background-color: var(--success-color); }
        .notification.error { background-color: var(--error-color); }
        @keyframes fadeOut { 0% { opacity: 1; } 80% { opacity: 1; } 100% { opacity: 0; display: none; } }
        @media print {
            .sidebar, .topbar, .buttons, .notification, .filter-form { display: none; }
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
                <li><a href="task_report.php" class="active"><i class="fas fa-chart-bar"></i> Reports</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <h1>Task Management Report</h1>
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

            <div class="filter-form">
                <form id="filterForm" method="POST" action="">
                    <label for="start_date">Start Date:</label>
                    <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">

                    <label for="end_date">End Date:</label>
                    <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">

                    <button type="submit">Apply Filters</button>
                    <button type="submit" class="reset-btn" onclick="resetForm()">Reset</button>
                </form>
                <?php if (!empty($errors)): ?>
                    <div class="error-messages">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>

            <div class="buttons">
                <button onclick="printReport()">Print Report</button>
                <button onclick="window.location.href='reports.php'">Back to Reports</button>
            </div>

            <div id="report-content">
                <div class="logo">
                    <img src="images/logo.png" alt="Magdalene Logo">
                </div>
                <h1>Task Management Report</h1>
                <p style="text-align: center; color: #555;">Generated on <?php echo date('F j, Y'); ?></p>

                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Total Tasks</h3>
                        <div class="stat-value"><?php echo $total_tasks; ?></div>
                        <p>All tasks in the system</p>
                    </div>
                    
                    <div class="stat-card">
                        <h3>Active Tasks</h3>
                        <div class="stat-value"><?php echo $active_tasks; ?></div>
                        <p>Currently active tasks</p>
                    </div>
                    
                    <div class="stat-card">
                        <h3>Archived Tasks</h3>
                        <div class="stat-value"><?php echo $archived_tasks; ?></div>
                        <p>Inactive or archived tasks</p>
                    </div>
                </div>
                
                <div class="status-stats">
                    <h2>Task Distribution by Status</h2>
                    <div class="status-grid">
                        <div class="status-item">
                            <span class="status-name">Pending</span>
                            <span class="status-count"><?php echo $pending_tasks; ?></span>
                        </div>
                        
                        <div class="status-item">
                            <span class="status-name">In Progress</span>
                            <span class="status-count"><?php echo $inprogress_tasks; ?></span>
                        </div>
                        
                        <div class="status-item">
                            <span class="status-name">Complete</span>
                            <span class="status-count"><?php echo $complete_tasks; ?></span>
                        </div>
                        
                        <div class="status-item">
                            <span class="status-name">Overdue</span>
                            <span class="status-count"><?php echo $overdue_tasks; ?></span>
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

        function resetForm() {
            document.getElementById('start_date').value = '';
            document.getElementById('end_date').value = '';
            document.getElementById('filterForm').submit();
        }

        // Client-side form validation
        document.getElementById('filterForm').addEventListener('submit', function(event) {
            let errors = [];
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;

            // Skip validation if reset button was clicked (both fields empty)
            if (!startDate && !endDate) {
                return;
            }

            // Validate dates
            if (!startDate || !endDate) {
                errors.push('Both start and end dates are required.');
            } else {
                const startDateObj = new Date(startDate);
                const endDateObj = new Date(endDate);
                if (isNaN(startDateObj) || isNaN(endDateObj)) {
                    errors.push('Invalid date format.');
                } else if (endDateObj < startDateObj) {
                    errors.push('End date cannot be before start date.');
                }
            }

            if (errors.length > 0) {
                event.preventDefault();
                alert('Please fix the following errors:\n- ' + errors.join('\n- '));
            }
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>