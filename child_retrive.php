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

// Function to sanitize filenames
function sanitizeFilename($string) {
    return preg_replace('/[^A-Za-z0-9\-_\.]/', '', str_replace(' ', '_', $string));
}

// Ensure the "is_graduate" column exists
$check_column = "SHOW COLUMNS FROM child_records LIKE 'is_graduate'";
$result = $conn->query($check_column);
if ($result->num_rows == 0) {
    $add_column = "ALTER TABLE child_records ADD COLUMN is_graduate BOOLEAN DEFAULT FALSE";
    $conn->query($add_column);
}

// Handle CSV Download
if (isset($_GET['download_csv']) && $_GET['download_csv'] == '1') {
    $show_archived = isset($_GET['show_archived']) ? (int)$_GET['show_archived'] : 0;
    $show_graduate = isset($_GET['show_graduate']) ? (int)$_GET['show_graduate'] : 0;
    $search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
    $search_term = "%$search_query%";
    
    // Fetch all relevant records
    $query = "SELECT fname, lname, dateofbirth, gender, staff_email, relatives_phonenumber, relatives_address, medical_info, education_info, child_backgroundinfo, archived, is_graduate 
              FROM child_records WHERE archived = ? AND is_graduate = ?";
    
    // Add search conditions if search term exists
    if ($search_query !== '') {
        $query .= " AND (fname LIKE ? OR lname LIKE ?)";
    }
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        die("Query preparation failed.");
    }
    
    // Bind parameters based on whether there's a search term
    if ($search_query !== '') {
        $stmt->bind_param("iiss", $show_archived, $show_graduate, $search_term, $search_term);
    } else {
        $stmt->bind_param("ii", $show_archived, $show_graduate);
    }
    
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        die("Query execution failed.");
    }
    
    $result = $stmt->get_result();
    
    // Check if there are any records
    if ($result->num_rows === 0) {
        header('Content-Type: text/plain');
        echo "No records found matching your criteria";
        exit;
    }

    // Set headers for CSV download 
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="child_records.csv"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    ob_end_clean();
    
    $output = fopen('php://output', 'w');
    if ($output === false) {
        error_log("Failed to open php://output");
        die("Output stream failed.");
    }
    
    fputcsv($output, [
        'First Name', 
        'Last Name', 
        'Date of Birth', 
        'Gender', 
        'Assigned Staff Email', 
        'Relative\'s Phone Number', 
        'Relative\'s Address', 
        'Medical Information', 
        'Education Information', 
        'Background Information', 
        'Status', 
        'Graduate Status'
    ]);
    
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['fname'],
            $row['lname'],
            $row['dateofbirth'],
            $row['gender'],
            $row['staff_email'],
            $row['relatives_phonenumber'],
            $row['relatives_address'],
            $row['medical_info'],
            $row['education_info'],
            $row['child_backgroundinfo'],
            $row['archived'] ? 'Archived' : 'Active',
            $row['is_graduate'] ? 'Graduate' : 'Not Graduate'
        ]);
    }
    
    fclose($output);
    $stmt->close();
    $conn->close();
    exit;
}

