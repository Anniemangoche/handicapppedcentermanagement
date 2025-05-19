<?php
ob_start(); // Start output buffering
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle CSV Download
if (isset($_GET['download_csv']) && $_GET['download_csv'] == '1') {
    $servername = "localhost";
    $dbusername = "root";
    $dbpassword = "";
    $database = "magdalene_management";

    $conn = new mysqli($servername, $dbusername, $dbpassword, $database);
    if ($conn->connect_error) {
        error_log("Connection failed: " . $conn->connect_error);
        die("Database connection failed.");
    }

    $show_archived = isset($_GET['show_archived']) ? (int)$_GET['show_archived'] : 0;
    $search_query = isset($_GET['search']) ? $_GET['search'] : '';
    $search_term = "%$search_query%";

    // Fetch all relevant records
    $query = "SELECT fname, lname, email, phone, role, archived FROM staff_records WHERE archived = ? AND (fname LIKE ? OR lname LIKE ?)";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        die("Query preparation failed.");
    }
    $stmt->bind_param("iss", $show_archived, $search_term, $search_term);
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        die("Query execution failed.");
    }
    $result = $stmt->get_result();

    // Set headers for CSV download 
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="staff_records.csv"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Clear any previous output
    ob_end_clean();

    // Open output stream
    $output = fopen('php://output', 'w');
    if ($output === false) {
        error_log("Failed to open php://output");
        die("Output stream failed.");
    }

    // Write CSV headers
    fputcsv($output, ['First Name', 'Last Name', 'Email', 'Phone', 'Role', 'Status']);

    // Write data rows
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['fname'],
            $row['lname'],
            $row['email'],
            $row['phone'],
            $row['role'],
            $row['archived'] ? 'Archived' : 'Active'
        ]);
    }

    // Close output stream
    fclose($output);
    $stmt->close();
    $conn->close();
    exit;
}

// Require PHPMailer files
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Database connection
$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$database = "magdalene_management";

$conn = new mysqli($servername, $dbusername, $dbpassword, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure all required columns exist
$required_columns = [
    'archived' => "ALTER TABLE staff_records ADD COLUMN archived TINYINT DEFAULT 0",
    'password' => "ALTER TABLE staff_records ADD COLUMN password VARCHAR(255)",
    'email_verified' => "ALTER TABLE staff_records ADD COLUMN email_verified TINYINT DEFAULT 0",
    'verification_token' => "ALTER TABLE staff_records ADD COLUMN verification_token VARCHAR(255) NULL",
    'verification_expiry' => "ALTER TABLE staff_records ADD COLUMN verification_expiry DATETIME NULL"
];

foreach ($required_columns as $column => $query) {
    $check = "SHOW COLUMNS FROM staff_records LIKE '$column'";
    $result = $conn->query($check);
    if ($result->num_rows == 0) {
        $conn->query($query);
    }
}

// Function to generate random password
function generatePassword($firstname) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $random_chars = '';
    for ($i = 0; $i < 4; $i++) {
        $random_chars .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $firstname . $random_chars;
}

// Function to send email with credentials
function sendCredentialsEmail($email, $fname, $lname, $role, $password, $verification_token) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'cen-01-42-21@unilia.ac.mw';
        $mail->Password = 'ksvh sety oqcv cgyv';
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;

        $mail->setFrom('cen-01-42-21@unilia.ac.mw', 'Magdalene Management');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Your Staff Account at Magdalene Management';

        $full_name = $fname . ' ' . $lname;
        $verification_link = "http://" . $_SERVER['HTTP_HOST'] . "/Magdalene-main/auth/verify.php?token=" . urlencode($verification_token);
        $mail->Body = "<h2>Welcome to Magdalene Management</h2>" .
                     "<p>Dear $full_name,</p>" .
                     "<p>Your account has been created with the following details:</p>" .
                     "<ul>" .
                     "<li><strong>Full Name:</strong> $full_name</li>" .
                     "<li><strong>Email:</strong> $email</li>" .
                     "<li><strong>Role:</strong> $role</li>" .
                     "<li><strong>Password:</strong> $password</li>" .
                     "</ul>" .
                     "<p>Please verify your email address by clicking the link below:</p>" .
                     "<p><a href='$verification_link'>Verify Email Address</a></p>" .
                     "<p>This link will expire in 24 hours.</p>" .
                     "<p>After verification, you can log in and change your password.</p>" .
                     "<p>If you didn't create an account, you can ignore this email.</p>";
        $mail->AltBody = "Dear $full_name,\n\n" .
                        "Welcome to Magdalene Management System!\n\n" .
                        "Your account has been created with the following details:\n" .
                        "Full Name: $full_name\n" .
                        "Email: $email\n" .
                        "Role: $role\n" .
                        "Password: $password\n\n" .
                        "Please verify your email by copying this link:\n" .
                        "$verification_link\n\n" .
                        "This link expires in 24 hours. After verification, you can log in and change your password.\n\n" .
                        "If you didn't create an account, ignore this email.\n\n" .
                        "Best regards,\nMagdalene Management Team";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("PHPMailer error for $email: {$mail->ErrorInfo}");
        return false;
    }
}

