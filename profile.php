<?php
// Start session if not already started
session_start();

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "magdalene_management";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables with default empty values
$user_email = "";
$user_fullname = "";
$user_location = "";
$user_phone = "";

// Function to safely check if a column exists in a table
function columnExists($conn, $table, $column) {
    $check_column = "SHOW COLUMNS FROM $table LIKE '$column'";
    $result = $conn->query($check_column);
    return ($result && $result->num_rows > 0);
}

// Function to find a column name by partial match
function findColumnByPartialName($conn, $table, $partialNames) {
    $check_columns = "SHOW COLUMNS FROM $table";
    $columns_result = $conn->query($check_columns);
    
    if ($columns_result) {
        while ($column = $columns_result->fetch_assoc()) {
            $field = strtolower($column['Field']);
            foreach ($partialNames as $name) {
                if (strpos($field, $name) !== false) {
                    return $column['Field'];
                }
            }
        }
    }
    return false;
}

// Check if user is logged in
if (isset($_SESSION['staff_id'])) {
    $staff_id = $_SESSION['staff_id'];
    
    // Build SQL based on available columns
    $sql = "SELECT ";
    $select_columns = [];
    
    if (columnExists($conn, 'staff_records', 'email')) {
        $select_columns[] = 'email';
    }
    if (columnExists($conn, 'staff_records', 'fname')) {
        $select_columns[] = 'fname';
    }
    if (columnExists($conn, 'staff_records', 'lname')) {
        $select_columns[] = 'lname';
    }
    if (columnExists($conn, 'staff_records', 'address')) {
        $select_columns[] = 'address';
    }
    if (columnExists($conn, 'staff_records', 'phone')) {
        $select_columns[] = 'phone';
    }
    
    $sql .= implode(', ', $select_columns) . " FROM staff_records WHERE staff_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $staff_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        if (isset($row['email'])) {
            $user_email = $row['email'];
        }
        
        $user_fullname = "";
        if (isset($row['fname'])) {
            $user_fullname .= $row['fname'];
        }
        if (isset($row['lname'])) {
            if (!empty($user_fullname)) $user_fullname .= " ";
            $user_fullname .= $row['lname'];
        }
        
        if (isset($row['address'])) {
            $user_location = $row['address'];
        }
        if (isset($row['phone'])) {
            $user_phone = $row['phone'];
        }
    }
    $stmt->close();
} else if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];
    
    $sql = "SELECT ";
    $select_columns = [];
    
    if (columnExists($conn, 'staff_records', 'email')) {
        $select_columns[] = 'email';
    }
    if (columnExists($conn, 'staff_records', 'fname')) {
        $select_columns[] = 'fname';
    }
    if (columnExists($conn, 'staff_records', 'lname')) {
        $select_columns[] = 'lname';
    }
    if (columnExists($conn, 'staff_records', 'address')) {
        $select_columns[] = 'address';
    }
    if (columnExists($conn, 'staff_records', 'phone')) {
        $select_columns[] = 'phone';
    }
    
    if (columnExists($conn, 'staff_records', 'email')) {
        $sql .= implode(', ', $select_columns) . " FROM staff_records WHERE email = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            if (isset($row['email'])) {
                $user_email = $row['email'];
            }
            
            $user_fullname = "";
            if (isset($row['fname'])) {
                $user_fullname .= $row['fname'];
            }
            if (isset($row['lname'])) {
                if (!empty($user_fullname)) $user_fullname .= " ";
                $user_fullname .= $row['lname'];
            }
            
            if (isset($row['address'])) {
                $user_location = $row['address'];
            }
            if (isset($row['phone'])) {
                $user_phone = $row['phone'];
            }
        }
        $stmt->close();
    }
}

