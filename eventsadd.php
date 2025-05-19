<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', 'C:/xampp/htdocs/Magdalene-main/errors.log');

// Database connection
$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$database = "magdalene_management";

$conn = new mysqli($servername, $dbusername, $dbpassword, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = isset($_POST['action']) ? trim($_POST['action']) : 'add_event';

    if (in_array($action, ['add_event', 'update_event'])) {
        if (!isset($_POST['date'])) {
            $_SESSION['notification'] = "Error: Date is required.";
            header("Location: eventsadd.php");
            exit;
        }
    
        $submitted_date = $_POST['date'];
        $today = date('Y-m-d');
        error_log("date comparison: submitted_date=$submitted_date, today=$today");
        if ($submitted_date >= $today) {
            $_SESSION['notification'] = "Error: Cannot select a future date.";
            header("Location: eventsadd.php");
            exit;
        }
    }    

    if ($action == 'archive_event' && isset($_POST['submit_archive'])) {
        // Archive event
        $event_id = $_POST['event_id'];
        $stmt = $conn->prepare("UPDATE events SET is_archived = 1 WHERE id = ?");
        $stmt->bind_param("i", $event_id);
        
        if ($stmt->execute()) {
            $_SESSION['notification'] = "Event archived successfully!";
        } else {
            $_SESSION['notification'] = "Error archiving event: " . $stmt->error;
        }
        $stmt->close();
        header("Location: eventsadd.php" . (isset($_GET['archived']) ? "?archived=" . $_GET['archived'] : ""));
        exit;
    } 
    elseif ($action == 'restore_event' && isset($_POST['submit_restore'])) {
        // Restore event
        $event_id = $_POST['event_id'];
        $stmt = $conn->prepare("UPDATE events SET is_archived = 0 WHERE id = ?");
        $stmt->bind_param("i", $event_id);
        
        if ($stmt->execute()) {
            $_SESSION['notification'] = "Event restored successfully!";
        } else {
            $_SESSION['notification'] = "Error restoring event: " . $stmt->error;
        }
        $stmt->close();
        header("Location: eventsadd.php" . (isset($_GET['archived']) ? "?archived=" . $_GET['archived'] : ""));
        exit;
    }
    elseif ($action == 'update_event' && isset($_POST['event_id'])) {
        // Update existing event
        $event_id = $_POST['event_id'];
        $name = $_POST['name'];
        $date = $_POST['date'];
        $time = $_POST['time'];
        $amount = $_POST['amount'];
        $description = $_POST['description'];
        
        // Check if new image was uploaded
        if ($_FILES["image"]["size"] > 0) {
            // Handle image upload
            $target_dir = "Uploads/";
            
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;

            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_path = $target_file;
                
                $stmt = $conn->prepare("UPDATE events SET name=?, date=?, time=?, amount=?, description=?, image_path=? WHERE id=?");
                $stmt->bind_param("ssssssi", $name, $date, $time, $amount, $description, $image_path, $event_id);
            } else {
                $_SESSION['notification'] = "Error uploading image.";
                header("Location: eventsadd.php");
                exit;
            }
        } else {
            // No new image, keep the existing one
            $stmt = $conn->prepare("UPDATE events SET name=?, date=?, time=?, amount=?, description=? WHERE id=?");
            $stmt->bind_param("sssssi", $name, $date, $time, $amount, $description, $event_id);
        }

        if ($stmt->execute()) {
            $_SESSION['notification'] = "Event updated successfully!";
        } else {
            $_SESSION['notification'] = "Error updating event: " . $stmt->error;
        }
        $stmt->close();
        header("Location: eventsadd.php" . (isset($_GET['archived']) ? "?archived=" . $_GET['archived'] : ""));
        exit;
    }
    elseif ($action == "add_event") {
        if (!isset($_POST['name'], $_POST['date'], $_POST['time'], $_POST['amount'], $_POST['description'])) {
            $_SESSION['notification'] = "Error: All fields are required.";
            header("Location: eventsadd.php");
            exit;
        }
        // Add new event
        $name = $_POST['name'];
        $date = $_POST['date'];
        $time = $_POST['time'];
        $amount = $_POST['amount'];
        $description = $_POST['description'];
       
        // Handle image upload
        $target_dir = "Uploads/";
        
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = $target_file;
    
            $stmt = $conn->prepare("INSERT INTO events (name, date, time, amount, status, description, image_path, is_archived, created_at) VALUES (?, ?, ?, ?, 'active', ?, ?, 0, NOW())");
            $stmt->bind_param("ssssss", $name, $date, $time, $amount, $description, $image_path);

            if ($stmt->execute()) {
                $_SESSION['notification'] = "Donation activity added successfully!";
            } else {
                $_SESSION['notification'] = "Error adding donation activity: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $_SESSION['notification'] = "Error uploading image.";
        }
        header("Location: eventsadd.php");
        exit;
    }
}