// Handle Add Staff Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_add_staff'])) {
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role = trim($_POST['role']);
    
    if (empty($fname) || empty($lname) || empty($email) || empty($phone) || empty($role)) {
        $_SESSION['error_message'] = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "Invalid email format.";
    } elseif (!in_array($role, ['cooker', 'volunteer', 'Donor', 'caregiver', 'teacher'])) {
        $_SESSION['error_message'] = "Invalid role selected.";
    } else {
        $check_query = "SELECT staff_id FROM staff_records WHERE email = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        if ($check_result->num_rows > 0) {
            $_SESSION['error_message'] = "Email address is already in use.";
        } else {
            $plain_password = generatePassword($fname);
            $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);
            $verification_token = bin2hex(random_bytes(32));
            $verification_expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
            
            $query = "INSERT INTO staff_records (fname, lname, email, phone, role, archived, password, verification_token, verification_expiry) 
                      VALUES (?, ?, ?, ?, ?, 0, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssssssss", $fname, $lname, $email, $phone, $role, $hashed_password, $verification_token, $verification_expiry);
            
            try {
                if ($stmt->execute()) {
                    if (sendCredentialsEmail($email, $fname, $lname, $role, $plain_password, $verification_token)) {
                        $_SESSION['success_message'] = "Staff record added successfully! Login credentials and verification link sent to the provided email.";
                    } else {
                        $_SESSION['warning_message'] = "Staff record added successfully! But failed to send email with credentials.";
                    }
                    header("Location: addstaff_retrive.php?show_archived=" . (isset($_GET['show_archived']) ? $_GET['show_archived'] : 0));
                    exit();
                } else {
                    $_SESSION['error_message'] = "Error adding staff record: " . $stmt->error;
                }
            } catch (mysqli_sql_exception $e) {
                $_SESSION['error_message'] = "Database error: " . $e->getMessage();
                error_log("mysqli_sql_exception in add staff: " . $e->getMessage());
            }
        }
        $check_stmt->close();
    }
    header("Location: addstaff_retrive.php");
    exit();
}

// Handle Edit Staff Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_edit_staff'])) {
    $staff_id = $_POST['staff_id'];
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role = trim($_POST['role']);

    if (empty($fname) || empty($lname) || empty($email) || empty($phone) || empty($role)) {
        $_SESSION['error_message'] = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "Invalid email format.";
    } elseif (!in_array($role, ['cooker', 'volunteer', 'Donor', 'caregiver', 'teacher'])) {
        $_SESSION['error_message'] = "Invalid role selected.";
    } else {
        $check_query = "SELECT staff_id FROM staff_records WHERE email = ? AND staff_id != ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("si", $email, $staff_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        if ($check_result->num_rows > 0) {
            $_SESSION['error_message'] = "Email address is already in use by another staff.";
        } else {
            $query = "UPDATE staff_records SET fname = ?, lname = ?, email = ?, phone = ?, role = ? WHERE staff_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssssi", $fname, $lname, $email, $phone, $role, $staff_id);

            try {
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Staff record updated successfully!";
                    header("Location: addstaff_retrive.php?show_archived=" . (isset($_GET['show_archived']) ? $_GET['show_archived'] : 0));
                    exit();
                } else {
                    $_SESSION['error_message'] = "Error updating staff record: " . $stmt->error;
                }
            } catch (mysqli_sql_exception $e) {
                $_SESSION['error_message'] = "Database error: " . $e->getMessage();
                error_log("mysqli_sql_exception in edit staff: " . $e->getMessage());
            }
        }
        $check_stmt->close();
    }
    header("Location: addstaff_retrive.php");
    exit();
}