$check_column = "SHOW COLUMNS FROM child_records LIKE 'archived'";
$result = $conn->query($check_column);
if ($result->num_rows == 0) {
    $add_column = "ALTER TABLE child_records ADD COLUMN archived TINYINT DEFAULT 0";
    $conn->query($add_column);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_add'])) {
    error_log("Add child POST request received: " . print_r($_POST, true));
    
    // Sanitize and validate inputs
    $fname = trim($_POST['fname'] ?? '');
    $lname = trim($_POST['lname'] ?? '');
    $dateofbirth = trim($_POST['dateofbirth'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $medical_info = trim($_POST['medical_info'] ?? '');
    $education_info = trim($_POST['education_info'] ?? '');
    $staff_email = trim($_POST['staff_email'] ?? '');
    $relatives_phonenumber = trim($_POST['relatives_phonenumber'] ?? '');
    $child_backgroundinfo = trim($_POST['child_backgroundinfo'] ?? '');
    $relatives_address = trim($_POST['relatives_address'] ?? '');

    // Validate required fields
    $required_fields = [
        'fname' => $fname,
        'lname' => $lname,
        'dateofbirth' => $dateofbirth,
        'gender' => $gender,
        'medical_info' => $medical_info,
        'education_info' => $education_info,
        'staff_email' => $staff_email,
        'relatives_phonenumber' => $relatives_phonenumber,
        'child_backgroundinfo' => $child_backgroundinfo,
        'relatives_address' => $relatives_address
    ];
    foreach ($required_fields as $field => $value) {
        if (empty($value)) {
            error_log("Validation failed: $field is empty");
            $_SESSION['notification'] = "Error: All fields are required.";
            header("Location: child_retrive.php");
            exit;
        }
    }

    // Validate date of birth
    $current_date = date('Y-m-d');
    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $dateofbirth)) {
        error_log("Invalid date format: $dateofbirth");
        $_SESSION['notification'] = "Error: Invalid date of birth format (YYYY-MM-DD).";
        header("Location: child_retrive.php");
        exit;
    }
    if ($dateofbirth > $current_date) {
        error_log("Future date of birth rejected: $dateofbirth");
        $_SESSION['notification'] = "Error: Date of birth cannot be in the future.";
        header("Location: child_retrive.php");
        exit;
    }

    // Prepare and execute the INSERT query
    $query = "INSERT INTO child_records (fname, lname, dateofbirth, gender, medical_info, education_info, staff_email, relatives_phonenumber, child_backgroundinfo, relatives_address, archived, is_graduate) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0)";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        $_SESSION['notification'] = "Error preparing query: " . $conn->error;
        header("Location: child_retrive.php");
        exit;
    }
    
    $stmt->bind_param("ssssssssss", $fname, $lname, $dateofbirth, $gender, $medical_info, $education_info, $staff_email, $relatives_phonenumber, $child_backgroundinfo, $relatives_address);
    
    if ($stmt->execute()) {
        error_log("Child record added successfully: $fname $lname");
        $_SESSION['notification'] = "Child record added successfully!";
        header("Location: child_retrive.php");
        exit;
    } else {
        error_log("Insert failed: " . $stmt->error);
        $_SESSION['notification'] = "Error adding child record: " . $stmt->error;
        header("Location: child_retrive.php");
        exit;
    }
    
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_edit'])) {
    $child_id = $_POST['child_id'] ?? 0;
    $fname = $_POST['fname'] ?? '';
    $lname = $_POST['lname'] ?? '';
    $dateofbirth = $_POST['dateofbirth'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $medical_info = $_POST['medical_info'] ?? '';
    $education_info = $_POST['education_info'] ?? '';
    $staff_email = $_POST['staff_email'] ?? '';
    $relatives_phonenumber = $_POST['relatives_phonenumber'] ?? '';
    $child_backgroundinfo = $_POST['child_backgroundinfo'] ?? '';
    $relatives_address = $_POST['relatives_address'] ?? '';

    $current_date = date('Y-m-d');
    if ($dateofbirth > $current_date) {
        $_SESSION['notification'] = "Error: Date of birth cannot be in the future.";
    } else {
        $query = "UPDATE child_records SET fname = ?, lname = ?, dateofbirth = ?, gender = ?, medical_info = ?, education_info = ?, staff_email = ?, relatives_phonenumber = ?, child_backgroundinfo = ?, relatives_address = ? 
                  WHERE child_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssssssssi", $fname, $lname, $dateofbirth, $gender, $medical_info, $education_info, $staff_email, $relatives_phonenumber, $child_backgroundinfo, $relatives_address, $child_id);

        if ($stmt->execute()) {
            $_SESSION['notification'] = "Child record updated successfully!";
            header("Location: child_retrive.php");
            exit;
        } else {
            $_SESSION['notification'] = "Error updating child record: " . $stmt->error;
        }
        $stmt->close();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_archive'])) {
    $child_id = $_POST['child_id'] ?? 0;
    $query = "UPDATE child_records SET archived = 1 WHERE child_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $child_id);
    if ($stmt->execute()) {
        $_SESSION['notification'] = "Child record archived successfully!";
        header("Location: child_retrive.php");
        exit;
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_restore'])) {
    $child_id = $_POST['child_id'] ?? 0;
    $query = "UPDATE child_records SET archived = 0 WHERE child_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $child_id);
    if ($stmt->execute()) {
        $_SESSION['notification'] = "Child record restored successfully!";
        header("Location: child_retrive.php");
        exit;
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_graduate'])) {
    $child_id = $_POST['child_id'] ?? 0;
    $query = "UPDATE child_records SET is_graduate = 1 WHERE child_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $child_id);
    if ($stmt->execute()) {
        $_SESSION['notification'] = "Child marked as graduate successfully!";
        header("Location: child_retrive.php");
        exit;
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_ungraduate'])) {
    $child_id = $_POST['child_id'] ?? 0;
    $query = "UPDATE child_records SET is_graduate = 0 WHERE child_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $child_id);
    if ($stmt->execute()) {
        $_SESSION['notification'] = "Child restored to active status successfully!";
        header("Location: child_retrive.php");
        exit;
    }
    $stmt->close();
}

$staff_query = "SELECT email, fname, lname, role FROM staff_records WHERE role IN ('caregiver', 'volunteer', 'cooker', 'teacher') AND archived = 0";
$staff_result = $conn->query($staff_query);

$records_per_page = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $records_per_page;

$show_archived = isset($_GET['show_archived']) ? $_GET['show_archived'] : 0;
$show_graduate = isset($_GET['show_graduate']) ? $_GET['show_graduate'] : 0;
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

$query = "SELECT * FROM child_records WHERE archived = ? AND is_graduate = ?";
$count_query = "SELECT COUNT(*) as total FROM child_records WHERE archived = ? AND is_graduate = ?";
$params = [$show_archived, $show_graduate];
$types = "ii";

if ($search_term !== '') {
    $query .= " AND (fname LIKE ? OR lname LIKE ?)";
    $count_query .= " AND (fname LIKE ? OR lname LIKE ?)";
    $search_pattern = "%" . $search_term . "%";
    $params[] = $search_pattern;
    $params[] = $search_pattern;
    $types .= "ss";
}

$query .= " ORDER BY child_id DESC LIMIT ? OFFSET ?";
$params[] = $records_per_page;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($count_query);
$stmt->bind_param(substr($types, 0, -2), ...array_slice($params, 0, -2));
$stmt->execute();
$total_records = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Child Management</title>
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
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .button-row button, .button-row a {
            padding: 10px 15px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s ease;
            font-weight: 500;
        }

        .button-row button:hover, .button-row a:hover {
            background-color: var(--secondary-color);
        }

        .search-container {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .search-form {
            display: flex;
            align-items: center;
            width: 100%;
            max-width: 500px;
            position: relative;
        }

        .search-form input[type="text"] {
            width: 100%;
            padding: 12px 80px 12px 20px;
            border: 1px solid var(--border-color);
            border-radius: 25px;
            font-size: 1rem;
            box-shadow: var(--shadow);
            transition: border-color 0.3s ease;
        }

        .search-form input[type="text"]:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .search-form .search-button, .search-form .clear-button {
            position: absolute;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
        }

        .search-form .search-button {
            right: 40px;
            color: var(--primary-color);
        }

        .search-form .clear-button {
            right: 10px;
            color: #6c757d;
        }

        .search-form .search-button:hover {
            color: var(--secondary-color);
        }

        .search-form .clear-button:hover {
            color: #495057;
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
            background-color: #f9f9f9;
        }

        table td button, table td a.button {
            padding: 8px 12px;
            margin: 0 5px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            color: white;
            transition: background-color 0.3s ease, transform 0.2s ease;
            font-weight: 500;
        }

        table td button:hover, table td a.button:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        table td button.edit-btn,
        table td button.graduate-btn, 
        table td button.ungraduate-btn {
            background-color: var(--edit-color);
        }

        table td button.edit-btn:hover,
        table td button.graduate-btn:hover, 
        table td button.ungraduate-btn:hover {
            background-color: #0056b3;
        }

        table td button.archive-btn, 
        table td button.restore-btn,
        table td button.download-btn,
        table td a.details-btn {
            background-color: var(--archive-color);
        }

        table td button.archive-btn:hover, 
        table td button.restore-btn:hover,
        table td button.download-btn:hover,
        table td a.details-btn:hover {
            background-color: #5a6268;
        }

        .form-popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: var(--shadow);
            z-index: 1000;
            width: 400px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .form-popup label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-popup input,
        .form-popup textarea,
        .form-popup select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .form-popup .form-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .form-popup button {
            padding: 10px 20px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-weight: 500;
        }

        .form-popup button:hover {
            background-color: var(--secondary-color);
        }

        .form-popup button.cancel {
            background-color: #6c757d;
        }

        .form-popup button.cancel:hover {
            background-color: #5a6268;
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

        .pagination {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .pagination a {
            padding: 8px 12px;
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }

        .pagination a:hover {
            background-color: var(--secondary-color);
        }

        .pagination a.active {
            background-color: var(--secondary-color);
            cursor: default;
        }

        .pagination a.disabled {
            background-color: #ddd;
            color: #666;
            cursor: not-allowed;
        }

        .clickable-row {
            cursor: pointer;
        }

        .clickable-row:hover {
            background-color: #f1f1f1 !important;
        }

        .error-message {
            color: var(--error-color);
            font-size: 0.9rem;
            margin-top: -8px;
            margin-bottom: 10px;
            display: none;
        }

        @media print {
            .sidebar, .topbar, .search-container, .button-row, .pagination, .notification, .overlay, .form-popup {
                display: none !important;
            }
            .main-content {
                margin-left: 0;
                padding: 0;
            }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            table th, table td {
                border: 1px solid #000;
                padding: 8px;
            }
            table th {
                background-color: #f0f0f0;
                color: #000;
            }
            table th:last-child, table td:last-child {
                display: none;
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
                <li><a href="child_retrive.php" class="active"><i class="fas fa-child"></i> Child Records</a></li>
                <li><a href="auth/addstaff_retrive.php"><i class="fas fa-users"></i> Staff Management</a></li>
                <li><a href="admin_don.php"><i class="fas fa-donate"></i> Donations</a></li>
                <li><a href="eventsadd.php"><i class="fas fa-calendar-alt"></i> Events</a></li>
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
                <h1>Child Records</h1>
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

            <div class="search-container">
                <form class="search-form" method="GET" action="">
                    <input type="text" id="searchInput" name="search" placeholder="Search by First or Last Name" value="<?php echo htmlspecialchars($search_term); ?>">
                    <input type="hidden" name="show_archived" value="<?php echo $show_archived; ?>">
                    <input type="hidden" name="show_graduate" value="<?php echo $show_graduate; ?>">
                    <button type="submit" class="search-button"><i class="fas fa-search"></i></button>
                    <button type="button" class="clear-button" onclick="clearSearch()"><i class="fas fa-times"></i></button>
                </form>
            </div>

            <div class="button-row">
                <button onclick="showAddChildForm()">Add Child</button>
                <a href="?show_archived=0&show_graduate=0<?php echo $search_term ? '&search=' . urlencode($search_term) : ''; ?>">
                    <button <?php echo $show_archived == 0 && $show_graduate == 0 ? 'style="background-color: var(--secondary-color);"' : ''; ?>>Active Records</button>
                </a>
                <a href="?show_archived=1&show_graduate=0<?php echo $search_term ? '&search=' . urlencode($search_term) : ''; ?>">
                    <button <?php echo $show_archived == 1 && $show_graduate == 0 ? 'style="background-color: var(--secondary-color);"' : ''; ?>>Archived Records</button>
                </a>
                <a href="?show_archived=0&show_graduate=1<?php echo $search_term ? '&search=' . urlencode($search_term) : ''; ?>">
                    <button <?php echo $show_archived == 0 && $show_graduate == 1 ? 'style="background-color: var(--secondary-color);"' : ''; ?>>Graduated Records</button>
                </a>
                <button onclick="printTable()"><i class="fas fa-print"></i> Print</button>
                <a href="?download_csv=1&show_archived=<?php echo $show_archived; ?>&show_graduate=<?php echo $show_graduate; ?>&search=<?php echo urlencode($search_term); ?>">
                    <button><i class="fas fa-download"></i> Download All as CSV</button>
                </a>
            </div>

            <table id="childTable">
                <thead>
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Date of Birth</th>
                        <th>Gender</th>
                        <th>Assigned Staff Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="clickable-row" data-child-id="<?php echo $row['child_id']; ?>">
                            <td><?php echo htmlspecialchars($row['fname']); ?></td>
                            <td><?php echo htmlspecialchars($row['lname']); ?></td>
                            <td><?php echo htmlspecialchars($row['dateofbirth']); ?></td>
                            <td><?php echo htmlspecialchars($row['gender']); ?></td>
                            <td><?php echo htmlspecialchars($row['staff_email']); ?></td>
                            <td onclick="event.stopPropagation()">
                                <button class="edit-btn" onclick='showEditChildForm(<?php echo json_encode($row); ?>)'><i class="fas fa-edit"></i>Edit</button>
                                <?php if ($show_archived == 0 && $show_graduate == 0): ?>
                                    <button class="archive-btn" onclick="archiveChild(<?php echo $row['child_id']; ?>)">Archive</button>
                                    <button class="graduate-btn" onclick="graduateChild(<?php echo $row['child_id']; ?>)">Graduate</button>
                                <?php elseif ($show_archived == 1): ?>
                                    <button class="restore-btn" onclick="restoreChild(<?php echo $row['child_id']; ?>)">Restore</button>
                                <?php elseif ($show_graduate == 1): ?>
                                    <button class="ungraduate-btn" onclick="ungraduateChild(<?php echo $row['child_id']; ?>)">Restore to Active</button>
                                <?php endif; ?>
                                <a href="child_details.php?child_id=<?php echo $row['child_id']; ?>" class="button details-btn">Details</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&show_archived=<?php echo $show_archived; ?>&show_graduate=<?php echo $show_graduate; ?><?php echo $search_term ? '&search=' . urlencode($search_term) : ''; ?>">Previous</a>
                <?php else: ?>
                    <a class="disabled">Previous</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&show_archived=<?php echo $show_archived; ?>&show_graduate=<?php echo $show_graduate; ?><?php echo $search_term ? '&search=' . urlencode($search_term) : ''; ?>" <?php echo $i == $page ? 'class="active"' : ''; ?>><?php echo $i; ?></a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&show_archived=<?php echo $show_archived; ?>&show_graduate=<?php echo $show_graduate; ?><?php echo $search_term ? '&search=' . urlencode($search_term) : ''; ?>">Next</a>
                <?php else: ?>
                    <a class="disabled">Next</a>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <div class="overlay" id="overlay" onclick="hideAllForms()"></div>

    <!-- Add Child Form -->
    <div class="form-popup" id="addChildForm">
        <h2>Add Child</h2>
        <form action="" method="POST" id="addChildFormElement">
            <label for="add_fname">First Name</label>
            <input type="text" id="add_fname" name="fname" required>
            <span class="error-message" id="add_fname_error"></span>

            <label for="add_lname">Last Name</label>
            <input type="text" id="add_lname" name="lname" required>
            <span class="error-message" id="add_lname_error"></span>

            <label for="add_dateofbirth">Date of Birth</label>
            <input type="date" id="add_dateofbirth" name="dateofbirth" required>
            <span class="error-message" id="add_dateofbirth_error"></span>

            <label for="add_gender">Gender</label>
            <select id="add_gender" name="gender" required>
                <option value="">Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>
            <span class="error-message" id="add_gender_error"></span>

            <label for="add_medical_info">Medical Information</label>
            <select id="add_medical_info" name="medical_info" required>
                <option value="">Select Medical Condition</option>
                <option value="None">None</option>
                <option value="Autism">Autism</option>
                <option value="Hearing Impairment">Hearing Impairment</option>
                <option value="Visual Impairment">Visual Impairment</option>
                <option value="Physical Impairment">Physical Impairment</option>
                <option value="Other">Other</option>
            </select>
            <span class="error-message" id="add_medical_info_error"></span>

            <label for="add_education_info">Education Information</label>
            <textarea id="add_education_info" name="education_info" required></textarea>
            <span class="error-message" id="add_education_info_error"></span>

            <label for="add_staff_email">Assigned Staff Email</label>
            <select id="add_staff_email" name="staff_email" required>
                <option value="">Select Staff</option>
                <?php 
                $staff_result->data_seek(0);
                while ($staff = $staff_result->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($staff['email']); ?>">
                        <?php echo htmlspecialchars($staff['email'] . ' (' . $staff['fname'] . ' ' . $staff['lname'] . ' - ' . $staff['role'] . ')'); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <span class="error-message" id="add_staff_email_error"></span>

            <label for="add_relatives_phonenumber">Relative's Phone Number</label>
            <input type="text" id="add_relatives_phonenumber" name="relatives_phonenumber" required>
            <span class="error-message" id="add_relatives_phonenumber_error"></span>

            <label for="add_child_backgroundinfo">Background Information</label>
            <textarea id="add_child_backgroundinfo" name="child_backgroundinfo" required></textarea>
            <span class="error-message" id="add_child_backgroundinfo_error"></span>

            <label for="add_relatives_address">Relative's Address</label>
            <textarea id="add_relatives_address" name="relatives_address" required></textarea>
            <span class="error-message" id="add_relatives_address_error"></span>

            <div class="form-buttons">
                <button type="submit" name="submit_add">Add Child</button>
                <button type="button" class="cancel" onclick="hideAddChildForm()">Cancel</button>
            </div>
        </form>
    </div>

    <!-- Edit Child Form -->
    <div class="form-popup" id="editChildForm">
        <h2>Edit Child</h2>
        <form action="" method="POST" id="editChildFormElement">
            <input type="hidden" name="child_id" id="edit_child_id">

            <label for="edit_fname">First Name</label>
            <input type="text" id="edit_fname" name="fname" required>
            <span class="error-message" id="edit_fname_error"></span>

            <label for="edit_lname">Last Name</label>
            <input type="text" id="edit_lname" name="lname" required>
            <span class="error-message" id="edit_lname_error"></span>

            <label for="edit_dateofbirth">Date of Birth</label>
            <input type="date" id="edit_dateofbirth" name="dateofbirth" required>
            <span class="error-message" id="edit_dateofbirth_error"></span>

            <label for="edit_gender">Gender</label>
            <select id="edit_gender" name="gender" required>
                <option value="">Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>
            <span class="error-message" id="edit_gender_error"></span>

            <label for="edit_medical_info">Medical Information</label>
            <select id="edit_medical_info" name="medical_info" required>
                <option value="">Select Medical Condition</option>
                <option value="None">None</option>
                <option value="Autism">Autism</option>
                <option value="Hearing Impairment">Hearing Impairment</option>
                <option value="Visual Impairment">Visual Impairment</option>
                <option value="Physical Impairment">Physical Impairment</option>
                <option value="Other">Other</option>
            </select>
            <span class="error-message" id="edit_medical_info_error"></span>

            <label for="edit_education_info">Education Information</label>
            <textarea id="edit_education_info" name="education_info" required></textarea>
            <span class="error-message" id="edit_education_info_error"></span>

            <label for="edit_staff_email">Assigned Staff Email</label>
            <select id="edit_staff_email" name="staff_email" required>
                <option value="">Select Staff</option>
                <?php 
                $staff_result->data_seek(0);
                while ($staff = $staff_result->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($staff['email']); ?>">
                        <?php echo htmlspecialchars($staff['email'] . ' (' . $staff['fname'] . ' ' . $staff['lname'] . ' - ' . $staff['role'] . ')'); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <span class="error-message" id="edit_staff_email_error"></span>

            <label for="edit_relatives_phonenumber">Relative's Phone Number</label>
            <input type="text" id="edit_relatives_phonenumber" name="relatives_phonenumber" required>
            <span class="error-message" id="edit_relatives_phonenumber_error"></span>

            <label for="edit_child_backgroundinfo">Background Information</label>
            <textarea id="edit_child_backgroundinfo" name="child_backgroundinfo" required></textarea>
            <span class="error-message" id="edit_child_backgroundinfo_error"></span>

            <label for="edit_relatives_address">Relative's Address</label>
            <textarea id="edit_relatives_address" name="relatives_address" required></textarea>
            <span class="error-message" id="edit_relatives_address_error"></span>

            <div class="form-buttons">
                <button type="submit" name="submit_edit">Update Child</button>
                <button type="button" class="cancel" onclick="hideEditChildForm()">Cancel</button>
            </div>
        </form>
    </div>

    <script>
        // Form validation
        function validateForm(formId) {
            const form = document.getElementById(formId);
            let isValid = true;
            const today = new Date('2025-05-12'); // Current date

            // Clear previous errors
            form.querySelectorAll('.error-message').forEach(el => {
                el.style.display = 'none';
                el.textContent = '';
            });

            // Validate fields
            const fields = [
                { id: 'fname', errorId: 'fname_error', message: 'First name is required' },
                { id: 'lname', errorId: 'lname_error', message: 'Last name is required' },
                { id: 'dateofbirth', errorId: 'dateofbirth_error', message: 'Date of birth is required' },
                { id: 'gender', errorId: 'gender_error', message: 'Gender is required' },
                { id: 'medical_info', errorId: 'medical_info_error', message: 'Medical information is required' },
                { id: 'education_info', errorId: 'education_info_error', message: 'Education information is required' },
                { id: 'staff_email', errorId: 'staff_email_error', message: 'Assigned staff email is required' },
                { id: 'relatives_phonenumber', errorId: 'relatives_phonenumber_error', message: 'Relative\'s phone number is required' },
                { id: 'child_backgroundinfo', errorId: 'child_backgroundinfo_error', message: 'Background information is required' },
                { id: 'relatives_address', errorId: 'relatives_address_error', message: 'Relative\'s address is required' }
            ];

            fields.forEach(field => {
                const prefix = formId === 'addChildFormElement' ? 'add_' : 'edit_';
                const input = form.querySelector(`#${prefix}${field.id}`);
                if (!input.value.trim()) {
                    const errorEl = form.querySelector(`#${prefix}${field.errorId}`);
                    errorEl.textContent = field.message;
                    errorEl.style.display = 'block';
                    isValid = false;
                }
            });

            // Validate date of birth
            const dobInput = form.querySelector(`#${formId === 'addChildFormElement' ? 'add_dateofbirth' : 'edit_dateofbirth'}`);
            if (dobInput.value) {
                const dob = new Date(dobInput.value);
                if (dob > today) {
                    const errorEl = form.querySelector(`#${formId === 'addChildFormElement' ? 'add_dateofbirth_error' : 'edit_dateofbirth_error'}`);
                    errorEl.textContent = 'Date of birth cannot be in the future';
                    errorEl.style.display = 'block';
                    isValid = false;
                }
            }

            return isValid;
        }

        // Add form submission validation
        document.getElementById('addChildFormElement').addEventListener('submit', function(e) {
            console.log('Add Child Form submitted');
            const formData = new FormData(this);
            console.log('Form data:', Object.fromEntries(formData));
            if (!validateForm('addChildFormElement')) {
                console.log('Validation failed');
                e.preventDefault();
            } else {
                console.log('Validation passed, submitting form');
            }
        });

        // Edit form submission validation
        document.getElementById('editChildFormElement').addEventListener('submit', function(e) {
            if (!validateForm('editChildFormElement')) {
                e.preventDefault();
            }
        });

        function showAddChildForm() {
            document.getElementById('addChildForm').style.display = 'block';
            document.getElementById('overlay').style.display = 'block';
            document.getElementById('addChildFormElement').reset();
            document.querySelectorAll('#addChildForm .error-message').forEach(el => {
                el.style.display = 'none';
                el.textContent = '';
            });
        }

        function hideAddChildForm() {
            document.getElementById('addChildForm').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }

        function showEditChildForm(data) {
            document.getElementById('edit_child_id').value = data.child_id;
            document.getElementById('edit_fname').value = data.fname;
            document.getElementById('edit_lname').value = data.lname;
            document.getElementById('edit_dateofbirth').value = data.dateofbirth;
            document.getElementById('edit_gender').value = data.gender;
            document.getElementById('edit_medical_info').value = data.medical_info;
            document.getElementById('edit_education_info').value = data.education_info;
            document.getElementById('edit_staff_email').value = data.staff_email;
            document.getElementById('edit_relatives_phonenumber').value = data.relatives_phonenumber;
            document.getElementById('edit_child_backgroundinfo').value = data.child_backgroundinfo;
            document.getElementById('edit_relatives_address').value = data.relatives_address;

            document.getElementById('editChildForm').style.display = 'block';
            document.getElementById('overlay').style.display = 'block';
            document.querySelectorAll('#editChildForm .error-message').forEach(el => {
                el.style.display = 'none';
                el.textContent = '';
            });
        }

        function hideEditChildForm() {
            document.getElementById('editChildForm').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }

        function archiveChild(child_id) {
            if (confirm('Are you sure you want to archive this child record?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'child_id';
                input.value = child_id;
                form.appendChild(input);
                const submit = document.createElement('input');
                submit.type = 'hidden';
                submit.name = 'submit_archive';
                form.appendChild(submit);
                document.body.appendChild(form);
                form.submit();
            }
        }

        function restoreChild(child_id) {
            if (confirm('Are you sure you want to restore this child record?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'child_id';
                input.value = child_id;
                form.appendChild(input);
                const submit = document.createElement('input');
                submit.type = 'hidden';
                submit.name = 'submit_restore';
                form.appendChild(submit);
                document.body.appendChild(form);
                form.submit();
            }
        }

        function graduateChild(child_id) {
            if (confirm('Are you sure you want to mark this child as a graduate?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'child_id';
                input.value = child_id;
                form.appendChild(input);
                const submit = document.createElement('input');
                submit.type = 'hidden';
                submit.name = 'submit_graduate';
                form.appendChild(submit);
                document.body.appendChild(form);
                form.submit();
            }
        }

        function ungraduateChild(child_id) {
            if (confirm('Are you sure you want to restore this child to active status?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'child_id';
                input.value = child_id;
                form.appendChild(input);
                const submit = document.createElement('input');
                submit.type = 'hidden';
                submit.name = 'submit_ungraduate';
                form.appendChild(submit);
                document.body.appendChild(form);
                form.submit();
            }
        }

        function clearSearch() {
            document.getElementById('searchInput').value = '';
            window.location.href = '?show_archived=<?php echo $show_archived; ?>&show_graduate=<?php echo $show_graduate; ?>';
        }

        function hideAllForms() {
            hideAddChildForm();
            hideEditChildForm();
        }

        function printTable() {
            window.print();
        }

        // Make entire row clickable for details
        document.querySelectorAll('.clickable-row').forEach(row => {
            row.addEventListener('click', function(e) {
                if (!e.target.closest('td:last-child')) {
                    const childId = this.getAttribute('data-child-id');
                    window.location.href = `child_details.php?child_id=${childId}`;
                }
            });
        });

        // Real-time search
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#childTable tbody tr');

            rows.forEach(row => {
                const fname = row.cells[0].textContent.toLowerCase();
                const lname = row.cells[1].textContent.toLowerCase();
                if (fname.includes(searchTerm) || lname.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Refocus search input after page load
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            if (searchInput.value) {
                searchInput.focus();
            }
        });
    </script>
</body>
</html>