// Define a function to handle profile updates
function updateProfile($conn) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';
        $location = isset($_POST['address']) ? trim($_POST['address']) : '';
        $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
        
        $identifier = '';
        $identifier_value = '';
        
        if (isset($_SESSION['staff_id'])) {
            $identifier = 'staff_id';
            $identifier_value = $_SESSION['staff_id'];
        } elseif (isset($_SESSION['email'])) {
            $identifier = 'email';
            $identifier_value = $_SESSION['email'];
        } else {
            header('Location: login.php');
            exit;
        }
        
        $sql_parts = [];
        $param_types = '';
        $param_values = [];
        
        if (!empty($email) && columnExists($conn, 'staff_records', 'email')) {
            $sql_parts[] = 'email = ?';
            $param_types .= 's';
            $param_values[] = $email;
            if (isset($_SESSION['email'])) {
                $_SESSION['email'] = $email;
            }
        }
        
        if (!empty($username)) {
            $name_parts = explode(' ', $username, 2);
            $fname = $name_parts[0];
            $lname = isset($name_parts[1]) ? $name_parts[1] : '';
            
            if (columnExists($conn, 'staff_records', 'fname')) {
                $sql_parts[] = 'fname = ?';
                $param_types .= 's';
                $param_values[] = $fname;
            }
            
            if (columnExists($conn, 'staff_records', 'lname')) {
                $sql_parts[] = 'lname = ?';
                $param_types .= 's';
                $param_values[] = $lname;
            }
        }
        
        if (!empty($password) && columnExists($conn, 'staff_records', 'password')) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql_parts[] = 'password = ?';
            $param_types .= 's';
            $param_values[] = $hashed_password;
        }
        
        if (!empty($location) && columnExists($conn, 'staff_records', 'address')) {
            $sql_parts[] = 'address = ?';
            $param_types .= 's';
            $param_values[] = $location;
        }
        
        if (!empty($phone) && columnExists($conn, 'staff_records', 'phone')) {
            $sql_parts[] = 'phone = ?';
            $param_types .= 's';
            $param_values[] = $phone;
        }
        
        if (!empty($sql_parts)) {
            $sql = "UPDATE staff_records SET " . implode(', ', $sql_parts) . " WHERE $identifier = ?";
            $param_types .= ($identifier === 'staff_id') ? 'i' : 's';
            $param_values[] = $identifier_value;
            
            $stmt = $conn->prepare($sql);
            $params = array_merge([$param_types], $param_values);
            $ref_params = [];
            
            foreach ($params as $key => $value) {
                $ref_params[$key] = &$params[$key];
            }
            
            call_user_func_array([$stmt, 'bind_param'], $ref_params);
            
            if ($stmt->execute()) {
                $_SESSION['notification'] = "Profile updated successfully!";
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit;
            } else {
                $_SESSION['notification'] = "Error updating profile: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Call the update function
updateProfile($conn);
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Magdalene Management</title>
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
            display: flex;
            flex-direction: column;
            align-items: center;
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
            width: 100%;
            max-width: 1200px;
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

        .profile-card {
            background-color: var(--card-bg);
            border-radius: 16px;
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 400px;
            padding: 20px;
            text-align: center;
            margin-top: 20px;
        }

        .profile-info {
            margin-bottom: 20px;
        }

        .user-name {
            font-size: 1.6rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 5px;
        }

        .user-email {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .profile-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            text-align: left;
        }

        .form-group label {
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--text-color);
            margin-bottom: 5px;
        }

        .form-control {
            padding: 8px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.9rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(146, 108, 84, 0.2);
        }

        .btn {
            padding: 10px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .btn:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
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

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
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

            .profile-card {
                max-width: 90%;
            }

            .user-name {
                font-size: 1.4rem;
            }
        }

        @media print {
            .sidebar, .topbar, .notification {
                display: none !important;
            }
            .main-content {
                margin-left: 0;
                padding: 0;
            }
            .profile-card {
                box-shadow: none;
                border: 1px solid #000;
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
                <li><a href="tasks.php"><i class="fas fa-tasks"></i> Tasks</a></li>
                <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                <li><a href="inventory.php"><i class="fas fa-boxes"></i> Inventory</a></li>
                <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                <li><a href="donor_profile.php" class="active"><i class="fas fa-user"></i> Profile</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <button class="toggle-sidebar"><i class="fas fa-bars"></i></button>
                <h1>My Profile</h1>
                <div class="user-actions">
                    <a href="donor_profile.php" title="Profile"><i class="fas fa-user"></i></a>
                    <a href="index.php" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
                </div>
            </header>

            <?php if (isset($_SESSION['notification'])): ?>
                <div class="notification <?php echo strpos($_SESSION['notification'], 'Error') === false ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($_SESSION['notification']); ?>
                </div>
                <?php unset($_SESSION['notification']); ?>
            <?php endif; ?>

            <div class="profile-card">
                <div class="profile-info">
                    <h3 class="user-name"><?php echo htmlspecialchars($user_fullname); ?></h3>
                    <p class="user-email"><?php echo htmlspecialchars($user_email); ?></p>
                </div>
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="profile-form">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user_email); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="username">Full Name</label>
                        <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($user_fullname); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password (leave blank to keep current)</label>
                        <input type="password" id="password" name="password" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text" id="address" name="address" class="form-control" value="<?php echo htmlspecialchars($user_location); ?>">
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($user_phone); ?>">
                    </div>
                    <button type="submit" class="btn">Update Profile</button>
                </form>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleSidebar = document.querySelector('.toggle-sidebar');
            const sidebar = document.querySelector('.sidebar');
            
            toggleSidebar.addEventListener('click', function() {
                sidebar.classList.toggle('active');
            });
        });
    </script>
</body>
</html>