// Handle Archive/Restore Staff
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['staff_action'])) {
    $staff_id = $_POST['staff_id'];
    $archived = $_POST['archived'] == '1' ? 0 : 1;
    
    $query = "UPDATE staff_records SET archived = ? WHERE staff_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $archived, $staff_id);
    
    try {
        if ($stmt->execute()) {
            $action = $archived ? "archived" : "restored";
            $_SESSION['success_message'] = "Staff record " . $action . " successfully!";
            header("Location: addstaff_retrive.php?show_archived=" . (isset($_GET['show_archived']) ? $_GET['show_archived'] : 0));
            exit();
        } else {
            $_SESSION['error_message'] = "Error updating staff record: " . $stmt->error;
        }
    } catch (mysqli_sql_exception $e) {
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
        error_log("mysqli_sql_exception in archive/restore staff: " . $e->getMessage());
    }
    header("Location: addstaff_retrive.php");
    exit();
}

// Search functionality
$search_query = '';
if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
}

// Pagination Variables
$records_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $records_per_page;

$show_archived = isset($_GET['show_archived']) ? $_GET['show_archived'] : 0;

$total_records_query = "SELECT COUNT(*) AS total FROM staff_records WHERE archived = ? AND (fname LIKE ? OR lname LIKE ?)";
$stmt = $conn->prepare($total_records_query);
$search_term = "%$search_query%";
$stmt->bind_param("iss", $show_archived, $search_term, $search_term);
$stmt->execute();
$total_records_result = $stmt->get_result();
$total_records = $total_records_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

