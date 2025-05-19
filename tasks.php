<?php
// Database connection
$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$database = "magdalene_management";

$conn = new mysqli($servername, $dbusername, $dbpassword, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// First, check if the archived column exists, and if not, add it
$check_column = "SHOW COLUMNS FROM activity_schedules LIKE 'archived'";
$result = $conn->query($check_column);
if ($result->num_rows == 0) {
    $add_column = "ALTER TABLE activity_schedules ADD COLUMN archived TINYINT DEFAULT 0";
    if ($conn->query($add_column) === TRUE) {
        echo "<script>console.log('Added archived column to activity_schedules table');</script>";
    } else {
        echo "<script>console.log('Error adding archived column: " . $conn->error . "');</script>";
    }
}

// Check if status column exists, if not add it
$check_status = "SHOW COLUMNS FROM activity_schedules LIKE 'status'";
$result = $conn->query($check_status);
if ($result->num_rows == 0) {
    $add_status = "ALTER TABLE activity_schedules ADD COLUMN status VARCHAR(20) DEFAULT 'pending'";
    if ($conn->query($add_status) === TRUE) {
        echo "<script>console.log('Added status column to activity_schedules table');</script>";
    } else {
        echo "<script>console.log('Error adding status column: " . $conn->error . "');</script>";
    }
}

// Handle form submission for adding new task
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_add'])) {
    $activity_name = $_POST['activity_name'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $assigned_staff_email = $_POST['assigned_staff'];
    $status = $_POST['status'] ?? 'pending';

    // Validate start_time is before end_time
    if (strtotime($start_time) >= strtotime($end_time)) {
        echo "<script>alert('Error: Start time must be before end time');</script>";
        exit;
    }

    // Fetch staff_id, fname, and lname based on the selected email
    $staff_query = "SELECT staff_id, fname, lname FROM staff_records WHERE email = ?";
    $stmt = $conn->prepare($staff_query);
    $stmt->bind_param("s", $assigned_staff_email);
    $stmt->execute();
    $staff_result = $stmt->get_result();
    
    if ($staff_row = $staff_result->fetch_assoc()) {
        $assigned_staff = $staff_row['staff_id'];
        $staff_name = $staff_row['fname'] . ' ' . $staff_row['lname'];
        
        $insert_query = "INSERT INTO activity_schedules (activity_name, start_time, end_time, assigned_staff, staff_name, archived, status) 
                         VALUES (?, ?, ?, ?, ?, 0, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ssssss", $activity_name, $start_time, $end_time, $assigned_staff, $staff_name, $status);
        
        if ($stmt->execute()) {
            echo "<script>alert('Task added successfully!');</script>";
        } else {
            echo "<script>alert('Error: " . $stmt->error . "');</script>";
        }
    } else {
        echo "<script>alert('Error: Staff not found for the selected email.');</script>";
    }
}

// Handle form submission for editing task
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_edit'])) {
    $activity_id = $_POST['activity_id'];
    $activity_name = $_POST['activity_name'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $assigned_staff_email = $_POST['assigned_staff'];
    $status = $_POST['status'] ?? 'pending';

    // Validate start_time is before end_time
    if (strtotime($start_time) >= strtotime($end_time)) {
        echo "<script>alert('Error: Start time must be before end time');</script>";
        exit;
    }

    // Fetch staff_id, fname, and lname based on the selected email
    $staff_query = "SELECT staff_id, fname, lname FROM staff_records WHERE email = ?";
    $stmt = $conn->prepare($staff_query);
    $stmt->bind_param("s", $assigned_staff_email);
    $stmt->execute();
    $staff_result = $stmt->get_result();
    
    if ($staff_row = $staff_result->fetch_assoc()) {
        $assigned_staff = $staff_row['staff_id'];
        $staff_name = $staff_row['fname'] . ' ' . $staff_row['lname'];
        
        $update_query = "UPDATE activity_schedules SET activity_name = ?, start_time = ?, end_time = ?, 
                         assigned_staff = ?, staff_name = ?, status = ? WHERE activity_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssssssi", $activity_name, $start_time, $end_time, $assigned_staff, $staff_name, $status, $activity_id);
        
        if ($stmt->execute()) {
            echo "<script>alert('Task updated successfully!');</script>";
        } else {
            echo "<script>alert('Error: " . $stmt->error . "');</script>";
        }
    } else {
        echo "<script>alert('Error: Staff not found for the selected email.');</script>";
    }
}

// Handle archiving task
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_archive'])) {
    $activity_id = $_POST['activity_id'];
    
    $archive_query = "UPDATE activity_schedules SET archived = 1 WHERE activity_id = ?";
    $stmt = $conn->prepare($archive_query);
    $stmt->bind_param("i", $activity_id);
    
    if ($stmt->execute()) {
        echo "<script>alert('Task archived successfully!');</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }
}

// Handle restoring archived task
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_restore'])) {
    $activity_id = $_POST['activity_id'];
    
    $restore_query = "UPDATE activity_schedules SET archived = 0 WHERE activity_id = ?";
    $stmt = $conn->prepare($restore_query);
    $stmt->bind_param("i", $activity_id);
    
    if ($stmt->execute()) {
        echo "<script>alert('Task restored successfully!');</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }
}

// Set default view to active tasks
$show_archived = isset($_GET['show_archived']) ? $_GET['show_archived'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management</title>
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
            --progress-complete: rgb(116, 106, 100);
            --progress-inprogress: rgb(242, 183, 143);
            --progress-pending: rgb(164, 157, 150);
            --progress-overdue: #7a5b47;
            --archive-color: #6c757d;
            --edit-color: #007bff;
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
        }
        
        button {
            padding: 10px 15px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 10px;
            transition: background-color 0.3s ease;
        }
        
        button:hover {
            background-color: var(--secondary-color);
        }
        
        .form-popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            border: 2px solid #ddd;
            border-radius: 10px;
            box-shadow: var(--shadow);
            padding: 20px;
            z-index: 1000;
            width: 350px;
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .contact-form form {
            display: flex;
            flex-direction: column;
        }

        .contact-form input, .contact-form select, .contact-form textarea {
            margin-bottom: 15px;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: inherit;
        }

        .contact-form button {
            padding: 12px;
            margin-top: 5px;
        }
        
        .confirm-dialog {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            border: 2px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            z-index: 1000;
            width: 300px;
            text-align: center;
        }
        
        .confirm-dialog button {
            margin-top: 15px;
            margin-right: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        table th {
            background-color: var(--primary-color);
            color: white;
        }
        
        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        
        .action-buttons button {
            margin: 2px;
            padding: 5px 10px;
        }
        
        .action-buttons .edit-btn {
            background-color: var(--edit-color);
        }
        
        .action-buttons .edit-btn:hover {
            background-color: #0056b3; /* Darker blue for hover */
        }
        
        .action-buttons .archive-btn,
        .action-buttons .restore-btn {
            background-color: var(--archive-color);
        }
        
        .action-buttons .archive-btn:hover,
        .action-buttons .restore-btn:hover {
            background-color: #5a6268; /* Darker gray for hover */
        }
        
        .active-indicator {
            background-color: #e6f7e6;
        }
        
        .archived-indicator {
            background-color: #f7e6e6;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            color: white;
            text-align: center;
            min-width: 80px;
        }
        
        .status-pending {
            background-color: var(--progress-pending);
        }
        
        .status-inprogress {
            background-color: var(--progress-inprogress);
        }
        
        .status-complete {
            background-color: var(--progress-complete);
        }
        
        .status-overdue {
            background-color: var(--progress-overdue);
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
            
            .form-popup {
                width: 90%;
                max-width: 350px;
            }
            
            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
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
                <li><a href="donated_materials.php"><i class="fas fa-box"></i> Donated Materials</a></li>
                <li><a href="tasks.php" class="active"><i class="fas fa-tasks"></i> Tasks</a></li>
                <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                <li><a href="inventory.php"><i class="fas fa-boxes"></i> Inventory</a></li>
                <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <button class="toggle-sidebar"><i class="fas fa-bars"></i></button>
                <h1>Tasks</h1>
                <div class="user-actions">
                    <a href="profile.php" title="Profile"><i class="fas fa-user"></i></a>
                    <a href="logout.php" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
                </div>
            </header>

            <div class="button-row">
                <button onclick="showAddTaskForm()">Add Task</button>
                <a href="?show_archived=0"><button <?php echo $show_archived == 0 ? 'style="background-color:#7d5b46"' : ''; ?>>Active Tasks</button></a>
                <a href="?show_archived=1"><button <?php echo $show_archived == 1 ? 'style="background-color:#7d5b46"' : ''; ?>>Archived Tasks</button></a>
            </div>

            <div class="overlay" id="overlay" onclick="hideAllForms()"></div>
            
            <div id="addTaskForm" class="form-popup contact-form">
                <h2>Add New Task</h2>
                <form action="" method="POST" id="addTaskForm">
                    <label>Task Name:</label>
                    <input type="text" name="activity_name" required>
                    <label>Start Time:</label>
                    <input type="datetime-local" name="start_time" required>
                    <label>End Time:</label>
                    <input type="datetime-local" name="end_time" required>
                    <label>Assigned Staff:</label>
                    <select name="assigned_staff" required>
                        <option value="">Select Staff</option>
                        <?php
                        $staff_query = "SELECT email, fname, lname, role FROM staff_records";
                        $staff_result = $conn->query($staff_query);
                        while ($staff_row = $staff_result->fetch_assoc()) {
                            $display_name = $staff_row['fname'] . ' ' . $staff_row['lname'] . ' (' . $staff_row['email'] . ', ' . $staff_row['role'] . ')';
                            echo "<option value='{$staff_row['email']}'>{$display_name}</option>";
                        }
                        ?>
                    </select>
                    <label>Status:</label>
                    <select name="status" required>
                        <option value="pending">Pending</option>
                        <option value="inprogress">In Progress</option>
                        <option value="complete">Complete</option>
                        <option value="overdue">Overdue</option>
                    </select>
                    <button type="submit" name="submit_add">Add Task</button>
                    <button type="button" onclick="hideAddTaskForm()">Cancel</button>
                </form>
            </div>
            
            <div id="editTaskForm" class="form-popup contact-form">
                <h2>Edit Task</h2>
                <form action="" method="POST" id="editTaskForm">
                    <input type="hidden" id="edit_activity_id" name="activity_id">
                    <label>Task Name:</label>
                    <input type="text" id="edit_activity_name" name="activity_name" required>
                    <label>Start Time:</label>
                    <input type="datetime-local" id="edit_start_time" name="start_time" required>
                    <label>End Time:</label>
                    <input type="datetime-local" id="edit_end_time" name="end_time" required>
                    <label>Assigned Staff:</label>
                    <select id="edit_assigned_staff" name="assigned_staff" required>
                        <option value="">Select Staff</option>
                        <?php
                        $staff_query = "SELECT email, fname, lname, role FROM staff_records";
                        $staff_result = $conn->query($staff_query);
                        while ($staff_row = $staff_result->fetch_assoc()) {
                            $display_name = $staff_row['fname'] . ' ' . $staff_row['lname'] . ' (' . $staff_row['email'] . ', ' . $staff_row['role'] . ')';
                            echo "<option value='{$staff_row['email']}'>{$display_name}</option>";
                        }
                        ?>
                    </select>
                    <label>Status:</label>
                    <select id="edit_status" name="status" required>
                        <option value="pending">Pending</option>
                        <option value="inprogress">In Progress</option>
                        <option value="complete">Complete</option>
                        <option value="overdue">Overdue</option>
                    </select>
                    <button type="submit" name="submit_edit">Update Task</button>
                    <button type="button" onclick="hideEditTaskForm()">Cancel</button>
                </form>
            </div>
            
            <div id="archiveConfirmDialog" class="confirm-dialog">
                <h3>Confirm Archive</h3>
                <p>Are you sure you want to archive this task?</p>
                <form action="" method="POST">
                    <input type="hidden" id="archive_activity_id" name="activity_id">
                    <button type="submit" name="submit_archive">Yes, Archive</button>
                    <button type="button" onclick="hideArchiveConfirm()">Cancel</button>
                </form>
            </div>
            
            <div id="restoreConfirmDialog" class="confirm-dialog">
                <h3>Confirm Restore</h3>
                <p>Are you sure you want to restore this task?</p>
                <form action="" method="POST">
                    <input type="hidden" id="restore_activity_id" name="activity_id">
                    <button type="submit" name="submit_restore">Yes, Restore</button>
                    <button type="button" onclick="hideRestoreConfirm()">Cancel</button>
                </form>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Task Name</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Staff Name</th>
                        <th>Progress</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT a.*, s.email as staff_email FROM activity_schedules a 
                              LEFT JOIN staff_records s ON a.assigned_staff = s.staff_id
                              WHERE a.archived = ?
                              ORDER BY a.start_time DESC";
                              
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("i", $show_archived);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $start_time_display = date("M d, Y h:i A", strtotime($row['start_time']));
                            $end_time_display = date("M d, Y h:i A", strtotime($row['end_time']));
                            $start_time_input = date("Y-m-d\TH:i", strtotime($row['start_time']));
                            $end_time_input = date("Y-m-d\TH:i", strtotime($row['end_time']));
                            
                            $status_class = '';
                            switch ($row['status']) {
                                case 'pending':
                                    $status_class = 'status-pending';
                                    break;
                                case 'inprogress':
                                    $status_class = 'status-inprogress';
                                    break;
                                case 'complete':
                                    $status_class = 'status-complete';
                                    break;
                                case 'overdue':
                                    $status_class = 'status-overdue';
                                    break;
                                default:
                                    $status_class = 'status-pending';
                            }
                            
                            echo "<tr class='" . ($show_archived ? "archived-indicator" : "active-indicator") . "'>";
                            echo "<td>{$row['activity_name']}</td>";
                            echo "<td>{$start_time_display}</td>";
                            echo "<td>{$end_time_display}</td>";
                            echo "<td>{$row['staff_name']} ({$row['staff_email']})</td>";
                            echo "<td><span class='status-badge $status_class'>" . ucfirst($row['status']) . "</span></td>";
                            echo "<td class='action-buttons'>";
                            
                            if ($show_archived == 0) {
                                echo "<button class='edit-btn' onclick='showEditForm({$row['activity_id']}, 
                                               \"" . addslashes($row['activity_name']) . "\", 
                                               \"" . $start_time_input . "\", 
                                               \"" . $end_time_input . "\", 
                                               \"" . $row['staff_email'] . "\",
                                               \"" . $row['status'] . "\")'>Edit</button>";
                                echo "<button class='archive-btn' onclick='showArchiveConfirm({$row['activity_id']})'>Archive</button>";
                            } else {
                                echo "<button class='restore-btn' onclick='showRestoreConfirm({$row['activity_id']})'>Restore</button>";
                            }
                            
                            echo "</td></tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align:center;'>No " . ($show_archived ? "archived" : "active") . " tasks found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </main>
    </div>

    <script>
        function showAddTaskForm() {
            document.getElementById('addTaskForm').style.display = 'block';
            document.getElementById('overlay').style.display = 'block';
        }
        
        function hideAddTaskForm() {
            document.getElementById('addTaskForm').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }
        
        function showEditForm(activityId, activityName, startTime, endTime, staffEmail, status) {
            document.getElementById('edit_activity_id').value = activityId;
            document.getElementById('edit_activity_name').value = activityName;
            document.getElementById('edit_start_time').value = startTime;
            document.getElementById('edit_end_time').value = endTime;
            document.getElementById('edit_status').value = status;
            
            const staffDropdown = document.getElementById('edit_assigned_staff');
            for (let i = 0; i < staffDropdown.options.length; i++) {
                if (staffDropdown.options[i].value === staffEmail) {
                    staffDropdown.selectedIndex = i;
                    break;
                }
            }
            
            document.getElementById('editTaskForm').style.display = 'block';
            document.getElementById('overlay').style.display = 'block';
        }
        
        function hideEditTaskForm() {
            document.getElementById('editTaskForm').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }
        
        function showArchiveConfirm(activityId) {
            document.getElementById('archive_activity_id').value = activityId;
            document.getElementById('archiveConfirmDialog').style.display = 'block';
            document.getElementById('overlay').style.display = 'block';
        }
        
        function hideArchiveConfirm() {
            document.getElementById('archiveConfirmDialog').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }
        
        function showRestoreConfirm(activityId) {
            document.getElementById('restore_activity_id').value = activityId;
            document.getElementById('restoreConfirmDialog').style.display = 'block';
            document.getElementById('overlay').style.display = 'block';
        }
        
        function hideRestoreConfirm() {
            document.getElementById('restoreConfirmDialog').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }
        
        function hideAllForms() {
            hideAddTaskForm();
            hideEditTaskForm();
            hideArchiveConfirm();
            hideRestoreConfirm();
        }
        
        document.querySelector('.toggle-sidebar').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // Date validation for Add Task form
        document.getElementById('addTaskForm').addEventListener('submit', function(e) {
            const startTime = new Date(document.querySelector('#addTaskForm [name="start_time"]').value);
            const endTime = new Date(document.querySelector('#addTaskForm [name="end_time"]').value);
            
            if (startTime >= endTime) {
                e.preventDefault();
                alert('Error: Start time must be before end time');
                document.querySelector('#addTaskForm [name="start_time"]').focus();
            }
        });

        // Date validation for Edit Task form
        document.getElementById('editTaskForm').addEventListener('submit', function(e) {
            const startTime = new Date(document.querySelector('#editTaskForm [name="start_time"]').value);
            const endTime = new Date(document.querySelector('#editTaskForm [name="end_time"]').value);
            
            if (startTime >= endTime) {
                e.preventDefault();
                alert('Error: Start time must be before end time');
                document.querySelector('#editTaskForm [name="start_time"]').focus();
            }
        });
    </script>
</body>
</html>