// Check if we're viewing archived events
$show_archived = isset($_GET['archived']) && $_GET['archived'] == 1;

// Pagination
$records_per_page = 5;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $records_per_page;

// Count total records for pagination (based on archive status)
$total_records_query = "SELECT COUNT(*) as total FROM events WHERE is_archived = " . ($show_archived ? "1" : "0");
$result_count = $conn->query($total_records_query);
$total_records = $result_count->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get events with pagination (based on archive status)
$query = "SELECT * FROM events WHERE is_archived = " . ($show_archived ? "1" : "0") . " ORDER BY date DESC LIMIT $offset, $records_per_page";
$result = $conn->query($query);

// Fetch total funds raised for each event
$funds_raised = [];
$funds_query = "SELECT event_name, SUM(fee) as total_raised FROM pay WHERE status = 'success' AND type = 'Deposit' GROUP BY event_name";
$funds_result = $conn->query($funds_query);
while ($row = $funds_result->fetch_assoc()) {
    $funds_raised[$row['event_name']] = $row['total_raised'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events Management - Magdalene Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #926c54;
            --secondary-color: #7a5b47;
            --accent-color: #e74c3c;
            --background-color: #f8f4f1;
            --text-color: #333;
            --card-bg: #ffffff;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --border-color: #e0e0e0;
            --highlight: #f1e4d8;
            --success-color: #28a745;
            --error-color: #dc3545;
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
            z-index: 100;
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
            border-radius: 12px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }

        .topbar h1 {
            font-size: 1.8rem;
            color: var(--primary-color);
            font-weight: 600;
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

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 4px;
            color: white;
            z-index: 1001;
            animation: fadeOut 5s forwards;
        }

        .notification.success {
            background-color: var(--success-color);
        }

        .notification.error {
            background-color: var(--error-color);
        }

        @keyframes fadeOut {
            0% { opacity: 1; }
            80% { opacity: 1; }
            100% { opacity: 0; display: none; }
        }

        .content-columns {
            display: flex;
            gap: 30px;
            margin-bottom: 30px;
        }

        .column {
            flex: 1;
        }

        .form-container {
            background-color: var(--card-bg);
            padding: 30px;
            border-radius: 12px;
            box-shadow: var(--shadow);
        }

        .form-container h2 {
            margin-bottom: 20px;
            color: var(--primary-color);
            text-align: center;
            font-weight: 600;
            position: relative;
            padding-bottom: 10px;
        }

        .form-container h2:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background-color: var(--primary-color);
            border-radius: 2px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-color);
        }

        input[type="text"],
        input[type="date"],
        input[type="time"],
        textarea,
        input[type="file"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s, box-shadow 0.3s;
            background-color: #fafafa;
        }

        textarea {
            height: 120px;
            resize: vertical;
        }

        input:focus,
        textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(146, 108, 84, 0.2);
            background-color: white;
        }

        input[type="file"] {
            background-color: #f9f9f9;
            border: 1px dashed var(--border-color);
            padding: 15px;
            cursor: pointer;
        }

        input[type="file"]::file-selector-button {
            background-color: var(--primary-color);
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-right: 10px;
            transition: background-color 0.2s;
        }

        input[type="file"]::file-selector-button:hover {
            background-color: var(--secondary-color);
        }

        button[type="submit"] {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 14px 20px;
            font-size: 16px;
            font-weight: 500;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        button[type="submit"]:hover {
            background-color: var(--secondary-color);
        }

        .events-container {
            background-color: var(--card-bg);
            padding: 30px;
            border-radius: 12px;
            box-shadow: var(--shadow);
        }

        .events-container h2 {
            margin-bottom: 20px;
            color: var(--primary-color);
            text-align: center;
            font-weight: 600;
            position: relative;
            padding-bottom: 10px;
        }

        .events-container h2:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background-color: var(--primary-color);
            border-radius: 2px;
        }

        .event-card {
            background-color: #f9f9f9;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .event-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .event-content {
            display: flex;
        }

        .event-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
        }

        .event-details {
            padding: 15px;
            flex: 1;
        }

        .event-name {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 5px;
            font-size: 1.1rem;
        }

        .event-meta {
            display: flex;
            gap: 15px;
            margin-bottom: 10px;
            color: #666;
            font-size: 0.9rem;
        }

        .event-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .event-description {
            font-size: 0.95rem;
            margin-bottom: 10px;
            color: #555;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .event-amount {
            font-weight: 600;
            color: var(--secondary-color);
        }

        .event-funds-raised {
            font-weight: 600;
            color: #2ecc71;
            margin-top: 5px;
        }

        .event-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: background-color 0.2s ease;
            cursor: pointer;
            border: none;
        }

        .btn-edit {
            background-color: #e9f0fd;
            color: #3b7ddd;
        }

        .btn-edit:hover {
            background-color: #d6e4fc;
        }

        .btn-archive {
            background-color: #fde9e9;
            color: #dd3b3b;
        }

        .btn-archive:hover {
            background-color: #fcdada;
        }

        .btn-restore {
            background-color: #e9f9f0;
            color: #2ecc71;
        }

        .btn-restore:hover {
            background-color: #d1f2e0;
        }

        .archive-toggle {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
            gap: 10px;
        }

        .archive-toggle button {
            padding: 8px 16px;
            border-radius: 6px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .archive-toggle .active-toggle {
            background-color: var(--primary-color);
            color: white;
        }

        .archive-toggle .inactive-toggle {
            background-color: #f0f0f0;
            color: #666;
        }

        .archive-toggle .inactive-toggle:hover {
            background-color: #e0e0e0;
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 30px;
            gap: 5px;
        }

        .pagination a, .pagination span {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .pagination a {
            background-color: white;
            color: var(--text-color);
            border: 1px solid var(--border-color);
        }

        .pagination a:hover {
            background-color: var(--highlight);
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .pagination .active {
            background-color: var(--primary-color);
            color: white;
            border: 1px solid var(--primary-color);
        }

        .pagination .disabled {
            background-color: #f5f5f5;
            color: #aaa;
            cursor: not-allowed;
            border: 1px solid #e0e0e0;
        }

        .no-events {
            text-align: center;
            padding: 40px 0;
            color: #777;
        }

        .no-events i {
            font-size: 3rem;
            color: #ccc;
            margin-bottom: 15px;
            display: block;
        }

        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal {
            background-color: white;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-title {
            font-size: 1.5rem;
            color: var(--primary-color);
            font-weight: 600;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #777;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-image {
            width: 100%;
            border-radius: 8px;
            margin-bottom: 20px;
            max-height: 300px;
            object-fit: cover;
        }

        .modal-meta {
            margin-bottom: 20px;
        }

        .modal-meta-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .modal-meta-item i {
            color: var(--primary-color);
            width: 20px;
        }

        .modal-description {
            margin-bottom: 20px;
            line-height: 1.7;
        }

        .modal-footer {
            padding: 15px 20px;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        @media (max-width: 992px) {
            .content-columns {
                flex-direction: column;
            }
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

            .event-content {
                flex-direction: column;
            }

            .event-image {
                width: 100%;
                height: 180px;
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
                <li><a href="eventsadd.php" class="active"><i class="fas fa-calendar-alt"></i> Events</a></li>
                <li><a href="donated_materials.php"><i class="fas fa-box"></i> Donated Materials</a></li>
                <li><a href="tasks.php"><i class="fas fa-tasks"></i> Tasks</a></li>
                <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                <li><a href="inventory.php"><i class="fas fa-boxes"></i> Inventory</a></li>
                <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <button class="toggle-sidebar"><i class="fas fa-bars"></i></button>
                <h1>Events Management</h1>
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

            <div class="content-columns">
                <!-- Form Column -->
                <div class="column">
                    <div class="form-container">
                        <h2>Add New Event</h2>
                        <form action="" method="POST" enctype="multipart/form-data" id="addEventForm">
                            <div class="form-group">
                                <label class="form-label" for="name">Event Name</label>
                                <input type="text" id="name" name="name" placeholder="Enter event name" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="date">Date</label>
                                <input type="date" id="date" name="date" max="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="time">Time</label>
                                <input type="time" id="time" name="time" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="amount">Goal Amount</label>
                                <input type="text" id="amount" name="amount" placeholder="Enter donation goal amount" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="description">Description</label>
                                <textarea id="description" name="description" placeholder="Describe the event or donation" required></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="image">Event Image</label>
                                <input type="file" id="image" name="image" accept="image/*" required>
                            </div>
                            
                            <button type="submit"><i class="fas fa-plus-circle"></i> Add Event</button>
                        </form>
                    </div>
                </div>
                
                <!-- Events List Column -->
                <div class="column">
                    <div class="events-container">
                        <h2><?php echo $show_archived ? 'Archived Events' : 'Active Events'; ?></h2>
                        
                        <!-- Archive toggle buttons -->
                        <div class="archive-toggle">
                            <button class="<?php echo !$show_archived ? 'active-toggle' : 'inactive-toggle'; ?>" 
                                    onclick="window.location.href='?archived=0'">
                                Active Events
                            </button>
                            <button class="<?php echo $show_archived ? 'active-toggle' : 'inactive-toggle'; ?>" 
                                    onclick="window.location.href='?archived=1'">
                                Archived Events
                            </button>
                        </div>
                        
                        <?php if($result && $result->num_rows > 0): ?>
                            <?php while($event = $result->fetch_assoc()): ?>
                                <div class="event-card">
                                    <div class="event-content">
                                        <img src="<?php echo htmlspecialchars($event['image_path']); ?>" alt="<?php echo htmlspecialchars($event['name']); ?>" class="event-image">
                                        <div class="event-details">
                                            <h3 class="event-name"><?php echo htmlspecialchars($event['name']); ?></h3>
                                            <div class="event-meta">
                                                <span><i class="far fa-calendar"></i> <?php echo date('M d, Y', strtotime($event['date'])); ?></span>
                                                <span><i class="far fa-clock"></i> <?php echo date('h:i A', strtotime($event['time'])); ?></span>
                                            </div>
                                            <p class="event-description"><?php echo htmlspecialchars($event['description']); ?></p>
                                            <p class="event-amount"><i class="fas fa-money-bill-wave"></i> Goal: <?php echo htmlspecialchars($event['amount']); ?></p>
                                            <p class="event-funds-raised"><i class="fas fa-donate"></i> Raised: <?php echo isset($funds_raised[$event['name']]) ? number_format($funds_raised[$event['name']], 2) : '0.00'; ?></p>
                                            <div class="event-actions">
                                                <button class="btn btn-edit edit-event" data-id="<?php echo $event['id']; ?>">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <?php if($show_archived): ?>
                                                    <form method="POST" onsubmit="return confirm('Are you sure you want to restore this event?');">
                                                        <input type="hidden" name="action" value="restore_event">
                                                        <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                                        <input type="hidden" name="submit_restore" value="1">
                                                        <button type="submit" class="btn btn-restore">
                                                            <i class="fas fa-undo"></i> Restore
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <form method="POST" onsubmit="return confirm('Are you sure you want to archive this event?');">
                                                        <input type="hidden" name="action" value="archive_event">
                                                        <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                                        <input type="hidden" name="submit_archive" value="1">
                                                        <button type="submit" class="btn btn-archive">
                                                            <i class="fas fa-archive"></i> Archive
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                            
                            <!-- Pagination -->
                            <div class="pagination">
                                <?php if($page > 1): ?>
                                    <a href="?page=1&archived=<?php echo $show_archived ? 1 : 0; ?>"><i class="fas fa-angle-double-left"></i></a>
                                    <a href="?page=<?php echo $page-1; ?>&archived=<?php echo $show_archived ? 1 : 0; ?>"><i class="fas fa-angle-left"></i></a>
                                <?php else: ?>
                                    <span class="disabled"><i class="fas fa-angle-double-left"></i></span>
                                    <span class="disabled"><i class="fas fa-angle-left"></i></span>
                                <?php endif; ?>
                                
                                <?php 
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $page + 2);
                                
                                for($i = $start_page; $i <= $end_page; $i++): 
                                ?>
                                    <?php if($i == $page): ?>
                                        <span class="active"><?php echo $i; ?></span>
                                    <?php else: ?>
                                        <a href="?page=<?php echo $i; ?>&archived=<?php echo $show_archived ? 1 : 0; ?>"><?php echo $i; ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                
                                <?php if($page < $total_pages): ?>
                                    <a href="?page=<?php echo $page+1; ?>&archived=<?php echo $show_archived ? 1 : 0; ?>"><i class="fas fa-angle-right"></i></a>
                                    <a href="?page=<?php echo $total_pages; ?>&archived=<?php echo $show_archived ? 1 : 0; ?>"><i class="fas fa-angle-double-right"></i></a>
                                <?php else: ?>
                                    <span class="disabled"><i class="fas fa-angle-right"></i></span>
                                    <span class="disabled"><i class="fas fa-angle-double-right"></i></span>
                                <?php endif; ?>
                            </div>
                            
                        <?php else: ?>
                            <div class="no-events">
                                <i class="fas fa-calendar-times"></i>
                                <p>No <?php echo $show_archived ? 'archived' : 'active'; ?> events found.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal for event editing -->
    <div class="modal-backdrop" id="eventModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Edit Event</h3>
                <button class="modal-close" id="closeModal">×</button>
            </div>
            <form id="editEventForm" method="POST" enctype="multipart/form-data">
                <div class="modal-body" id="modalContent">
                    <input type="hidden" name="action" value="update_event">
                    <input type="hidden" name="event_id" id="editEventId">
                    
                    <img id="currentEventImage" src="" alt="Current Event Image" class="modal-image">
                    
                    <div class="form-group">
                        <label class="form-label" for="editName">Event Name</label>
                        <input type="text" id="editName" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="editDate">Date</label>
                        <input type="date" id="editDate" name="date" max="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="editTime">Time</label>
                        <input type="time" id="editTime" name="time" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="editAmount">Goal Amount</label>
                        <input type="text" id="editAmount" name="amount" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="editDescription">Description</label>
                        <textarea id="editDescription" name="description" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="editImage">Change Image (Leave blank to keep current)</label>
                        <input type="file" id="editImage" name="image" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-archive" id="closeModalBtn">Cancel</button>
                    <button type="submit" class="btn btn-edit"><i class="fas fa-save"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Toggle sidebar on mobile
        const toggleSidebar = document.querySelector('.toggle-sidebar');
        const sidebar = document.querySelector('.sidebar');

        toggleSidebar.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });

        // Date validation for Add Event form
        const addEventForm = document.getElementById('addEventForm');
        addEventForm.addEventListener('submit', function(e) {
            const dateInput = document.getElementById('date');
            const selectedDate = new Date(dateInput.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0); // Reset time for comparison

            if (selectedDate > today) {
                e.preventDefault();
                alert('Error: Cannot select a future date');
                dateInput.focus();
            }
        });

        // Date validation for Edit Event form
        const editEventForm = document.getElementById('editEventForm');
        editEventForm.addEventListener('submit', function(e) {
            const dateInput = document.getElementById('editDate');
            const selectedDate = new Date(dateInput.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if (selectedDate > today) {
                e.preventDefault();
                alert('Error: Cannot select a future date');
                dateInput.focus();
            }
        });

        // Event edit modal functionality
        const modal = document.getElementById('eventModal');
        const closeModal = document.getElementById('closeModal');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const editButtons = document.querySelectorAll('.edit-event');

        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const eventId = this.getAttribute('data-id');
                
                const card = this.closest('.event-card');
                const name = card.querySelector('.event-name').textContent;
                const dateText = card.querySelector('.event-meta span:nth-child(1)').textContent.replace('', '');
                const timeText = card.querySelector('.event-meta span:nth-child(2)').textContent.replace('', '');
                const description = card.querySelector('.event-description').textContent;
                const amount = card.querySelector('.event-amount').textContent.replace('Goal:', '').trim();
                const imageSrc = card.querySelector('.event-image').src;
                
                const dateParts = dateText.split(' ');
                const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
                const monthIndex = monthNames.indexOf(dateParts[0]);
                const day = dateParts[1].replace(',', '');
                const year = dateParts[2];
                const formattedDate = `${year}-${String(monthIndex + 1).padStart(2, '0')}-${day.padStart(2, '0')}`;
                
                const timeParts = timeText.split(' ');
                const timeValue = timeParts[0];
                const period = timeParts[1];
                const [hours, minutes] = timeValue.split(':');
                let formattedTime = '';
                
                if (period === 'AM') {
                    formattedTime = `${hours.padStart(2, '0')}:${minutes}`;
                    if (hours === '12') {
                        formattedTime = `00:${minutes}`;
                    }
                } else {
                    formattedTime = `${String(parseInt(hours) + 12).padStart(2, '0')}:${minutes}`;
                    if (hours === '12') {
                        formattedTime = `12:${minutes}`;
                    }
                }
                
                document.getElementById('editEventId').value = eventId;
                document.getElementById('editName').value = name;
                document.getElementById('editDate').value = formattedDate;
                document.getElementById('editTime').value = formattedTime;
                document.getElementById('editAmount').value = amount.replace('', '').trim();
                document.getElementById('editDescription').value = description;
                document.getElementById('currentEventImage').src = imageSrc;
                
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            });
        });

        closeModal.addEventListener('click', () => {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        });

        closeModalBtn.addEventListener('click', () => {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        });

        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });

        editEventForm.addEventListener('submit', function(e) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        });
    </script>
</body>
</html>