$query = "SELECT * FROM staff_records WHERE archived = ? AND (fname LIKE ? OR lname LIKE ?) ORDER BY staff_id DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("issii", $show_archived, $search_term, $search_term, $records_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Management</title>
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

        .button-row {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .button-row button, .button-row a button {
            padding: 10px 15px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .button-row button:hover, .button-row a button:hover {
            background-color: var(--secondary-color);
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

        .pagination {
            margin-top: 20px;
            text-align: center;
        }

        .pagination a {
            display: inline-block;
            padding: 10px 15px;
            margin: 0 5px;
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }

        .pagination a:hover {
            background-color: var(--secondary-color);
        }

        .pagination .active {
            background-color: var(--secondary-color);
            pointer-events: none;
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
        }

        .form-popup h2 {
            margin-bottom: 15px;
            color: var(--primary-color);
        }

        .form-popup input, .form-popup select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .form-popup button {
            padding: 10px 15px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }

        .form-popup button:hover {
            background-color: var(--secondary-color);
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

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .action-buttons button {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .edit-btn {
            background-color: #007bff;
            color: white;
        }

        .archive-btn {
            background-color: #6c757d;;
            color: white;
        }

        .restore-btn {
            background-color: #2ecc71;
            color: white;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }

        .alert-success {
            color: #3c763d;
            background-color: #dff0d8;
            border-color: #d6e9c6;
        }

        .alert-danger {
            color: #a94442;
            background-color: #f2dede;
            border-color: #ebccd1;
        }

        .alert-warning {
            color: #8a6d3b;
            background-color: #fcf8e3;
            border-color: #faebcc;
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
                <li><a href="addstaff_retrive.php" class="active"><i class="fas fa-users"></i> Staff Management</a></li>
                <li><a href="admin_don.php"><i class="fas fa-donate"></i> Donations</a></li>
                <li><a href="eventsadd.php"><i class="fas fa-calendar-alt"></i> Events</a></li>
                <li><a href="tasks.php"><i class="fas fa-tasks"></i> Tasks</a></li>
                <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                <li><a href="inventory.php"><i class="fas fa-boxes"></i> Inventory</a></li>
                <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <h1>Staff Management</h1>
                <div class="user-actions">
                    <a href="logout.php" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
                </div>
            </header>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo $_SESSION['success_message'];
                    unset($_SESSION['success_message']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger">
                    <?php 
                    echo $_SESSION['error_message'];
                    unset($_SESSION['error_message']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['warning_message'])): ?>
                <div class="alert alert-warning">
                    <?php 
                    echo $_SESSION['warning_message'];
                    unset($_SESSION['warning_message']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="button-row">
                <button onclick="showAddStaffForm()">Add Staff</button>
                <a href="?show_archived=0&page=1"><button <?php echo $show_archived == 0 ? 'style="background-color: var(--secondary-color);"' : ''; ?>>Active Records</button></a>
                <a href="?show_archived=1&page=1"><button <?php echo $show_archived == 1 ? 'style="background-color: var(--secondary-color);"' : ''; ?>>Archived Records</button></a>
                <a href="?show_archived=<?php echo $show_archived; ?>&download_csv=1&search=<?php echo urlencode($search_query); ?>"><button>Download All</button></a>
                <form method="GET" style="margin-left: auto;">
                    <input type="hidden" name="show_archived" value="<?php echo $show_archived; ?>">
                    <input type="text" name="search" placeholder="Search by name" value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit">Search</button>
                </form>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['fname']); ?></td>
                            <td><?php echo htmlspecialchars($row['lname']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td><?php echo htmlspecialchars($row['role']); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="edit-btn" onclick="showEditStaffForm(<?php echo htmlspecialchars(json_encode($row)); ?>)">Edit</button>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="staff_id" value="<?php echo $row['staff_id']; ?>">
                                        <input type="hidden" name="archived" value="<?php echo $row['archived']; ?>">
                                        <input type="hidden" name="staff_action" value="toggle_archive">
                                        <?php if ($show_archived == 0): ?>
                                            <button type="submit" class="archive-btn">Archive</button>
                                        <?php else: ?>
                                            <button type="submit" class="restore-btn">Restore</button>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <div class="pagination">
                <?php if ($current_page > 1): ?>
                    <a href="?show_archived=<?php echo $show_archived; ?>&page=<?php echo $current_page - 1; ?>&search=<?php echo urlencode($search_query); ?>">Previous</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?show_archived=<?php echo $show_archived; ?>&page=<?php echo $i; ?>&search=<?php echo urlencode($search_query); ?>" class="<?php echo $i == $current_page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>

                <?php if ($current_page < $total_pages): ?>
                    <a href="?show_archived=<?php echo $show_archived; ?>&page=<?php echo $current_page + 1; ?>&search=<?php echo urlencode($search_query); ?>">Next</a>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <div class="overlay" id="overlay"></div>

    <div class="form-popup" id="addStaffForm">
        <h2>Add Staff</h2>
        <form action="" method="POST">
            <input type="text" name="fname" placeholder="First Name" required>
            <input type="text" name="lname" placeholder="Last Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="phone" placeholder="Phone" required>
            <select name="role" required>
                <option value="" disabled selected>Select Role</option>
                <option value="cooker">Cooker</option>
                <option value="volunteer">Volunteer</option>
                <option value="Donor">Donor</option>
                <option value="caregiver">Caregiver</option>
                <option value="teacher">Teacher</option>
            </select>
            <button type="submit" name="submit_add_staff">Add Staff</button>
            <button type="button" onclick="hideAddStaffForm()">Cancel</button>
        </form>
    </div>

    <div class="form-popup" id="editStaffForm">
        <h2>Edit Staff</h2>
        <form action="" method="POST">
            <input type="hidden" name="staff_id" id="edit_staff_id">
            <input type="text" name="fname" id="edit_fname" placeholder="First Name" required>
            <input type="text" name="lname" id="edit_lname" placeholder="Last Name" required>
            <input type="email" name="email" id="edit_email" placeholder="Email" required>
            <input type="text" name="phone" id="edit_phone" placeholder="Phone" required>
            <select name="role" id="edit_role" required>
                <option value="" disabled>Select Role</option>
                <option value="cooker">Cooker</option>
                <option value="volunteer">Volunteer</option>
                <option value="Donor">Donor</option>
                <option value="caregiver">Caregiver</option>
                <option value="teacher">Teacher</option>
            </select>
            <button type="submit" name="submit_edit_staff">Update Staff</button>
            <button type="button" onclick="hideEditStaffForm()">Cancel</button>
        </form>
    </div>

    <script>
        function showAddStaffForm() {
            document.getElementById("overlay").style.display = "block";
            document.getElementById("addStaffForm").style.display = "block";
        }

        function hideAddStaffForm() {
            document.getElementById("overlay").style.display = "none";
            document.getElementById("addStaffForm").style.display = "none";
        }

        function showEditStaffForm(staff) {
            document.getElementById("edit_staff_id").value = staff.staff_id;
            document.getElementById("edit_fname").value = staff.fname;
            document.getElementById("edit_lname").value = staff.lname;
            document.getElementById("edit_email").value = staff.email;
            document.getElementById("edit_phone").value = staff.phone;
            document.getElementById("edit_role").value = staff.role;
            document.getElementById("overlay").style.display = "block";
            document.getElementById("editStaffForm").style.display = "block";
        }

        function hideEditStaffForm() {
            document.getElementById("overlay").style.display = "none";
            document.getElementById("editStaffForm").style.display = "none";
        }
    </script>
</body>
</html>
<?php ob_end_flush(); ?>