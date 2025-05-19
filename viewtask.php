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

// Task retrieval
$tasks = [];
$message = "";

if (isset($login_email)) {
    $query = "SELECT activity_id, activity_name, description, start_time, end_time, activity_date, status 
              FROM activity_schedules 
              WHERE staff_name = ? AND archived = 0";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $user_name);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $tasks[] = $row;
            }
        } else {
            $message = "No task assigned.";
        }
    } else {
        $message = "Error retrieving tasks: " . $stmt->error;
    }
    $stmt->close();
} else {
    $message = "User not logged in.";
}

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $activityId = $_POST['activity_id'];
    $status = $_POST['status'];
    
    $query = "UPDATE activity_schedules SET status = ? WHERE activity_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status, $activityId);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Task status updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating task status: " . $stmt->error;
    }
    
    $stmt->close();
    header("Location: viewtask.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Tasks | Staff Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
   
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

        .dashboard-content {
            background-color: var(--card-bg);
            padding: 30px;
            border-radius: 8px;
            box-shadow: var(--shadow);
        }

        .task-container {
            margin-top: 30px;
        }

        .task {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: var(--shadow);
        }

        .task h3 {
            margin: 0 0 10px 0;
            color: var(--primary-color);
        }

        .task p {
            margin: 5px 0;
            color: #555;
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

        /* Form Styles */
        .status-form {
            margin: 15px 0;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 4px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        select {
            width: 100%;
            padding: 8px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            margin-bottom: 10px;
        }

        button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }

        button:hover {
            background-color: var(--secondary-color);
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
                <h2>Staff Dashboard</h2>
            </div>
            <ul>
                <li><a href="volunteer_staff.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="viewtask.php" class="active"><i class="fas fa-tasks"></i> Tasks</a></li>
                <li><a href="staff_profile.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="staff_messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="topbar">
                <h1>Tasks</h1>
                <div class="user-actions">
                    <a href="staff_profile.php" title="Profile"><i class="fas fa-user"></i></a>
                    <a href="logout.php" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
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

                <!-- Task Section -->
                <div id="tasks" class="task-container">
                    <h2>Assigned Tasks</h2>
                    <?php if (!empty($message)): ?>
                        <p class="message"><?php echo $message; ?></p>
                    <?php else: ?>
                        <?php foreach ($tasks as $task): ?>
                            <div class="task">
                                <h3><?php echo htmlspecialchars($task['activity_name']); ?></h3>
                                <p><strong>Description:</strong> <?php echo htmlspecialchars($task['description']); ?></p>
                                <p><strong>Date:</strong> <?php echo htmlspecialchars($task['activity_date']); ?></p>
                                <p><strong>Time:</strong> <?php echo htmlspecialchars($task['start_time']) . " - " . htmlspecialchars($task['end_time']); ?></p>
                                <p><strong>Status:</strong> <?php echo ucfirst(str_replace('_', ' ', $task['status'])); ?></p>
                                
                                <!-- Status Update Form -->
                                <form class="status-form" method="post">
                                    <input type="hidden" name="activity_id" value="<?php echo $task['activity_id']; ?>">
                                    <div class="form-group">
                                        <label for="status">Update Status:</label>
                                        <select name="status" id="status">
                                            <option value="pending" <?php echo $task['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="in_progress" <?php echo $task['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                            <option value="completed" <?php echo $task['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="on_hold" <?php echo $task['status'] == 'on_hold' ? 'selected' : ''; ?>>On Hold</option>
                                        </select>
                                    </div>
                                    <button type="submit" name="update_status">Update Status</button>
                                </form>
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